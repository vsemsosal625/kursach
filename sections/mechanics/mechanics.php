<?php
require_once __DIR__ . '/../../config/init.php';
requireLogin();

$pdo = getDB();

$searchQuery = $_GET['search'] ?? '';
$sortBy = $_GET['sort'] ?? 'name';
$categoryFilter = $_GET['category'] ?? '';
$validCategories = ['Виды контроля', 'Типы урона', 'Защитные механики'];

$sql = "SELECT * FROM game_mechanic WHERE category IN ('Виды контроля', 'Типы урона', 'Защитные механики')";
$params = [];

if (!empty($categoryFilter) && in_array($categoryFilter, $validCategories)) {
    $sql .= " AND category = ?";
    $params[] = $categoryFilter;
}

if ($searchQuery) {
    $sql .= " AND title LIKE ?";
    $params[] = "%$searchQuery%";
}

switch ($sortBy) {
    case 'name': $sql .= " ORDER BY title ASC"; break;
    case 'date': $sql .= " ORDER BY created_date DESC"; break;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$mechanics = $stmt->fetchAll();

$groupedMechanics = [];
foreach ($mechanics as $m) {
    if (isset($m['category'])) $groupedMechanics[$m['category']][] = $m;
}

$categoryOrder = (!empty($categoryFilter) && isset($groupedMechanics[$categoryFilter])) ? [$categoryFilter] : $validCategories;

$categoryEmojis = [
    'Виды контроля' => '🎯',
    'Типы урона' => '💥',
    'Защитные механики' => '🛡️'
];

$pageTitle = 'Игровые механики';
$currentPage = 'mechanics'; // Чтобы в шапке раздел светился активным

require_once __DIR__ . '/../../includes/header.php';
?>

<style>
/* Фиолетовая подсветка активного раздела «Игровые механики» в шапке */
.mechanics-page .nav-btn.active-page { background: rgba(139,92,246,0.15); color: #fff; }
</style>

<div class="page-header">
    <h1 class="page-title">⚡ <?= $pageTitle ?></h1>
    <div class="controls">
        <div class="filter-group">
            <span class="filter-label">Категория:</span>
            <div class="filter-dropdown">
                <button class="filter-btn" onclick="toggleMechanicFilter()">
                    <span id="currentFilterText"><?= !empty($categoryFilter) ? ($categoryEmojis[$categoryFilter] ?? '') . ' ' . htmlspecialchars($categoryFilter) : '🌐 Все' ?></span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="filter-options" id="filterOptions">
                    <div class="filter-option <?= empty($categoryFilter) ? 'active' : '' ?>" onclick="applyMechanicFilter('')">🌐 Все категории</div>
                    <?php foreach ($categoryEmojis as $cat => $emoji): ?>
                        <div class="filter-option <?= $categoryFilter === $cat ? 'active' : '' ?>" onclick="applyMechanicFilter('<?= htmlspecialchars($cat) ?>')">
                            <?= $emoji ?> <?= htmlspecialchars($cat) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="filter-group">
            <span class="filter-label">Сортировка:</span>
            <div class="sort-dropdown">
                <button class="sort-btn" onclick="toggleSort()">
                    <span id="currentSortText"><?= $sortBy === 'name' ? '📝 По названию' : '📅 По дате' ?></span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="sort-options" id="sortOptions">
                    <div class="sort-option <?= $sortBy === 'name' ? 'active' : '' ?>" onclick="applySort('name')">📝 По названию</div>
                    <div class="sort-option <?= $sortBy === 'date' ? 'active' : '' ?>" onclick="applySort('date')">📅 По дате</div>
                </div>
            </div>
        </div>
        <div class="search-box">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="search-input" id="searchInputMechanics" placeholder="Поиск механик..." value="<?= htmlspecialchars($searchQuery) ?>">
        </div>
    </div>
</div>

<?php if (empty($mechanics)): ?>
    <div class="no-results">
        <i class="fas fa-search"></i>
        <p>Механики не найдены</p>
    </div>
<?php else: ?>
    <div class="mechanics-sections">
        <?php foreach ($categoryOrder as $cat): ?>
            <?php if (!isset($groupedMechanics[$cat])) continue; ?>
            <div class="mechanics-category-block">
                <h2 class="category-heading">
                    <?= $categoryEmojis[$cat] ?> <?= htmlspecialchars($cat) ?>
                </h2>
                <div class="mechanics-grid">
                    <?php foreach ($groupedMechanics[$cat] as $m): ?>
                        <a href="<?= BASE_URL ?>/sections/mechanics/mechanic.php?id=<?= $m['id_game_mechanic'] ?>" class="mechanic-card">
                            <div class="icon-wrap">
                                <i class="fas fa-bolt"></i>
                            </div>
                            <span class="category"><?= htmlspecialchars($m['category']) ?></span>
                            <h3><?= htmlspecialchars($m['title']) ?></h3>
                            <div class="preview"><?= htmlspecialchars(mb_substr($m['content'], 0, 250)) ?>...</div>
                            <div class="click-hint">Нажмите для подробной информации →</div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script>
    function toggleMechanicFilter() { 
        const f = document.getElementById('filterOptions');
        const s = document.getElementById('sortOptions');
        if(f) f.classList.toggle('show');
        if(s) s.classList.remove('show'); 
    }
    function toggleSort() { 
        const s = document.getElementById('sortOptions');
        const f = document.getElementById('filterOptions');
        if(s) s.classList.toggle('show'); 
        if(f) f.classList.remove('show'); 
    }
    function applyMechanicFilter(cat) { 
        const url = new URL(window.location);
        if(cat) url.searchParams.set('category', cat);
        else url.searchParams.delete('category');
        url.searchParams.delete('search');
        window.location.href = url; 
    }
    function applySort(sort) { 
        const url = new URL(window.location);
        url.searchParams.set('sort', sort); 
        window.location.href = url; 
    }
    document.addEventListener('click', function(e) { 
        if (!e.target.closest('.filter-dropdown') && !e.target.closest('.sort-dropdown')) { 
            const f = document.getElementById('filterOptions');
            const s = document.getElementById('sortOptions');
            if(f) f.classList.remove('show'); 
            if(s) s.classList.remove('show'); 
        } 
    });
    const searchInputMech = document.getElementById('searchInputMechanics'); 
    if(searchInputMech) {
        let searchTimeoutMech;
        searchInputMech.addEventListener('input', function() { 
            clearTimeout(searchTimeoutMech); 
            const query = this.value.trim(); 
            searchTimeoutMech = setTimeout(() => { 
                const url = new URL(window.location); 
                if(query) url.searchParams.set('search', query); 
                else url.searchParams.delete('search');
                window.location.href = url; 
            }, 500); 
        });
    }
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
