<?php
// rating.php — раздел «Рейтинг героев»
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) { header('Location: auth.php'); exit; }

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

foreach ($heroesData as $hid => &$hero) {
    $hero['views'] = $viewsData[$hid] ?? 0;
}
unset($hero);

usort($heroesData, function ($a, $b) { return $b['views'] - $a['views']; });
$topHeroes = $heroesData;

$pageTitle = 'Рейтинг героев';
$currentPage = '';

require_once 'includes/header.php';
?>

<style>
/* Раздел «Рейтинг героев» — золотой акцент */
.page-title { border-left-color: #fbbf24; }
.top-list { margin-top: 20px; }
.top-item { display: grid; grid-template-columns: 80px 120px 1fr 100px; align-items: center; gap: 20px; background: linear-gradient(135deg, #1b2838 0%, #2a475e 100%); border: 1px solid #36414d; border-radius: 12px; padding: 20px 25px; margin-bottom: 15px; transition: all 0.3s; text-decoration: none; color: inherit; }
.top-item:hover { transform: translateX(10px); border-color: #fbbf24; box-shadow: 0 8px 25px rgba(251,191,36,0.2); }
.rank-badge { width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 28px; font-weight: 700; background: #36414d; color: #e0e0e0; }
.rank-1 { background: linear-gradient(135deg, #fbbf24, #f59e0b); color: #000; box-shadow: 0 0 20px rgba(251,191,36,0.5); }
.rank-2 { background: linear-gradient(135deg, #9ca3af, #6b7280); color: #fff; }
.rank-3 { background: linear-gradient(135deg, #d97706, #92400e); color: #fff; }
.hero-image { width: 100px; height: 60px; background: #0f1419; border-radius: 8px; display: flex; align-items: center; justify-content: center; overflow: hidden; border: 2px solid #36414d; }
.hero-image img { width: 100%; height: 100%; object-fit: cover; }
.hero-image i { font-size: 32px; color: #3b82f6; }
.hero-info h3 { color: #fff; font-size: 20px; font-weight: 600; margin-bottom: 5px; }
.hero-info .attr { color: #8f98a0; font-size: 14px; }
.views-count { text-align: center; background: rgba(251,191,36,0.15); border: 1px solid #fbbf24; border-radius: 8px; padding: 10px; }
.views-count .number { color: #fbbf24; font-size: 24px; font-weight: 700; display: block; }
.views-count .label { color: #8f98a0; font-size: 11px; }
@media (max-width: 768px) {
    .top-item { grid-template-columns: 60px 80px 1fr 80px; gap: 10px; padding: 15px; }
    .rank-badge { width: 45px; height: 45px; font-size: 20px; }
    .hero-image { width: 70px; height: 45px; }
}
</style>

<div class="page-header">
    <h1 class="page-title">🏆 Рейтинг героев</h1>
</div>

<div class="top-list">
    <?php foreach ($topHeroes as $rank => $hero): ?>
        <a href="hero.php?id=<?= $hero['id'] ?>&from=rating" class="top-item">
            <div class="rank-badge rank-<?= $rank + 1 ?>"><?= $rank + 1 ?></div>
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

<?php require_once 'includes/footer.php'; ?>
