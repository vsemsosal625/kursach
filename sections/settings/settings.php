<?php
// sections/settings/settings.php — раздел «Оптимальные настройки игры» (список)
require_once __DIR__ . '/../../config/init.php';
requireLogin();

$pdo = getDB();

$searchQuery    = $_GET['search'] ?? '';
$sortBy         = $_GET['sort'] ?? 'name';
$categoryFilter = $_GET['category'] ?? '';

$validCategories = ['Способности', 'Предметы', 'Курьер', 'Автоатака', 'Камера', 'Прочее'];

$sql = "SELECT * FROM game_mechanic WHERE category IN ('Способности', 'Предметы', 'Курьер', 'Автоатака', 'Камера', 'Прочее')";
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
$settings = $stmt->fetchAll();

$groupedSettings = [];
foreach ($settings as $s) {
    if (isset($s['category'])) $groupedSettings[$s['category']][] = $s;
}

$categoryEmojis = [
    'Способности' => '✨',
    'Предметы' => '🎒',
    'Курьер' => '📦',
    'Автоатака' => '⚔️',
    'Камера' => '📷',
    'Прочее' => '⚙️'
];

$pageTitle = 'Оптимальные настройки игры';
$currentPage = '';

require_once __DIR__ . '/../../includes/header.php';
?>

<style>
/* Раздел «Оптимальные настройки игры» — фиолетовый акцент */
.search-input:focus { border-color: #8b5cf6; }
.search-icon { color: #a78bfa; }
.settings-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 25px; margin-top: 20px; }
.setting-category { margin-bottom: 30px; }
.category-header { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #8b5cf6; }
.category-icon { font-size: 28px; }
.category-title { color: #fff; font-size: 22px; font-weight: 700; }
.setting-card { background: linear-gradient(135deg, #1b2838 0%, #2a475e 100%); border: 1px solid #36414d; border-radius: 12px; padding: 20px; transition: all 0.3s; cursor: pointer; text-decoration: none; color: inherit; display: flex; flex-direction: column; min-height: 240px; }
.setting-card:hover { transform: translateY(-5px); border-color: #8b5cf6; box-shadow: 0 8px 25px rgba(139,92,246,0.3); }
.setting-card h3 { color: #fff; font-size: 18px; margin-bottom: 12px; font-weight: 600; }
.setting-card .preview { color: #acb2b8; font-size: 14px; line-height: 1.6; flex: 1; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 6; -webkit-box-orient: vertical; }
.setting-card .click-hint { color: #8b5cf6; font-size: 13px; margin-top: 12px; text-align: center; font-weight: 600; }
@media (max-width: 900px) { .settings-grid { grid-template-columns: 1fr; } }
</style>

<div class="page-header">
    <h1 class="page-title">⚙️ <?= $pageTitle ?></h1>
    <div class="controls">
        <div class="filter-group">
            <span class="filter-label">Категория:</span>
            <div class="filter-dropdown">
                <button class="filter-btn" onclick="toggleSettingFilter()">
                    <span id="currentFilterText"><?= !empty($categoryFilter) && isset($categoryEmojis[$categoryFilter]) ? $categoryEmojis[$categoryFilter] . ' ' . htmlspecialchars($categoryFilter) : '🌐 Все' ?></span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="filter-options" id="filterOptions">
                    <div class="filter-option <?= empty($categoryFilter) ? 'active' : '' ?>" onclick="applySettingFilter('')">🌐 Все категории</div>
                    <?php foreach ($validCategories as $cat): ?>
                        <div class="filter-option <?= $categoryFilter === $cat ? 'active' : '' ?>" onclick="applySettingFilter('<?= htmlspecialchars($cat) ?>')">
                            <?= $categoryEmojis[$cat] ?> <?= htmlspecialchars($cat) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="filter-group">
            <span class="filter-label">Сортировка:</span>
            <div class="sort-dropdown">
                <button class="sort-btn" onclick="toggleSettingSort()">
                    <span id="currentSortText"><?= $sortBy === 'date' ? '📅 По дате' : '📝 По названию' ?></span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="sort-options" id="sortOptions">
                    <div class="sort-option <?= $sortBy === 'name' ? 'active' : '' ?>" onclick="applySettingSort('name')">📝 По названию</div>
                    <div class="sort-option <?= $sortBy === 'date' ? 'active' : '' ?>" onclick="applySettingSort('date')">📅 По дате</div>
                </div>
            </div>
        </div>
        <div class="search-box">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="search-input" id="searchInputSettings" placeholder="Поиск настроек..." value="<?= htmlspecialchars($searchQuery) ?>">
        </div>
    </div>
</div>

<?php if (empty($groupedSettings)): ?>
    <div class="no-results">
        <i class="fas fa-search"></i>
        <p>Настройки не найдены</p>
    </div>
<?php else: ?>
    <div class="settings-grid">
        <?php foreach ($groupedSettings as $category => $items): ?>
            <?php foreach ($items as $item): ?>
                <div class="setting-category">
                    <div class="category-header">
                        <span class="category-icon"><?= $categoryEmojis[$category] ?? '⚙️' ?></span>
                        <h2 class="category-title"><?= htmlspecialchars($category) ?></h2>
                    </div>
                    <a href="<?= BASE_URL ?>/sections/settings/setting_detail.php?id=<?= $item['id_game_mechanic'] ?>" class="setting-card">
                        <h3><?= htmlspecialchars($item['title']) ?></h3>
                        <div class="preview"><?= htmlspecialchars(mb_substr($item['content'], 0, 280)) ?>...</div>
                        <div class="click-hint">Нажмите для подробной информации →</div>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script>
function toggleSettingFilter() {
    var f = document.getElementById('filterOptions');
    var s = document.getElementById('sortOptions');
    if (f) f.classList.toggle('show');
    if (s) s.classList.remove('show');
}
function toggleSettingSort() {
    var s = document.getElementById('sortOptions');
    var f = document.getElementById('filterOptions');
    if (s) s.classList.toggle('show');
    if (f) f.classList.remove('show');
}
function applySettingFilter(cat) {
    var url = new URL(window.location);
    if (cat) url.searchParams.set('category', cat);
    else url.searchParams.delete('category');
    url.searchParams.delete('search');
    window.location.href = url;
}
function applySettingSort(sort) {
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
    var input = document.getElementById('searchInputSettings');
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