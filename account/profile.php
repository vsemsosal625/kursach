<?php
require_once __DIR__ . '/../config/init.php';
requireLogin();

$pdo = getDB();

$userId = $_SESSION['user_id'];
$success = '';
$error = '';
$activeTab = 'data';

$stmt = $pdo->prepare("SELECT * FROM `user` WHERE id_user = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();
if (!$user) { session_destroy(); header('Location: ' . BASE_URL . '/auth/auth.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_profile') {
        $name = trim($_POST['name'] ?? '');
        $surname = trim($_POST['surname'] ?? '');
        $patronymic = trim($_POST['patronymic'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        if (empty($name) || empty($surname)) {
            $error = 'Заполните имя и фамилию';
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE `user` SET name = ?, surname = ?, patronymic = ?, phone = ? WHERE id_user = ?");
                $stmt->execute([$name, $surname, $patronymic, $phone, $userId]);
                $success = 'Профиль успешно обновлён';
                $user['name'] = $name; $user['surname'] = $surname; $user['patronymic'] = $patronymic; $user['phone'] = $phone;
            } catch (Exception $e) {
                $error = 'Ошибка обновления: ' . $e->getMessage();
            }
        }
    } elseif ($_POST['action'] === 'change_login') {
        $activeTab = 'security';
        $newLogin = trim($_POST['login'] ?? '');
        if (empty($newLogin)) {
            $error = 'Введите новый логин';
        } elseif (mb_strlen($newLogin) < 3) {
            $error = 'Логин должен быть не менее 3 символов';
        } else {
            $chk = $pdo->prepare("SELECT id_user FROM `user` WHERE login = ? AND id_user <> ?");
            $chk->execute([$newLogin, $userId]);
            if ($chk->fetch()) {
                $error = 'Такой логин уже занят';
            } else {
                try {
                    $stmt = $pdo->prepare("UPDATE `user` SET login = ? WHERE id_user = ?");
                    $stmt->execute([$newLogin, $userId]);
                    $_SESSION['user_login'] = $newLogin;
                    $user['login'] = $newLogin;
                    $success = 'Логин успешно изменён';
                } catch (Exception $e) {
                    $error = 'Ошибка смены логина: ' . $e->getMessage();
                }
            }
        }
    } elseif ($_POST['action'] === 'change_email') {
        $activeTab = 'security';
        $newEmail = trim($_POST['email'] ?? '');
        if (empty($newEmail) || !filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            $error = 'Введите корректный email';
        } else {
            $chk = $pdo->prepare("SELECT id_user FROM `user` WHERE email = ? AND id_user <> ?");
            $chk->execute([$newEmail, $userId]);
            if ($chk->fetch()) {
                $error = 'Этот email уже используется';
            } else {
                try {
                    $stmt = $pdo->prepare("UPDATE `user` SET email = ? WHERE id_user = ?");
                    $stmt->execute([$newEmail, $userId]);
                    $user['email'] = $newEmail;
                    $success = 'Email успешно изменён';
                } catch (Exception $e) {
                    $error = 'Ошибка смены email: ' . $e->getMessage();
                }
            }
        }
    } elseif ($_POST['action'] === 'change_password') {
        $activeTab = 'security';
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $error = 'Заполните все поля';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'Новые пароли не совпадают';
        } elseif (!password_verify($currentPassword, $user['password'])) {
            $error = 'Текущий пароль неверный';
        } elseif (strlen($newPassword) < 6) {
            $error = 'Пароль должен быть не менее 6 символов';
        } else {
            try {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE `user` SET password = ? WHERE id_user = ?");
                $stmt->execute([$hashedPassword, $userId]);
                $success = 'Пароль успешно изменён';
            } catch (Exception $e) {
                $error = 'Ошибка смены пароля: ' . $e->getMessage();
            }
        }
    }
}

$pageTitle = 'Личный кабинет';
$currentPage = '';

require_once __DIR__ . '/../includes/header.php';
?>

<style>
.profile-page-title { border-left-color: #3b82f6 !important; }
.profile-page-title i { color: #3b82f6; }
.profile-grid { display: grid; grid-template-columns: 300px 1fr; gap: 30px; align-items: start; padding-bottom: 30px; }
.profile-sidebar { background: linear-gradient(135deg, #1b2838 0%, #2a475e 100%); border: 1px solid #36414d; border-radius: 12px; padding: 30px; text-align: center; }
.avatar-container { width: 130px; height: 130px; background: linear-gradient(135deg, #3b82f6, #2563eb); border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; font-size: 56px; color: #fff; border: 4px solid rgba(255,255,255,0.1); }
.profile-name { color: #fff; font-size: 22px; font-weight: 700; margin-bottom: 8px; }
.profile-email { color: #8f98a0; font-size: 14px; margin-bottom: 20px; word-break: break-all; }
.profile-nav { display: flex; flex-direction: column; gap: 10px; margin-top: 20px; }
.profile-nav-btn { background: rgba(59,130,246,0.1); border: 2px solid transparent; color: #e0e0e0; padding: 12px 20px; border-radius: 8px; cursor: pointer; font-weight: 500; transition: all 0.3s; text-align: left; display: flex; align-items: center; gap: 12px; }
.profile-nav-btn:hover, .profile-nav-btn.active { background: rgba(59,130,246,0.2); border-color: #3b82f6; color: #fff; }
.profile-content { background: linear-gradient(135deg, #1b2838 0%, #2a475e 100%); border: 1px solid #36414d; border-radius: 12px; padding: 30px; }
.profile-content .section-title { color: #fff; font-size: 20px; font-weight: 600; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; }
.profile-content .section-title i { color: #3b82f6; }
.security-block { padding-bottom: 25px; margin-bottom: 25px; border-bottom: 1px solid #36414d; }
.security-block:last-child { padding-bottom: 0; margin-bottom: 0; border-bottom: none; }
.profile-content .form-group { margin-bottom: 20px; }
.profile-content .form-group label { display: block; color: #acb2b8; font-size: 14px; margin-bottom: 8px; font-weight: 500; }
.profile-content .form-control { background: rgba(27,40,56,0.8); border: 2px solid #36414d; color: #e0e0e0; padding: 12px 16px; border-radius: 8px; font-size: 15px; transition: all 0.3s; width: 100%; }
.profile-content .form-control:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.1); }
.profile-content .form-control:disabled { background: rgba(27,40,56,0.4); color: #8f98a0; cursor: not-allowed; }
.btn-save { background: linear-gradient(135deg, #3b82f6, #2563eb); color: #fff; border: none; padding: 12px 28px; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; transition: all 0.3s; }
.btn-save:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(59,130,246,0.4); }
.profile-alert { padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
.profile-alert.success { background: rgba(16,185,129,0.15); border: 1px solid #10b981; color: #6ee7b7; }
.profile-alert.error { background: rgba(239,68,68,0.15); border: 1px solid #ef4444; color: #fca5a5; }
.tab-content { display: none; }
.tab-content.active { display: block; }
.profile-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
@media (max-width: 900px) { .profile-grid { grid-template-columns: 1fr; } .profile-row { grid-template-columns: 1fr; } }
</style>

<div class="page-header">
    <h1 class="page-title profile-page-title"><i class="fas fa-user-circle"></i> Личный кабинет</h1>
</div>

<?php if ($success): ?><div class="profile-alert success"><i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?></div><?php endif; ?>
<?php if ($error): ?><div class="profile-alert error"><i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?></div><?php endif; ?>

<div class="profile-grid">
    <div class="profile-sidebar">
        <div class="avatar-container"><i class="fas fa-user"></i></div>
        <div class="profile-name"><?= htmlspecialchars(($user['name'] ?? '') . ' ' . ($user['surname'] ?? '')) ?></div>
        <div class="profile-email"><i class="fas fa-at me-1"></i><?= htmlspecialchars($user['login'] ?? '') ?></div>
        <div class="profile-nav">
            <button type="button" class="profile-nav-btn <?= $activeTab === 'data' ? 'active' : '' ?>" onclick="showTab('data', this)"><i class="fas fa-user"></i> Мои данные</button>
            <button type="button" class="profile-nav-btn <?= $activeTab === 'security' ? 'active' : '' ?>" onclick="showTab('security', this)"><i class="fas fa-shield-alt"></i> Безопасность</button>
        </div>
    </div>

    <div class="profile-content">
        <div id="tab-data" class="tab-content <?= $activeTab === 'data' ? 'active' : '' ?>">
            <h2 class="section-title"><i class="fas fa-user-edit"></i> Редактирование профиля</h2>
            <form method="POST">
                <input type="hidden" name="action" value="update_profile">
                <div class="profile-row">
                    <div class="form-group"><label>Имя <span style="color:#ef4444;">*</span></label><input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required></div>
                    <div class="form-group"><label>Фамилия <span style="color:#ef4444;">*</span></label><input type="text" name="surname" class="form-control" value="<?= htmlspecialchars($user['surname'] ?? '') ?>" required></div>
                </div>
                <div class="form-group"><label>Отчество</label><input type="text" name="patronymic" class="form-control" value="<?= htmlspecialchars($user['patronymic'] ?? '') ?>"></div>
                <div class="form-group"><label>Телефон</label><input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="+7 (999) 999-99-99"></div>
                <div class="form-group"><label>Дата регистрации</label><input type="text" class="form-control" value="<?= !empty($user['registration_date']) ? date('d.m.Y', strtotime($user['registration_date'])) : '—' ?>" disabled></div>
                <button type="submit" class="btn-save"><i class="fas fa-save me-2"></i>Сохранить изменения</button>
            </form>
        </div>

        <div id="tab-security" class="tab-content <?= $activeTab === 'security' ? 'active' : '' ?>">
            <div class="security-block">
                <h2 class="section-title"><i class="fas fa-at"></i> Смена логина</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="change_login">
                    <div class="form-group"><label>Логин (используется для входа)</label><input type="text" name="login" class="form-control" value="<?= htmlspecialchars($user['login'] ?? '') ?>" required minlength="3"></div>
                    <button type="submit" class="btn-save"><i class="fas fa-save me-2"></i>Сохранить логин</button>
                </form>
            </div>

            <div class="security-block">
                <h2 class="section-title"><i class="fas fa-envelope"></i> Смена почты</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="change_email">
                    <div class="form-group"><label>Электронная почта</label><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required></div>
                    <button type="submit" class="btn-save"><i class="fas fa-save me-2"></i>Сохранить почту</button>
                </form>
            </div>

            <div class="security-block">
                <h2 class="section-title"><i class="fas fa-lock"></i> Смена пароля</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="change_password">
                    <div class="form-group"><label>Текущий пароль</label><input type="password" name="current_password" class="form-control" required></div>
                    <div class="form-group"><label>Новый пароль</label><input type="password" name="new_password" class="form-control" required minlength="6"><small style="color:#8f98a0;font-size:12px;">Минимум 6 символов</small></div>
                    <div class="form-group"><label>Подтвердите новый пароль</label><input type="password" name="confirm_password" class="form-control" required minlength="6"></div>
                    <button type="submit" class="btn-save"><i class="fas fa-key me-2"></i>Сменить пароль</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function showTab(tabName, btn) {
    document.querySelectorAll('.tab-content').forEach(function(t){ t.classList.remove('active'); });
    document.querySelectorAll('.profile-nav-btn').forEach(function(b){ b.classList.remove('active'); });
    var tab = document.getElementById('tab-' + tabName);
    if (tab) tab.classList.add('active');
    if (btn) btn.classList.add('active');
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>