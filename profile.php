<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit;
}

require_once 'config/db.php';
$pdo = getDB();

$userId = $_SESSION['user_id'];
$success = '';
$error = '';

// Получение данных пользователя
$stmt = $pdo->prepare("SELECT * FROM user WHERE id_user = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header('Location: auth.php');
    exit;
}

// Обработка обновления профиля
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_profile') {
        $name = trim($_POST['name'] ?? '');
        $surname = trim($_POST['surname'] ?? '');
        $patronymic = trim($_POST['patronymic'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        
        if (empty($name) || empty($surname) || empty($email)) {
            $error = 'Заполните все обязательные поля';
        } else {
            try {
                $stmt = $pdo->prepare("
                    UPDATE user 
                    SET name = ?, surname = ?, patronymic = ?, phone = ?, email = ?
                    WHERE id_user = ?
                ");
                $stmt->execute([$name, $surname, $patronymic, $phone, $email, $userId]);
                $success = 'Профиль успешно обновлён';
                $_SESSION['user_name'] = $name;
            } catch (Exception $e) {
                $error = 'Ошибка обновления: ' . $e->getMessage();
            }
        }
    } elseif ($_POST['action'] === 'change_password') {
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
                $stmt = $pdo->prepare("UPDATE user SET password = ? WHERE id_user = ?");
                $stmt->execute([$hashedPassword, $userId]);
                $success = 'Пароль успешно изменён';
            } catch (Exception $e) {
                $error = 'Ошибка смены пароля: ' . $e->getMessage();
            }
        }
    }
}

$pageTitle = 'Личный кабинет';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> | Игровой справочник</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            background: #0f1419; 
            color: #e0e0e0; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .top-navbar { 
            background: linear-gradient(135deg, #1a2332 0%, #2d3748 100%); 
            padding: 15px 40px; 
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.3); 
            position: sticky; 
            top: 0; 
            z-index: 1000; 
        }
        .nav-left { display: flex; align-items: center; flex: 1; }
        .user-icon-wrapper { position: relative; cursor: pointer; margin-right: 40px; z-index: 1001; }
        .user-icon { 
            width: 45px; height: 45px; 
            background: linear-gradient(135deg, #3b82f6, #2563eb); 
            border-radius: 50%; 
            display: flex; align-items: center; justify-content: center; 
            transition: all 0.3s; 
        }
        .user-icon:hover { transform: scale(1.05); box-shadow: 0 0 15px rgba(59,130,246,0.5); }
        .user-icon i { color: white; font-size: 22px; }
        .user-dropdown { 
            position: absolute; top: 55px; left: 0; 
            background: #2d3748; border-radius: 8px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.4); 
            min-width: 260px; opacity: 0; visibility: hidden; 
            transition: all 0.3s; overflow: hidden; z-index: 1002; 
        }
        .user-icon-wrapper:hover .user-dropdown { opacity: 1; visibility: visible; }
        .user-dropdown a { 
            display: block; padding: 14px 20px; 
            color: #e0e0e0; text-decoration: none; 
            transition: all 0.2s; border-bottom: 1px solid #3d4a5c; 
            position: relative; z-index: 1003; 
        }
        .user-dropdown a:hover { background: rgba(59,130,246,0.2); color: white; padding-left: 25px; }
        .nav-buttons { display: flex; justify-content: space-between; flex: 1; gap: 0; }
        .nav-btn { 
            color: #b0b8c8; text-decoration: none; 
            font-weight: 500; font-size: 15px; 
            padding: 10px 20px; border-radius: 8px; 
            transition: all 0.3s; text-transform: uppercase; 
            letter-spacing: 0.5px; display: flex; 
            align-items: center; gap: 10px; flex: 1; 
            justify-content: center; margin: 0 5px; 
        }
        .nav-btn:hover { color: #fff; background: rgba(59, 130, 246, 0.2); }
        .nav-btn .icon { font-size: 24px; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; }
        .settings-icon { 
            width: 45px; height: 45px; 
            background: linear-gradient(135deg, #8b5cf6, #ec4899); 
            border-radius: 50%; 
            display: flex; align-items: center; justify-content: center; 
            cursor: pointer; transition: all 0.3s; margin-left: 30px; 
            box-shadow: 0 2px 8px rgba(139, 92, 246, 0.3); 
        }
        .settings-icon:hover { transform: rotate(30deg); box-shadow: 0 0 20px rgba(236, 72, 153, 0.6); background: linear-gradient(135deg, #ec4899, #8b5cf6); }
        .settings-icon i { color: white; font-size: 20px; }
        .main-wrapper { 
            max-width: 1200px; 
            margin: 0 auto; 
            padding: 30px 40px; 
            flex: 1;
            width: 100%;
        }
        .page-title { 
            color: #fff; 
            font-size: 32px; 
            font-weight: 700; 
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .page-title i { color: #3b82f6; }
        .profile-grid {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 30px;
        }
        .profile-sidebar {
            background: linear-gradient(135deg, #1b2838 0%, #2a475e 100%);
            border: 1px solid #36414d;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
        }
        .avatar-container {
            width: 150px;
            height: 150px;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 64px;
            color: white;
            border: 4px solid rgba(255,255,255,0.1);
        }
        .profile-name {
            color: #fff;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .profile-email {
            color: #8f98a0;
            font-size: 14px;
            margin-bottom: 20px;
        }
        .profile-nav {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 30px;
        }
        .profile-nav-btn {
            background: rgba(59,130,246,0.1);
            border: 2px solid transparent;
            color: #e0e0e0;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
            text-align: left;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .profile-nav-btn:hover, .profile-nav-btn.active {
            background: rgba(59,130,246,0.2);
            border-color: #3b82f6;
            color: #fff;
        }
        .profile-content {
            background: linear-gradient(135deg, #1b2838 0%, #2a475e 100%);
            border: 1px solid #36414d;
            border-radius: 12px;
            padding: 30px;
        }
        .section-title {
            color: #fff;
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .section-title i { color: #3b82f6; }
        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block;
            color: #acb2b8;
            font-size: 14px;
            margin-bottom: 8px;
            font-weight: 500;
        }
        .form-control {
            background: rgba(27,40,56,0.8);
            border: 2px solid #36414d;
            color: #e0e0e0;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s;
            width: 100%;
        }
        .form-control:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
        }
        .form-control:disabled {
            background: rgba(27,40,56,0.4);
            color: #8f98a0;
            cursor: not-allowed;
        }
        .btn-save {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            border: none;
            padding: 12px 28px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(59,130,246,0.4);
        }
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .alert-success {
            background: rgba(16,185,129,0.15);
            border: 1px solid #10b981;
            color: #6ee7b7;
        }
        .alert-error {
            background: rgba(239,68,68,0.15);
            border: 1px solid #ef4444;
            color: #fca5a5;
        }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .footer-panel { 
            background: #171a21; 
            padding: 25px; 
            text-align: center; 
            margin-top: auto;
            border-top: 1px solid #2a475e; 
        }
        .footer-panel a { 
            color: #8f98a0; 
            text-decoration: none; 
            margin: 0 15px; 
            font-size: 13px; 
        }
        .footer-panel a:hover { color: #fff; }
        .footer-panel p { color: #8f98a0; margin-top: 15px; font-size: 12px; }
        @media (max-width: 900px) {
            .top-navbar { padding: 12px 20px; flex-wrap: wrap; gap: 15px; }
            .nav-left { width: 100%; justify-content: space-between; }
            .nav-buttons { width: 100%; order: 3; margin-top: 10px; }
            .user-icon-wrapper { margin-right: 0; }
            .settings-icon { margin-left: 0; }
            .main-wrapper { padding: 20px; }
            .profile-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <nav class="top-navbar">
        <div class="nav-left">
            <div class="user-icon-wrapper">
                <div class="user-icon"><i class="fas fa-user"></i></div>
                <div class="user-dropdown">
                    <a href="profile.php"><i class="fas fa-user-circle me-2" style="color: #3b82f6;"></i>Личный кабинет</a>
                    <a href="index.php"><i class="fas fa-home me-2" style="color: #10b981;"></i>Главная страница</a>
                    <a href="logout.php"><i class="fas fa-sign-out-alt me-2" style="color: #ef4444;"></i>Выйти</a>
                </div>
            </div>
            <div class="nav-buttons">
                <a href="heroes.php" class="nav-btn"><span class="icon"><i class="fas fa-dragon" style="color: #f59e0b;"></i></span><span>Герои</span></a>
                <a href="items.php" class="nav-btn"><span class="icon"><i class="fas fa-gem" style="color: #06b6d4;"></i></span><span>Предметы</span></a>
                <a href="mechanics.php" class="nav-btn"><span class="icon"><i class="fas fa-bolt" style="color: #8b5cf6;"></i></span><span>Игровые механики</span></a>
                <a href="favorites.php" class="nav-btn"><span class="icon"><i class="fas fa-bookmark" style="color: #fbbf24;"></i></span><span>Избранное</span></a>
                <a href="feedback.php" class="nav-btn"><span class="icon"><i class="fas fa-comments" style="color: #10b981;"></i></span><span>Обратная связь</span></a>
            </div>
        </div>
        <div class="settings-icon" onclick="window.location.href='site_settings.php'"><i class="fas fa-cog"></i></div>
    </nav>

    <div class="main-wrapper">
        <h1 class="page-title">
            <i class="fas fa-user-circle"></i>
            Личный кабинет
        </h1>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <div class="profile-grid">
            <!-- Боковая панель -->
            <div class="profile-sidebar">
                <div class="avatar-container">
                    <i class="fas fa-user"></i>
                </div>
                <div class="profile-name"><?= htmlspecialchars($user['name'] . ' ' . $user['surname']) ?></div>
                <div class="profile-email"><?= htmlspecialchars($user['email']) ?></div>
                
                <div class="profile-nav">
                    <button class="profile-nav-btn active" onclick="showTab('data')">
                        <i class="fas fa-user"></i>
                        Мои данные
                    </button>
                    <button class="profile-nav-btn" onclick="showTab('security')">
                        <i class="fas fa-shield-alt"></i>
                        Безопасность
                    </button>
                </div>
            </div>
            
            <!-- Основной контент -->
            <div class="profile-content">
                <!-- Вкладка: Мои данные -->
                <div id="tab-data" class="tab-content active">
                    <h2 class="section-title">
                        <i class="fas fa-user-edit"></i>
                        Редактирование профиля
                    </h2>
                    
                    <form method="POST">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div class="form-group">
                                <label>Имя <span style="color: #ef4444;">*</span></label>
                                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Фамилия <span style="color: #ef4444;">*</span></label>
                                <input type="text" name="surname" class="form-control" value="<?= htmlspecialchars($user['surname']) ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Отчество</label>
                            <input type="text" name="patronymic" class="form-control" value="<?= htmlspecialchars($user['patronymic'] ?? '') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Электронная почта <span style="color: #ef4444;">*</span></label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Телефон</label>
                            <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="+7 (999) 999-99-99">
                        </div>
                        
                        <div class="form-group">
                            <label>Дата регистрации</label>
                            <input type="text" class="form-control" value="<?= date('d.m.Y', strtotime($user['registration_date'])) ?>" disabled>
                        </div>
                        
                        <button type="submit" class="btn-save">
                            <i class="fas fa-save me-2"></i>Сохранить изменения
                        </button>
                    </form>
                </div>
                
                <!-- Вкладка: Безопасность -->
                <div id="tab-security" class="tab-content">
                    <h2 class="section-title">
                        <i class="fas fa-lock"></i>
                        Смена пароля
                    </h2>
                    
                    <form method="POST">
                        <input type="hidden" name="action" value="change_password">
                        
                        <div class="form-group">
                            <label>Текущий пароль</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Новый пароль</label>
                            <input type="password" name="new_password" class="form-control" required minlength="6">
                            <small style="color: #8f98a0; font-size: 12px;">Минимум 6 символов</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Подтвердите новый пароль</label>
                            <input type="password" name="confirm_password" class="form-control" required minlength="6">
                        </div>
                        
                        <button type="submit" class="btn-save">
                            <i class="fas fa-key me-2"></i>Сменить пароль
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer-panel">
        <a href="tactics.php">Тактики</a>
        <a href="heroes.php">Герои</a>
        <a href="items.php">Предметы</a>
        <a href="mechanics.php">Механики</a>
        <a href="feedback.php">Обратная связь</a>
        <p>&copy; 2026 Игровой справочник. ГБПОУИО «ИАТ». Курсовая работа</p>
    </footer>

    <script>
        function showTab(tabName) {
            // Скрыть все вкладки
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Убрать активность с кнопок
            document.querySelectorAll('.profile-nav-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Показать нужную вкладку
            document.getElementById('tab-' + tabName).classList.add('active');
            
            // Активировать кнопку
            event.target.closest('.profile-nav-btn').classList.add('active');
        }
    </script>
</body>
</html>