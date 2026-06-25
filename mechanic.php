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
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> | Игровой справочник</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <?php include 'includes/header.php'; ?>

    <div class="main-wrapper">
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

    <?php include 'includes/footer.php'; ?>
    <script src="js/script.js"></script>

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
</body>
</html>