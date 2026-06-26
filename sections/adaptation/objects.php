<?php
// sections/adaptation/objects.php — раздел «Адаптация и расчет времени» (список)
require_once __DIR__ . '/../../config/init.php';
requireLogin();

$pdo = getDB();

$searchQuery = $_GET['search'] ?? '';
$sortBy      = $_GET['sort'] ?? 'name';

$sql = "SELECT * FROM game_mechanic WHERE category = 'Объекты'";
$params = [];
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
$objects = $stmt->fetchAll();

$pageTitle = 'Адаптация и расчет времени';
$currentPage = '';

require_once __DIR__ . '/../../includes/header.php';
?>

<style>
/* Раздел «Адаптация и расчет времени» — зелёный акцент */
.page-title { border-left-color: #10b981; }
.sort-btn { background: rgba(16,185,129,0.15); border-color: #10b981; color: #6ee7b7; }
.sort-btn:hover { background: rgba(16,185,129,0.3); color: #fff; }
.sort-option:hover { background: rgba(16,185,129,0.2); color: #fff; }
.sort-option.active { background: rgba(16,185,129,0.3); color: #6ee7b7; }
.search-input:focus { border-color: #10b981; }

.objects-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; margin-top: 20px; }
.object-card { background: linear-gradient(135deg, #1b2838 0%, #2a475e 100%); border: 1px solid #36414d; border-radius: 12px; padding: 25px; transition: all 0.3s; cursor: pointer; text-decoration: none; color: inherit; display: flex; flex-direction: column; min-height: 320px; }
.object-card:hover { transform: translateY(-5px); border-color: #10b981; box-shadow: 0 8px 25px rgba(16,185,129,0.3); }
.object-card .icon-wrap { width: 60px; height: 60px; background: rgba(16,185,129,0.15); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 15px; color: #10b981; font-size: 32px; }
.object-card .category { display: inline-block; background: rgba(16,185,129,0.2); color: #6ee7b7; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; margin-bottom: 15px; width: fit-content; }
.object-card h3 { color: #fff; font-size: 22px; margin-bottom: 10px; font-weight: 700; }
.object-card .preview { color: #acb2b8; font-size: 14px; line-height: 1.6; flex: 1; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 6; -webkit-box-orient: vertical; }
.object-card .click-hint { color: #10b981; font-size: 13px; margin-top: 15px; text-align: center; font-weight: 600; }
@media (max-width: 900px) { .objects-grid { grid-template-columns: 1fr; } }
</style>

<div class="page-header">
    <h1 class="page-title">⏱️ <?= $pageTitle ?></h1>
    <div class="controls">
        <div class="filter-group">
            <span class="filter-label">Сортировка:</span>
            <div class="sort-dropdown">
                <button class="sort-btn" onclick="toggleObjectSort()">
                    <span id="currentSortText"><?= $sortBy === 'date' ? '📅 По дате' : '📝 По названию' ?></span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="sort-options" id="sortOptions">
                    <div class="sort-option <?= $sortBy === 'name' ? 'active' : '' ?>" onclick="applyObjectSort('name')">📝 По названию</div>
                    <div class="sort-option <?= $sortBy === 'date' ? 'active' : '' ?>" onclick="applyObjectSort('date')">📅 По дате</div>
                </div>
            </div>
        </div>
        <div class="search-box">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="search-input" id="searchInputObjects" placeholder="Поиск..." value="<?= htmlspecialchars($searchQuery) ?>">
        </div>
    </div>
</div>

<?php if (empty($objects)): ?>
    <div class="no-results">
        <i class="fas fa-search"></i>
        <p>Записи не найдены</p>
    </div>
<?php else: ?>
    <div class="objects-grid">
        <?php foreach ($objects as $obj): ?>
            <a href="<?= BASE_URL ?>/sections/adaptation/object.php?id=<?= $obj['id_game_mechanic'] ?>" class="object-card">
                <div class="icon-wrap"><i class="fas fa-clock"></i></div>
                <span class="category"><?= htmlspecialchars($obj['category']) ?></span>
                <h3><?= htmlspecialchars($obj['title']) ?></h3>
                <div class="preview"><?= htmlspecialchars(mb_substr($obj['content'], 0, 300)) ?>...</div>
                <div class="click-hint">Нажмите для подробной информации →</div>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script>
function toggleObjectSort() {
    var s = document.getElementById('sortOptions');
    if (s) s.classList.toggle('show');
}
function applyObjectSort(sort) {
    var url = new URL(window.location);
    url.searchParams.set('sort', sort);
    window.location.href = url;
}
document.addEventListener('click', function (e) {
    if (!e.target.closest('.sort-dropdown')) {
        var s = document.getElementById('sortOptions');
        if (s) s.classList.remove('show');
    }
});
(function () {
    var input = document.getElementById('searchInputObjects');
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
