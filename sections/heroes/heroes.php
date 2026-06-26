<?php
$pageTitle = 'Герои';
$currentPage = 'heroes';

// 12 героев - по 3 из каждого атрибута
$heroes = [
    // СИЛА
    ['id'=>1, 'name'=>'Pudge', 'attr'=>'strength', 'attr_name'=>'Сила', 'image_url'=>'https://ru.dotabuff.com/assets/heroes/pudge-d8673aca5ef38b0cff4826c8c7d22e09e8e09b44940a86859c8161553caefa8c.jpg'],
    ['id'=>2, 'name'=>'Centaur Warrunner', 'attr'=>'strength', 'attr_name'=>'Сила', 'image_url'=>'https://ru.dotabuff.com/assets/heroes/centaur-warrunner-57b9e5d75f9bd84e2651254d28cb50a63e91a3e8699095d16d1776cbff8d80c5.jpg'],
    ['id'=>3, 'name'=>'Wraith King', 'attr'=>'strength', 'attr_name'=>'Сила', 'image_url'=>'https://ru.dotabuff.com/assets/heroes/wraith-king-233a53f103c784de0f480cec4f18dd8490bd6da44357154e4717dfb31ffbb2b3.jpg'],
    
    // ЛОВКОСТЬ
    ['id'=>7, 'name'=>'Phantom Assassin', 'attr'=>'agility', 'attr_name'=>'Ловкость', 'image_url'=>'https://ru.dotabuff.com/assets/heroes/phantom-assassin-7654f46ff00ddaefca29b284c7a70705a0c305250560f0543eaa8539e3d848f8.jpg'],
    ['id'=>8, 'name'=>'Templar Assassin', 'attr'=>'agility', 'attr_name'=>'Ловкость', 'image_url'=>'https://ru.dotabuff.com/assets/heroes/templar-assassin-59dffc687571d6282dd71ab1e5eae130e3c3789b343d06832a0c170cd94b0322.jpg'],
    ['id'=>9, 'name'=>'Medusa', 'attr'=>'agility', 'attr_name'=>'Ловкость', 'image_url'=>'https://ru.dotabuff.com/assets/heroes/medusa-2d3f561c0312520e3d2b03808b0df8025ea98ec977d9a1701d67ed22e11e2565.jpg'],
    
    // ИНТЕЛЛЕКТ
    ['id'=>13, 'name'=>'Lion', 'attr'=>'intelligence', 'attr_name'=>'Интеллект', 'image_url'=>'https://ru.dotabuff.com/assets/heroes/lion-aa7c75a15844883581f25be8dca60efd72e7273a7dd8fa9c785c79f6bd7fdf42.jpg'],
    ['id'=>14, 'name'=>'Zeus', 'attr'=>'intelligence', 'attr_name'=>'Интеллект', 'image_url'=>'https://ru.dotabuff.com/assets/heroes/zeus-270c72957e96bab2b1ecab445e0f4f62454f61a722085c83c749909b90c3912a.jpg'],
    ['id'=>15, 'name'=>'Dark Willow', 'attr'=>'intelligence', 'attr_name'=>'Интеллект', 'image_url'=>'https://ru.dotabuff.com/assets/heroes/dark-willow-72b9b406f55446c501688c97f7954ac9c238bd48714cc322ca190d6fc1b6dbc2.jpg'],
    
    // УНИВЕРСАЛЫ
    ['id'=>19, 'name'=>'Marci', 'attr'=>'universal', 'attr_name'=>'Универсальный', 'image_url'=>'https://ru.dotabuff.com/assets/heroes/marci-9a0a2c4d90dc63116a5ba23439d97194915d3abd083cccc226a9b3c21fcdaa81.jpg'],
    ['id'=>20, 'name'=>'Techies', 'attr'=>'universal', 'attr_name'=>'Универсальный', 'image_url'=>'https://ru.dotabuff.com/assets/heroes/techies-e199ba8af1a4508668ec6cc16ecc96fe38231a4dd021a72e30d76d14e7e2cdb8.jpg'],
    ['id'=>21, 'name'=>'Batrider', 'attr'=>'universal', 'attr_name'=>'Универсальный', 'image_url'=>'https://ru.dotabuff.com/assets/heroes/batrider-2cea2260556b67fe7d44f4b325cf6673d55cb03d4b419f4e68ac9acab243c09d.jpg'],
];

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