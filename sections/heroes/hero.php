<?php
require_once __DIR__ . '/../../config/init.php';
requireLogin();

$pageTitle = 'Герой';
$currentPage = 'heroes';

$pdo = getDB();
require_once __DIR__ . '/../../config/heroes_data.php';

$id = $_GET['id'] ?? 1;
$hero = $heroesData[$id] ?? null;
if (!$hero) { header('Location: ' . BASE_URL . '/sections/heroes/heroes.php'); exit; }

try {
    $stmt = $pdo->prepare("INSERT INTO hero_views (hero_id, views) VALUES (?, 1) ON DUPLICATE KEY UPDATE views = views + 1");
    $stmt->execute([$id]);
} catch (Exception $e) {
}

$isFavorite = false;
if (isLoggedIn()) {
    $stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND item_type = 'hero' AND item_id = ?");
    $stmt->execute([$_SESSION['user_id'], $id]);
    $isFavorite = $stmt->fetch() ? true : false;
}

$from = $_GET['from'] ?? '';
if ($from === 'rating') {
    $backUrl = BASE_URL . '/sections/rating/rating.php';
    $backText = 'Назад к рейтингу';
} elseif ($from === 'favorites') {
    $backUrl = BASE_URL . '/account/favorites.php';
    $backText = 'Назад к избранному';
} else {
    $backUrl = BASE_URL . '/sections/heroes/heroes.php';
    $backText = 'Назад к списку героев';
}

require_once __DIR__ . '/../../includes/header.php';
?>

<style>
.detail-top-bar { display:flex; align-items:center; justify-content:flex-start; gap:12px; flex-wrap:wrap; margin-bottom:20px; }
.detail-top-bar .back-btn { margin-bottom:0; }
.detail-top-bar .fav-btn { margin:0; }
</style>

<div id="top">
    <div class="detail-top-bar">
        <a href="<?= $backUrl ?>" class="back-btn">
            <i class="fas fa-arrow-left"></i><?= $backText ?>
        </a>
        <button id="favBtn" class="fav-btn" onclick="toggleFavorite('hero', <?= $id ?>)" style="background: <?= $isFavorite ? 'rgba(251,191,36,0.3)' : 'rgba(251,191,36,0.15)' ?>;">
            <i class="fas <?= $isFavorite ? 'fa-star' : 'fa-bookmark' ?>"></i> <?= $isFavorite ? 'В избранном' : 'Добавить в избранное' ?>
        </button>
    </div>

    <div class="hero-detail-layout">
        <!-- Левая часть: Аватар + Имя + Советы -->
        <div class="hero-left">

            <div class="hero-avatar-box">
                <?php if (!empty($hero['image_url'])): ?>
                    <img src="<?= htmlspecialchars($hero['image_url']) ?>" alt="<?= htmlspecialchars($hero['name']) ?>">
                <?php else: ?>
                    <i class="fas fa-user hero-avatar-placeholder"></i>
                <?php endif; ?>
            </div>

            <div class="hero-name-row">
    <span class="hero-name-text"><?= htmlspecialchars(explode(' — ', $hero['name'])[0]) ?></span>
    <span class="hero-attr-badge attr-<?= $hero['attr'] ?>"><?= $hero['attr_name'] ?></span>
</div>

            <!-- Блок СОВЕТЫ -->
            <div class="hero-tips-box">
                <h4><i class="fas fa-lightbulb"></i>Советы</h4>
                <p><?= htmlspecialchars($hero['tips']) ?></p>
            </div>
        </div>

        <!-- Правая часть: Блоки информации -->
        <div class="hero-right">
            <div class="info-block">
                <h4>Основной атрибут</h4>
                <p><?= $hero['attr_name'] ?></p>
            </div>
            <div class="info-block">
                <h4>Тип атаки</h4>
                <p><?= $hero['attack'] ?></p>
            </div>
            <div class="info-block">
                <h4>Игровые роли (позиции)</h4>
                <p><?= $hero['roles'] ?></p>
            </div>
            <div class="info-block">
                <h4>Характеристики</h4>
                <div class="stats-grid">
                    <div class="stat-item">
                        <span>Сила</span>
                        <strong><?= $hero['str'] ?></strong>
                        <span class="stat-growth">+<?= $hero['s_gain'] ?> за уровень</span>
                    </div>
                    <div class="stat-item">
                        <span>Ловкость</span>
                        <strong><?= $hero['agi'] ?></strong>
                        <span class="stat-growth">+<?= $hero['a_gain'] ?> за уровень</span>
                    </div>
                    <div class="stat-item">
                        <span>Интеллект</span>
                        <strong><?= $hero['int'] ?></strong>
                        <span class="stat-growth">+<?= $hero['i_gain'] ?> за уровень</span>
                    </div>
                </div>
            </div>
            <div class="info-block">
                <h4>Способности</h4>
                <ul class="abilities-list">
                    <?php foreach ($hero['abilities'] as $abil): ?>
                        <li><?= htmlspecialchars($abil) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- Нижний блок: Описание героя -->
    <div class="hero-lore-block">
        <h3><i class="fas fa-info-circle"></i>Описание героя</h3>
        <p><?= nl2br(htmlspecialchars($hero['description'])) ?></p>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>