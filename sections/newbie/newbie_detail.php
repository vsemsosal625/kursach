<?php
// sections/newbie/newbie_detail.php — детальная карточка раздела «Руководство для новичков»
require_once __DIR__ . '/../../config/init.php';
requireLogin();

$pdo = getDB();

$id   = $_GET['id'] ?? 0;
$from = $_GET['from'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM game_mechanic WHERE id_game_mechanic = ?");
$stmt->execute([$id]);
$mechanic = $stmt->fetch();

if (!$mechanic) { header('Location: ' . BASE_URL . '/sections/newbie/newbie.php'); exit; }

$stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND item_type = 'newbie' AND item_id = ?");
$stmt->execute([$_SESSION['user_id'], $id]);
$isFavorite = $stmt->fetch() ? true : false;

if ($from === 'favorites') {
    $backLink = BASE_URL . '/account/favorites.php';
    $backText = 'Назад к избранному';
} else {
    $backLink = BASE_URL . '/sections/newbie/newbie.php';
    $backText = 'Назад к разделу Руководство для новичков';
}

$categoryEmojis = [
    'Крипы' => '👾',
    'Игровые цели' => '🎯',
    'Командные постройки' => '🏰'
];

$pageTitle = $mechanic['title'];
$currentPage = '';

require_once __DIR__ . '/../../includes/header.php';
?>

<a href="<?= $backLink ?>" class="back-btn">
    <i class="fas fa-arrow-left"></i> <?= $backText ?>
</a>

<button id="favBtn" class="fav-btn" onclick="toggleFavorite('newbie', <?= $id ?>)" style="background: <?= $isFavorite ? 'rgba(251,191,36,0.3)' : 'rgba(251,191,36,0.15)' ?>;">
    <i class="fas <?= $isFavorite ? 'fa-star' : 'fa-bookmark' ?>"></i> <?= $isFavorite ? 'В избранном' : 'Добавить в избранное' ?>
</button>

<div class="mechanic-detail">
    <h1><?= htmlspecialchars($mechanic['title']) ?></h1>
    <div class="meta">
        <span class="category-badge">
            <?= $categoryEmojis[$mechanic['category']] ?? '' ?> <?= htmlspecialchars($mechanic['category']) ?>
        </span>
        <span><i class="far fa-calendar"></i> <?= date('d.m.Y', strtotime($mechanic['created_date'])) ?></span>
    </div>
    <div class="content"><?= nl2br(htmlspecialchars($mechanic['content'])) ?></div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>