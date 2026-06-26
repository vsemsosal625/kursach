<?php
require_once __DIR__ . '/../config/init.php';
requireUser();

$pdo = getDB();
$userId = $_SESSION['user_id'];

// Определяем имя первичного ключа таблицы feedback (id / id_feedback / ...)
$pkCol = 'id';
try {
    foreach ($pdo->query("SHOW COLUMNS FROM feedback") as $col) {
        if (($col['Key'] ?? '') === 'PRI') { $pkCol = $col['Field']; break; }
    }
} catch (Exception $e) {}

// AJAX-удаление обращения (раньше было в delete_feedback.php — теперь всё в одном файле)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['ajax'] ?? '') === 'delete') {
    header('Content-Type: application/json');
    $delId = (int)($_POST['id'] ?? 0);
    if ($delId <= 0) { echo json_encode(['success' => false, 'error' => 'Неверные данные']); exit; }
    try {
        $stmt = $pdo->prepare("DELETE FROM feedback WHERE `$pkCol` = ? AND user_id = ?");
        $stmt->execute([$delId, $userId]);
        echo json_encode($stmt->rowCount() > 0 ? ['success' => true] : ['success' => false, 'error' => 'Обращение не найдено']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

$success = '';
$error = '';

// Обработка отправки отзыва
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? '';
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $priority = $_POST['priority'] ?? 'medium';
    if (empty($type) || empty($title) || empty($content)) {
        $error = 'Заполните все обязательные поля';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO feedback (user_id, type, title, content, priority, created_date, status) VALUES (?, ?, ?, ?, ?, NOW(), 'new')");
            $stmt->execute([$userId, $type, $title, $content, $priority]);
            $success = 'Ваш отзыв успешно отправлен! Спасибо за обратную связь.';
        } catch (Exception $e) {
            $error = 'Ошибка при отправке: ' . $e->getMessage();
        }
    }
}

$stmt = $pdo->prepare("SELECT * FROM feedback WHERE user_id = ? ORDER BY created_date DESC");
$stmt->execute([$userId]);
$feedbacks = $stmt->fetchAll();

$pageTitle = 'Обратная связь';
$currentPage = 'feedback';

require_once __DIR__ . '/../includes/header.php';
?>

<style>
.feedback-page .page-title { border-left: 4px solid #10b981; }
.feedback-page .page-title i { color: #10b981; }
.feedback-page .nav-btn.active-page { background: rgba(16,185,129,0.15); color: #fff; }
.feedback-page .page-header { display: block; margin-bottom: 6px !important; }
.feedback-page .page-title { margin-bottom: 0 !important; white-space: normal; }
.page-subtitle { color: #8f98a0; font-size: 16px; margin: 0 0 22px; }
.feedback-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; align-items: start; }
.feedback-form, .feedback-history { background: linear-gradient(135deg, #1b2838 0%, #2a475e 100%); border: 1px solid #36414d; border-radius: 12px; padding: 30px; }
.section-title { color: #fff; font-size: 20px; font-weight: 600; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
.section-title i { color: #10b981; }
.form-group { margin-bottom: 20px; }
.form-group label { display: block; color: #acb2b8; font-size: 14px; margin-bottom: 8px; font-weight: 500; }
.form-group .req { color: #ef4444; }
.form-control { background: rgba(27,40,56,0.8); border: 2px solid #36414d; color: #e0e0e0; padding: 12px 16px; border-radius: 8px; font-size: 15px; transition: all 0.3s; width: 100%; }
.form-control:focus { outline: none; border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,0.1); }
textarea.form-control { min-height: 150px; resize: vertical; }
.btn-submit { background: linear-gradient(135deg, #10b981, #059669); color: #fff; border: none; padding: 14px 28px; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s; width: 100%; }
.btn-submit:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(16,185,129,0.4); }
.alert { padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
.alert-success { background: rgba(16,185,129,0.15); border: 1px solid #10b981; color: #6ee7b7; }
.alert-error { background: rgba(239,68,68,0.15); border: 1px solid #ef4444; color: #fca5a5; }
.feedback-item { position: relative; background: rgba(27,40,56,0.6); border: 1px solid #36414d; border-radius: 8px; padding: 20px 20px 50px; margin-bottom: 15px; transition: border-color 0.3s, transform 0.3s; }
.feedback-item:hover { border-color: #10b981; }
.feedback-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 12px; }
.feedback-type { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; margin-bottom: 10px; }
.type-bug { background: rgba(239,68,68,0.2); color: #fca5a5; }
.type-feature { background: rgba(59,130,246,0.2); color: #93c5fd; }
.type-feedback { background: rgba(16,185,129,0.2); color: #6ee7b7; }
.type-other { background: rgba(139,92,246,0.2); color: #c4b5fd; }
.feedback-title { color: #fff; font-size: 16px; font-weight: 600; }
.feedback-content { color: #acb2b8; font-size: 14px; line-height: 1.6; margin-bottom: 12px; }
.feedback-meta { display: flex; gap: 20px; flex-wrap: wrap; font-size: 13px; color: #8f98a0; align-items: center; }
.status-badge { padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; white-space: nowrap; }
.status-new { background: rgba(245,158,11,0.2); color: #fbbf24; }
.status-review { background: rgba(59,130,246,0.2); color: #93c5fd; }
.status-resolved { background: rgba(16,185,129,0.2); color: #6ee7b7; }
.status-rejected { background: rgba(239,68,68,0.2); color: #fca5a5; }
.priority-badge { padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; }
.priority-low { background: rgba(139,92,246,0.2); color: #c4b5fd; }
.priority-medium { background: rgba(245,158,11,0.2); color: #fbbf24; }
.priority-high { background: rgba(239,68,68,0.2); color: #fca5a5; }
.has-answer { color: #10b981; }
.admin-response { margin-top: 12px; padding-top: 12px; border-top: 1px solid #36414d; }
.admin-response-title { color: #10b981; font-size: 13px; margin-bottom: 6px; }
.admin-response-text { color: #acb2b8; font-size: 14px; line-height: 1.6; }
.no-feedback { text-align: center; padding: 40px 20px; color: #8f98a0; }
.no-feedback i { font-size: 48px; margin-bottom: 15px; color: #4a5568; }
.fb-delete-btn { position: absolute; right: 16px; bottom: 14px; opacity: 0; pointer-events: none; transform: translateY(4px); background: rgba(239,68,68,0.15); border: 1px solid #ef4444; color: #fca5a5; padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; gap: 6px; }
.feedback-item:hover .fb-delete-btn { opacity: 1; pointer-events: auto; transform: translateY(0); }
.fb-delete-btn:hover { background: rgba(239,68,68,0.4); color: #fff; }
.feedback-pager { display: flex; justify-content: center; gap: 8px; margin-top: 20px; flex-wrap: wrap; }
.pager-btn { min-width: 38px; height: 38px; padding: 0 12px; background: rgba(27,40,56,0.8); border: 1px solid #36414d; color: #cbd5e1; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; transition: all 0.2s; }
.pager-btn:hover:not(:disabled) { border-color: #10b981; color: #fff; }
.pager-btn.active { background: rgba(16,185,129,0.2); border-color: #10b981; color: #6ee7b7; }
.pager-btn:disabled { opacity: 0.4; cursor: default; }
@media (max-width: 900px) { .feedback-grid { grid-template-columns: 1fr; } }
</style>

<div class="page-header">
    <h1 class="page-title"><i class="fas fa-comments"></i> Обратная связь</h1>
</div>
<p class="page-subtitle">Поделитесь своим мнением, сообщите об ошибках или предложите новые идеи для улучшения справочника</p>

<?php if ($success): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="feedback-grid">
    <div class="feedback-form">
        <h2 class="section-title"><i class="fas fa-paper-plane"></i> Отправить отзыв</h2>
        <form method="POST">
            <div class="form-group">
                <label>Тип обращения <span class="req">*</span></label>
                <select name="type" class="form-control" required>
                    <option value="">Выберите тип...</option>
                    <option value="bug">🐛 Ошибка/Баг</option>
                    <option value="feature">💡 Предложение/Идея</option>
                    <option value="feedback">💬 Общий отзыв</option>
                    <option value="other">❓ Другое</option>
                </select>
            </div>
            <div class="form-group">
                <label>Заголовок <span class="req">*</span></label>
                <input type="text" name="title" class="form-control" placeholder="Краткое описание вашего обращения" required>
            </div>
            <div class="form-group">
                <label>Приоритет</label>
                <select name="priority" class="form-control">
                    <option value="low">🟢 Низкий</option>
                    <option value="medium" selected>🟡 Средний</option>
                    <option value="high">🔴 Высокий</option>
                </select>
            </div>
            <div class="form-group">
                <label>Сообщение <span class="req">*</span></label>
                <textarea name="content" class="form-control" placeholder="Опишите подробно вашу проблему, предложение или отзыв..." required></textarea>
            </div>
            <button type="submit" class="btn-submit"><i class="fas fa-paper-plane me-2"></i>Отправить</button>
        </form>
    </div>

    <div class="feedback-history">
        <h2 class="section-title"><i class="fas fa-history"></i> Мои обращения</h2>
        <?php if (empty($feedbacks)): ?>
            <div class="no-feedback">
                <i class="fas fa-inbox"></i>
                <p>У вас пока нет отправленных обращений</p>
            </div>
        <?php else: ?>
            <div id="feedbackList">
                <?php foreach ($feedbacks as $fb): ?>
                    <?php
                        $fbId = (int)($fb[$pkCol] ?? 0);
                        $fbType = $fb['type'] ?? 'other';
                        $fbStatus = $fb['status'] ?? 'new';
                        $fbPriority = $fb['priority'] ?? 'medium';
                        $fbAdmin = $fb['admin_response'] ?? '';
                    ?>
                    <div class="feedback-item" data-id="<?= $fbId ?>">
                        <div class="feedback-header">
                            <div>
                                <span class="feedback-type type-<?= htmlspecialchars($fbType) ?>">
                                    <?php
                                    $typeIcons = ['bug' => '🐛', 'feature' => '💡', 'feedback' => '💬', 'other' => '❓'];
                                    $typeNames = ['bug' => 'Ошибка', 'feature' => 'Предложение', 'feedback' => 'Отзыв', 'other' => 'Другое'];
                                    echo ($typeIcons[$fbType] ?? '') . ' ' . ($typeNames[$fbType] ?? $fbType);
                                    ?>
                                </span>
                                <div class="feedback-title"><?= htmlspecialchars($fb['title'] ?? '') ?></div>
                            </div>
                            <span class="status-badge status-<?= htmlspecialchars($fbStatus) ?>">
                                <?php
                                $statusNames = ['new' => 'Новое', 'review' => 'На рассмотрении', 'resolved' => 'Решено', 'rejected' => 'Отклонено'];
                                echo $statusNames[$fbStatus] ?? $fbStatus;
                                ?>
                            </span>
                        </div>
                        <div class="feedback-content"><?= nl2br(htmlspecialchars($fb['content'] ?? '')) ?></div>
                        <div class="feedback-meta">
                            <span><i class="far fa-calendar me-1"></i><?= !empty($fb['created_date']) ? date('d.m.Y H:i', strtotime($fb['created_date'])) : '' ?></span>
                            <span class="priority-badge priority-<?= htmlspecialchars($fbPriority) ?>">
                                <?php
                                $priorityNames = ['low' => 'Низкий', 'medium' => 'Средний', 'high' => 'Высокий'];
                                echo $priorityNames[$fbPriority] ?? $fbPriority;
                                ?>
                            </span>
                            <?php if (!empty($fbAdmin)): ?>
                                <span class="has-answer"><i class="fas fa-check-circle me-1"></i>Есть ответ</span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($fbAdmin)): ?>
                            <div class="admin-response">
                                <div class="admin-response-title"><i class="fas fa-reply me-1"></i>Ответ администратора:</div>
                                <div class="admin-response-text"><?= nl2br(htmlspecialchars($fbAdmin)) ?></div>
                            </div>
                        <?php endif; ?>
                        <button type="button" class="fb-delete-btn" onclick="deleteFeedback(<?= $fbId ?>, this)" title="Удалить обращение">
                            <i class="fas fa-trash"></i> Удалить
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
            <div id="feedbackPager" class="feedback-pager"></div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>