<?php
session_start();
require_once 'config/db.php';

$pdo = getDB();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name']);
    $surname = trim($_POST['surname']);
    $login   = trim($_POST['login']);
    $email   = trim($_POST['email']);
    $pass    = $_POST['password'];

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
            $success = 'Регистрация успешна! <a href="auth.php" style="color:#3b82f6;font-weight:600;">Войти</a>';
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
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #0f1419;
            color: #e0e0e0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .auth-wrapper { width: 100%; max-width: 440px; padding: 20px; }
        .auth-card {
            background: linear-gradient(135deg, #1b2838 0%, #2a475e 100%);
            border: 1px solid #36414d;
            border-radius: 12px;
            padding: 40px 35px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.4);
        }
        .auth-title {
            text-align: center;
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 25px;
            color: #fff;
        }
        .auth-title i { color: #10b981; margin-right: 10px; }
        .dark-input {
            width: 100%;
            background: rgba(0,0,0,0.3);
            border: 1px solid #36414d;
            color: #e0e0e0;
            padding: 13px 16px;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s;
            margin-bottom: 16px;
        }
        .dark-input::placeholder { color: #8f98a0; }
        .dark-input:focus {
            background: rgba(0,0,0,0.45);
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.25);
            color: #fff;
            outline: none;
        }
        .btn-dark-primary {
            width: 100%;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            border: none;
            color: white;
            padding: 14px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 8px;
        }
        .btn-dark-primary:hover {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(59,130,246,0.4);
        }
        .auth-footer {
            text-align: center;
            margin-top: 22px;
            color: #8f98a0;
            font-size: 14px;
        }
        .auth-footer a {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }
        .auth-footer a:hover { color: #60a5fa; text-decoration: underline; }
        .alert-dark {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 500;
        }
        .alert-error { background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #fca5a5; }
        .alert-success { background: rgba(16, 185, 129, 0.15); border: 1px solid #10b981; color: #6ee7b7; }
    </style>
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <h2 class="auth-title"><i class="fas fa-user-plus"></i>Регистрация</h2>
            <?php if ($error): ?><div class="alert-dark alert-error"><?= $error ?></div><?php endif; ?>
            <?php if ($success): ?><div class="alert-dark alert-success"><?= $success ?></div><?php endif; ?>
            <form method="POST">
                <input type="text" name="name" class="dark-input" placeholder="Имя" required>
                <input type="text" name="surname" class="dark-input" placeholder="Фамилия" required>
                <input type="text" name="login" class="dark-input" placeholder="Логин" required minlength="3">
                <input type="email" name="email" class="dark-input" placeholder="Электронная почта" required>
                <input type="password" name="password" class="dark-input" placeholder="Пароль" required minlength="6">
                <button type="submit" class="btn-dark-primary">Зарегистрироваться</button>
            </form>
            <div class="auth-footer">
                Уже есть аккаунт? <a href="auth.php">Войти</a>
            </div>
        </div>
    </div>
</body>
</html>