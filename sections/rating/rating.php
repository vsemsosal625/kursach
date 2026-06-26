<?php
// sections/rating/rating.php — раздел «Рейтинг героев»
require_once __DIR__ . '/../../config/init.php';
requireLogin();

$pdo = getDB();

// Герои берутся из БД (таблица hero) — редактируются в админ-панели.
require_once __DIR__ . '/../../config/heroes_data.php';

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

require_once __DIR__ . '/../../includes/header.php';
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
        <a href="<?= BASE_URL ?>/sections/heroes/hero.php?id=<?= $hero['id'] ?>&from=rating" class="top-item">
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

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>