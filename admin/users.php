<?php
require_once __DIR__ . '/../config/init.php';
requireAdmin();

$pageTitle = 'Пользователи';
$currentPage = 'admin';

$pdo = getDB();
ensureColumn($pdo, 'user', 'feedback_blocked', 'feedback_blocked TINYINT(1) NOT NULL DEFAULT 0');

$selfId = (int)($_SESSION['user_id'] ?? 0);
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $uid = (int)($_POST['user_id'] ?? 0);
        if ($uid <= 0) {
            $error = 'Неверный пользователь';
        } elseif ($uid === $selfId) {
            $error = 'Нельзя удалить собственную учётную запись';
        } else {
            try {
                $stmt = $pdo->prepare("DELETE FROM `user` WHERE id_user = ?");
                $stmt->execute([$uid]);
                $success = 'Пользователь удалён';
            } catch (Exception $e) { $error = 'Ошибка удаления: ' . $e->getMessage(); }
        }
    } elseif ($action === 'toggle_block') {
        $uid = (int)($_POST['user_id'] ?? 0);
        if ($uid > 0) {
            $stmt = $pdo->prepare("UPDATE `user` SET feedback_blocked = 1 - feedback_blocked WHERE id_user = ?");
            $stmt->execute([$uid]);
            $success = 'Статус доступа к обратной связи изменён';
        }
    } elseif ($action === 'add' || $action === 'edit') {
        $uid = (int)($_POST['user_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $surname = trim($_POST['surname'] ?? '');
        $patronymic = trim($_POST['patronymic'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $login = trim($_POST['login'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'user';
        $password = $_POST['password'] ?? '';
        if (!in_array($role, ['user','admin'])) $role = 'user';

        if (!isValidName($name)) {
            $error = 'Имя: только русские или латинские буквы (от 2 до 50 символов)';
        } elseif (!isValidName($surname)) {
            $error = 'Фамилия: только русские или латинские буквы (от 2 до 50 символов)';
        } elseif ($patronymic !== '' && !isValidName($patronymic)) {
            $error = 'Отчество: только русские или латинские буквы (от 2 до 50 символов)';
        } elseif (!isValidLogin($login)) {
            $error = 'Логин должен состоять только из цифр (от 3 до 20 цифр)';
        } elseif (!isValidEmail($email)) {
            $error = 'Введите корректный email с существующим доменом';
        } elseif (!isValidPhone($phone)) {
            $error = 'Телефон может содержать только цифры и символы + - ( )';
        } elseif ($action === 'add' && (strlen($password) < 6 || strlen($password) > 72)) {
            $error = 'Пароль: от 6 до 72 символов';
        } elseif ($action === 'edit' && $password !== '' && (strlen($password) < 6 || strlen($password) > 72)) {
            $error = 'Пароль: от 6 до 72 символов';
        } else {
            $chk = $pdo->prepare("SELECT id_user FROM `user` WHERE login = ? AND id_user <> ?");
            $chk->execute([$login, $uid]);
            $chkE = $pdo->prepare("SELECT id_user FROM `user` WHERE email = ? AND id_user <> ?");
            $chkE->execute([$email, $uid]);
            if ($chk->fetch()) {
                $error = 'Пользователь с таким логином уже существует';
            } elseif ($chkE->fetch()) {
                $error = 'Пользователь с такой почтой уже существует';
            } else {
                try {
                    if ($action === 'add') {
                        $hash = password_hash($password, PASSWORD_BCRYPT);
                        $stmt = $pdo->prepare("INSERT INTO `user` (name, surname, patronymic, phone, login, email, password, registration_date, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$name, $surname, $patronymic !== '' ? $patronymic : null, $phone !== '' ? $phone : null, $login, $email, $hash, date('Y-m-d'), $role]);
                        $success = 'Пользователь добавлен';
                    } else {
                        if ($password !== '') {
                            $hash = password_hash($password, PASSWORD_BCRYPT);
                            $stmt = $pdo->prepare("UPDATE `user` SET name=?, surname=?, patronymic=?, phone=?, login=?, email=?, role=?, password=? WHERE id_user=?");
                            $stmt->execute([$name, $surname, $patronymic !== '' ? $patronymic : null, $phone !== '' ? $phone : null, $login, $email, $role, $hash, $uid]);
                        } else {
                            $stmt = $pdo->prepare("UPDATE `user` SET name=?, surname=?, patronymic=?, phone=?, login=?, email=?, role=? WHERE id_user=?");
                            $stmt->execute([$name, $surname, $patronymic !== '' ? $patronymic : null, $phone !== '' ? $phone : null, $login, $email, $role, $uid]);
                        }
                        if ($uid === $selfId) { $_SESSION['user_role'] = $role; $_SESSION['user_login'] = $login; }
                        $success = 'Данные пользователя обновлены';
                    }
                } catch (Exception $e) { $error = 'Ошибка сохранения: ' . $e->getMessage(); }
            }
        }
    }
}

$users = $pdo->query("SELECT * FROM `user` ORDER BY id_user ASC")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<style>
.us-wrap { max-width: 1100px; margin:0 auto; }
.us-back { display:inline-block; color:#fbbf24; text-decoration:none; margin-bottom:16px; }
.us-head { display:flex; align-items:center; gap:12px; margin-bottom:18px; }
.us-head i { color:#fbbf24; font-size:26px; }
.us-head h1 { margin:0; color:#fff; font-size:26px; }
.us-alert { padding:14px 18px; border-radius:8px; margin-bottom:18px; font-size:14px; }
.us-alert.success { background:rgba(16,185,129,0.15); border:1px solid #10b981; color:#6ee7b7; }
.us-alert.error { background:rgba(239,68,68,0.15); border:1px solid #ef4444; color:#fca5a5; }
.btn-add { background:#fbbf24; color:#1b2838; border:none; border-radius:8px; padding:11px 20px; font-weight:600; cursor:pointer; margin-bottom:18px; }
.user-form-box { background:linear-gradient(135deg,#1b2838,#2a475e); border:1px solid #36414d; border-radius:12px; padding:24px; margin-bottom:24px; }
.user-form-box h2 { color:#fff; font-size:20px; margin:0 0 18px; }
.us-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
.us-grid label { display:block; color:#acb2b8; font-size:13px; margin-bottom:6px; }
.us-grid input, .us-grid select { width:100%; background:#0f1923; border:1px solid #36414d; border-radius:8px; color:#e0e0e0; padding:10px 12px; box-sizing:border-box; }
.us-grid small { display:block; color:#8f98a0; font-size:12px; margin-top:4px; }
.us-form-actions { display:flex; gap:10px; margin-top:18px; }
.btn-save { background:#fbbf24; color:#1b2838; border:none; border-radius:8px; padding:10px 20px; font-weight:600; cursor:pointer; }
.btn-cancel { background:transparent; color:#c7d0d8; border:1px solid #36414d; border-radius:8px; padding:10px 20px; cursor:pointer; }
.users-table { width:100%; border-collapse:collapse; background:#1b2838; border:1px solid #36414d; border-radius:12px; overflow:hidden; }
.users-table th, .users-table td { padding:12px 14px; text-align:left; font-size:14px; border-bottom:1px solid #2a3744; color:#e0e0e0; vertical-align:middle; }
.users-table th { background:#0f1923; color:#c7d0d8; font-size:13px; text-transform:uppercase; letter-spacing:.03em; }
.users-table tr:last-child td { border-bottom:none; }
.role-badge { padding:3px 10px; border-radius:12px; font-size:12px; font-weight:600; }
.role-admin { background:rgba(251,191,36,0.2); color:#fcd34d; }
.role-user { background:rgba(59,130,246,0.2); color:#93c5fd; }
.blk { padding:3px 10px; border-radius:12px; font-size:12px; font-weight:600; }
.blk-on { background:rgba(239,68,68,0.2); color:#fca5a5; }
.blk-off { background:rgba(16,185,129,0.2); color:#6ee7b7; }
.u-row-actions { display:flex; gap:6px; flex-wrap:wrap; }
.u-row-actions button { border:none; border-radius:7px; padding:7px 10px; font-size:12px; font-weight:600; cursor:pointer; }
.btn-edit { background:rgba(59,130,246,0.2); color:#93c5fd; }
.btn-blk { background:transparent; color:#fbbf24; border:1px solid #fbbf24 !important; }
.btn-blk.on { color:#6ee7b7; border-color:#10b981 !important; }
.btn-rm { background:transparent; color:#f87171; border:1px solid #f87171 !important; }
.self-tag { color:#8f98a0; font-size:12px; font-style:italic; }
@media (max-width: 800px) { .us-grid { grid-template-columns:1fr; } .users-table { display:block; overflow-x:auto; } }
</style>

<div class="us-wrap">
    <a class="us-back" href="<?= BASE_URL ?>/admin/index.php"><i class="fas fa-arrow-left"></i> Назад в админ-панель</a>
    <div class="us-head"><i class="fas fa-users-cog"></i><h1>Управление пользователями</h1></div>

    <?php if ($success): ?><div class="us-alert success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="us-alert error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div><?php endif; ?>

    <button class="btn-add" onclick="showAddForm()"><i class="fas fa-user-plus"></i> Добавить пользователя</button>

    <div id="userFormBox" class="user-form-box" style="display:none;">
        <h2 id="form-title">Новый пользователь</h2>
        <form method="post">
            <input type="hidden" name="action" id="form-action" value="add">
            <input type="hidden" name="user_id" id="form-id" value="0">
            <div class="us-grid">
                <div><label>Имя *</label><input type="text" name="name" id="f-name" maxlength="50" pattern="[A-Za-zА-Яа-яЁё -]{2,50}" title="Только русские или латинские буквы" required></div>
                <div><label>Фамилия *</label><input type="text" name="surname" id="f-surname" maxlength="50" pattern="[A-Za-zА-Яа-яЁё -]{2,50}" title="Только русские или латинские буквы" required></div>
                <div><label>Отчество</label><input type="text" name="patronymic" id="f-patronymic" maxlength="50" pattern="[A-Za-zА-Яа-яЁё -]{2,50}" title="Только русские или латинские буквы"></div>
                <div><label>Телефон</label><input type="tel" name="phone" id="f-phone" maxlength="20" pattern="[0-9+() -]{5,20}" title="Только цифры и символы + - ( )"></div>
                <div><label>Логин (только цифры) *</label><input type="text" name="login" id="f-login" inputmode="numeric" minlength="3" maxlength="20" pattern="[0-9]{3,20}" title="Только цифры, от 3 до 20" required><small>Только цифры (от 3 до 20)</small></div>
                <div><label>Email *</label><input type="email" name="email" id="f-email" maxlength="150" required></div>
                <div><label>Роль</label><select name="role" id="f-role"><option value="user">Пользователь</option><option value="admin">Администратор</option></select></div>
                <div><label>Пароль <span id="pass-star">*</span></label><input type="password" name="password" id="f-password" minlength="6" maxlength="72"><small id="pass-hint">От 6 до 72 символов</small></div>
            </div>
            <div class="us-form-actions">
                <button type="submit" class="btn-save" id="submit-btn"><i class="fas fa-save"></i> Создать</button>
                <button type="button" class="btn-cancel" onclick="cancelForm()">Отмена</button>
            </div>
        </form>
    </div>

    <table class="users-table">
        <thead>
            <tr><th>ID</th><th>ФИО</th><th>Логин</th><th>Email</th><th>Роль</th><th>Обратная связь</th><th>Действия</th></tr>
        </thead>
        <tbody>
        <?php foreach ($users as $u):
            $uid = (int)$u['id_user'];
            $blocked = (int)($u['feedback_blocked'] ?? 0) === 1;
            $urole = $u['role'] ?? 'user';
        ?>
            <tr>
                <td><?= $uid ?></td>
                <td><?= htmlspecialchars(trim(($u['surname'] ?? '') . ' ' . ($u['name'] ?? '') . ' ' . ($u['patronymic'] ?? ''))) ?></td>
                <td><?= htmlspecialchars($u['login'] ?? '') ?></td>
                <td><?= htmlspecialchars($u['email'] ?? '') ?></td>
                <td><span class="role-badge role-<?= $urole === 'admin' ? 'admin' : 'user' ?>"><?= $urole === 'admin' ? 'Администратор' : 'Пользователь' ?></span></td>
                <td><?= $blocked ? '<span class="blk blk-on">Заблокирована</span>' : '<span class="blk blk-off">Активна</span>' ?></td>
                <td>
                    <div class="u-row-actions">
                        <button type="button" class="btn-edit" onclick="editUser(this)"
                            data-id="<?= $uid ?>"
                            data-name="<?= htmlspecialchars($u['name'] ?? '', ENT_QUOTES) ?>"
                            data-surname="<?= htmlspecialchars($u['surname'] ?? '', ENT_QUOTES) ?>"
                            data-patronymic="<?= htmlspecialchars($u['patronymic'] ?? '', ENT_QUOTES) ?>"
                            data-phone="<?= htmlspecialchars($u['phone'] ?? '', ENT_QUOTES) ?>"
                            data-login="<?= htmlspecialchars($u['login'] ?? '', ENT_QUOTES) ?>"
                            data-email="<?= htmlspecialchars($u['email'] ?? '', ENT_QUOTES) ?>"
                            data-role="<?= htmlspecialchars($urole, ENT_QUOTES) ?>"><i class="fas fa-edit"></i> Изменить</button>
                        <form method="post" style="display:inline;" onsubmit="return confirm('Изменить доступ к обратной связи?');">
                            <input type="hidden" name="action" value="toggle_block">
                            <input type="hidden" name="user_id" value="<?= $uid ?>">
                            <button type="submit" class="btn-blk<?= $blocked ? ' on' : '' ?>"><i class="fas fa-ban"></i> <?= $blocked ? 'Разблок.' : 'Блок ОС' ?></button>
                        </form>
                        <?php if ($uid !== $selfId): ?>
                        <form method="post" style="display:inline;" onsubmit="return confirm('Удалить пользователя безвозвратно?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="user_id" value="<?= $uid ?>">
                            <button type="submit" class="btn-rm"><i class="fas fa-trash"></i> Удалить</button>
                        </form>
                        <?php else: ?><span class="self-tag">это вы</span><?php endif; ?>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
function showAddForm() {
    document.getElementById('form-action').value = 'add';
    document.getElementById('form-id').value = '0';
    document.getElementById('form-title').textContent = 'Новый пользователь';
    document.getElementById('submit-btn').innerHTML = '<i class="fas fa-save"></i> Создать';
    ['name','surname','patronymic','phone','login','email','password'].forEach(function(k){ document.getElementById('f-' + k).value = ''; });
    document.getElementById('f-role').value = 'user';
    document.getElementById('f-password').setAttribute('required', 'required');
    document.getElementById('pass-star').style.display = '';
    document.getElementById('pass-hint').textContent = 'От 6 до 72 символов';
    var box = document.getElementById('userFormBox');
    box.style.display = 'block';
    box.scrollIntoView({ behavior: 'smooth' });
}
function editUser(btn) {
    document.getElementById('form-action').value = 'edit';
    document.getElementById('form-id').value = btn.getAttribute('data-id');
    document.getElementById('form-title').textContent = 'Редактирование пользователя';
    document.getElementById('submit-btn').innerHTML = '<i class="fas fa-save"></i> Сохранить';
    document.getElementById('f-name').value = btn.getAttribute('data-name');
    document.getElementById('f-surname').value = btn.getAttribute('data-surname');
    document.getElementById('f-patronymic').value = btn.getAttribute('data-patronymic');
    document.getElementById('f-phone').value = btn.getAttribute('data-phone');
    document.getElementById('f-login').value = btn.getAttribute('data-login');
    document.getElementById('f-email').value = btn.getAttribute('data-email');
    document.getElementById('f-role').value = btn.getAttribute('data-role');
    document.getElementById('f-password').value = '';
    document.getElementById('f-password').removeAttribute('required');
    document.getElementById('pass-star').style.display = 'none';
    document.getElementById('pass-hint').textContent = 'Оставьте пустым, чтобы не менять пароль';
    var box = document.getElementById('userFormBox');
    box.style.display = 'block';
    box.scrollIntoView({ behavior: 'smooth' });
}
function cancelForm() {
    document.getElementById('userFormBox').style.display = 'none';
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
