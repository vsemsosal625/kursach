<?php
// synergy.php — раздел «Синергия героев на линии» (список)
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) { header('Location: auth.php'); exit; }

require_once 'config/db.php';
$pdo = getDB();

$searchQuery    = $_GET['search'] ?? '';
$sortBy         = $_GET['sort'] ?? 'name';
$categoryFilter = $_GET['category'] ?? '';

$sql = "SELECT * FROM game_mechanic WHERE category = 'Синергия героев на линии'";
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
$allSynergies = $stmt->fetchAll();

$synergies = ['Легкая линия' => [], 'Сложная линия' => []];
foreach ($allSynergies as $syn) {
    if (preg_match('/Тип линии: (.+)/', $syn['content'], $match)) {
        $laneType = trim($match[1]);
        if (isset($synergies[$laneType])) {
            $synergies[$laneType][] = $syn;
        }
    }
}

if (!empty($categoryFilter) && isset($synergies[$categoryFilter])) {
    $synergies = [$categoryFilter => $synergies[$categoryFilter]];
} elseif (!empty($categoryFilter)) {
    $synergies = [];
}

$laneEmojis = ['Легкая линия' => '⚔️', 'Сложная линия' => '🛡️'];

$pageTitle = 'Синергия героев на линии';
$currentPage = '';

require_once 'includes/header.php';
?>

<style>
/* Раздел «Синергия героев на линии» — фиолетовый акцент */
.search-input:focus { border-color: #8b5cf6; }
.search-icon { color: #a78bfa; }
.category-section { margin-bottom: 50px; }
.category-title { color: #fff; font-size: 24px; font-weight: 700; margin-bottom: 25px; padding-left: 15px; border-left: 4px solid #8b5cf6; }
.synergy-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 25px; }
.synergy-card { background: linear-gradient(135deg, #1b2838 0%, #2a475e 100%); border: 1px solid #36414d; border-radius: 12px; padding: 25px; transition: all 0.3s; cursor: pointer; text-decoration: none; color: inherit; display: flex; flex-direction: column; height: 320px; }
.synergy-card:hover { transform: translateY(-5px); border-color: #8b5cf6; box-shadow: 0 8px 25px rgba(139,92,246,0.3); }
.lane-badge { display: inline-block; background: rgba(139,92,246,0.2); color: #c4b5fd; padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 600; margin-bottom: 15px; width: fit-content; }
.heroes-row { display: flex; gap: 30px; margin-bottom: 15px; padding-bottom: 12px; border-bottom: 1px solid #36414d; }
.synergy-card .hero-item { text-align: left; }
.synergy-card .hero-name { color: #fff; font-size: 16px; font-weight: 700; margin-bottom: 3px; }
.synergy-card .hero-pos { color: #8f98a0; font-size: 12px; }
.heroes-plus { display: flex; align-items: center; color: #8b5cf6; font-size: 20px; font-weight: 700; padding: 0 10px; }
.synergy-card h3 { color: #fff; font-size: 18px; margin-bottom: 10px; font-weight: 600; }
.synergy-card .preview { color: #acb2b8; font-size: 14px; line-height: 1.6; flex: 1; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 4; -webkit-box-orient: vertical; }
.synergy-card .click-hint { color: #8b5cf6; font-size: 13px; margin-top: 15px; text-align: center; font-weight: 600; }
@media (max-width: 900px) { .synergy-grid { grid-template-columns: 1fr; } }
</style>

<div class="page-header">
    <h1 class="page-title">🤝 <?= $pageTitle ?></h1>
    <div class="controls">
        <div class="filter-group">
            <span class="filter-label">Категория:</span>
            <div class="filter-dropdown">
                <button class="filter-btn" onclick="toggleSynergyFilter()">
                    <span id="currentFilterText"><?= !empty($categoryFilter) && isset($laneEmojis[$categoryFilter]) ? $laneEmojis[$categoryFilter] . ' ' . htmlspecialchars($categoryFilter) : '🌐 Все линии' ?></span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="filter-options" id="filterOptions">
                    <div class="filter-option <?= empty($categoryFilter) ? 'active' : '' ?>" onclick="applySynergyFilter('')">🌐 Все линии</div>
                    <div class="filter-option <?= $categoryFilter === 'Легкая линия' ? 'active' : '' ?>" onclick="applySynergyFilter('Легкая линия')">⚔️ Легкая линия</div>
                    <div class="filter-option <?= $categoryFilter === 'Сложная линия' ? 'active' : '' ?>" onclick="applySynergyFilter('Сложная линия')">🛡️ Сложная линия</div>
                </div>
            </div>
        </div>
        <div class="filter-group">
            <span class="filter-label">Сортировка:</span>
            <div class="sort-dropdown">
                <button class="sort-btn" onclick="toggleSynergySort()">
                    <span id="currentSortText"><?= $sortBy === 'date' ? '📅 По дате' : '📝 По названию' ?></span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="sort-options" id="sortOptions">
                    <div class="sort-option <?= $sortBy === 'name' ? 'active' : '' ?>" onclick="applySynergySort('name')">📝 По названию</div>
                    <div class="sort-option <?= $sortBy === 'date' ? 'active' : '' ?>" onclick="applySynergySort('date')">📅 По дате</div>
                </div>
            </div>
        </div>
        <div class="search-box">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="search-input" id="searchInputSynergy" placeholder="Поиск связок..." value="<?= htmlspecialchars($searchQuery) ?>">
        </div>
    </div>
</div>

<?php $totalSynergies = array_sum(array_map('count', $synergies)); ?>
<?php if ($totalSynergies === 0): ?>
    <div class="no-results">
        <i class="fas fa-search"></i>
        <p>Записи не найдены</p>
    </div>
<?php else: ?>
    <?php foreach ($synergies as $laneType => $laneSynergies): ?>
        <?php if (empty($laneSynergies)) continue; ?>
        <div class="category-section">
            <h2 class="category-title"><?= $laneEmojis[$laneType] ?? '' ?> <?= htmlspecialchars($laneType) ?></h2>
            <div class="synergy-grid">
                <?php foreach ($laneSynergies as $syn): ?>
                    <a href="synergy_detail.php?id=<?= $syn['id_game_mechanic'] ?>" class="synergy-card">
                        <span class="lane-badge"><?= htmlspecialchars($laneType) ?></span>
                        <div class="heroes-row">
                            <?php
                            preg_match_all('/• (.+) \((\d+) позиция\)/', $syn['content'], $heroMatches, PREG_SET_ORDER);
                            $heroCount = count($heroMatches);
                            foreach ($heroMatches as $index => $hm): ?>
                                <div class="hero-item">
                                    <div class="hero-name"><?= htmlspecialchars($hm[1]) ?></div>
                                    <div class="hero-pos"><?= $hm[2] ?> позиция</div>
                                </div>
                                <?php if ($index < $heroCount - 1): ?>
                                    <div class="heroes-plus">+</div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        <?php
                        $desc = '';
                        if (preg_match('/Описание взаимодействия:\s*(.+)/s', $syn['content'], $descMatch)) {
                            $desc = trim($descMatch[1]);
                        }
                        ?>
                        <div class="preview"><?= htmlspecialchars(mb_substr($desc, 0, 280)) ?>...</div>
                        <div class="click-hint">Нажмите для подробного разбора →</div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<script>
function toggleSynergyFilter() {
    var f = document.getElementById('filterOptions');
    var s = document.getElementById('sortOptions');
    if (f) f.classList.toggle('show');
    if (s) s.classList.remove('show');
}
function toggleSynergySort() {
    var s = document.getElementById('sortOptions');
    var f = document.getElementById('filterOptions');
    if (s) s.classList.toggle('show');
    if (f) f.classList.remove('show');
}
function applySynergyFilter(cat) {
    var url = new URL(window.location);
    if (cat) url.searchParams.set('category', cat);
    else url.searchParams.delete('category');
    url.searchParams.delete('search');
    window.location.href = url;
}
function applySynergySort(sort) {
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
    var input = document.getElementById('searchInputSynergy');
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
