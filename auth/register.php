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
    } elseif (!isValidName($name)) {
        $error = 'Имя может содержать только русские или латинские буквы (от 2 до 50 символов)';
    } elseif (!isValidName($surname)) {
        $error = 'Фамилия может содержать только русские или латинские буквы (от 2 до 50 символов)';
    } elseif (!isValidLogin($login)) {
        $error = 'Логин должен состоять только из цифр (от 3 до 20 цифр)';
    } elseif (!isValidEmail($email)) {
        $error = 'Введите корректный email с существующим доменом (например, name@gmail.com)';
    } elseif (strlen($pass) < 6 || strlen($pass) > 72) {
        $error = 'Пароль должен содержать от 6 до 72 символов';
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
    <style>
    .auth-or { text-align: center; color: #8f98a0; margin: 18px 0 10px; font-size: 13px; }
    .btn-guest { display: inline-flex; align-items: center; justify-content: center; gap: 8px; width: 100%; padding: 12px; border-radius: 8px; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.18); color: #cbd5e1; text-decoration: none; font-weight: 600; transition: all .25s; box-sizing: border-box; }
    .btn-guest:hover { background: rgba(255,255,255,0.12); color: #fff; }
    .auth-hint { display:block; color:#8f98a0; font-size:12px; margin-top:4px; }
    </style>
</head>
<body class="auth-page">
    <div class="auth-card">
        <h2 class="auth-title"><i class="fas fa-user-plus"></i>Регистрация</h2>

        <?php if ($error): ?><div class="auth-alert error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="auth-alert success"><?= $success ?></div><?php endif; ?>

        <form method="POST" data-auth-form>
            <div class="form-group">
                <label>Имя</label>
                <input type="text" name="name" class="auth-input" required maxlength="50" pattern="[A-Za-zА-Яа-яЁё -]{2,50}" title="Только русские или латинские буквы, от 2 до 50 символов">
            </div>
            <div class="form-group">
                <label>Фамилия</label>
                <input type="text" name="surname" class="auth-input" required maxlength="50" pattern="[A-Za-zА-Яа-яЁё -]{2,50}" title="Только русские или латинские буквы, от 2 до 50 символов">
            </div>
            <div class="form-group">
                <label>Логин</label>
                <input type="text" name="login" class="auth-input" required minlength="3" maxlength="20" inputmode="numeric" pattern="[0-9]{3,20}" title="Только цифры, от 3 до 20 символов">
                <small class="auth-hint">Только цифры (от 3 до 20)</small>
            </div>
            <div class="form-group">
                <label>Электронная почта</label>
                <input type="email" name="email" class="auth-input" required maxlength="150">
            </div>
            <div class="form-group">
                <label>Пароль</label>
                <input type="password" name="password" class="auth-input" required minlength="6" maxlength="72">
            </div>
            <button type="submit" class="btn-auth">Зарегистрироваться</button>
        </form>

        <div class="auth-or">или</div>
        <a href="auth.php?guest=1" class="btn-guest"><i class="fas fa-user-secret"></i> Продолжить как гость</a>

        <div class="auth-footer">
            Уже есть аккаунт? <a href="auth.php">Войти</a>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/js/auth.js"></script>
</body>
</html>
