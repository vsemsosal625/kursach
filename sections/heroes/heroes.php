<?php
$pageTitle = 'Герои';
$currentPage = 'heroes';

// Данные героев берутся из БД (таблица hero), редактируются в админ-панели.
require_once __DIR__ . '/../../config/heroes_data.php';
$heroes = array_values($heroesData);

// Получаем атрибут для фильтрации
$filterAttr = $_GET['attr'] ?? '';
$searchQuery = $_GET['search'] ?? '';

// Фильтрация по атрибуту
if ($filterAttr) {
    $heroes = array_filter($heroes, function($h) use ($filterAttr) {
        return $h['attr'] === $filterAttr;
    });
}

// Поиск
if ($searchQuery) {
    $heroes = array_filter($heroes, function($h) use ($searchQuery) {
        return stripos($h['name'], $searchQuery) !== false;
    });
}

// Эмодзи для атрибутов
$attrEmojis = [
    'strength' => '🔴',
    'agility' => '🟢',
    'intelligence' => '🔵',
    'universal' => '🟡'
];

require_once __DIR__ . '/../../includes/header.php';
?>

<body data-search-query="<?= strtolower(htmlspecialchars($searchQuery)) ?>">

<style>
/* Раздел «Герои» — оранжевый акцент (#f59e0b) под цвет раздела в шапке. */
/* Префикс .heroes-page перекрывает общие фиолетовые правила механик. */
.heroes-page .page-title { border-left-color: #f59e0b; }
.heroes-page .filter-btn { background: rgba(245,158,11,0.15); border-color: #f59e0b; color: #fbbf24; }
.heroes-page .filter-btn:hover { background: rgba(245,158,11,0.3); color: #fff; }
.heroes-page .filter-option:hover { background: rgba(245,158,11,0.2); color: #fff; }
.heroes-page .filter-option.active { background: rgba(245,158,11,0.3); color: #fbbf24; }
.heroes-page .search-input:focus { border-color: #f59e0b; }
</style>

<div class="page-header">
    <h1 class="page-title">🐉 Каталог героев</h1>
    <div class="controls">
        <div class="filter-group">
            <span class="filter-label">Сортировать по атрибуту:</span>
            <div class="filter-dropdown">
                <button class="filter-btn" onclick="toggleFilter()">
                    <span id="currentFilter"><?= $filterAttr ? $attrEmojis[$filterAttr] . ' ' . ['strength'=>'Сила','agility'=>'Ловкость','intelligence'=>'Интеллект','universal'=>'Универсальный'][$filterAttr] : '🌐 Все' ?></span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="filter-options" id="filterOptions">
                    <div class="filter-option <?= !$filterAttr ? 'active' : '' ?>" onclick="applyFilter('')">🌐 Все</div>
                    <div class="filter-option <?= $filterAttr === 'strength' ? 'active' : '' ?>" onclick="applyFilter('strength')">🔴 Сила</div>
                    <div class="filter-option <?= $filterAttr === 'agility' ? 'active' : '' ?>" onclick="applyFilter('agility')">🟢 Ловкость</div>
                    <div class="filter-option <?= $filterAttr === 'intelligence' ? 'active' : '' ?>" onclick="applyFilter('intelligence')">🔵 Интеллект</div>
                    <div class="filter-option <?= $filterAttr === 'universal' ? 'active' : '' ?>" onclick="applyFilter('universal')">🟡 Универсальный</div>
                </div>
            </div>
        </div>
        <div class="search-box">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="search-input" id="searchInput" placeholder="Поиск героев..." value="<?= htmlspecialchars($searchQuery) ?>">
        </div>
    </div>
</div>

<?php if (empty($filterAttr)): ?>
<div class="heroes-main-grid">
    <!-- ЛЕВАЯ СТОРОНА -->
    <div style="display: flex; flex-direction: column; gap: 40px;">
        <!-- СИЛА -->
        <div class="attribute-section" data-attr="strength">
            <h2 class="attribute-title attr-strength"><i class="fas fa-fist-raised" style="margin-right: 10px;"></i>СИЛА</h2>
            <div class="heroes-grid">
                <?php foreach (array_filter($heroes, fn($h) => $h['attr'] === 'strength') as $hero): ?>
                    <a href="<?= BASE_URL ?>/sections/heroes/hero.php?id=<?= $hero['id'] ?>" class="hero-card" data-name="<?= strtolower($hero['name']) ?>">
                        <div class="hero-avatar">
                            <?php if (!empty($hero['image_url'])): ?>
                                <img src="<?= htmlspecialchars($hero['image_url']) ?>" alt="<?= $hero['name'] ?>">
                            <?php else: ?>
                                <i class="fas fa-user hero-avatar-placeholder"></i>
                            <?php endif; ?>
                        </div>
                        <div class="hero-name"><?= $hero['name'] ?></div>
                        <span class="attr-badge badge-strength"><?= $hero['attr_name'] ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- ИНТЕЛЛЕКТ -->
        <div class="attribute-section" data-attr="intelligence">
            <h2 class="attribute-title attr-intelligence"><i class="fas fa-brain" style="margin-right: 10px;"></i>ИНТЕЛЛЕКТ</h2>
            <div class="heroes-grid">
                <?php foreach (array_filter($heroes, fn($h) => $h['attr'] === 'intelligence') as $hero): ?>
                    <a href="<?= BASE_URL ?>/sections/heroes/hero.php?id=<?= $hero['id'] ?>" class="hero-card" data-name="<?= strtolower($hero['name']) ?>">
                        <div class="hero-avatar">
                            <?php if (!empty($hero['image_url'])): ?>
                                <img src="<?= htmlspecialchars($hero['image_url']) ?>" alt="<?= $hero['name'] ?>">
                            <?php else: ?>
                                <i class="fas fa-user hero-avatar-placeholder"></i>
                            <?php endif; ?>
                        </div>
                        <div class="hero-name"><?= $hero['name'] ?></div>
                        <span class="attr-badge badge-intelligence"><?= $hero['attr_name'] ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- ПРАВАЯ СТОРОНА -->
    <div style="display: flex; flex-direction: column; gap: 40px;">
        <!-- ЛОВКОСТЬ -->
        <div class="attribute-section" data-attr="agility">
            <h2 class="attribute-title attr-agility"><i class="fas fa-bolt" style="margin-right: 10px;"></i>ЛОВКОСТЬ</h2>
            <div class="heroes-grid">
                <?php foreach (array_filter($heroes, fn($h) => $h['attr'] === 'agility') as $hero): ?>
                    <a href="<?= BASE_URL ?>/sections/heroes/hero.php?id=<?= $hero['id'] ?>" class="hero-card" data-name="<?= strtolower($hero['name']) ?>">
                        <div class="hero-avatar">
                            <?php if (!empty($hero['image_url'])): ?>
                                <img src="<?= htmlspecialchars($hero['image_url']) ?>" alt="<?= $hero['name'] ?>">
                            <?php else: ?>
                                <i class="fas fa-user hero-avatar-placeholder"></i>
                            <?php endif; ?>
                        </div>
                        <div class="hero-name"><?= $hero['name'] ?></div>
                        <span class="attr-badge badge-agility"><?= $hero['attr_name'] ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- УНИВЕРСАЛЫ -->
        <div class="attribute-section" data-attr="universal">
            <h2 class="attribute-title attr-universal"><i class="fas fa-star" style="margin-right: 10px;"></i>УНИВЕРСАЛЬНЫЕ</h2>
            <div class="heroes-grid">
                <?php foreach (array_filter($heroes, fn($h) => $h['attr'] === 'universal') as $hero): ?>
                    <a href="<?= BASE_URL ?>/sections/heroes/hero.php?id=<?= $hero['id'] ?>" class="hero-card" data-name="<?= strtolower($hero['name']) ?>">
                        <div class="hero-avatar">
                            <?php if (!empty($hero['image_url'])): ?>
                                <img src="<?= htmlspecialchars($hero['image_url']) ?>" alt="<?= $hero['name'] ?>">
                            <?php else: ?>
                                <i class="fas fa-user hero-avatar-placeholder"></i>
                            <?php endif; ?>
                        </div>
                        <div class="hero-name"><?= $hero['name'] ?></div>
                        <span class="attr-badge badge-universal"><?= $hero['attr_name'] ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<!-- ОТОБРАЖЕНИЕ ТОЛЬКО ВЫБРАННОГО АТРИБУТА -->
<div class="attribute-section" style="max-width: 800px; margin: 0 auto;">
    <h2 class="attribute-title attr-<?= $filterAttr ?>">
        <i class="fas fa-<?= $filterAttr === 'strength' ? 'fist-raised' : ($filterAttr === 'agility' ? 'bolt' : ($filterAttr === 'intelligence' ? 'brain' : 'star')) ?>" style="margin-right: 10px;"></i>
        <?= ['strength'=>'СИЛА','agility'=>'ЛОВКОСТЬ','intelligence'=>'ИНТЕЛЛЕКТ','universal'=>'УНИВЕРСАЛЬНЫЕ'][$filterAttr] ?>
    </h2>
    <div class="heroes-grid">
        <?php foreach ($heroes as $hero): ?>
            <a href="<?= BASE_URL ?>/sections/heroes/hero.php?id=<?= $hero['id'] ?>" class="hero-card" data-name="<?= strtolower($hero['name']) ?>">
                <div class="hero-avatar">
                    <?php if (!empty($hero['image_url'])): ?>
                        <img src="<?= htmlspecialchars($hero['image_url']) ?>" alt="<?= $hero['name'] ?>">
                    <?php else: ?>
                        <i class="fas fa-user hero-avatar-placeholder"></i>
                    <?php endif; ?>
                </div>
                <div class="hero-name"><?= $hero['name'] ?></div>
                <span class="attr-badge badge-<?= $hero['attr'] ?>"><?= $hero['attr_name'] ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>