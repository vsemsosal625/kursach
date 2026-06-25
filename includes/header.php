<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit;
}

if (!isset($pageTitle)) {
    $pageTitle = 'Игровой справочник';
}

if (!isset($currentPage)) {
    $currentPage = '';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> | Игровой справочник</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <!-- Скроллбар всегда зарезервирован, чтобы интерфейс/футер не съезжал на длинных страницах -->
    <style>html { overflow-y: scroll; }</style>
    <!-- Тема оформления (светлая/тёмная) — применяется до отрисовки, чтобы не было мигания -->
    <script>(function(){try{if(localStorage.getItem('siteTheme')==='light'){document.documentElement.classList.add('light-theme');}}catch(e){}})();</script>
    <style>
    /* Поисковая строка раздела «Игровые механики» — фиолетовая под дизайн раздела */
    .mechanics-page .search-input:focus { border-color: #8b5cf6; }
    .mechanics-page .search-icon { color: #a78bfa; }

    /* ===== СВЕТЛАЯ ТЕМА (глобально, переключается в «Настройках сайта») ===== */
    html.light-theme body { background: #eef1f5; color: #1f2733; }
    html.light-theme .top-navbar { background: linear-gradient(135deg, #ffffff 0%, #e7edf5 100%); box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
    html.light-theme .nav-btn { color: #4a5568; }
    html.light-theme .nav-btn:hover, html.light-theme .nav-btn.active-page { color: #111827; background: rgba(59,130,246,0.12); }
    html.light-theme .user-dropdown { background: #ffffff; box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
    html.light-theme .user-dropdown a { color: #2d3748; border-bottom-color: #e2e8f0; }
    html.light-theme .user-dropdown a:hover { background: rgba(59,130,246,0.12); color: #111827; }
    html.light-theme .footer-panel { background: #ffffff; border-top-color: #d8dee8; }
    html.light-theme .footer-panel a, html.light-theme .footer-panel p { color: #5b6470; }
    html.light-theme .footer-panel a:hover { color: #111827; }
    html.light-theme .page-title { color: #111827; }
    html.light-theme .page-subtitle { color: #5b6470; }
    html.light-theme .updates-container,
    html.light-theme .popular-sections,
    html.light-theme .section-card,
    html.light-theme .attribute-section,
    html.light-theme .hero-card,
    html.light-theme .item-card,
    html.light-theme .mechanic-card,
    html.light-theme .mechanic-detail,
    html.light-theme .tactic-detail,
    html.light-theme .hero-detail-layout .info-block,
    html.light-theme .hero-detail-layout .hero-tips-box,
    html.light-theme .hero-lore-block,
    html.light-theme .feedback-form,
    html.light-theme .feedback-history,
    html.light-theme .feedback-item,
    html.light-theme .profile-sidebar,
    html.light-theme .profile-content,
    html.light-theme .fav-card,
    html.light-theme .settings-section {
        background: #ffffff !important;
        border-color: #dce2ea !important;
        color: #1f2733;
    }
    html.light-theme h1, html.light-theme h2, html.light-theme h3, html.light-theme h4 { color: #111827; }
    html.light-theme .mechanic-card .preview,
    html.light-theme .mechanic-detail .content,
    html.light-theme .tactic-detail .content,
    html.light-theme .hero-lore-block p,
    html.light-theme .summary-text,
    html.light-theme .setting-description,
    html.light-theme .feedback-content,
    html.light-theme .profile-email { color: #3a4250; }
    html.light-theme .form-control { background: #f4f6f9 !important; border-color: #cbd5e0 !important; color: #1f2733 !important; }
    html.light-theme .form-control:disabled { background: #e9edf2 !important; color: #6b7280 !important; }
    html.light-theme .search-input { background: #f4f6f9; border-color: #cbd5e0; color: #1f2733; }
    html.light-theme .filter-options, html.light-theme .sort-options { background: #ffffff; border-color: #dce2ea; }
    html.light-theme .filter-option, html.light-theme .sort-option { color: #2d3748; border-bottom-color: #e2e8f0; }
    html.light-theme .setting-label, html.light-theme .section-title { color: #111827; }
    html.light-theme .profile-nav-btn { color: #2d3748; }
    html.light-theme .fav-section { background: #eef2f7; border-color: #d8dee8; color: #3a4250; }
    </style>
</head>
<body class="<?= !empty($currentPage) ? $currentPage . '-page' : '' ?>" <?= !empty($bodyAttrs) ? $bodyAttrs : '' ?>>
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
                <a href="heroes.php" class="nav-btn <?= $currentPage === 'heroes' ? 'active-page' : '' ?>"><span class="icon"><i class="fas fa-dragon" style="color: #f59e0b;"></i></span><span>Герои</span></a>
                <a href="items.php" class="nav-btn <?= $currentPage === 'items' ? 'active-page' : '' ?>"><span class="icon"><i class="fas fa-gem" style="color: #06b6d4;"></i></span><span>Предметы</span></a>
                <a href="mechanics.php" class="nav-btn <?= $currentPage === 'mechanics' ? 'active-page' : '' ?>"><span class="icon"><i class="fas fa-bolt" style="color: #8b5cf6;"></i></span><span>Игровые механики</span></a>
                <a href="favorites.php" class="nav-btn <?= $currentPage === 'favorites' ? 'active-page' : '' ?>"><span class="icon"><i class="fas fa-bookmark" style="color: #fbbf24;"></i></span><span>Избранное</span></a>
                <a href="feedback.php" class="nav-btn <?= $currentPage === 'feedback' ? 'active-page' : '' ?>"><span class="icon"><i class="fas fa-comments" style="color: #10b981;"></i></span><span>Обратная связь</span></a>
            </div>
        </div>
        <div class="settings-icon" onclick="window.location.href='site_settings.php'"><i class="fas fa-cog"></i></div>
    </nav>

    <div class="main-wrapper">