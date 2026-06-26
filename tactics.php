<?php
// tactics.php — раздел «Функциональные роли игроков» (список)
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) { header('Location: auth.php'); exit; }

require_once 'config/db.php';
$pdo = getDB();

$searchQuery   = $_GET['search'] ?? '';
$sortBy        = $_GET['sort'] ?? 'name';
$categoryFilter = $_GET['category'] ?? '';

$validCategories = ['Основа (1-3 позиция)', 'Поддержка (4-5 позиция)'];

$sql = "SELECT * FROM game_mechanic WHERE category IN ('Основа (1-3 позиция)', 'Поддержка (4-5 позиция)')";
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
    case 'date': $sql .= " ORDER BY created_date DESC"; break;
    case 'name':
    default:     $sql .= " ORDER BY title ASC"; break;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tactics = $stmt->fetchAll();

$grouped = [];
foreach ($tactics as $t) {
    if (isset($t['category'])) $grouped[$t['category']][] = $t;
}
$categoryOrder = (!empty($categoryFilter) && isset($grouped[$categoryFilter])) ? [$categoryFilter] : $validCategories;

$categoryEmojis = [
    'Основа (1-3 позиция)' => '🛡️',
    'Поддержка (4-5 позиция)' => '💚',
];

$pageTitle = 'Функциональные роли игроков';
$currentPage = '';

require_once 'includes/header.php';
?>

<style>
/* Раздел «Функциональные роли игроков» — янтарный акцент */
.page-title { border-left-color: #f59e0b; }
.category-heading { border-left-color: #f59e0b; }
.mechanic-card:hover { border-color: #f59e0b; box-shadow: 0 8px 25px rgba(245,158,11,0.3); }
.mechanic-card .icon-wrap { background: rgba(245,158,11,0.15); color: #fbbf24; }
.mechanic-card .category { background: rgba(245,158,11,0.2); color: #fcd34d; }
.mechanic-card .click-hint { color: #fbbf24; }
.search-input:focus { border-color: #f59e0b; }
</style>

<div class="page-header">
    <h1 class="page-title">📋 <?= $pageTitle ?></h1>
    <div class="controls">
        <div class="filter-group">
            <span class="filter-label">Категория:</span>
            <div class="filter-dropdown">
                <button class="filter-btn" onclick="toggleTacticFilter()">
                    <span id="currentFilterText"><?= !empty($categoryFilter) && isset($categoryEmojis[$categoryFilter]) ? $categoryEmojis[$categoryFilter] . ' ' . htmlspecialchars($categoryFilter) : '🌐 Все' ?></span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="filter-options" id="filterOptions">
                    <div class="filter-option <?= empty($categoryFilter) ? 'active' : '' ?>" onclick="applyTacticFilter('')">🌐 Все позиции</div>
                    <?php foreach ($validCategories as $cat): ?>
                        <div class="filter-option <?= $categoryFilter === $cat ? 'active' : '' ?>" onclick="applyTacticFilter('<?= htmlspecialchars($cat) ?>')">
                            <?= $categoryEmojis[$cat] ?> <?= htmlspecialchars($cat) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="filter-group">
            <span class="filter-label">Сортировка:</span>
            <div class="sort-dropdown">
                <button class="sort-btn" onclick="toggleTacticSort()">
                    <span id="currentSortText"><?= $sortBy === 'date' ? '📅 По дате' : '📝 По названию' ?></span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="sort-options" id="sortOptions">
                    <div class="sort-option <?= $sortBy === 'name' ? 'active' : '' ?>" onclick="applyTacticSort('name')">📝 По названию</div>
                    <div class="sort-option <?= $sortBy === 'date' ? 'active' : '' ?>" onclick="applyTacticSort('date')">📅 По дате</div>
                </div>
            </div>
        </div>
        <div class="search-box">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="search-input" id="searchInputTactics" placeholder="Поиск ролей..." value="<?= htmlspecialchars($searchQuery) ?>">
        </div>
    </div>
</div>

<?php if (empty($tactics)): ?>
    <div class="no-results">
        <i class="fas fa-search"></i>
        <p>Роли не найдены</p>
    </div>
<?php else: ?>
    <div class="mechanics-sections">
        <?php foreach ($categoryOrder as $cat): ?>
            <?php if (!isset($grouped[$cat])) continue; ?>
            <div class="mechanics-category-block">
                <h2 class="category-heading"><?= $categoryEmojis[$cat] ?? '' ?> <?= htmlspecialchars($cat) ?></h2>
                <div class="mechanics-grid">
                    <?php foreach ($grouped[$cat] as $t): ?>
                        <a href="tactic.php?id=<?= $t['id_game_mechanic'] ?>" class="mechanic-card">
                            <div class="icon-wrap"><i class="fas fa-user"></i></div>
                            <span class="category"><?= htmlspecialchars($t['category']) ?></span>
                            <h3><?= htmlspecialchars($t['title']) ?></h3>
                            <div class="preview"><?= htmlspecialchars(mb_substr($t['content'], 0, 200)) ?>...</div>
                            <div class="click-hint">Нажмите для подробной информации →</div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script>
function toggleTacticFilter() {
    var f = document.getElementById('filterOptions');
    var s = document.getElementById('sortOptions');
    if (f) f.classList.toggle('show');
    if (s) s.classList.remove('show');
}
function toggleTacticSort() {
    var s = document.getElementById('sortOptions');
    var f = document.getElementById('filterOptions');
    if (s) s.classList.toggle('show');
    if (f) f.classList.remove('show');
}
function applyTacticFilter(cat) {
    var url = new URL(window.location);
    if (cat) url.searchParams.set('category', cat);
    else url.searchParams.delete('category');
    url.searchParams.delete('search');
    window.location.href = url;
}
function applyTacticSort(sort) {
    var url = new URL(window.location);
    url.searchParams.set('sort', sort);
    window.location.href = url;
}
document.addEventListener('click', function (e) {
    if (!e.target.closest('.filter-dropdown') && !e.target.closest('.sort-dropdown')) {
        var f = document.getElementById('filterOptions');
        var s = document.getElementById('sortOptions');
        if (f) f.classList.remove('show');
        if (s) s.classList.remove('show');
    }
});
(function () {
    var input = document.getElementById('searchInputTactics');
    if (!input) return;
    var t;
    input.addEventListener('input', function () {
        clearTimeout(t);
        var q = this.value.trim();
        t = setTimeout(function () {
            var url = new URL(window.location);
            if (q) url.searchParams.set('search', q);
            else url.searchParams.delete('search');
            window.location.href = url;
        }, 500);
    });
})();
</script>

<?php require_once 'includes/footer.php'; ?>
