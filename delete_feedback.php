<?php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Не авторизован']);
    exit;
}

require_once 'config/db.php';
$pdo = getDB();

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Неверные данные']);
    exit;
}

// Определяем имя первичного ключа таблицы feedback
$pkCol = 'id';
try {
    foreach ($pdo->query("SHOW COLUMNS FROM feedback") as $col) {
        if (($col['Key'] ?? '') === 'PRI') { $pkCol = $col['Field']; break; }
    }
} catch (Exception $e) {}

try {
    // Удаляем только своё обращение
    $stmt = $pdo->prepare("DELETE FROM feedback WHERE `$pkCol` = ? AND user_id = ?");
    $stmt->execute([$id, $_SESSION['user_id']]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Обращение не найдено']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}