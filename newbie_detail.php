<?php
// newbie_detail.php — детальная карточка раздела «Руководство для новичков»
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) { header('Location: auth.php'); exit; }

require_once 'config/db.php';
$pdo = getDB();

$id   = $_GET['id'] ?? 0;
$from = $_GET['from'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM game_mechanic WHERE id_game_mechanic = ?");
$stmt->execute([$id]);
$mechanic = $stmt->fetch();

if (!$mechanic) { header('Location: newbie.php'); exit; }

$stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND item_type = 'newbie' AND item_id = ?");
$stmt->execute([$_SESSION['user_id'], $id]);
$isFavorite = $stmt->fetch() ? true : false;

if ($from === 'favorites') {
    $backLink = 'favorites.php';
    $backText = 'Назад к избранному';
} else {
    $backLink = 'newbie.php';
    $backText = 'Назад к разделу Руководство для новичков';
}

$categoryEmojis = [
    'Крипы' => '👾',
    'Игровые цели' => '🎯',
    'Командные постройки' => '🏰'
];

$pageTitle = $mechanic['title'];
$currentPage = '';

require_once 'includes/header.php';
?>

<a href="<?= $backLink ?>" class="back-btn">
    <i class="fas fa-arrow-left"></i> <?= $backText ?>
</a>

<button id="favBtn" class="fav-btn" onclick="toggleFavorite('newbie', <?= $id ?>)">
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

<?php require_once 'includes/footer.php'; ?>
