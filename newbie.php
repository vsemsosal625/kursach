<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit;
}

require_once 'config/db.php';
$pdo = getDB();

$searchQuery = $_GET['search'] ?? '';
$sortBy = $_GET['sort'] ?? 'name';
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

// ПОИСК ТОЛЬКО ПО НАЗВАНИЮ
if ($searchQuery) {
    $sql .= " AND title LIKE ?";
    $params[] = "%$searchQuery%";
}

switch ($sortBy) {
    case 'name': $sql .= " ORDER BY title ASC"; break;
    case 'date': $sql .= " ORDER BY created_date DESC"; break;
    case 'category': $sql .= " ORDER BY FIELD(category, 'Крипы', 'Игровые цели', 'Командные постройки'), title ASC"; break;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$mechanics = $stmt->fetchAll();

$groupedMechanics = [];
foreach ($mechanics as $m) {
    if (isset($m['category'])) $groupedMechanics[$m['category']][] = $m;
}

$categoryOrder = (!empty($categoryFilter) && isset($groupedMechanics[$categoryFilter])) ? [$categoryFilter] : $validCategories;

// Эмодзи для категорий
$categoryEmojis = [
    'Крипы' => '👾',
    'Игровые цели' => '🎯',
    'Командные постройки' => '🏰'
];

// ОПИСАНИЯ КАТЕГОРИЙ
$categoryDescriptions = [
    'Крипы' => 'Базовые существа в Dota 2. Каждое существо, не являющееся героем, постройкой, вардом или курьером, считается крипом. Крипы могут принадлежать фракции, быть нейтральными или подконтрольными игроку существами. В отличие от героев, крипы не получают опыт и не могут повышать свой уровень. Все их характеристики являются установленными значениями (но всё ещё могут быть изменены модификаторами). Большинство крипов дают определенное количество золота и опыта героям в виде награды за убийство.',
    'Игровые цели' => 'Главная игровая цель в Dota 2 — совместными усилиями команды уничтожить главное здание противника, которое называется Древний. Оно расположено в самом центре вражеской базы.',
    'Командные постройки' => 'Особый тип существ, неподконтрольный ни одному игроку. В начале каждого матча команды получают в распоряжение набор построек, появляющихся в заданных позициях. Обе фракции имеют одинаковый набор, отличающийся лишь визуально. Постройки в большинстве своём выполняют защитную функцию и являются главными целевыми объектами игры.'
];

$pageTitle = 'Руководство для новичков';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> | Игровой справочник</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #0f1419; color: #e0e0e0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; min-height: 100vh; display: flex; flex-direction: column; }
        .top-navbar { background: linear-gradient(135deg, #1a2332 0%, #2d3748 100%); padding: 15px 40px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 10px rgba(0,0,0,0.3); position: sticky; top: 0; z-index: 1000; }
        .nav-left { display: flex; align-items: center; flex: 1; }
        .user-icon-wrapper { position: relative; cursor: pointer; margin-right: 40px; z-index: 1001; }
        .user-icon { width: 45px; height: 45px; background: linear-gradient(135deg, #3b82f6, #2563eb); border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s; }
        .user-icon:hover { transform: scale(1.05); box-shadow: 0 0 15px rgba(59,130,246,0.5); }
        .user-icon i { color: white; font-size: 22px; }
        .user-dropdown { position: absolute; top: 55px; left: 0; background: #2d3748; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.4); min-width: 260px; opacity: 0; visibility: hidden; transition: all 0.3s; overflow: hidden; z-index: 1002; }
        .user-icon-wrapper:hover .user-dropdown { opacity: 1; visibility: visible; }
        .user-dropdown a { display: block; padding: 14px 20px; color: #e0e0e0; text-decoration: none; transition: all 0.2s; border-bottom: 1px solid #3d4a5c; position: relative; z-index: 1003; }
        .user-dropdown a:hover { background: rgba(59,130,246,0.2); color: white; padding-left: 25px; }
        .nav-buttons { display: flex; justify-content: space-between; flex: 1; gap: 0; }
        .nav-btn { color: #b0b8c8; text-decoration: none; font-weight: 500; font-size: 15px; padding: 10px 20px; border-radius: 8px; transition: all 0.3s; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 10px; flex: 1; justify-content: center; margin: 0 5px; }
        .nav-btn:hover { color: #fff; background: rgba(59, 130, 246, 0.2); }
        .nav-btn .icon { font-size: 24px; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; }
        .settings-icon { width: 45px; height: 45px; background: linear-gradient(135deg, #8b5cf6, #ec4899); border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s; margin-left: 30px; box-shadow: 0 2px 8px rgba(139, 92, 246, 0.3); }
        .settings-icon:hover { transform: rotate(30deg); box-shadow: 0 0 20px rgba(236, 72, 153, 0.6); background: linear-gradient(135deg, #ec4899, #8b5cf6); }
        .settings-icon i { color: white; font-size: 20px; }
        .main-wrapper { max-width: 1400px; margin: 0 auto; padding: 30px 40px; flex: 1; width: 100%; }
        
        .page-header { 
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
            flex-wrap: nowrap;
        }
        
        .page-title { 
            color: #fff; 
            font-size: 28px; 
            font-weight: 700; 
            border-left: 4px solid #8b5cf6; 
            padding-left: 15px; 
            margin: 0;
            white-space: nowrap;
        }
        
        .controls { 
            display: flex;
            gap: 15px;
            align-items: center;
            margin-left: auto;
            white-space: nowrap;
        }
        
        .filter-group { display: flex; align-items: center; gap: 10px; }
        .filter-label { color: #acb2b8; font-size: 14px; font-weight: 500; white-space: nowrap; }
        .filter-dropdown, .sort-dropdown { position: relative; }
        .filter-btn, .sort-btn { 
            background: rgba(139,92,246,0.15); 
            border: 2px solid #8b5cf6; 
            color: #a78bfa; 
            padding: 8px 16px; 
            border-radius: 8px; 
            cursor: pointer; 
            font-weight: 600; 
            font-size: 14px; 
            transition: all 0.3s; 
            display: flex; 
            align-items: center; 
            gap: 8px;
            min-width: 160px;
            justify-content: space-between;
        }
        .filter-btn:hover, .sort-btn:hover { background: rgba(139,92,246,0.3); color: #fff; }
        .filter-options, .sort-options { 
            position: absolute; 
            top: calc(100% + 5px); 
            right: 0; 
            background: #1b2838; 
            border: 1px solid #36414d; 
            border-radius: 8px; 
            min-width: 200px; 
            display: none; 
            z-index: 100; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.3); 
        }
        .filter-options.show, .sort-options.show { display: block; }
        .filter-option, .sort-option { 
            padding: 10px 16px; 
            cursor: pointer; 
            transition: all 0.2s; 
            border-bottom: 1px solid #36414d; 
            color: #e0e0e0; 
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .filter-option:hover, .sort-option:hover { background: rgba(139,92,246,0.2); color: #fff; }
        .filter-option:last-child, .sort-option:last-child { border-bottom: none; }
        .filter-option.active, .sort-option.active { background: rgba(139,92,246,0.3); color: #a78bfa; font-weight: 600; }
        .search-box { position: relative; }
        .search-input { 
            background: rgba(27,40,56,0.8); 
            border: 2px solid #36414d; 
            color: #e0e0e0; 
            padding: 8px 16px 8px 40px; 
            border-radius: 8px; 
            font-size: 14px; 
            width: 200px; 
            transition: all 0.3s; 
        }
        .search-input:focus { outline: none; border-color: #8b5cf6; width: 240px; }
        .search-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #8f98a0; font-size: 14px; }
        
        .mechanics-sections { margin-top: 0; min-height: 50vh; }
        .mechanics-category-block { margin-bottom: 50px; }
        
        /* КЛИКАБЕЛЬНЫЙ ЗАГОЛОВОК КАТЕГОРИИ */
        .category-heading { 
            color: #fff; 
            font-size: 24px; 
            font-weight: 700; 
            margin-bottom: 20px; 
            padding-left: 15px; 
            border-left: 4px solid #8b5cf6; 
            display: flex; 
            align-items: center; 
            gap: 10px;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
        }
        .category-heading:hover {
            color: #a78bfa;
            padding-left: 20px;
        }
        .category-heading::after {
            content: '️';
            font-size: 18px;
            margin-left: auto;
            opacity: 0.5;
            transition: opacity 0.3s;
        }
        .category-heading:hover::after {
            opacity: 1;
        }
        
        /* ОПИСАНИЕ КАТЕГОРИИ */
        .category-description {
            background: rgba(139,92,246,0.1);
            border: 1px solid #8b5cf6;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            color: #e0e0e0;
            font-size: 15px;
            line-height: 1.6;
            display: none;
            animation: slideDown 0.3s ease;
        }
        .category-description.show {
            display: block;
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .mechanics-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 25px; }
        .mechanic-card { 
            background: linear-gradient(135deg, #1b2838 0%, #2a475e 100%); 
            border: 1px solid #36414d; 
            border-radius: 12px; 
            padding: 25px; 
            transition: all 0.3s; 
            cursor: pointer; 
            text-decoration: none; 
            color: inherit; 
            display: flex;
            flex-direction: column;
            height: 280px;
        }
        .mechanic-card:hover { 
            transform: translateY(-5px); 
            border-color: #8b5cf6; 
            box-shadow: 0 8px 25px rgba(139,92,246,0.3); 
        }
        .mechanic-card .icon-wrap { 
            width: 50px; height: 50px; 
            background: rgba(139,92,246,0.15); 
            border-radius: 12px; 
            display: flex; align-items: center; justify-content: center; 
            margin-bottom: 15px; color: #a78bfa; font-size: 24px; 
        }
        .mechanic-card h3 { color: #fff; font-size: 20px; margin-bottom: 8px; font-weight: 600; }
        .mechanic-card .category { 
            display: inline-block; 
            background: rgba(139,92,246,0.2); 
            color: #c4b5fd; 
            padding: 4px 12px; 
            border-radius: 20px; 
            font-size: 12px; 
            font-weight: 600; 
            margin-bottom: 12px; 
            width: fit-content; 
        }
        .mechanic-card .preview { 
            color: #acb2b8; 
            font-size: 14px; 
            line-height: 1.6; 
            flex: 1;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 5;
            -webkit-box-orient: vertical;
        }
        .mechanic-card .click-hint {
            color: #8b5cf6;
            font-size: 13px;
            margin-top: 15px;
            text-align: center;
            font-weight: 600;
        }
        .footer-panel { 
            background: #171a21; 
            padding: 25px; 
            text-align: center; 
            margin-top: auto;
            border-top: 1px solid #2a475e; 
        }
        .footer-panel a { color: #8f98a0; text-decoration: none; margin: 0 15px; font-size: 13px; }
        .footer-panel a:hover { color: #fff; }
        .footer-panel p { color: #8f98a0; margin-top: 15px; font-size: 12px; }
        .no-results { text-align: center; padding: 60px 20px; color: #8f98a0; font-size: 16px; }
        .no-results i { font-size: 64px; margin-bottom: 20px; color: #4a5568; }
        
        @media (max-width: 1000px) { 
            .page-header { flex-wrap: wrap; }
            .controls { width: 100%; justify-content: flex-start; flex-wrap: wrap; }
        }
        @media (max-width: 900px) { 
            .mechanics-grid { grid-template-columns: 1fr; } 
        }
        @media (max-width: 768px) {
            .top-navbar { padding: 12px 20px; flex-wrap: wrap; gap: 15px; }
            .nav-left { width: 100%; justify-content: space-between; }
            .nav-buttons { width: 100%; order: 3; margin-top: 10px; }
            .user-icon-wrapper { margin-right: 0; }
            .settings-icon { margin-left: 0; }
            .main-wrapper { padding: 20px; }
            .page-header { flex-direction: column; align-items: flex-start; }
            .controls { width: 100%; }
            .search-input { width: 100%; }
            .search-input:focus { width: 100%; }
        }
    </style>
</head>
<body>
    <nav class="top-navbar">
        <div class="nav-left">
            <div class="user-icon-wrapper">
                <div class="user-icon"><i class="fas fa-user"></i></div>
                <div class="user-dropdown">
                    <a href="profile.php"><i class="fas fa-user-circle me-2" style="color: #3b82f6;"></i>Личный кабинет</a>
                    <a href="index.php"><i class="fas fa-home me-2" style="color: #10b981;"></i>Главная страница</a>
                    <a href="logout.php"><i class="fas fa-sign-out-alt me-2" style="color: #ef4444;"></i>Выйти</a>
                </div>
            </div>
            <div class="nav-buttons">
                <a href="heroes.php" class="nav-btn"><span class="icon"><i class="fas fa-dragon" style="color: #f59e0b;"></i></span><span>Герои</span></a>
                <a href="items.php" class="nav-btn"><span class="icon"><i class="fas fa-gem" style="color: #06b6d4;"></i></span><span>Предметы</span></a>
                <a href="mechanics.php" class="nav-btn"><span class="icon"><i class="fas fa-bolt" style="color: #8b5cf6;"></i></span><span>Игровые механики</span></a>
                <a href="favorites.php" class="nav-btn"><span class="icon"><i class="fas fa-bookmark" style="color: #fbbf24;"></i></span><span>Избранное</span></a>
                <a href="feedback.php" class="nav-btn"><span class="icon"><i class="fas fa-comments" style="color: #10b981;"></i></span><span>Обратная связь</span></a>
            </div>
        </div>
        <div class="settings-icon" onclick="window.location.href='site_settings.php'"><i class="fas fa-cog"></i></div>
    </nav>

    <div class="main-wrapper">
        <div class="page-header">
            <h1 class="page-title">📖 <?= $pageTitle ?></h1>
            <div class="controls">
                <div class="filter-group">
                    <span class="filter-label">Категория:</span>
                    <div class="filter-dropdown">
                        <button class="filter-btn" onclick="toggleFilter()">
                            <span id="currentFilterText"><?= !empty($categoryFilter) && isset($categoryEmojis[$categoryFilter]) ? $categoryEmojis[$categoryFilter] . ' ' . $categoryFilter : '🌐 Все' ?></span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="filter-options" id="filterOptions">
                            <div class="filter-option <?= empty($categoryFilter) ? 'active' : '' ?>" onclick="applyFilter('')">🌐 Все категории</div>
                            <?php foreach ($validCategories as $cat): ?>
                                <div class="filter-option <?= $categoryFilter === $cat ? 'active' : '' ?>" onclick="applyFilter('<?= htmlspecialchars($cat) ?>')">
                                    <?= $categoryEmojis[$cat] ?> <?= htmlspecialchars($cat) ?>
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
                    <input type="text" class="search-input" id="searchInput" placeholder="Поиск по названию..." value="<?= htmlspecialchars($searchQuery) ?>">
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
                    <?php if (!isset($groupedMechanics[$cat])) continue; ?>
                    <div class="mechanics-category-block">
                        <!-- КЛИКАБЕЛЬНЫЙ ЗАГОЛОВОК -->
                        <h2 class="category-heading" onclick="toggleCategoryDescription('<?= $cat ?>')">
                            <?= $categoryEmojis[$cat] ?> <?= htmlspecialchars($cat) ?>
                        </h2>
                        <!-- ОПИСАНИЕ КАТЕГОРИИ -->
                        <div class="category-description" id="desc-<?= str_replace(' ', '_', $cat) ?>">
                            <strong>📚 Описание раздела:</strong><br>
                            <?= htmlspecialchars($categoryDescriptions[$cat]) ?>
                        </div>
                        <div class="mechanics-grid">
                            <?php foreach ($groupedMechanics[$cat] as $m): ?>
                                <a href="newbie_detail.php?id=<?= $m['id_game_mechanic'] ?>" class="mechanic-card">
                                    <div class="icon-wrap">
                                        <i class="fas fa-book-open"></i>
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
    </div>

    <footer class="footer-panel">
        <a href="tactics.php">Тактики</a>
        <a href="heroes.php">Герои</a>
        <a href="items.php">Предметы</a>
        <a href="mechanics.php">Механики</a>
        <a href="feedback.php">Обратная связь</a>
        <p>&copy; 2026 Игровой справочник. ГБПОУИО «ИАТ». Курсовая работа</p>
    </footer>

    <script>
        function toggleFilter() { document.getElementById('filterOptions').classList.toggle('show'); document.getElementById('sortOptions').classList.remove('show'); }
        function toggleSort() { document.getElementById('sortOptions').classList.toggle('show'); document.getElementById('filterOptions').classList.remove('show'); }
        function applyFilter(cat) { 
            const url = new URL(window.location); 
            if(cat) {
                url.searchParams.set('category', cat); 
                const emojis = {'Крипы': '👾', 'Игровые цели': '🎯', 'Командные постройки': '🏰'};
                document.getElementById('currentFilterText').textContent = (emojis[cat] || '') + ' ' + cat;
            } else {
                url.searchParams.delete('category'); 
                document.getElementById('currentFilterText').textContent = '🌐 Все категории';
            }
            url.searchParams.delete('search'); window.location.href = url; 
        }
        function applySort(sort) { 
            const url = new URL(window.location); 
            url.searchParams.set('sort', sort); 
            const sortTexts = {'name': '📝 По названию', 'date': '📅 По дате'};
            document.getElementById('currentSortText').textContent = sortTexts[sort] || 'По названию';
            window.location.href = url; 
        }
        document.addEventListener('click', function(e) { 
            if (!e.target.closest('.filter-dropdown') && !e.target.closest('.sort-dropdown')) { 
                document.getElementById('filterOptions').classList.remove('show'); 
                document.getElementById('sortOptions').classList.remove('show'); 
            } 
        });
        
        // ПЕРЕКЛЮЧЕНИЕ ОПИСАНИЯ КАТЕГОРИИ
        function toggleCategoryDescription(catName) {
            const descId = 'desc-' + catName.replace(/ /g, '_');
            const descElement = document.getElementById(descId);
            if (descElement) {
                descElement.classList.toggle('show');
            }
        }
        
        const searchInput = document.getElementById('searchInput'); let searchTimeout;
        searchInput.addEventListener('input', function() { 
            clearTimeout(searchTimeout); const query = this.value.trim(); 
            searchTimeout = setTimeout(() => { 
                const url = new URL(window.location); 
                if(query) url.searchParams.set('search', query); else url.searchParams.delete('search');
                window.location.href = url; 
            }, 500); 
        });
    </script>
</body>
</html>