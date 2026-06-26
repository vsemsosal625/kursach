<?php
// auth/register.php — страница регистрации
require_once __DIR__ . '/../config/init.php';

$pdo = getDB();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $surname = trim($_POST['surname'] ?? '');
    $login   = trim($_POST['login'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $pass    = $_POST['password'] ?? '';

    if (empty($name) || empty($surname) || empty($login) || empty($email) || empty($pass)) {
        $error = 'Заполните все обязательные поля';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Некорректный формат электронной почты';
    } elseif (strlen($pass) < 6) {
        $error = 'Пароль должен содержать минимум 6 символов';
    } else {
        $stmt = $pdo->prepare("SELECT id_user FROM `user` WHERE login = ? OR email = ?");
        $stmt->execute([$login, $email]);
        if ($stmt->fetch()) {
            $error = 'Пользователь с таким логином или email уже существует';
        } else {
            $hash = password_hash($pass, PASSWORD_BCRYPT);
            $date = date('Y-m-d');
            $stmt = $pdo->prepare("INSERT INTO `user` (name, surname, patronymic, phone, login, email, password, registration_date) VALUES (?, ?, NULL, NULL, ?, ?, ?, ?)");
            $stmt->execute([$name, $surname, $login, $email, $hash, $date]);
            $success = 'Регистрация успешна! <a href="auth.php">Войти</a>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация | Игровой справочник</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/auth.css">
</head>
<body class="auth-page">
    <div class="auth-card">
        <h2 class="auth-title"><i class="fas fa-user-plus"></i>Регистрация</h2>

        <?php if ($error): ?><div class="auth-alert error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="auth-alert success"><?= $success ?></div><?php endif; ?>

        <form method="POST" data-auth-form>
            <div class="form-group">
                <label>Имя</label>
                <input type="text" name="name" class="auth-input" required>
            </div>
            <div class="form-group">
                <label>Фамилия</label>
                <input type="text" name="surname" class="auth-input" required>
            </div>
            <div class="form-group">
                <label>Логин</label>
                <input type="text" name="login" class="auth-input" required minlength="3">
            </div>
            <div class="form-group">
                <label>Электронная почта</label>
                <input type="email" name="email" class="auth-input" required>
            </div>
            <div class="form-group">
                <label>Пароль</label>
                <input type="password" name="password" class="auth-input" required minlength="6">
            </div>
            <button type="submit" class="btn-auth">Зарегистрироваться</button>
        </form>

        <div class="auth-footer">
            Уже есть аккаунт? <a href="auth.php">Войти</a>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/js/auth.js"></script>
</body>
</html>
