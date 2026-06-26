<?php
// auth/auth.php — страница входа в систему
require_once __DIR__ . '/../config/init.php';

$pdo = getDB();
$error = '';

if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? '');
    $password   = $_POST['password'] ?? '';

    if (empty($identifier) || empty($password)) {
        $error = 'Введите логин/email и пароль';
    } else {
        $stmt = $pdo->prepare("SELECT id_user, login, password FROM `user` WHERE login = ? OR email = ?");
        $stmt->execute([$identifier, $identifier]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']    = $user['id_user'];
            $_SESSION['user_login'] = $user['login'];
            header('Location: ' . BASE_URL . '/index.php');
            exit;
        } else {
            $error = 'Неверный логин/email или пароль';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход | Игровой справочник</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/auth.css">
</head>
<body class="auth-page">
    <div class="auth-card">
        <h1 class="auth-title"><i class="fas fa-right-to-bracket"></i>Вход в систему</h1>

        <?php if ($error): ?>
            <div class="auth-alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" data-auth-form>
            <div class="form-group">
                <label>Логин или Email</label>
                <input type="text" name="identifier" class="auth-input" required autofocus>
            </div>
            <div class="form-group">
                <label>Пароль</label>
                <input type="password" name="password" class="auth-input" required>
            </div>
            <button type="submit" class="btn-auth">Войти</button>
        </form>

        <div class="auth-footer">
            Нет аккаунта? <a href="register.php">Зарегистрироваться</a>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/js/auth.js"></script>
</body>
</html>
