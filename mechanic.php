<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) { header('Location: auth.php'); exit; }

require_once 'config/db.php';
$pdo = getDB();

$id = $_GET['id'] ?? 0;
$category = $_GET['category'] ?? '';
$from = $_GET['from'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM game_mechanic WHERE id_game_mechanic = ?");
$stmt->execute([$id]);
$mechanic = $stmt->fetch();
if (!$mechanic) { header('Location: mechanics.php'); exit; }

$stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND item_type = 'mechanic' AND item_id = ?");
$stmt->execute([$_SESSION['user_id'], $id]);
$isFavorite = $stmt->fetch() ? true : false;

if ($from === 'favorites') {
    $backLink = 'favorites.php';
    $backText = 'Назад к избранному';
} else {
    $validTacticsCategories = ['Основа (1-3 позиция)', 'Поддержка (4-5 позиция)'];
    $mechanicCategory = $mechanic['category'] ?? '';
    $backLink = in_array($mechanicCategory, $validTacticsCategories) ? 'tactics.php' : 'mechanics.php';
    $backText = in_array($mechanicCategory, $validTacticsCategories) 
        ? 'Назад к разделу Функциональные роли игроков' 
        : 'Назад к списку механик';
}

$categoryEmojis = [
    'Виды контроля' => '🎯',
    'Типы урона' => '💥',
    'Защитные механики' => '🛡️',
    'Основа (1-3 позиция)' => '🛡️',
    'Поддержка (4-5 позиция)' => '💚'
];

$pageTitle = htmlspecialchars($mechanic['title']);
$currentPage = 'mechanics'; // Чтобы в шапке раздел светился активным

require_once 'includes/header.php';
?>

<style>
/* Фиолетовая подсветка активного раздела «Игровые механики» в шапке */
.mechanics-page .nav-btn.active-page { background: rgba(139,92,246,0.15); color: #fff; }
</style>

<div id="top">
    <a href="<?= $backLink ?><?= $category && $from !== 'favorites' ? '?category=' . urlencode($category) : '' ?>" class="back-btn">
        <i class="fas fa-arrow-left"></i><?= $backText ?>
    </a>

    <button id="favBtn" class="fav-btn" onclick="toggleFavorite('mechanic', <?= $id ?>)" style="background: <?= $isFavorite ? 'rgba(251,191,36,0.3)' : 'rgba(251,191,36,0.15)' ?>;">
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
</div>

<script>
    function toggleFavorite(type, id) {
        const formData = new FormData();
        formData.append('item_type', type);
        formData.append('item_id', id);

        fetch('add_favorite.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const btn = document.getElementById('favBtn');
                if (data.action === 'added') {
                    btn.innerHTML = '<i class="fas fa-star"></i> В избранном';
                    btn.style.background = 'rgba(251,191,36,0.3)';
                } else {
                    btn.innerHTML = '<i class="fas fa-bookmark"></i> Добавить в избранное';
                    btn.style.background = 'rgba(251,191,36,0.15)';
                }
            }
        })
        .catch(err => console.error('Ошибка:', err));
    }
</script>

<?php require_once 'includes/footer.php'; ?>
