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

$sql = "SELECT * FROM game_mechanic WHERE category = 'Объекты'";
$params = [];

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
$objects = $stmt->fetchAll();

$pageTitle = 'Адаптация и расчет времени';
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
            border-left: 4px solid #10b981; 
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
        
        .sort-dropdown { position: relative; }
        .sort-btn { 
            background: rgba(16,185,129,0.15); 
            border: 2px solid #10b981; 
            color: #6ee7b7; 
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
        .sort-btn:hover { background: rgba(16,185,129,0.3); color: #fff; }
        .sort-options { 
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
        .sort-options.show { display: block; }
        .sort-option { 
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
        .sort-option:hover { background: rgba(16,185,129,0.2); color: #fff; }
        .sort-option:last-child { border-bottom: none; }
        .sort-option.active { background: rgba(16,185,129,0.3); color: #6ee7b7; font-weight: 600; }
        .search-box { position: relative; }
        .search-input { 
            background: rgba(27,40,56,0.8); 
            border: 2px solid #36414d; 
            color: #e0e0e0; 
            padding: 8px 16px 8px 40px; 
            border-radius: 8px; 
            font-size: 14px; 
            width: 220px; 
            transition: all 0.3s; 
        }
        .search-input:focus { outline: none; border-color: #10b981; width: 240px; }
        .search-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #8f98a0; font-size: 14px; }
        
        .objects-grid { 
            display: grid; 
            grid-template-columns: repeat(3, 1fr); 
            gap: 30px; 
            margin-top: 20px; 
        }
        .object-card { 
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
            height: 350px; /* Фиксированная высота */
        }
        .object-card:hover { 
            transform: translateY(-5px); 
            border-color: #10b981; 
            box-shadow: 0 8px 25px rgba(16,185,129,0.3); 
        }
        .object-card .icon-wrap {
            width: 60px;
            height: 60px;
            background: rgba(16,185,129,0.15);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            color: #10b981;
            font-size: 32px;
        }
        .object-card h3 { 
            color: #fff; 
            font-size: 22px; 
            margin-bottom: 10px; 
            font-weight: 700; 
        }
        .object-card .category { 
            display: inline-block; 
            background: rgba(16,185,129,0.2); 
            color: #6ee7b7; 
            padding: 4px 12px; 
            border-radius: 20px; 
            font-size: 12px; 
            font-weight: 600; 
            margin-bottom: 15px; 
            width: fit-content;
        }
        .object-card .preview { 
            color: #acb2b8; 
            font-size: 14px; 
            line-height: 1.6; 
            flex: 1;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 6; /* Показываем 6 строк */
            -webkit-box-orient: vertical;
        }
        .object-card .click-hint {
            color: #10b981;
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
        .footer-panel a { 
            color: #8f98a0; 
            text-decoration: none; 
            margin: 0 15px; 
            font-size: 13px; 
        }
        .footer-panel a:hover { color: #fff; }
        .footer-panel p { color: #8f98a0; margin-top: 15px; font-size: 12px; }
        .no-results { 
            text-align: center; 
            padding: 60px 20px; 
            color: #8f98a0; 
            font-size: 16px; 
        }
        .no-results i { 
            font-size: 64px; 
            margin-bottom: 20px; 
            color: #4a5568; 
        }
        
        @media (max-width: 1000px) { 
            .page-header { flex-wrap: wrap; }
            .controls { width: 100%; justify-content: flex-start; }
        }
        @media (max-width: 900px) { 
            .objects-grid { grid-template-columns: 1fr; } 
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
            <h1 class="page-title">⏱️ Адаптация и расчет времени</h1>
            <div class="controls">
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
                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="search-input" id="searchInput" placeholder="Поиск объектов..." value="<?= htmlspecialchars($searchQuery) ?>">
                </div>
            </div>
        </div>
        
        <?php if (empty($objects)): ?>
            <div class="no-results">
                <i class="fas fa-search"></i>
                <p>Объекты не найдены</p>
            </div>
        <?php else: ?>
            <div class="objects-grid">
                <?php foreach ($objects as $obj): ?>
                    <a href="object.php?id=<?= $obj['id_game_mechanic'] ?>" class="object-card">
                        <div class="icon-wrap">
                            <i class="fas fa-clock"></i>
                        </div>
                        <span class="category"><?= htmlspecialchars($obj['category']) ?></span>
                        <h3><?= htmlspecialchars($obj['title']) ?></h3>
                        <div class="preview"><?= htmlspecialchars(mb_substr($obj['content'], 0, 300)) ?>...</div>
                        <div class="click-hint">Нажмите для подробной информации →</div>
                    </a>
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
        function toggleSort() { 
            document.getElementById('sortOptions').classList.toggle('show'); 
        }
        function applySort(sort) { 
            const url = new URL(window.location); 
            url.searchParams.set('sort', sort); 
            const sortTexts = {'name': '📝 По названию', 'date': '📅 По дате'};
            document.getElementById('currentSortText').textContent = sortTexts[sort] || 'По названию';
            window.location.href = url; 
        }
        document.addEventListener('click', function(e) { 
            if (!e.target.closest('.sort-dropdown')) { 
                document.getElementById('sortOptions').classList.remove('show'); 
            } 
        });
        const searchInput = document.getElementById('searchInput'); 
        let searchTimeout;
        searchInput.addEventListener('input', function() { 
            clearTimeout(searchTimeout); 
            const query = this.value.trim(); 
            searchTimeout = setTimeout(() => { 
                const url = new URL(window.location); 
                if(query) url.searchParams.set('search', query); 
                else url.searchParams.delete('search');
                window.location.href = url; 
            }, 500); 
        });
    </script>
</body>
</html>