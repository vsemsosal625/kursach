<?php
require_once __DIR__ . '/../config/init.php';
requireAdmin();

$pageTitle = 'Обратная связь';
$currentPage = 'admin';

$pdo = getDB();
ensureColumn($pdo, 'user', 'feedback_blocked', 'feedback_blocked TINYINT(1) NOT NULL DEFAULT 0');

// Определяем имя первичного ключа таблицы feedback (id / id_feedback и т.п.)
$pkCol = 'id';
try {
    $cols = $pdo->query("SHOW COLUMNS FROM feedback")->fetchAll();
    foreach ($cols as $c) {
        if (($c['Key'] ?? '') === 'PRI') { $pkCol = $c['Field']; break; }
    }
} catch (Exception $e) {}

// Обработка действий (PRG: POST -> Redirect -> GET)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $fid = (int)($_POST['feedback_id'] ?? 0);
    if ($action === 'respond' && $fid > 0) {
        $status = $_POST['status'] ?? 'review';
        if (!in_array($status, ['new','review','resolved','rejected'])) $status = 'review';
        $response = trim($_POST['admin_response'] ?? '');
        $stmt = $pdo->prepare("UPDATE feedback SET status = ?, admin_response = ? WHERE `$pkCol` = ?");
        $stmt->execute([$status, $response !== '' ? $response : null, $fid]);
    } elseif ($action === 'delete' && $fid > 0) {
        $stmt = $pdo->prepare("DELETE FROM feedback WHERE `$pkCol` = ?");
        $stmt->execute([$fid]);
    } elseif ($action === 'toggle_block') {
        $uid = (int)($_POST['user_id'] ?? 0);
        if ($uid > 0) {
            ensureColumn($pdo, 'user', 'feedback_blocked', 'feedback_blocked TINYINT(1) NOT NULL DEFAULT 0');
            // Администраторам нельзя ограничивать обратную связь.
            $tr = $pdo->prepare("SELECT role FROM `user` WHERE id_user = ?");
            $tr->execute([$uid]);
            if ($tr->fetchColumn() !== 'admin') {
                $stmt = $pdo->prepare("UPDATE `user` SET feedback_blocked = 1 - feedback_blocked WHERE id_user = ?");
                $stmt->execute([$uid]);
            }
        }
    }
    $back = isset($_POST['filter']) && $_POST['filter'] !== 'all' ? '?status=' . urlencode($_POST['filter']) : '';
    header('Location: ' . BASE_URL . '/admin/feedback.php' . $back);
    exit;
}

$filter = $_GET['status'] ?? 'all';
if (!in_array($filter, ['all','new','review','resolved','rejected'])) $filter = 'all';

$sql = "SELECT f.*, u.login AS u_login, u.name AS u_name, u.surname AS u_surname, u.patronymic AS u_patronymic, u.role AS u_role, u.feedback_blocked AS u_blocked FROM feedback f LEFT JOIN `user` u ON f.user_id = u.id_user";
$params = [];
if ($filter !== 'all') { $sql .= " WHERE f.status = ?"; $params[] = $filter; }
$sql .= " ORDER BY f.created_date DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll();

$typeLabels = ['bug'=>'🐞 Ошибка','feature'=>'💡 Предложение','feedback'=>'💬 Отзыв','other'=>'📌 Другое'];
$statusLabels = ['new'=>'Новое','review'=>'На рассмотрении','resolved'=>'Решено','rejected'=>'Отклонено'];
$statusColors = ['new'=>'#3b82f6','review'=>'#f59e0b','resolved'=>'#10b981','rejected'=>'#ef4444'];
$priorityLabels = ['low'=>'Низкий','medium'=>'Средний','high'=>'Высокий'];
$priorityColors = ['low'=>'#10b981','medium'=>'#f59e0b','high'=>'#ef4444'];
$tabs = ['all'=>'Все','new'=>'Новые','review'=>'На рассмотрении','resolved'=>'Решённые','rejected'=>'Отклонённые'];

require_once __DIR__ . '/../includes/header.php';
?>

<style>
.fb-wrap { max-width: 980px; margin:0 auto; }
.fb-back { display:inline-block; color:#fbbf24; text-decoration:none; margin-bottom:16px; }
.fb-head { display:flex; align-items:center; gap:12px; margin-bottom:18px; }
.fb-head i { color:#fbbf24; font-size:26px; }
.fb-head h1 { margin:0; color:#fff; font-size:26px; }
.fb-tabs { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:22px; }
.fb-tab { padding:7px 16px; border-radius:20px; background:#1b2838; border:1px solid #36414d; color:#c7d0d8; text-decoration:none; font-size:14px; }
.fb-tab.active { background:#fbbf24; color:#1b2838; border-color:#fbbf24; font-weight:600; }
.fb-card { background:linear-gradient(135deg,#1b2838,#2a475e); border:1px solid #36414d; border-radius:12px; padding:20px; margin-bottom:16px; }
.fb-top { display:flex; justify-content:space-between; flex-wrap:wrap; gap:10px; align-items:center; margin-bottom:10px; }
.fb-card h3 { margin:0; color:#fff; font-size:18px; }
.fb-badges { display:flex; gap:8px; flex-wrap:wrap; }
.fb-badge { padding:4px 12px; border-radius:14px; font-size:12px; font-weight:600; color:#fff; }
.fb-meta { color:#8f98a0; font-size:13px; margin-bottom:12px; display:flex; flex-wrap:wrap; gap:14px; align-items:center; }
.fb-meta .fb-login { color:#93c5fd; }
.fb-blocked-tag { background:rgba(239,68,68,0.18); color:#fca5a5; border:1px solid #ef4444; padding:2px 10px; border-radius:12px; font-size:12px; font-weight:600; }
.fb-content { color:#e0e0e0; line-height:1.7; white-space:pre-wrap; margin-bottom:14px; }
.fb-response { background:rgba(16,185,129,0.1); border-left:3px solid #10b981; padding:10px 14px; border-radius:6px; color:#c7d0d8; margin-bottom:14px; white-space:pre-wrap; }
.fb-response b { color:#6ee7b7; }
.fb-form { border-top:1px solid #36414d; padding-top:14px; display:flex; flex-direction:column; gap:10px; }
.fb-form textarea { width:100%; background:#0f1923; border:1px solid #36414d; border-radius:8px; color:#e0e0e0; padding:10px; resize:vertical; min-height:70px; }
.fb-form .row { display:flex; gap:10px; flex-wrap:wrap; align-items:center; }
.fb-form select { background:#0f1923; border:1px solid #36414d; border-radius:8px; color:#e0e0e0; padding:8px 12px; }
.fb-actions { display:flex; gap:10px; flex-wrap:wrap; margin-top:10px; }
.btn-save { background:#fbbf24; color:#1b2838; border:none; border-radius:8px; padding:9px 18px; font-weight:600; cursor:pointer; }
.btn-del { background:transparent; color:#f87171; border:1px solid #f87171; border-radius:8px; padding:9px 16px; cursor:pointer; }
.btn-block { background:transparent; color:#fbbf24; border:1px solid #fbbf24; border-radius:8px; padding:9px 16px; cursor:pointer; font-weight:600; }
.btn-block.unblock { color:#6ee7b7; border-color:#10b981; }
.fb-empty { text-align:center; color:#8f98a0; padding:50px; }
</style>

<div class="fb-wrap">
    <a class="fb-back" href="<?= BASE_URL ?>/admin/index.php"><i class="fas fa-arrow-left"></i> Назад в админ-панель</a>
    <div class="fb-head"><i class="fas fa-comments"></i><h1>Обратная связь от пользователей</h1></div>

    <div class="fb-tabs">
        <?php foreach ($tabs as $tk => $tl): ?>
            <a class="fb-tab<?= $tk === $filter ? ' active' : '' ?>" href="<?= BASE_URL ?>/admin/feedback.php<?= $tk === 'all' ? '' : '?status=' . $tk ?>"><?= $tl ?></a>
        <?php endforeach; ?>
    </div>

    <?php if (empty($items)): ?>
        <div class="fb-empty"><i class="fas fa-inbox" style="font-size:40px;opacity:.4;"></i><p>Обращений пока нет.</p></div>
    <?php else: foreach ($items as $f):
        $st = $f['status'] ?? 'new';
        $pr = $f['priority'] ?? 'medium';
        $blocked = (int)($f['u_blocked'] ?? 0) === 1;
        $authorRole = $f['u_role'] ?? 'user';
        $fio = trim(($f['u_surname'] ?? '') . ' ' . ($f['u_name'] ?? '') . ' ' . ($f['u_patronymic'] ?? ''));
        $ulogin = $f['u_login'] ?? '';
        if ($fio === '') $fio = $ulogin !== '' ? $ulogin : ('ID ' . ($f['user_id'] ?? '?'));
    ?>
        <div class="fb-card">
            <div class="fb-top">
                <h3><?= htmlspecialchars($f['title'] ?? '') ?></h3>
                <div class="fb-badges">
                    <span class="fb-badge" style="background:<?= $priorityColors[$pr] ?? '#8f98a0' ?>;"><?= $priorityLabels[$pr] ?? $pr ?></span>
                    <span class="fb-badge" style="background:<?= $statusColors[$st] ?? '#8f98a0' ?>;"><?= $statusLabels[$st] ?? $st ?></span>
                </div>
            </div>
            <div class="fb-meta">
                <span><?= $typeLabels[$f['type'] ?? 'other'] ?? htmlspecialchars($f['type'] ?? '') ?></span>
                <span><i class="fas fa-user"></i> <?= htmlspecialchars($fio) ?></span>
                <?php if ($ulogin !== ''): ?><span class="fb-login"><i class="fas fa-at"></i> логин: <?= htmlspecialchars($ulogin) ?></span><?php endif; ?>
                <span><i class="far fa-calendar"></i> <?= !empty($f['created_date']) ? date('d.m.Y H:i', strtotime($f['created_date'])) : '' ?></span>
                <?php if ($blocked): ?><span class="fb-blocked-tag"><i class="fas fa-ban"></i> Обратная связь заблокирована</span><?php endif; ?>
            </div>
            <div class="fb-content"><?= htmlspecialchars($f['content'] ?? '') ?></div>
            <?php if (!empty($f['admin_response'])): ?>
                <div class="fb-response"><b>Ответ администратора:</b><br><?= htmlspecialchars($f['admin_response']) ?></div>
            <?php endif; ?>
            <form class="fb-form" method="post">
                <input type="hidden" name="action" value="respond">
                <input type="hidden" name="feedback_id" value="<?= $f[$pkCol] ?>">
                <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
                <textarea name="admin_response" placeholder="Ответ пользователю (необязательно)"><?= htmlspecialchars($f['admin_response'] ?? '') ?></textarea>
                <div class="row">
                    <select name="status">
                        <?php foreach ($statusLabels as $sv => $slbl): ?>
                            <option value="<?= $sv ?>"<?= $sv === $st ? ' selected' : '' ?>><?= $slbl ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn-save" type="submit"><i class="fas fa-save"></i> Сохранить</button>
                </div>
            </form>
            <div class="fb-actions">
                <?php if (!empty($f['user_id']) && $authorRole !== 'admin'): ?>
                <form method="post" onsubmit="return confirm('<?= $blocked ? 'Разблокировать обратную связь у этого пользователя?' : 'Заблокировать этому пользователю доступ к обратной связи?' ?>');">
                    <input type="hidden" name="action" value="toggle_block">
                    <input type="hidden" name="user_id" value="<?= (int)$f['user_id'] ?>">
                    <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
                    <button class="btn-block<?= $blocked ? ' unblock' : '' ?>" type="submit"><i class="fas fa-ban"></i> <?= $blocked ? 'Разблокировать обратную связь' : 'Заблокировать обратную связь' ?></button>
                </form>
                <?php endif; ?>
                <form method="post" onsubmit="return confirm('Удалить это обращение безвозвратно?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="feedback_id" value="<?= $f[$pkCol] ?>">
                    <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
                    <button class="btn-del" type="submit"><i class="fas fa-trash"></i> Удалить</button>
                </form>
            </div>
        </div>
    <?php endforeach; endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
