<?php
session_start();
require_once 'config/db.php';

// Получаем PDO соединение
$pdo = getDB();

$error = '';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier']);
    $password = $_POST['password'];

    if (empty($identifier) || empty($password)) {
        $error = 'Введите логин/email и пароль';
    } else {
        $stmt = $pdo->prepare("SELECT id_user, login, password FROM `user` WHERE login = ? OR email = ?");
        $stmt->execute([$identifier, $identifier]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['user_login'] = $user['login'];
            header('Location: index.php');
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
            align-items: center;
            justify-content: center;
        }
        .auth-container {
            background: linear-gradient(135deg, #1b2838 0%, #2a475e 100%);
            border: 1px solid #36414d;
            border-radius: 12px;
            padding: 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.3);
        }
        .auth-title {
            color: #fff;
            font-size: 28px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 30px;
        }
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
        }
        .form-control:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
        }
        .btn-login {
            width: 100%;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(59,130,246,0.4);
        }
        .error-message {
            background: rgba(239,68,68,0.15);
            border: 1px solid #ef4444;
            color: #fca5a5;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .auth-footer {
            text-align: center;
            margin-top: 20px;
            color: #8f98a0;
            font-size: 14px;
        }
        .auth-footer a {
            color: #66c0f4;
            text-decoration: none;
            font-weight: 600;
        }
        .auth-footer a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="auth-container">
        <h1 class="auth-title">Вход в систему</h1>
        
        <?php if ($error): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Логин или Email</label>
                <input type="text" name="identifier" class="form-control" required autofocus>
            </div>
            
            <div class="form-group">
                <label>Пароль</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            
            <button type="submit" class="btn-login">Войти</button>
        </form>
        
        <div class="auth-footer">
            Нет аккаунта? <a href="register.php">Зарегистрироваться</a>
        </div>
    </div>
</body>
</html>