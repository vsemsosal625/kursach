<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) { header('Location: auth.php'); exit; }

require_once 'config/db.php';
$pdo = getDB();

$tacticId = $_GET['id'] ?? null;
if (!$tacticId) { header('Location: tactics.php'); exit; }

try {
    $stmt = $pdo->prepare("SELECT * FROM game_mechanic WHERE id_game_mechanic = ?");
    $stmt->execute([$tacticId]);
    $tactic = $stmt->fetch();
    if (!$tactic) { header('Location: tactics.php'); exit; }
} catch (Exception $e) {
    die("Ошибка загрузки: " . $e->getMessage());
}

// Проверяем, есть ли в избранном
$stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND item_type = 'mechanic' AND item_id = ?");
$stmt->execute([$_SESSION['user_id'], $tacticId]);
$isFavorite = $stmt->fetch() ? true : false;

// Кнопка «Назад» зависит от того, откуда пришли
$from = $_GET['from'] ?? '';
if ($from === 'favorites') {
    $backLink = 'favorites.php';
    $backText = 'Назад к избранному';
} else {
    $backLink = 'tactics.php';
    $backText = 'Назад к списку ролей';
}

$pageTitle = $tactic['title'];
$currentPage = '';

require_once 'includes/header.php';
?>

<style>
.tactic-back { display: inline-flex; align-items: center; gap: 8px; color: #fbbf24; text-decoration: none; margin-bottom: 20px; font-size: 14px; transition: color 0.2s; }
.tactic-back:hover { color: #f59e0b; }
.tactic-detail { background: linear-gradient(135deg, #1b2838 0%, #2a475e 100%); border: 1px solid #36414d; border-radius: 12px; padding: 40px; }
.tactic-detail h1 { color: #fff; font-size: 32px; margin-bottom: 15px; font-weight: 700; }
.tactic-detail .meta { color: #8f98a0; font-size: 14px; margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px solid #36414d; display: flex; align-items: center; gap: 15px; flex-wrap: wrap; }
.tactic-detail .category-badge { display: inline-block; background: rgba(245,158,11,0.2); color: #fbbf24; padding: 6px 16px; border-radius: 20px; font-size: 13px; font-weight: 600; }
.tactic-detail .content { color: #e0e0e0; font-size: 16px; line-height: 1.8; white-space: pre-wrap; }
.tactic-fav-btn { background: <?= $isFavorite ? 'rgba(251,191,36,0.3)' : 'rgba(251,191,36,0.15)' ?>; border: 2px solid #fbbf24; color: #fbbf24; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; margin: 0 0 20px 20px; transition: all 0.3s; font-size: 14px; }
.tactic-fav-btn:hover { background: rgba(251,191,36,0.4); color: #fff; }
</style>

<a href="<?= $backLink ?>" class="tactic-back"><i class="fas fa-arrow-left"></i> <?= $backText ?></a>
<button id="favBtn" class="tactic-fav-btn" onclick="toggleFavorite('mechanic', <?= $tacticId ?>)">
    <i class="fas <?= $isFavorite ? 'fa-star' : 'fa-bookmark' ?>"></i> <?= $isFavorite ? 'В избранном' : 'Добавить в избранное' ?>
</button>

<div class="tactic-detail">
    <h1><?= htmlspecialchars($tactic['title']) ?></h1>
    <div class="meta">
        <span class="category-badge"><i class="fas fa-tag me-2"></i><?= htmlspecialchars($tactic['category']) ?></span>
        <span><i class="far fa-calendar"></i> <?= !empty($tactic['created_date']) ? date('d.m.Y', strtotime($tactic['created_date'])) : '' ?></span>
    </div>
    <div class="content"><?= nl2br(htmlspecialchars($tactic['content'])) ?></div>
</div>

<?php require_once 'includes/footer.php'; ?>