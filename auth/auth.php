<?php
// auth/auth.php — страница входа в систему (вход только по логину)
require_once __DIR__ . '/../config/init.php';

$pdo = getDB();
$error = '';

if (isset($_GET['guest'])) {
    $_SESSION['is_guest'] = true;
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login    = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($login) || empty($password)) {
        $error = 'Введите логин и пароль';
    } else {
        $stmt = $pdo->prepare("SELECT id_user, login, password FROM `user` WHERE login = ?");
        $stmt->execute([$login]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']    = $user['id_user'];
            $_SESSION['user_login'] = $user['login'];
            unset($_SESSION['user_role']);
            unset($_SESSION['is_guest']);
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        } else {
            $error = 'Неверный логин или пароль';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Тема оформления: по умолчанию светлая, тёмная только при явном выборе -->
    <script>(function(){try{if(localStorage.getItem('siteTheme')!=='dark'){document.documentElement.classList.add('light-theme');}}catch(e){}})();</script>
    <title>Вход | Игровой справочник</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/auth.css">
    <style>
    .auth-or { text-align: center; color: #8f98a0; margin: 18px 0 10px; font-size: 13px; }
    .btn-guest { display: inline-flex; align-items: center; justify-content: center; gap: 8px; width: 100%; padding: 12px; border-radius: 8px; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.18); color: #cbd5e1; text-decoration: none; font-weight: 600; transition: all .25s; box-sizing: border-box; }
    .btn-guest:hover { background: rgba(255,255,255,0.12); color: #fff; }
    </style>
</head>
<body class="auth-page">
    <button type="button" class="auth-theme-toggle" id="authThemeToggle" title="Сменить тему" aria-label="Сменить тему"><i class="fas fa-moon"></i></button>
    <div class="auth-card">
        <h1 class="auth-title"><i class="fas fa-right-to-bracket"></i>Вход в систему</h1>

        <?php if ($error): ?>
            <div class="auth-alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" data-auth-form>
            <div class="form-group">
                <label>Логин</label>
                <input type="text" name="login" class="auth-input" required autofocus inputmode="numeric">
            </div>
            <div class="form-group">
                <label>Пароль</label>
                <input type="password" name="password" class="auth-input" required>
            </div>
            <button type="submit" class="btn-auth">Войти</button>
        </form>

        <div class="auth-or">или</div>
        <a href="?guest=1" class="btn-guest"><i class="fas fa-user-secret"></i> Продолжить как гость</a>

        <div class="auth-footer">
            Нет аккаунта? <a href="register.php">Зарегистрироваться</a>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/js/auth.js"></script>
    <script>
    (function(){
        var btn = document.getElementById('authThemeToggle');
        if(!btn) return;
        function syncIcon(){
            var light = document.documentElement.classList.contains('light-theme');
            btn.innerHTML = light ? '<i class="fas fa-moon"></i>' : '<i class="fas fa-sun"></i>';
            btn.setAttribute('title', light ? 'Включить тёмную тему' : 'Включить светлую тему');
        }
        syncIcon();
        btn.addEventListener('click', function(){
            var light = document.documentElement.classList.toggle('light-theme');
            try{ localStorage.setItem('siteTheme', light ? 'light' : 'dark'); }catch(e){}
            syncIcon();
        });
    })();
    </script>
</body>
</html>
