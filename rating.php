<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit;
}

require_once 'config/db.php';
$pdo = getDB();

$heroesData = [
    1 => ['id'=>1, 'name'=>'Pudge', 'attr'=>'strength', 'attr_name'=>'Сила', 'image_url'=>'https://ru.dotabuff.com/assets/heroes/pudge-d8673aca5ef38b0cff4826c8c7d22e09e8e09b44940a86859c8161553caefa8c.jpg'],
    2 => ['id'=>2, 'name'=>'Centaur Warrunner', 'attr'=>'strength', 'attr_name'=>'Сила', 'image_url'=>'https://ru.dotabuff.com/assets/heroes/centaur-warrunner-57b9e5d75f9bd84e2651254d28cb50a63e91a3e8699095d16d1776cbff8d80c5.jpg'],
    3 => ['id'=>3, 'name'=>'Wraith King', 'attr'=>'strength', 'attr_name'=>'Сила', 'image_url'=>'https://ru.dotabuff.com/assets/heroes/wraith-king-233a53f103c784de0f480cec4f18dd8490bd6da44357154e4717dfb31ffbb2b3.jpg'],
    7 => ['id'=>7, 'name'=>'Phantom Assassin', 'attr'=>'agility', 'attr_name'=>'Ловкость', 'image_url'=>'https://ru.dotabuff.com/assets/heroes/phantom-assassin-7654f46ff00ddaefca29b284c7a70705a0c305250560f0543eaa8539e3d848f8.jpg'],
    8 => ['id'=>8, 'name'=>'Templar Assassin', 'attr'=>'agility', 'attr_name'=>'Ловкость', 'image_url'=>'https://ru.dotabuff.com/assets/heroes/templar-assassin-59dffc687571d6282dd71ab1e5eae130e3c3789b343d06832a0c170cd94b0322.jpg'],
    9 => ['id'=>9, 'name'=>'Medusa', 'attr'=>'agility', 'attr_name'=>'Ловкость', 'image_url'=>'https://ru.dotabuff.com/assets/heroes/medusa-2d3f561c0312520e3d2b03808b0df8025ea98ec977d9a1701d67ed22e11e2565.jpg'],
    13 => ['id'=>13, 'name'=>'Lion', 'attr'=>'intelligence', 'attr_name'=>'Интеллект', 'image_url'=>'https://ru.dotabuff.com/assets/heroes/lion-aa7c75a15844883581f25be8dca60efd72e7273a7dd8fa9c785c79f6bd7fdf42.jpg'],
    14 => ['id'=>14, 'name'=>'Zeus', 'attr'=>'intelligence', 'attr_name'=>'Интеллект', 'image_url'=>'https://ru.dotabuff.com/assets/heroes/zeus-270c72957e96bab2b1ecab445e0f4f62454f61a722085c83c749909b90c3912a.jpg'],
    15 => ['id'=>15, 'name'=>'Dark Willow', 'attr'=>'intelligence', 'attr_name'=>'Интеллект', 'image_url'=>'https://ru.dotabuff.com/assets/heroes/dark-willow-72b9b406f55446c501688c97f7954ac9c238bd48714cc322ca190d6fc1b6dbc2.jpg'],
    19 => ['id'=>19, 'name'=>'Marci', 'attr'=>'universal', 'attr_name'=>'Универсальный', 'image_url'=>'https://ru.dotabuff.com/assets/heroes/marci-9a0a2c4d90dc63116a5ba23439d97194915d3abd083cccc226a9b3c21fcdaa81.jpg'],
    20 => ['id'=>20, 'name'=>'Techies', 'attr'=>'universal', 'attr_name'=>'Универсальный', 'image_url'=>'https://ru.dotabuff.com/assets/heroes/techies-e199ba8af1a4508668ec6cc16ecc96fe38231a4dd021a72e30d76d14e7e2cdb8.jpg'],
    21 => ['id'=>21, 'name'=>'Batrider', 'attr'=>'universal', 'attr_name'=>'Универсальный', 'image_url'=>'https://ru.dotabuff.com/assets/heroes/batrider-2cea2260556b67fe7d44f4b325cf6673d55cb03d4b419f4e68ac9acab243c09d.jpg'],
];

$viewsData = [];
try {
    $stmt = $pdo->query("SELECT hero_id, views FROM hero_views");
    foreach ($stmt->fetchAll() as $row) {
        $viewsData[$row['hero_id']] = $row['views'];
    }
} catch (Exception $e) {}

foreach ($heroesData as $id => &$hero) {
    $hero['views'] = $viewsData[$id] ?? 0;
}
unset($hero);

usort($heroesData, function($a, $b) {
    return $b['views'] - $a['views'];
});

$topHeroes = $heroesData;

$pageTitle = 'Рейтинг героев';
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
        body { background: #0f1419; color: #e0e0e0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; min-height: 100vh; display: flex; flex-direction: column; }
        .top-navbar { background: linear-gradient(135deg, #1a2332 0%, #2d3748 100%); padding: 15px 40px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 10px rgba(0,0,0,0.3); position: sticky; top: 0; z-index: 1000; }
        .nav-left { display: flex; align-items: center; flex: 1; }
        .user-icon-wrapper { position: relative; cursor: pointer; margin-right: 40px; z-index: 1001; }
        .user-icon { width: 45px; height: 45px; background: linear-gradient(135deg, #3b82f6, #2563eb); border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s; }
        .user-icon:hover { transform: scale(1.05); box-shadow: 0 0 15px rgba(59,130,246,0.5); }
        .user-icon i { color: white; font-size: 22px; }
        .user-dropdown { position: absolute; top: 55px; left: 0; background: #2d3748; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.4); min-width: 260px; opacity: 0; visibility: hidden; transition: all 0.3s; overflow: hidden; z-index: 1002; }
        .user-icon-wrapper:hover .user-dropdown { opacity: 1; visibility: visible; }
        .user-dropdown a { display: block; padding: 14px 20px; color: #e0e0e0; text-decoration: none; transition: all 0.2s; border-bottom: 1px solid #3d4a5c; position: relative; z-index: 1003; }
        .user-dropdown a:hover { background: rgba(59,130,246,0.2); color: white; padding-left: 25px; }
        .nav-buttons { display: flex; justify-content: space-between; flex: 1; gap: 0; }
        .nav-btn { color: #b0b8c8; text-decoration: none; font-weight: 500; font-size: 15px; padding: 10px 20px; border-radius: 8px; transition: all 0.3s; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 10px; flex: 1; justify-content: center; margin: 0 5px; }
        .nav-btn:hover { color: #fff; background: rgba(59, 130, 246, 0.2); }
        .nav-btn .icon { font-size: 24px; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; }
        .settings-icon { width: 45px; height: 45px; background: linear-gradient(135deg, #8b5cf6, #ec4899); border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s; margin-left: 30px; box-shadow: 0 2px 8px rgba(139, 92, 246, 0.3); }
        .settings-icon:hover { transform: rotate(30deg); box-shadow: 0 0 20px rgba(236, 72, 153, 0.6); background: linear-gradient(135deg, #ec4899, #8b5cf6); }
        .settings-icon i { color: white; font-size: 20px; }
        .main-wrapper { max-width: 1200px; margin: 0 auto; padding: 30px 40px; flex: 1; width: 100%; }
        
        .page-header { 
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
            flex-wrap: nowrap;
        }
        .page-title { 
            color: #fff; 
            font-size: 28px; 
            font-weight: 700; 
            border-left: 4px solid #fbbf24; 
            padding-left: 15px; 
            margin: 0;
            white-space: nowrap;
        }
        
        .top-list { margin-top: 20px; }
        .top-item { 
            display: grid;
            grid-template-columns: 80px 120px 1fr 100px;
            align-items: center;
            gap: 20px;
            background: linear-gradient(135deg, #1b2838 0%, #2a475e 100%);
            border: 1px solid #36414d;
            border-radius: 12px;
            padding: 20px 25px;
            margin-bottom: 15px;
            transition: all 0.3s;
            text-decoration: none;
            color: inherit;
        }
        .top-item:hover {
            transform: translateX(10px);
            border-color: #fbbf24;
            box-shadow: 0 8px 25px rgba(251,191,36,0.2);
        }
        .rank-badge {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            font-weight: 700;
            background: #36414d;
            color: #e0e0e0;
        }
        .rank-1 { background: linear-gradient(135deg, #fbbf24, #f59e0b); color: #000; box-shadow: 0 0 20px rgba(251,191,36,0.5); }
        .rank-2 { background: linear-gradient(135deg, #9ca3af, #6b7280); color: #fff; }
        .rank-3 { background: linear-gradient(135deg, #d97706, #92400e); color: #fff; }
        
        .hero-image {
            width: 100px;
            height: 60px;
            background: #0f1419;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border: 2px solid #36414d;
        }
        .hero-image img { width: 100%; height: 100%; object-fit: cover; }
        .hero-image i { font-size: 32px; color: #3b82f6; }
        
        .hero-info h3 {
            color: #fff;
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        .hero-info .attr {
            color: #8f98a0;
            font-size: 14px;
        }
        
        .views-count {
            text-align: center;
            background: rgba(251,191,36,0.15);
            border: 1px solid #fbbf24;
            border-radius: 8px;
            padding: 10px;
        }
        .views-count .number {
            color: #fbbf24;
            font-size: 24px;
            font-weight: 700;
            display: block;
        }
        .views-count .label {
            color: #8f98a0;
            font-size: 11px;
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
            .top-item { grid-template-columns: 60px 80px 1fr 80px; gap: 10px; padding: 15px; }
            .rank-badge { width: 45px; height: 45px; font-size: 20px; }
            .hero-image { width: 70px; height: 45px; }
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
        <div class="page-header">
            <h1 class="page-title">🏆 Рейтинг героев</h1>
        </div>
        
        <div class="top-list">
            <?php foreach ($topHeroes as $rank => $hero): ?>
                <a href="hero.php?id=<?= $hero['id'] ?>&from=rating" class="top-item">
                    <div class="rank-badge rank-<?= $rank + 1 ?>">
                        <?= $rank + 1 ?>
                    </div>
                    <div class="hero-image">
                        <?php if (!empty($hero['image_url'])): ?>
                            <img src="<?= htmlspecialchars($hero['image_url']) ?>" alt="<?= htmlspecialchars($hero['name']) ?>">
                        <?php else: ?>
                            <i class="fas fa-user"></i>
                        <?php endif; ?>
                    </div>
                    <div class="hero-info">
                        <h3><?= htmlspecialchars($hero['name']) ?></h3>
                        <span class="attr">
                            <?php
                            $attrLabels = ['strength' => '🔴 Сила', 'agility' => '🟢 Ловкость', 'intelligence' => '🔵 Интеллект', 'universal' => '🟡 Универсальный'];
                            echo $attrLabels[$hero['attr']] ?? $hero['attr_name'];
                            ?>
                        </span>
                    </div>
                    <div class="views-count">
                        <span class="number"><?= $hero['views'] ?></span>
                        <span class="label">просмотров</span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <footer class="footer-panel">
        <a href="tactics.php">Тактики</a>
        <a href="heroes.php">Герои</a>
        <a href="items.php">Предметы</a>
        <a href="mechanics.php">Механики</a>
        <a href="objects.php">Адаптация</a>
        <a href="feedback.php">Обратная связь</a>
        <p>&copy; 2026 Игровой справочник. ГБПОУИО «ИАТ». Курсовая работа</p>
    </footer>
</body>
</html>