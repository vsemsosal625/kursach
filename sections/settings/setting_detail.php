<?php
// sections/settings/setting_detail.php — детальная карточка раздела «Оптимальные настройки игры»
require_once __DIR__ . '/../../config/init.php';
requireLogin();

$pdo = getDB();

$id   = $_GET['id'] ?? 0;
$from = $_GET['from'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM game_mechanic WHERE id_game_mechanic = ?");
$stmt->execute([$id]);
$setting = $stmt->fetch();

if (!$setting) { header('Location: ' . BASE_URL . '/sections/settings/settings.php'); exit; }

$stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND item_type = 'setting' AND item_id = ?");
$stmt->execute([$_SESSION['user_id'], $id]);
$isFavorite = $stmt->fetch() ? true : false;

if ($from === 'favorites') {
    $backLink = BASE_URL . '/account/favorites.php';
    $backText = 'Назад к избранному';
} else {
    $backLink = BASE_URL . '/sections/settings/settings.php';
    $backText = 'Назад к разделу Оптимальные настройки игры';
}

$categoryEmojis = [
    'Способности' => '✨',
    'Предметы' => '🎒',
    'Курьер' => '📦',
    'Автоатака' => '⚔️',
    'Камера' => '📷',
    'Прочее' => '⚙️'
];

$pageTitle = $setting['title'];
$currentPage = '';

require_once __DIR__ . '/../../includes/header.php';
?>

<a href="<?= $backLink ?>" class="back-btn">
    <i class="fas fa-arrow-left"></i> <?= $backText ?>
</a>

<button id="favBtn" class="fav-btn" onclick="toggleFavorite('setting', <?= $id ?>)" style="background: <?= $isFavorite ? 'rgba(251,191,36,0.3)' : 'rgba(251,191,36,0.15)' ?>;">
    <i class="fas <?= $isFavorite ? 'fa-star' : 'fa-bookmark' ?>"></i> <?= $isFavorite ? 'В избранном' : 'Добавить в избранное' ?>
</button>

<div class="mechanic-detail">
    <h1><?= htmlspecialchars($setting['title']) ?></h1>
    <div class="meta">
        <span class="category-badge">
            <?= $categoryEmojis[$setting['category']] ?? '⚙️' ?> <?= htmlspecialchars($setting['category']) ?>
        </span>
        <span><i class="far fa-calendar"></i> <?= date('d.m.Y', strtotime($setting['created_date'])) ?></span>
    </div>
    <div class="content"><?= nl2br(htmlspecialchars($setting['content'])) ?></div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>