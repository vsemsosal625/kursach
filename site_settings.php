<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) { header('Location: auth.php'); exit; }

$pageTitle = 'Настройки сайта';
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
        :root {
            --bg-primary: #0f1419;
            --bg-secondary: #1b2838;
            --bg-card: linear-gradient(135deg, #1b2838 0%, #2a475e 100%);
            --text-primary: #e0e0e0;
            --text-secondary: #acb2b8;
            --border-color: #36414d;
            --accent-color: #8b5cf6;
            --accent-hover: rgba(139,92,246,0.3);
        }

        [data-theme="light"] {
            --bg-primary: #f5f5f5;
            --bg-secondary: #ffffff;
            --bg-card: linear-gradient(135deg, #ffffff 0%, #f0f0f0 100%);
            --text-primary: #1a1a1a;
            --text-secondary: #666666;
            --border-color: #d0d0d0;
            --accent-color: #6d28d9;
            --accent-hover: rgba(109,40,217,0.2);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            background: var(--bg-primary); 
            color: var(--text-primary); 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            min-height: 100vh; 
            display: flex; 
            flex-direction: column;
            transition: all 0.3s;
        }
        .top-navbar { 
            background: linear-gradient(135deg, #1a2332 0%, #2d3748 100%); 
            padding: 15px 40px; 
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.3); 
            position: sticky; 
            top: 0; 
            z-index: 1000; 
        }
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
        .main-wrapper { max-width: 900px; margin: 0 auto; padding: 30px 40px; flex: 1; width: 100%; }
        
        .page-header { 
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
        }
        .page-title { 
            color: var(--text-primary); 
            font-size: 28px; 
            font-weight: 700; 
            border-left: 4px solid var(--accent-color); 
            padding-left: 15px; 
            margin: 0;
        }
        
        .settings-section {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
            transition: all 0.3s;
        }
        .settings-section:hover {
            border-color: var(--accent-color);
        }
        .section-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid var(--border-color);
        }
        .section-icon {
            font-size: 24px;
            color: var(--accent-color);
        }
        .section-title {
            color: var(--text-primary);
            font-size: 20px;
            font-weight: 600;
        }
        
        .setting-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid var(--border-color);
        }
        .setting-item:last-child {
            border-bottom: none;
        }
        .setting-label {
            color: var(--text-primary);
            font-size: 15px;
            font-weight: 500;
        }
        .setting-description {
            color: var(--text-secondary);
            font-size: 13px;
            margin-top: 4px;
        }
        
        /* Переключатель */
        .toggle-switch {
            position: relative;
            width: 50px;
            height: 26px;
        }
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: var(--border-color);
            transition: .4s;
            border-radius: 26px;
        }
        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        input:checked + .toggle-slider {
            background-color: var(--accent-color);
        }
        input:checked + .toggle-slider:before {
            transform: translateX(24px);
        }
        
        /* Селект */
        .setting-select {
            background: var(--bg-secondary);
            border: 2px solid var(--border-color);
            color: var(--text-primary);
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .setting-select:focus {
            outline: none;
            border-color: var(--accent-color);
        }
        .setting-select:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        /* Кнопка сброса */
        .reset-btn {
            background: rgba(239,68,68,0.15);
            border: 2px solid #ef4444;
            color: #fca5a5;
            padding: 10px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .reset-btn:hover {
            background: rgba(239,68,68,0.3);
            color: #fff;
        }
        
        .footer-panel { 
            background: var(--bg-secondary); 
            padding: 25px; 
            text-align: center; 
            margin-top: auto;
            border-top: 1px solid var(--border-color); 
        }
        .footer-panel a { 
            color: var(--text-secondary); 
            text-decoration: none; 
            margin: 0 15px; 
            font-size: 13px; 
        }
        .footer-panel a:hover { color: var(--text-primary); }
        .footer-panel p { color: var(--text-secondary); margin-top: 15px; font-size: 12px; }
        
        @media (max-width: 768px) {
            .top-navbar { padding: 12px 20px; flex-wrap: wrap; gap: 15px; }
            .nav-left { width: 100%; justify-content: space-between; }
            .nav-buttons { width: 100%; order: 3; margin-top: 10px; }
            .user-icon-wrapper { margin-right: 0; }
            .settings-icon { margin-left: 0; }
            .main-wrapper { padding: 20px; }
            .page-header { flex-direction: column; align-items: flex-start; }
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
            <h1 class="page-title">⚙️ <?= $pageTitle ?></h1>
        </div>
        
        <!-- ТЕМА -->
        <div class="settings-section">
            <div class="section-header">
                <i class="fas fa-palette section-icon"></i>
                <h2 class="section-title">Внешний вид</h2>
            </div>
            <div class="setting-item">
                <div>
                    <div class="setting-label">Тёмная тема оформления</div>
                    <div class="setting-description">
                        Включите для тёмной темы, выключите для светлой
                    </div>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" id="themeToggle" checked>
                    <span class="toggle-slider"></span>
                </label>
            </div>
        </div>
        
        <!-- ЯЗЫК -->
        <div class="settings-section">
            <div class="section-header">
                <i class="fas fa-language section-icon"></i>
                <h2 class="section-title">Язык интерфейса</h2>
            </div>
            <div class="setting-item">
                <div>
                    <div class="setting-label">Выберите язык отображения</div>
                    <div class="setting-description">Русский язык доступен сейчас, английский — в разработке</div>
                </div>
                <select class="setting-select" id="language">
                    <option value="ru" selected>Русский</option>
                    <option value="en" disabled>English (Скоро)</option>
                </select>
            </div>
        </div>
        
        <!-- СБРОС -->
        <div style="text-align: center; margin-top: 30px;">
            <button class="reset-btn" onclick="resetSettings()">
                <i class="fas fa-undo"></i>Сбросить настройки
            </button>
        </div>
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
        // Загрузка настроек
        function loadSettings() {
            const settings = JSON.parse(localStorage.getItem('siteSettings') || '{}');
            
            // Тема (по умолчанию тёмная)
            const themeToggle = document.getElementById('themeToggle');
            if (settings.theme === 'light') {
                themeToggle.checked = false;
                document.documentElement.setAttribute('data-theme', 'light');
            } else {
                themeToggle.checked = true;
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        }
        
        // Сохранение настроек
        function saveSettings() {
            const settings = {
                theme: document.getElementById('themeToggle').checked ? 'dark' : 'light'
            };
            localStorage.setItem('siteSettings', JSON.stringify(settings));
        }
        
        // Сброс настроек
        function resetSettings() {
            if (confirm('Вы уверены, что хотите сбросить все настройки?')) {
                localStorage.removeItem('siteSettings');
                location.reload();
            }
        }
        
        // Обработчик переключения темы
        document.getElementById('themeToggle').addEventListener('change', function() {
            const theme = this.checked ? 'dark' : 'light';
            document.documentElement.setAttribute('data-theme', theme);
            saveSettings();
        });
        
        // Загрузка при старте
        loadSettings();
    </script>
</body>
</html>