<?php
require_once __DIR__ . '/../../config/init.php';
requireLogin();

$pageTitle = 'Предметы';
$currentPage = 'items';

// Данные предметов берутся из БД (таблица item), редактируются в админ-панели.
require_once __DIR__ . '/../../config/items_data.php';

// Сопоставление русской категории (в БД) с кодом фильтра
function itemCatCode($ru) {
    $map = ['Артефакт'=>'artifact', 'Оружие'=>'weapon', 'Расходники'=>'consumable', 'Поддержка'=>'support'];
    return $map[$ru] ?? 'artifact';
}

$items = [];
foreach ($itemsData as $it) {
    $items[] = [
        'id' => $it['id'],
        'name' => $it['name'],
        'category' => itemCatCode($it['category']),
        'category_name' => $it['category'],
        'image_url' => $it['image_url'] ?? '',
    ];
}

// Получаем категорию для фильтрации
$filterCategory = $_GET['category'] ?? '';
$searchQuery = $_GET['search'] ?? '';

// Фильтрация по категории
if ($filterCategory) {
    $items = array_filter($items, function($i) use ($filterCategory) {
        return $i['category'] === $filterCategory;
    });
}

// Поиск
if ($searchQuery) {
    $items = array_filter($items, function($i) use ($searchQuery) {
        return stripos($i['name'], $searchQuery) !== false;
    });
}

// Эмодзи для категорий
$catEmoji = [
    'artifact' => '👑',
    'weapon' => '⚔️',
    'consumable' => '🧪',
    'support' => '💚'
];

// Data-атрибут для подсветки при загрузке
$bodyAttrs = 'data-search-query="' . strtolower(htmlspecialchars($searchQuery)) . '"';

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <h1 class="page-title">💎 Каталог предметов</h1>
    <div class="controls">
        <div class="filter-group">
            <span class="filter-label">Сортировать по категории:</span>
            <div class="filter-dropdown">
                <button class="filter-btn" onclick="toggleFilter()">
                    <span id="currentFilter"><?= $filterCategory ? $catEmoji[$filterCategory] . ' ' . ['artifact'=>'Артефакты','weapon'=>'Оружие','consumable'=>'Расходники','support'=>'Поддержка'][$filterCategory] : '🌐 Все' ?></span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="filter-options" id="filterOptions">
                    <div class="filter-option <?= !$filterCategory ? 'active' : '' ?>" onclick="applyItemFilter('')">🌐 Все</div>
                    <div class="filter-option <?= $filterCategory === 'artifact' ? 'active' : '' ?>" onclick="applyItemFilter('artifact')">👑 Артефакты</div>
                    <div class="filter-option <?= $filterCategory === 'weapon' ? 'active' : '' ?>" onclick="applyItemFilter('weapon')">⚔️ Оружие</div>
                    <div class="filter-option <?= $filterCategory === 'consumable' ? 'active' : '' ?>" onclick="applyItemFilter('consumable')">🧪 Расходники</div>
                    <div class="filter-option <?= $filterCategory === 'support' ? 'active' : '' ?>" onclick="applyItemFilter('support')">💚 Поддержка</div>
                </div>
            </div>
        </div>
        <div class="search-box">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="search-input" id="searchInput" placeholder="Поиск предметов..." value="<?= htmlspecialchars($searchQuery) ?>">
        </div>
    </div>
</div>

<?php if (empty($filterCategory)): ?>
<div class="items-main-grid">
    <!-- ЛЕВАЯ СТОРОНА -->
    <div style="display: flex; flex-direction: column; gap: 40px;">
        <!-- АРТЕФАКТЫ -->
        <div class="category-section" data-category="artifact">
            <h2 class="category-title cat-artifact">👑 АРТЕФАКТЫ</h2>
            <div class="items-grid">
                <?php foreach (array_filter($items, fn($i) => $i['category'] === 'artifact') as $item): ?>
                    <a href="<?= BASE_URL ?>/sections/items/item.php?id=<?= $item['id'] ?>" class="item-card" data-name="<?= strtolower($item['name']) ?>">
                        <div class="item-avatar">
                            <?php if (!empty($item['image_url'])): ?>
                                <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= $item['name'] ?>">
                            <?php else: ?>
                                <i class="fas fa-gem item-avatar-placeholder"></i>
                            <?php endif; ?>
                        </div>
                        <div class="item-name"><?= $item['name'] ?></div>
                        <span class="cat-badge badge-artifact"><?= $item['category_name'] ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- РАСХОДНИКИ -->
        <div class="category-section" data-category="consumable">
            <h2 class="category-title cat-consumable">🧪 РАСХОДНИКИ</h2>
            <div class="items-grid">
                <?php foreach (array_filter($items, fn($i) => $i['category'] === 'consumable') as $item): ?>
                    <a href="<?= BASE_URL ?>/sections/items/item.php?id=<?= $item['id'] ?>" class="item-card" data-name="<?= strtolower($item['name']) ?>">
                        <div class="item-avatar">
                            <?php if (!empty($item['image_url'])): ?>
                                <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= $item['name'] ?>">
                            <?php else: ?>
                                <i class="fas fa-flask item-avatar-placeholder"></i>
                            <?php endif; ?>
                        </div>
                        <div class="item-name"><?= $item['name'] ?></div>
                        <span class="cat-badge badge-consumable"><?= $item['category_name'] ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- ПРАВАЯ СТОРОНА -->
    <div style="display: flex; flex-direction: column; gap: 40px;">
        <!-- ОРУЖИЕ -->
        <div class="category-section" data-category="weapon">
            <h2 class="category-title cat-weapon">⚔️ ОРУЖИЕ</h2>
            <div class="items-grid">
                <?php foreach (array_filter($items, fn($i) => $i['category'] === 'weapon') as $item): ?>
                    <a href="<?= BASE_URL ?>/sections/items/item.php?id=<?= $item['id'] ?>" class="item-card" data-name="<?= strtolower($item['name']) ?>">
                        <div class="item-avatar">
                            <?php if (!empty($item['image_url'])): ?>
                                <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= $item['name'] ?>">
                            <?php else: ?>
                                <i class="fas fa-sword item-avatar-placeholder"></i>
                            <?php endif; ?>
                        </div>
                        <div class="item-name"><?= $item['name'] ?></div>
                        <span class="cat-badge badge-weapon"><?= $item['category_name'] ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- ПОДДЕРЖКА -->
        <div class="category-section" data-category="support">
            <h2 class="category-title cat-support">💚 ПОДДЕРЖКА</h2>
            <div class="items-grid">
                <?php foreach (array_filter($items, fn($i) => $i['category'] === 'support') as $item): ?>
                    <a href="<?= BASE_URL ?>/sections/items/item.php?id=<?= $item['id'] ?>" class="item-card" data-name="<?= strtolower($item['name']) ?>">
                        <div class="item-avatar">
                            <?php if (!empty($item['image_url'])): ?>
                                <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= $item['name'] ?>">
                            <?php else: ?>
                                <i class="fas fa-hand-holding-heart item-avatar-placeholder"></i>
                            <?php endif; ?>
                        </div>
                        <div class="item-name"><?= $item['name'] ?></div>
                        <span class="cat-badge badge-support"><?= $item['category_name'] ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<!-- ОТОБРАЖЕНИЕ ТОЛЬКО ВЫБРАННОЙ КАТЕГОРИИ -->
<div class="category-section" style="max-width: 800px; margin: 0 auto;">
    <h2 class="category-title cat-<?= $filterCategory ?>">
        <?= $catEmoji[$filterCategory] ?> <?= ['artifact'=>'АРТЕФАКТЫ','weapon'=>'ОРУЖИЕ','consumable'=>'РАСХОДНИКИ','support'=>'ПОДДЕРЖКА'][$filterCategory] ?>
    </h2>
    <div class="items-grid">
        <?php foreach ($items as $item): ?>
            <a href="<?= BASE_URL ?>/sections/items/item.php?id=<?= $item['id'] ?>" class="item-card" data-name="<?= strtolower($item['name']) ?>">
                <div class="item-avatar">
                    <?php if (!empty($item['image_url'])): ?>
                        <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= $item['name'] ?>">
                    <?php else: ?>
                        <i class="fas fa-gem item-avatar-placeholder"></i>
                    <?php endif; ?>
                </div>
                <div class="item-name"><?= $item['name'] ?></div>
                <span class="cat-badge badge-<?= $item['category'] ?>"><?= $item['category_name'] ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>