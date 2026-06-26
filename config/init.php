<?php
// config/init.php — единая инициализация проекта: сессия, базовый URL и подключение к БД.
// Подключайте этот файл первым в каждом скрипте.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Базовый URL проекта (например, "/game_guide"). Вычисляется автоматически,
// поэтому все ссылки и пути работают независимо от имени папки проекта.
if (!defined('BASE_URL')) {
    $docRoot = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/'));
    $projectRoot = str_replace('\\', '/', dirname(__DIR__));
    $base = '';
    if ($docRoot !== '' && stripos($projectRoot, $docRoot) === 0) {
        $base = substr($projectRoot, strlen($docRoot));
    }
    define('BASE_URL', rtrim($base, '/'));
}

require_once __DIR__ . '/db.php';

/**
 * Перенаправляет неавторизованных пользователей на страницу входа.
 */
function requireLogin()
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . '/auth/auth.php');
        exit;
    }
}
