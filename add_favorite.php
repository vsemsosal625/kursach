<?php
session_start();
if (!isset($_SESSION['user_id'])) { echo json_encode(['success' => false, 'error' => 'Не авторизован']); exit; }

require_once 'config/db.php';
$pdo = getDB();

$user_id = $_SESSION['user_id'];
$item_type = $_POST['item_type'] ?? '';
$item_id = (int)($_POST['item_id'] ?? 0);

$valid_types = ['hero', 'item', 'mechanic', 'tactic', 'object', 'synergy', 'newbie', 'setting'];
if (!in_array($item_type, $valid_types) || $item_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Неверные данные']);
    exit;
}

try {
    // Проверяем, есть ли уже в избранном
    $stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND item_type = ? AND item_id = ?");
    $stmt->execute([$user_id, $item_type, $item_id]);
    
    if ($stmt->fetch()) {
        // Удаляем из избранного
        $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND item_type = ? AND item_id = ?");
        $stmt->execute([$user_id, $item_type, $item_id]);
        echo json_encode(['success' => true, 'action' => 'removed']);
    } else {
        // Добавляем в избранное
        $stmt = $pdo->prepare("INSERT INTO favorites (user_id, item_type, item_id) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $item_type, $item_id]);
        echo json_encode(['success' => true, 'action' => 'added']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}