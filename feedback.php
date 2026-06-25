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

// Обработка отправки отзыва
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? '';
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $priority = $_POST['priority'] ?? 'medium';
    
    if (empty($type) || empty($title) || empty($content)) {
        $error = 'Заполните все обязательные поля';
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO feedback (user_id, type, title, content, priority, created_date, status) 
                VALUES (?, ?, ?, ?, ?, NOW(), 'new')
            ");
            $stmt->execute([$userId, $type, $title, $content, $priority]);
            $success = 'Ваш отзыв успешно отправлен! Спасибо за обратную связь.';
        } catch (Exception $e) {
            $error = 'Ошибка при отправке: ' . $e->getMessage();
        }
    }
}

// Получение истории отзывов пользователя
$stmt = $pdo->prepare("SELECT * FROM feedback WHERE user_id = ? ORDER BY created_date DESC");
$stmt->execute([$userId]);
$feedbacks = $stmt->fetchAll();

$pageTitle = 'Обратная связь';
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
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .page-title i { color: #10b981; }
        .page-subtitle {
            color: #8f98a0;
            font-size: 16px;
            margin-bottom: 30px;
        }
        .feedback-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        .feedback-form, .feedback-history {
            background: linear-gradient(135deg, #1b2838 0%, #2a475e 100%);
            border: 1px solid #36414d;
            border-radius: 12px;
            padding: 30px;
        }
        .section-title {
            color: #fff;
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .section-title i { color: #10b981; }
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
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16,185,129,0.1);
        }
        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }
        .btn-submit {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border: none;
            padding: 14px 28px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16,185,129,0.4);
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
        .feedback-item {
            background: rgba(27,40,56,0.6);
            border: 1px solid #36414d;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s;
        }
        .feedback-item:hover {
            border-color: #10b981;
            transform: translateX(5px);
        }
        .feedback-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }
        .feedback-type {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .type-bug { background: rgba(239,68,68,0.2); color: #fca5a5; }
        .type-feature { background: rgba(59,130,246,0.2); color: #93c5fd; }
        .type-feedback { background: rgba(16,185,129,0.2); color: #6ee7b7; }
        .type-other { background: rgba(139,92,246,0.2); color: #c4b5fd; }
        .feedback-title {
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .feedback-content {
            color: #acb2b8;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 12px;
        }
        .feedback-meta {
            display: flex;
            gap: 20px;
            font-size: 13px;
            color: #8f98a0;
        }
        .status-badge {
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }
        .status-new { background: rgba(245,158,11,0.2); color: #fbbf24; }
        .status-review { background: rgba(59,130,246,0.2); color: #93c5fd; }
        .status-resolved { background: rgba(16,185,129,0.2); color: #6ee7b7; }
        .status-rejected { background: rgba(239,68,68,0.2); color: #fca5a5; }
        .priority-badge {
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }
        .priority-low { background: rgba(139,92,246,0.2); color: #c4b5fd; }
        .priority-medium { background: rgba(245,158,11,0.2); color: #fbbf24; }
        .priority-high { background: rgba(239,68,68,0.2); color: #fca5a5; }
        .no-feedback {
            text-align: center;
            padding: 40px 20px;
            color: #8f98a0;
        }
        .no-feedback i {
            font-size: 48px;
            margin-bottom: 15px;
            color: #4a5568;
        }
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
            .feedback-grid { grid-template-columns: 1fr; }
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
                <a href="feedback.php" class="nav-btn" style="background: rgba(16,185,129,0.15); color: #fff;"><span class="icon"><i class="fas fa-comments" style="color: #10b981;"></i></span><span>Обратная связь</span></a>
            </div>
        </div>
        <div class="settings-icon" onclick="window.location.href='site_settings.php'"><i class="fas fa-cog"></i></div>
    </nav>

    <div class="main-wrapper">
        <h1 class="page-title">
            <i class="fas fa-comments"></i>
            Обратная связь
        </h1>
        <p class="page-subtitle">
            Поделитесь своим мнением, сообщите об ошибках или предложите новые идеи для улучшения справочника
        </p>
        
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
        
        <div class="feedback-grid">
            <!-- Форма отправки -->
            <div class="feedback-form">
                <h2 class="section-title">
                    <i class="fas fa-paper-plane"></i>
                    Отправить отзыв
                </h2>
                
                <form method="POST">
                    <div class="form-group">
                        <label>Тип обращения <span style="color: #ef4444;">*</span></label>
                        <select name="type" class="form-control" required>
                            <option value="">Выберите тип...</option>
                            <option value="bug">🐛 Ошибка/Баг</option>
                            <option value="feature">💡 Предложение/Идея</option>
                            <option value="feedback">💬 Общий отзыв</option>
                            <option value="other">❓ Другое</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Заголовок <span style="color: #ef4444;">*</span></label>
                        <input type="text" name="title" class="form-control" 
                               placeholder="Краткое описание вашего обращения" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Приоритет</label>
                        <select name="priority" class="form-control">
                            <option value="low">🟢 Низкий</option>
                            <option value="medium" selected>🟡 Средний</option>
                            <option value="high">🔴 Высокий</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Сообщение <span style="color: #ef4444;">*</span></label>
                        <textarea name="content" class="form-control" 
                                  placeholder="Опишите подробно вашу проблему, предложение или отзыв..." required></textarea>
                    </div>
                    
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-paper-plane me-2"></i>Отправить
                    </button>
                </form>
            </div>
            
            <!-- История отзывов -->
            <div class="feedback-history">
                <h2 class="section-title">
                    <i class="fas fa-history"></i>
                    Мои обращения
                </h2>
                
                <?php if (empty($feedbacks)): ?>
                    <div class="no-feedback">
                        <i class="fas fa-inbox"></i>
                        <p>У вас пока нет отправленных обращений</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($feedbacks as $fb): ?>
                        <div class="feedback-item">
                            <div class="feedback-header">
                                <div>
                                    <span class="feedback-type type-<?= htmlspecialchars($fb['type']) ?>">
                                        <?php
                                        $typeIcons = ['bug' => '🐛', 'feature' => '💡', 'feedback' => '💬', 'other' => '❓'];
                                        $typeNames = ['bug' => 'Ошибка', 'feature' => 'Предложение', 'feedback' => 'Отзыв', 'other' => 'Другое'];
                                        echo $typeIcons[$fb['type']] . ' ' . $typeNames[$fb['type']];
                                        ?>
                                    </span>
                                    <div class="feedback-title"><?= htmlspecialchars($fb['title']) ?></div>
                                </div>
                                <div>
                                    <span class="status-badge status-<?= htmlspecialchars($fb['status']) ?>">
                                        <?php
                                        $statusNames = ['new' => 'Новое', 'review' => 'На рассмотрении', 'resolved' => 'Решено', 'rejected' => 'Отклонено'];
                                        echo $statusNames[$fb['status']] ?? $fb['status'];
                                        ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="feedback-content">
                                <?= nl2br(htmlspecialchars($fb['content'])) ?>
                            </div>
                            
                            <div class="feedback-meta">
                                <span>
                                    <i class="far fa-calendar me-1"></i>
                                    <?= date('d.m.Y H:i', strtotime($fb['created_date'])) ?>
                                </span>
                                <span class="priority-badge priority-<?= htmlspecialchars($fb['priority']) ?>">
                                    <?php
                                    $priorityNames = ['low' => 'Низкий', 'medium' => 'Средний', 'high' => 'Высокий'];
                                    echo $priorityNames[$fb['priority']];
                                    ?>
                                </span>
                                <?php if ($fb['admin_response']): ?>
                                    <span style="color: #10b981;">
                                        <i class="fas fa-check-circle me-1"></i>Есть ответ
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($fb['admin_response']): ?>
                                <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #36414d;">
                                    <div style="color: #10b981; font-size: 13px; margin-bottom: 6px;">
                                        <i class="fas fa-reply me-1"></i>Ответ администратора:
                                    </div>
                                    <div style="color: #acb2b8; font-size: 14px; line-height: 1.6;">
                                        <?= nl2br(htmlspecialchars($fb['admin_response'])) ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
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
</body>
</html>