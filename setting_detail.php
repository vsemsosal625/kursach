<?php
// setting_detail.php — детальная карточка раздела «Оптимальные настройки игры»
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) { header('Location: auth.php'); exit; }

require_once 'config/db.php';
$pdo = getDB();

$id   = $_GET['id'] ?? 0;
$from = $_GET['from'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM game_mechanic WHERE id_game_mechanic = ?");
$stmt->execute([$id]);
$setting = $stmt->fetch();

if (!$setting) { header('Location: settings.php'); exit; }

$stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND item_type = 'setting' AND item_id = ?");
$stmt->execute([$_SESSION['user_id'], $id]);
$isFavorite = $stmt->fetch() ? true : false;

if ($from === 'favorites') {
    $backLink = 'favorites.php';
    $backText = 'Назад к избранному';
} else {
    $backLink = 'settings.php';
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

require_once 'includes/header.php';
?>

<a href="<?= $backLink ?>" class="back-btn">
    <i class="fas fa-arrow-left"></i> <?= $backText ?>
</a>

<button id="favBtn" class="fav-btn" onclick="toggleFavorite('setting', <?= $id ?>)">
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

<?php require_once 'includes/footer.php'; ?>
