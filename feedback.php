<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) { header('Location: auth.php'); exit; }

require_once 'config/db.php';
$pdo = getDB();

$userId = $_SESSION['user_id'];
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
            $stmt = $pdo->prepare("
                INSERT INTO feedback (user_id, type, title, content, priority, created_date, status)
                VALUES (?, ?, ?, ?, ?, NOW(), 'new')
            ");
            $stmt->execute([$userId, $type, $title, $content, $priority]);
            $success = 'Ваш отзыв успешно отправлен! Спасибо за обратную связь.';
        } catch (Exception $e) {
            $error = 'Ошибка при отправке: ' . $e->getMessage();
        }
    }
}

// История обращений пользователя (новые сверху)
$stmt = $pdo->prepare("SELECT * FROM feedback WHERE user_id = ? ORDER BY created_date DESC");
$stmt->execute([$userId]);
$feedbacks = $stmt->fetchAll();

$pageTitle = 'Обратная связь';
$currentPage = 'feedback';

require_once 'includes/header.php';
?>

<link rel="stylesheet" href="css/feedback.css">

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
    <!-- Форма отправки -->
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

    <!-- История обращений -->
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
                    <div class="feedback-item" data-id="<?= (int)$fb['id'] ?>">
                        <div class="feedback-header">
                            <div>
                                <span class="feedback-type type-<?= htmlspecialchars($fb['type']) ?>">
                                    <?php
                                    $typeIcons = ['bug' => '🐛', 'feature' => '💡', 'feedback' => '💬', 'other' => '❓'];
                                    $typeNames = ['bug' => 'Ошибка', 'feature' => 'Предложение', 'feedback' => 'Отзыв', 'other' => 'Другое'];
                                    echo ($typeIcons[$fb['type']] ?? '') . ' ' . ($typeNames[$fb['type']] ?? $fb['type']);
                                    ?>
                                </span>
                                <div class="feedback-title"><?= htmlspecialchars($fb['title']) ?></div>
                            </div>
                            <span class="status-badge status-<?= htmlspecialchars($fb['status']) ?>">
                                <?php
                                $statusNames = ['new' => 'Новое', 'review' => 'На рассмотрении', 'resolved' => 'Решено', 'rejected' => 'Отклонено'];
                                echo $statusNames[$fb['status']] ?? $fb['status'];
                                ?>
                            </span>
                        </div>

                        <div class="feedback-content"><?= nl2br(htmlspecialchars($fb['content'])) ?></div>

                        <div class="feedback-meta">
                            <span><i class="far fa-calendar me-1"></i><?= date('d.m.Y H:i', strtotime($fb['created_date'])) ?></span>
                            <span class="priority-badge priority-<?= htmlspecialchars($fb['priority']) ?>">
                                <?php
                                $priorityNames = ['low' => 'Низкий', 'medium' => 'Средний', 'high' => 'Высокий'];
                                echo $priorityNames[$fb['priority']] ?? $fb['priority'];
                                ?>
                            </span>
                            <?php if (!empty($fb['admin_response'])): ?>
                                <span class="has-answer"><i class="fas fa-check-circle me-1"></i>Есть ответ</span>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($fb['admin_response'])): ?>
                            <div class="admin-response">
                                <div class="admin-response-title"><i class="fas fa-reply me-1"></i>Ответ администратора:</div>
                                <div class="admin-response-text"><?= nl2br(htmlspecialchars($fb['admin_response'])) ?></div>
                            </div>
                        <?php endif; ?>

                        <button type="button" class="fb-delete-btn" onclick="deleteFeedback(<?= (int)$fb['id'] ?>, this)" title="Удалить обращение">
                            <i class="fas fa-trash"></i> Удалить
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
            <div id="feedbackPager" class="feedback-pager"></div>
        <?php endif; ?>
    </div>
</div>

<script src="js/feedback.js"></script>

<?php require_once 'includes/footer.php'; ?>