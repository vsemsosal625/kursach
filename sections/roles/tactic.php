<?php
// sections/roles/tactic.php — детальная карточка раздела «Функциональные роли игроков»
require_once __DIR__ . '/../../config/init.php';
requireLogin();

$pdo = getDB();

$id   = $_GET['id'] ?? 0;
$from = $_GET['from'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM game_mechanic WHERE id_game_mechanic = ?");
$stmt->execute([$id]);
$tactic = $stmt->fetch();
if (!$tactic) { header('Location: ' . BASE_URL . '/sections/roles/tactics.php'); exit; }

$isFavorite = false;
if (isLoggedIn()) {
    $stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND item_type = 'mechanic' AND item_id = ?");
    $stmt->execute([$_SESSION['user_id'], $id]);
    $isFavorite = $stmt->fetch() ? true : false;
}

if ($from === 'favorites') {
    $backLink = BASE_URL . '/account/favorites.php';
    $backText = 'Назад к избранному';
} else {
    $backLink = BASE_URL . '/sections/roles/tactics.php';
    $backText = 'Назад к разделу Функциональные роли игроков';
}

$categoryEmojis = [
    'Основа (1-3 позиция)' => '🛡️',
    'Поддержка (4-5 позиция)' => '💚',
];

$pageTitle = $tactic['title'];
$currentPage = '';

require_once __DIR__ . '/../../includes/header.php';
?>

<style>
/* Детальная карточка роли — янтарный акцент */
.back-btn { color: #fbbf24; }
.back-btn:hover { color: #f59e0b; }
.mechanic-detail .category-badge { background: rgba(245,158,11,0.2); color: #fbbf24; }
</style>

<div id="top">
    <a href="<?= $backLink ?>" class="back-btn"><i class="fas fa-arrow-left"></i><?= $backText ?></a>

    <button id="favBtn" class="fav-btn" onclick="toggleFavorite('mechanic', <?= $id ?>)" style="background: <?= $isFavorite ? 'rgba(251,191,36,0.3)' : 'rgba(251,191,36,0.15)' ?>;">
        <i class="fas <?= $isFavorite ? 'fa-star' : 'fa-bookmark' ?>"></i> <?= $isFavorite ? 'В избранном' : 'Добавить в избранное' ?>
    </button>

    <div class="mechanic-detail">
        <h1><?= htmlspecialchars($tactic['title']) ?></h1>
        <div class="meta">
            <span class="category-badge"><?= $categoryEmojis[$tactic['category']] ?? '🎯' ?> <?= htmlspecialchars($tactic['category']) ?></span>
            <span><i class="far fa-calendar"></i> <?= !empty($tactic['created_date']) ? date('d.m.Y', strtotime($tactic['created_date'])) : '' ?></span>
        </div>
        <div class="content"><?= nl2br(htmlspecialchars($tactic['content'])) ?></div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
