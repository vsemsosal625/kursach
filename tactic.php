<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit;
}

require_once 'config/db.php';
$pdo = getDB();

$tacticId = $_GET['id'] ?? null;
if (!$tacticId) {
    header('Location: tactics.php');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM game_mechanic WHERE id_game_mechanic = ?");
    $stmt->execute([$tacticId]);
    $tactic = $stmt->fetch();
    
    if (!$tactic) {
        header('Location: tactics.php');
        exit;
    }
} catch (Exception $e) {
    die("Ошибка загрузки тактики: " . $e->getMessage());
}

// Иконки для категорий
$categoryIcons = [
    'Основы' => 'fa-book',
    'Стратегия' => 'fa-chess',
    'Макро' => 'fa-map-marked-alt'
];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tactic['title']) ?> | Игровой справочник</title>
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
            max-width: 1000px; 
            margin: 0 auto; 
            padding: 30px 40px; 
            flex: 1;
            width: 100%;
        }
        .back-link { 
            display: inline-flex; 
            align-items: center; 
            gap: 8px; 
            color: #8f98a0; 
            text-decoration: none; 
            margin-bottom: 20px; 
            font-size: 14px; 
            transition: color 0.2s; 
        }
        .back-link:hover { color: #f59e0b; }
        .tactic-detail { 
            background: linear-gradient(135deg, #1b2838 0%, #2a475e 100%); 
            border: 1px solid #36414d; 
            border-radius: 12px; 
            padding: 40px; 
        }
        .tactic-detail h1 { 
            color: #fff; 
            font-size: 32px; 
            margin-bottom: 15px; 
            font-weight: 700; 
        }
        .tactic-detail .meta { 
            color: #8f98a0; 
            font-size: 14px; 
            margin-bottom: 25px; 
            padding-bottom: 20px; 
            border-bottom: 1px solid #36414d; 
        }
        .tactic-detail .category-badge {
            display: inline-block;
            background: rgba(245,158,11,0.2);
            color: #fbbf24;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            margin-right: 15px;
        }
        .tactic-detail .content { 
            color: #e0e0e0; 
            font-size: 16px; 
            line-height: 1.8; 
            white-space: pre-wrap; 
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
        @media (max-width: 768px) {
            .top-navbar { padding: 12px 20px; flex-wrap: wrap; gap: 15px; }
            .nav-left { width: 100%; justify-content: space-between; }
            .nav-buttons { width: 100%; order: 3; margin-top: 10px; }
            .user-icon-wrapper { margin-right: 0; }
            .settings-icon { margin-left: 0; }
            .main-wrapper { padding: 20px; }
            .tactic-detail { padding: 25px; }
            .tactic-detail h1 { font-size: 24px; }
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
                <a href="collections.php" class="nav-btn"><span class="icon"><i class="fas fa-trophy" style="color: #10b981;"></i></span><span>Коллекции</span></a>
            </div>
        </div>
        <div class="settings-icon" onclick="window.location.href='site_settings.php'"><i class="fas fa-cog"></i></div>
    </nav>

    <div class="main-wrapper">
        <a href="tactics.php" class="back-link">
            <?php
// Проверяем, есть ли в избранном
$stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND item_type = 'mechanic' AND item_id = ?");
$stmt->execute([$_SESSION['user_id'], $tacticId]);
$isFavorite = $stmt->fetch() ? true : false;

// Определяем кнопку "Назад"
$from = $_GET['from'] ?? '';
if ($from === 'favorites') {
    $backLink = 'favorites.php';
    $backText = 'Назад к избранному';
} else {
    $backLink = 'tactics.php';
    $backText = 'Назад к списку тактик';
}
?>

<a href="<?= $backLink ?>" class="back-link">
    <i class="fas fa-arrow-left"></i><?= $backText ?>
</a>

<!-- КНОПКА ИЗБРАННОГО -->
<button id="favBtn" class="fav-btn" onclick="toggleFavorite('mechanic', <?= $tacticId ?>)" style="background: <?= $isFavorite ? 'rgba(251,191,36,0.3)' : 'rgba(251,191,36,0.15)' ?>; border: 2px solid #fbbf24; color: #fbbf24; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; margin: 0 0 20px 20px; transition: all 0.3s; font-size: 14px;">
    <i class="fas <?= $isFavorite ? 'fa-star' : 'fa-bookmark' ?>"></i> <?= $isFavorite ? 'В избранном' : 'Добавить в избранное' ?>
</button>
            <i class="fas fa-arrow-left"></i>Назад к списку тактик
        </a>
        
        <div class="tactic-detail">
            <h1><?= htmlspecialchars($tactic['title']) ?></h1>
            <div class="meta">
                <span class="category-badge">
                    <i class="fas <?= $categoryIcons[$tactic['category']] ?? 'fa-tag' ?> me-2"></i>
                    <?= htmlspecialchars($tactic['category']) ?>
                </span>
                <span><i class="far fa-calendar"></i> <?= date('d.m.Y', strtotime($tactic['created_date'])) ?></span>
            </div>
            <div class="content"><?= nl2br(htmlspecialchars($tactic['content'])) ?></div>
        </div>
    </div>

    <footer class="footer-panel">
        <a href="tactics.php">Тактики</a>
        <a href="heroes.php">Герои</a>
        <a href="items.php">Предметы</a>
        <a href="mechanics.php">Механики</a>
        <a href="newbie.php">Гайд для новичка</a>
        <p>&copy; 2026 Игровой справочник. ГБПОУИО «ИАТ». Курсовая работа</p>
    </footer>
    <script>
function toggleFavorite(type, id) {
    const formData = new FormData();
    formData.append('item_type', type);
    formData.append('item_id', id);
    
    fetch('add_favorite.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const btn = document.getElementById('favBtn');
            if (data.action === 'added') {
                btn.innerHTML = '<i class="fas fa-star"></i> В избранном';
                btn.style.background = 'rgba(251,191,36,0.3)';
                btn.style.borderColor = '#fbbf24';
                btn.style.color = '#fbbf24';
            } else {
                btn.innerHTML = '<i class="fas fa-bookmark"></i> Добавить в избранное';
                btn.style.background = 'rgba(251,191,36,0.15)';
                btn.style.borderColor = '#fbbf24';
                btn.style.color = '#fbbf24';
            }
        }
    })
    .catch(err => console.error('Ошибка:', err));
}
</script>
</body>
</html>