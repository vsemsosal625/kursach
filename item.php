<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) { header('Location: auth.php'); exit; }

$pageTitle = 'Предмет';
$currentPage = 'items';

require_once 'config/items_data.php';

$id = $_GET['id'] ?? 1;
$item = $itemsData[$id] ?? null;
if (!$item) { header('Location: items.php'); exit; }

require_once 'config/db.php';
$pdo = getDB();
$stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND item_type = 'item' AND item_id = ?");
$stmt->execute([$_SESSION['user_id'], $id]);
$isFavorite = $stmt->fetch() ? true : false;

$from = $_GET['from'] ?? '';
if ($from === 'favorites') {
    $backUrl = 'favorites.php';
    $backText = 'Назад к избранному';
} else {
    $backUrl = 'items.php';
    $backText = 'Назад к списку предметов';
}

// Цвет категории
$categoryColors = [
    'Артефакт'   => ['bg' => 'rgba(245,158,11,0.2)',  'color' => '#fcd34d', 'border' => '#f59e0b'],
    'Оружие'     => ['bg' => 'rgba(239,68,68,0.2)',   'color' => '#fca5a5', 'border' => '#ef4444'],
    'Расходники' => ['bg' => 'rgba(16,185,129,0.2)',  'color' => '#6ee7b7', 'border' => '#10b981'],
    'Поддержка'  => ['bg' => 'rgba(59,130,246,0.2)',  'color' => '#93c5fd', 'border' => '#3b82f6'],
];
$cc = $categoryColors[$item['category']] ?? ['bg' => 'rgba(6,182,212,0.2)', 'color' => '#67e8f9', 'border' => '#06b6d4'];

require_once 'includes/header.php';
?>

<div id="top">

    <a href="<?= $backUrl ?>" class="back-btn"><i class="fas fa-arrow-left"></i><?= $backText ?></a>

    <div class="hero-detail-layout">
        <!-- Левая часть: Аватар + Имя + Советы -->
        <div class="hero-left">
            <div class="hero-avatar-box">
                <?php if (!empty($item['image_url'])): ?>
                    <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                <?php else: ?>
                    <i class="fas fa-gem hero-avatar-placeholder"></i>
                <?php endif; ?>
            </div>

            <div class="hero-name-row">
                <span class="hero-name-text"><?= htmlspecialchars($item['name']) ?></span>
                <span class="hero-attr-badge" style="background: <?= $cc['bg'] ?>; color: <?= $cc['color'] ?>; border: 1px solid <?= $cc['border'] ?>;"><?= htmlspecialchars($item['category']) ?></span>
            </div>

            <button id="favBtn" class="fav-btn" onclick="toggleFavorite('item', <?= $id ?>)" style="background: <?= $isFavorite ? 'rgba(251,191,36,0.3)' : 'rgba(251,191,36,0.15)' ?>;">
                <i class="fas <?= $isFavorite ? 'fa-star' : 'fa-bookmark' ?>"></i> <?= $isFavorite ? 'В избранном' : 'Добавить в избранное' ?>
            </button>

            <!-- Блок СОВЕТЫ -->
            <div class="hero-tips-box">
                <h4><i class="fas fa-lightbulb"></i>Советы</h4>
                <p><?= htmlspecialchars($item['tips']) ?></p>
            </div>
        </div>

        <!-- Правая часть: Блоки информации -->
        <div class="hero-right">
            <div class="info-block">
                <h4>Стоимость в золоте</h4>
                <p><?= htmlspecialchars($item['cost']) ?></p>
            </div>
            <div class="info-block">
                <h4>Категория предмета</h4>
                <p><?= htmlspecialchars($item['category']) ?></p>
            </div>
            <div class="info-block">
                <h4>Бонусы к характеристикам</h4>
                <p><?= htmlspecialchars($item['bonuses']) ?></p>
            </div>
            <div class="info-block">
                <h4>Составные части</h4>
                <p><?= htmlspecialchars($item['components']) ?></p>
            </div>
            <div class="info-block">
                <h4>Активные и пассивные эффекты</h4>
                <p><?= htmlspecialchars($item['effects']) ?></p>
            </div>
            <div class="info-block">
                <h4>Силен против</h4>
                <p><?= htmlspecialchars($item['strong_against']) ?></p>
            </div>
        </div>
    </div>

    <!-- Нижний блок: Описание предмета -->
    <div class="hero-lore-block">
        <h3><i class="fas fa-info-circle"></i>Информация о предмете</h3>
        <p><?= nl2br(htmlspecialchars($item['description'])) ?></p>
    </div>

</div>

<?php require_once 'includes/footer.php'; ?>