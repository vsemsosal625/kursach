<?php
// object.php — детальная карточка раздела «Адаптация и расчет времени»
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) { header('Location: auth.php'); exit; }

require_once 'config/db.php';
$pdo = getDB();

$id   = $_GET['id'] ?? 0;
$from = $_GET['from'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM game_mechanic WHERE id_game_mechanic = ?");
$stmt->execute([$id]);
$object = $stmt->fetch();
if (!$object) { header('Location: objects.php'); exit; }

$stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND item_type = 'object' AND item_id = ?");
$stmt->execute([$_SESSION['user_id'], $id]);
$isFavorite = $stmt->fetch() ? true : false;

if ($from === 'favorites') {
    $backLink = 'favorites.php';
    $backText = 'Назад к избранному';
} else {
    $backLink = 'objects.php';
    $backText = 'Назад к разделу Адаптация и расчет времени';
}

$pageTitle = $object['title'];
$currentPage = '';

require_once 'includes/header.php';
?>

<style>
/* Детальная карточка объекта — зелёный акцент, кнопка избранного янтарная */
.back-btn { color: #6ee7b7; }
.back-btn:hover { color: #10b981; }
.object-detail { background: linear-gradient(135deg, #1b2838 0%, #2a475e 100%); border: 1px solid #36414d; border-radius: 12px; padding: 40px; }
.object-detail h1 { color: #fff; font-size: 32px; margin-bottom: 15px; font-weight: 700; }
.object-detail .meta { color: #8f98a0; font-size: 14px; margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px solid #36414d; display: flex; align-items: center; gap: 15px; flex-wrap: wrap; }
.object-detail .category-badge { display: inline-block; background: rgba(16,185,129,0.2); color: #6ee7b7; padding: 6px 16px; border-radius: 20px; font-size: 13px; font-weight: 600; }
.object-detail .content { color: #e0e0e0; font-size: 16px; line-height: 1.8; white-space: pre-wrap; }
</style>

<div id="top">
    <a href="<?= $backLink ?>" class="back-btn"><i class="fas fa-arrow-left"></i><?= $backText ?></a>

    <button id="favBtn" class="fav-btn" onclick="toggleFavorite('object', <?= $id ?>)" style="background: <?= $isFavorite ? 'rgba(251,191,36,0.3)' : 'rgba(251,191,36,0.15)' ?>;">
        <i class="fas <?= $isFavorite ? 'fa-star' : 'fa-bookmark' ?>"></i> <?= $isFavorite ? 'В избранном' : 'Добавить в избранное' ?>
    </button>

    <div class="object-detail">
        <h1><?= htmlspecialchars($object['title']) ?></h1>
        <div class="meta">
            <span class="category-badge"><i class="fas fa-clock me-2"></i><?= htmlspecialchars($object['category']) ?></span>
            <span><i class="far fa-calendar"></i> <?= !empty($object['created_date']) ? date('d.m.Y', strtotime($object['created_date'])) : '' ?></span>
        </div>
        <div class="content"><?= nl2br(htmlspecialchars($object['content'])) ?></div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
