<?php
// includes/header.php
require_once __DIR__ . '/../config/init.php';
requireLogin();

$isLoggedInUser = isLoggedIn();
$isAdminUser    = isAdmin();
$navUserLabel   = $isLoggedInUser ? ($_SESSION['user_login'] ?? 'Профиль') : 'Гость';

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
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
    <!-- Светлая тема (перекрывает style.css и встроенные стили страниц) -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/theme.css">
    <!-- Скроллбар всегда зарезервирован, чтобы интерфейс/футер не съезжал на длинных страницах -->
    <style>html { overflow-y: scroll; }</style>
    <!-- Базовый URL проекта для использования в скриптах (fetch и т.д.) -->
    <script>window.BASE_URL = '<?= BASE_URL ?>';</script>
    <!-- Тема оформления: по умолчанию светлая, тёмная только при явном выборе. Применяется до отрисовки, чтобы не было мигания -->
    <script>(function(){try{if(localStorage.getItem('siteTheme')!=='dark'){document.documentElement.classList.add('light-theme');}}catch(e){}})();</script>
    <style>
    /* Поисковая строка раздела «Игровые механики» — фиолетовая под дизайн раздела */
    .mechanics-page .search-input:focus { border-color: #8b5cf6; }
    .mechanics-page .search-icon { color: #a78bfa; }
    /* Подпись текущей роли в выпадающем меню пользователя */
    .user-dropdown-label { padding: 10px 14px; font-size: 13px; color: #8f98a0; border-bottom: 1px solid rgba(255,255,255,0.08); font-weight: 600; white-space: nowrap; }
    .user-dropdown-label .role-admin { color: #fbbf24; }
    </style>
</head>
<body class="<?= !empty($currentPage) ? $currentPage . '-page' : '' ?>" <?= !empty($bodyAttrs) ? $bodyAttrs : '' ?>>
    <nav class="top-navbar">
        <div class="nav-left">
            <div class="user-icon-wrapper">
                <div class="user-icon"><i class="fas <?= $isAdminUser ? 'fa-user-shield' : 'fa-user' ?>"></i></div>
                <div class="user-dropdown">
                    <div class="user-dropdown-label">
                        <?php if ($isAdminUser): ?>
                            <i class="fas fa-user-shield me-2" style="color:#fbbf24;"></i><span class="role-admin"><?= htmlspecialchars($navUserLabel) ?> · админ</span>
                        <?php elseif ($isLoggedInUser): ?>
                            <i class="fas fa-user me-2" style="color:#3b82f6;"></i><?= htmlspecialchars($navUserLabel) ?>
                        <?php else: ?>
                            <i class="fas fa-user-secret me-2" style="color:#8f98a0;"></i>Гость
                        <?php endif; ?>
                    </div>
                    <?php if ($isLoggedInUser): ?>
                        <a href="<?= BASE_URL ?>/account/profile.php"><i class="fas fa-user-circle me-2" style="color: #3b82f6;"></i>Личный кабинет</a>
                        <?php if ($isAdminUser): ?>
                            <a href="<?= BASE_URL ?>/admin/index.php"><i class="fas fa-user-shield me-2" style="color: #fbbf24;"></i>Админ-панель</a>
                        <?php endif; ?>
                        <a href="<?= BASE_URL ?>/index.php"><i class="fas fa-home me-2" style="color: #10b981;"></i>Главная страница</a>
                        <a href="<?= BASE_URL ?>/auth/logout.php"><i class="fas fa-sign-out-alt me-2" style="color: #ef4444;"></i>Выйти</a>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/index.php"><i class="fas fa-home me-2" style="color: #10b981;"></i>Главная страница</a>
                        <a href="<?= BASE_URL ?>/auth/auth.php"><i class="fas fa-right-to-bracket me-2" style="color: #10b981;"></i>Войти</a>
                        <a href="<?= BASE_URL ?>/auth/register.php"><i class="fas fa-user-plus me-2" style="color: #3b82f6;"></i>Регистрация</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="nav-buttons">
                <a href="<?= BASE_URL ?>/sections/heroes/heroes.php" class="nav-btn <?= $currentPage === 'heroes' ? 'active-page' : '' ?>"><span class="icon"><i class="fas fa-dragon" style="color: #f59e0b;"></i></span><span>Герои</span></a>
                <a href="<?= BASE_URL ?>/sections/items/items.php" class="nav-btn <?= $currentPage === 'items' ? 'active-page' : '' ?>"><span class="icon"><i class="fas fa-gem" style="color: #06b6d4;"></i></span><span>Предметы</span></a>
                <a href="<?= BASE_URL ?>/sections/mechanics/mechanics.php" class="nav-btn <?= $currentPage === 'mechanics' ? 'active-page' : '' ?>"><span class="icon"><i class="fas fa-bolt" style="color: #8b5cf6;"></i></span><span>Игровые механики</span></a>
                <a href="<?= BASE_URL ?>/account/favorites.php" class="nav-btn <?= $currentPage === 'favorites' ? 'active-page' : '' ?>"><span class="icon"><i class="fas fa-bookmark" style="color: #fbbf24;"></i></span><span>Избранное</span></a>
                <a href="<?= BASE_URL ?>/account/feedback.php" class="nav-btn <?= $currentPage === 'feedback' ? 'active-page' : '' ?>"><span class="icon"><i class="fas fa-comments" style="color: #10b981;"></i></span><span>Обратная связь</span></a>
                <?php if ($isAdminUser): ?>
                <a href="<?= BASE_URL ?>/admin/index.php" class="nav-btn <?= $currentPage === 'admin' ? 'active-page' : '' ?>"><span class="icon"><i class="fas fa-user-shield" style="color: #fbbf24;"></i></span><span>Админ-панель</span></a>
                <?php endif; ?>
            </div>
        </div>
        <div class="settings-icon" onclick="window.location.href='<?= BASE_URL ?>/account/site_settings.php'"><i class="fas fa-cog"></i></div>
    </nav>

    <div class="main-wrapper">