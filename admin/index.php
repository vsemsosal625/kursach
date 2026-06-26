<?php
require_once __DIR__ . '/../config/init.php';
requireAdmin();

$pageTitle = 'Админ-панель';
$currentPage = 'admin';

$pdo = getDB();

$usersCount = 0; $feedbackTotal = 0; $feedbackNew = 0; $mechanicTotal = 0;
try { $usersCount = (int)$pdo->query("SELECT COUNT(*) FROM `user`")->fetchColumn(); } catch (Exception $e) {}
try { $feedbackTotal = (int)$pdo->query("SELECT COUNT(*) FROM feedback")->fetchColumn(); } catch (Exception $e) {}
try { $feedbackNew = (int)$pdo->query("SELECT COUNT(*) FROM feedback WHERE status = 'new'")->fetchColumn(); } catch (Exception $e) {}
try { $mechanicTotal = (int)$pdo->query("SELECT COUNT(*) FROM game_mechanic")->fetchColumn(); } catch (Exception $e) {}

$contentSections = [
    'mechanics'  => ['Игровые механики', 'fa-gears'],
    'roles'      => ['Функциональные роли игроков', 'fa-users-gear'],
    'adaptation' => ['Адаптация и расчёт времени', 'fa-hourglass-half'],
    'newbie'     => ['Для новичков', 'fa-graduation-cap'],
    'synergy'    => ['Синергия героев на линии', 'fa-link'],
    'settings'   => ['Оптимальные настройки игры', 'fa-sliders'],
];

require_once __DIR__ . '/../includes/header.php';
?>

<style>
.admin-wrap { max-width: 1100px; margin: 0 auto; }
.admin-head { display:flex; align-items:center; gap:14px; margin-bottom:8px; }
.admin-head i { color:#fbbf24; font-size:30px; }
.admin-head h1 { margin:0; color:#fff; font-size:30px; }
.admin-sub { color:#8f98a0; margin-bottom:28px; }
.admin-stats { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:16px; margin-bottom:32px; }
.stat-card { background:linear-gradient(135deg,#1b2838,#2a475e); border:1px solid #36414d; border-radius:12px; padding:22px; }
.stat-card .num { font-size:32px; font-weight:700; color:#fbbf24; }
.stat-card .lbl { color:#c7d0d8; font-size:14px; margin-top:4px; }
.stat-card.alert .num { color:#f87171; }
.admin-section-title { color:#fff; font-size:20px; margin:24px 0 14px; }
.admin-cards { display:grid; grid-template-columns:repeat(auto-fit,minmax(250px,1fr)); gap:16px; }
.admin-card { display:flex; align-items:center; gap:14px; background:#1b2838; border:1px solid #36414d; border-radius:12px; padding:20px; color:#e0e0e0; text-decoration:none; transition:.2s; }
.admin-card:hover { border-color:#fbbf24; transform:translateY(-2px); color:#fff; }
.admin-card i { font-size:24px; color:#fbbf24; width:30px; text-align:center; }
.admin-card .ac-title { font-weight:600; }
.admin-note { margin-top:28px; background:rgba(251,191,36,0.08); border:1px solid rgba(251,191,36,0.3); border-radius:12px; padding:18px 20px; color:#c7d0d8; font-size:14px; line-height:1.6; }
.admin-note b { color:#fbbf24; }
.admin-note code { background:#0f1923; padding:2px 6px; border-radius:4px; color:#fcd34d; }
</style>

<div class="admin-wrap">
    <div class="admin-head">
        <i class="fas fa-user-shield"></i>
        <h1>Админ-панель</h1>
    </div>
    <p class="admin-sub">Управление контентом и пользователями сайта</p>

    <div class="admin-stats">
        <div class="stat-card"><div class="num"><?= $usersCount ?></div><div class="lbl"><i class="fas fa-users"></i> Пользователей</div></div>
        <div class="stat-card<?= $feedbackNew > 0 ? ' alert' : '' ?>"><div class="num"><?= $feedbackNew ?></div><div class="lbl"><i class="fas fa-envelope"></i> Новых обращений</div></div>
        <div class="stat-card"><div class="num"><?= $feedbackTotal ?></div><div class="lbl"><i class="fas fa-comments"></i> Всего обращений</div></div>
        <div class="stat-card"><div class="num"><?= $mechanicTotal ?></div><div class="lbl"><i class="fas fa-database"></i> Записей контента</div></div>
    </div>

    <h2 class="admin-section-title"><i class="fas fa-inbox"></i> Обратная связь</h2>
    <div class="admin-cards">
        <a class="admin-card" href="<?= BASE_URL ?>/admin/feedback.php">
            <i class="fas fa-comments"></i>
            <div><div class="ac-title">Обращения пользователей</div><div style="font-size:13px;color:#8f98a0;">Просмотр, ответы, удаление</div></div>
        </a>
    </div>

    <h2 class="admin-section-title"><i class="fas fa-dragon"></i> Герои и предметы</h2>
    <div class="admin-cards">
        <a class="admin-card" href="<?= BASE_URL ?>/admin/heroes_items.php?type=hero">
            <i class="fas fa-dragon"></i>
            <div><div class="ac-title">Герои</div><div style="font-size:13px;color:#8f98a0;">Добавить, изменить, удалить · фото по ссылке</div></div>
        </a>
        <a class="admin-card" href="<?= BASE_URL ?>/admin/heroes_items.php?type=item">
            <i class="fas fa-gem"></i>
            <div><div class="ac-title">Предметы</div><div style="font-size:13px;color:#8f98a0;">Добавить, изменить, удалить · фото по ссылке</div></div>
        </a>
    </div>

    <h2 class="admin-section-title"><i class="fas fa-folder-open"></i> Управление разделами</h2>
    <div class="admin-cards">
        <?php foreach ($contentSections as $key => $sec): ?>
            <a class="admin-card" href="<?= BASE_URL ?>/admin/content.php?section=<?= $key ?>">
                <i class="fas <?= $sec[1] ?>"></i>
                <div><div class="ac-title"><?= htmlspecialchars($sec[0]) ?></div><div style="font-size:13px;color:#8f98a0;">Добавить, изменить, удалить</div></div>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
