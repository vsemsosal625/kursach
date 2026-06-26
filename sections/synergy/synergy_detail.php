<?php
// sections/synergy/synergy_detail.php — детальная карточка раздела «Синергия героев на линии»
require_once __DIR__ . '/../../config/init.php';
requireLogin();

$pdo = getDB();

$id   = $_GET['id'] ?? 0;
$from = $_GET['from'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM game_mechanic WHERE id_game_mechanic = ?");
$stmt->execute([$id]);
$synergy = $stmt->fetch();

if (!$synergy) { header('Location: ' . BASE_URL . '/sections/synergy/synergy.php'); exit; }

$stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND item_type = 'synergy' AND item_id = ?");
$stmt->execute([$_SESSION['user_id'], $id]);
$isFavorite = $stmt->fetch() ? true : false;

if ($from === 'favorites') {
    $backLink = BASE_URL . '/account/favorites.php';
    $backText = 'Назад к избранному';
} else {
    $backLink = BASE_URL . '/sections/synergy/synergy.php';
    $backText = 'Назад к разделу Синергия героев на линии';
}

$laneMatch = [];
preg_match('/Тип линии: (.+)/', $synergy['content'], $laneMatch);
$laneType = trim($laneMatch[1] ?? '');
$laneEmoji = $laneType === 'Легкая линия' ? '⚔️' : '🛡️';

$pageTitle = $synergy['title'];
$currentPage = '';

require_once __DIR__ . '/../../includes/header.php';
?>

<style>
.mechanic-detail .meta { display: flex; flex-wrap: wrap; gap: 15px; align-items: center; }
.mechanic-detail .lane-badge { display: inline-block; background: rgba(16,185,129,0.2); color: #6ee7b7; padding: 6px 16px; border-radius: 20px; font-size: 13px; font-weight: 600; }
</style>

<a href="<?= $backLink ?>" class="back-btn">
    <i class="fas fa-arrow-left"></i> <?= $backText ?>
</a>

<button id="favBtn" class="fav-btn" onclick="toggleFavorite('synergy', <?= $id ?>)" style="background: <?= $isFavorite ? 'rgba(251,191,36,0.3)' : 'rgba(251,191,36,0.15)' ?>;">
    <i class="fas <?= $isFavorite ? 'fa-star' : 'fa-bookmark' ?>"></i> <?= $isFavorite ? 'В избранном' : 'Добавить в избранное' ?>
</button>

<div class="mechanic-detail">
    <h1><?= htmlspecialchars($synergy['title']) ?></h1>
    <div class="meta">
        <span class="category-badge"><?= $laneEmoji ?> <?= htmlspecialchars($synergy['category']) ?></span>
        <?php if (!empty($laneType)): ?>
            <span class="lane-badge"><?= $laneEmoji ?> <?= htmlspecialchars($laneType) ?></span>
        <?php endif; ?>
        <span><i class="far fa-calendar"></i> <?= date('d.m.Y', strtotime($synergy['created_date'])) ?></span>
    </div>
    <div class="content"><?= nl2br(htmlspecialchars($synergy['content'])) ?></div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>