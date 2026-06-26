<?php
// sections/newbie/newbie.php — раздел «Руководство для новичков» (список)
require_once __DIR__ . '/../../config/init.php';
requireLogin();

$pdo = getDB();

$searchQuery    = $_GET['search'] ?? '';
$sortBy         = $_GET['sort'] ?? 'name';
$categoryFilter = $_GET['category'] ?? '';

$validCategories = ['Крипы', 'Игровые цели', 'Командные постройки'];

$sql = "SELECT * FROM game_mechanic WHERE 1=1";
$params = [];

if (!empty($categoryFilter) && in_array($categoryFilter, $validCategories)) {
    $sql .= " AND category = ?";
    $params[] = $categoryFilter;
} else {
    $sql .= " AND category IN ('" . implode("','", $validCategories) . "')";
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
$mechanics = $stmt->fetchAll();

$grouped = [];
foreach ($mechanics as $m) {
    if (isset($m['category'])) $grouped[$m['category']][] = $m;
}
$categoryOrder = (!empty($categoryFilter) && isset($grouped[$categoryFilter])) ? [$categoryFilter] : $validCategories;

$categoryEmojis = [
    'Крипы' => '👾',
    'Игровые цели' => '🎯',
    'Командные постройки' => '🏰'
];

$categoryDescriptions = [
    'Крипы' => 'Базовые существа в Dota 2. Каждое существо, не являющееся героем, постройкой, вардом или курьером, считается крипом. Крипы могут принадлежать фракции, быть нейтральными или подконтрольными игроку существами. В отличие от героев, крипы не получают опыт и не могут повышать свой уровень. Все их характеристики являются установленными значениями (но всё ещё могут быть изменены модификаторами). Большинство крипов дают определенное количество золота и опыта героям в виде награды за убийство.',
    'Игровые цели' => 'Главная игровая цель в Dota 2 — совместными усилиями команды уничтожить главное здание противника, которое называется Древний. Оно расположено в самом центре вражеской базы.',
    'Командные постройки' => 'Особый тип существ, неподконтрольный ни одному игроку. В начале каждого матча команды получают в распоряжение набор построек, появляющихся в заданных позициях. Обе фракции имеют одинаковый набор, отличающийся лишь визуально. Постройки в большинстве своём выполняют защитную функцию и являются главными целевыми объектами игры.'
];

$pageTitle = 'Руководство для новичков';
$currentPage = '';

require_once __DIR__ . '/../../includes/header.php';
?>

<style>
/* Раздел «Руководство для новичков» — фиолетовый акцент */
.search-input:focus { border-color: #8b5cf6; }
.search-icon { color: #a78bfa; }
.category-heading { cursor: pointer; transition: all 0.3s; position: relative; }
.category-heading:hover { color: #a78bfa; padding-left: 20px; }
.category-heading::after { content: '▾'; font-size: 18px; margin-left: auto; opacity: 0.5; transition: opacity 0.3s; }
.category-heading:hover::after { opacity: 1; }
.category-description { background: rgba(139,92,246,0.1); border: 1px solid #8b5cf6; border-radius: 8px; padding: 20px; margin-bottom: 20px; color: #e0e0e0; font-size: 15px; line-height: 1.6; display: none; animation: slideDown 0.3s ease; }
.category-description.show { display: block; }
@keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
</style>

<div class="page-header">
    <h1 class="page-title">📖 <?= $pageTitle ?></h1>
    <div class="controls">
        <div class="filter-group">
            <span class="filter-label">Категория:</span>
            <div class="filter-dropdown">
                <button class="filter-btn" onclick="toggleNewbieFilter()">
                    <span id="currentFilterText"><?= !empty($categoryFilter) && isset($categoryEmojis[$categoryFilter]) ? $categoryEmojis[$categoryFilter] . ' ' . htmlspecialchars($categoryFilter) : '🌐 Все' ?></span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="filter-options" id="filterOptions">
                    <div class="filter-option <?= empty($categoryFilter) ? 'active' : '' ?>" onclick="applyNewbieFilter('')">🌐 Все категории</div>
                    <?php foreach ($validCategories as $cat): ?>
                        <div class="filter-option <?= $categoryFilter === $cat ? 'active' : '' ?>" onclick="applyNewbieFilter('<?= htmlspecialchars($cat) ?>')">
                            <?= $categoryEmojis[$cat] ?> <?= htmlspecialchars($cat) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="filter-group">
            <span class="filter-label">Сортировка:</span>
            <div class="sort-dropdown">
                <button class="sort-btn" onclick="toggleNewbieSort()">
                    <span id="currentSortText"><?= $sortBy === 'date' ? '📅 По дате' : '📝 По названию' ?></span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="sort-options" id="sortOptions">
                    <div class="sort-option <?= $sortBy === 'name' ? 'active' : '' ?>" onclick="applyNewbieSort('name')">📝 По названию</div>
                    <div class="sort-option <?= $sortBy === 'date' ? 'active' : '' ?>" onclick="applyNewbieSort('date')">📅 По дате</div>
                </div>
            </div>
        </div>
        <div class="search-box">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="search-input" id="searchInputNewbie" placeholder="Поиск по названию..." value="<?= htmlspecialchars($searchQuery) ?>">
        </div>
    </div>
</div>

<?php if (empty($mechanics)): ?>
    <div class="no-results">
        <i class="fas fa-search"></i>
        <p>Записи не найдены</p>
    </div>
<?php else: ?>
    <div class="mechanics-sections">
        <?php foreach ($categoryOrder as $cat): ?>
            <?php if (!isset($grouped[$cat])) continue; ?>
            <div class="mechanics-category-block">
                <h2 class="category-heading" onclick="toggleNewbieCategory('<?= $cat ?>')">
                    <?= $categoryEmojis[$cat] ?> <?= htmlspecialchars($cat) ?>
                </h2>
                <div class="category-description" id="desc-<?= str_replace(' ', '_', $cat) ?>">
                    <strong>📚 Описание раздела:</strong><br>
                    <?= htmlspecialchars($categoryDescriptions[$cat] ?? '') ?>
                </div>
                <div class="mechanics-grid">
                    <?php foreach ($grouped[$cat] as $m): ?>
                        <a href="<?= BASE_URL ?>/sections/newbie/newbie_detail.php?id=<?= $m['id_game_mechanic'] ?>" class="mechanic-card">
                            <div class="icon-wrap"><i class="fas fa-book-open"></i></div>
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
function toggleNewbieFilter() {
    var f = document.getElementById('filterOptions');
    var s = document.getElementById('sortOptions');
    if (f) f.classList.toggle('show');
    if (s) s.classList.remove('show');
}
function toggleNewbieSort() {
    var s = document.getElementById('sortOptions');
    var f = document.getElementById('filterOptions');
    if (s) s.classList.toggle('show');
    if (f) f.classList.remove('show');
}
function applyNewbieFilter(cat) {
    var url = new URL(window.location);
    if (cat) url.searchParams.set('category', cat);
    else url.searchParams.delete('category');
    url.searchParams.delete('search');
    window.location.href = url;
}
function applyNewbieSort(sort) {
    var url = new URL(window.location);
    url.searchParams.set('sort', sort);
    window.location.href = url;
}
function toggleNewbieCategory(catName) {
    var el = document.getElementById('desc-' + catName.replace(/ /g, '_'));
    if (el) el.classList.toggle('show');
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
    var input = document.getElementById('searchInputNewbie');
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

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>