<?php
// config/init.php — единая инициализация проекта: сессия, базовый URL, подключение к БД и роли пользователей.
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

/* =====================================================================
 *  СИСТЕМА ПОЛЬЗОВАТЕЛЕЙ И РОЛЕЙ
 *  Три роли:
 *    - guest  — гость (нажал «Продолжить как гость»): только просмотр разделов.
 *    - user   — авторизованный пользователь: избранное, обратная связь, кабинет.
 *    - admin  — администратор: всё то же + админ-панель управления сайтом.
 * ===================================================================== */

/** Авторизован ли пользователь (вошёл в свой аккаунт). */
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

/** Гость — не вошёл в аккаунт, но нажал «Продолжить как гость». */
function isGuest()
{
    return !isLoggedIn() && !empty($_SESSION['is_guest']);
}

/** Текущая роль: 'admin' | 'user' | 'guest' | '' (никто). */
function currentRole()
{
    if (!isLoggedIn()) {
        return isGuest() ? 'guest' : '';
    }
    if (!isset($_SESSION['user_role'])) {
        $_SESSION['user_role'] = 'user';
        try {
            $pdo = getDB();
            $stmt = $pdo->prepare("SELECT role FROM `user` WHERE id_user = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $r = $stmt->fetchColumn();
            if ($r) {
                $_SESSION['user_role'] = $r;
            }
        } catch (Exception $e) {
            // Если колонки role ещё нет в БД — считаем пользователя обычным.
        }
    }
    return $_SESSION['user_role'];
}

/** Является ли текущий пользователь администратором. */
function isAdmin()
{
    return isLoggedIn() && currentRole() === 'admin';
}

/**
 * Доступ к сайту (просмотр разделов): нужен либо вход, либо режим гостя.
 * Полностью неавторизованных отправляем на страницу входа.
 */
function requireLogin()
{
    if (!isLoggedIn() && !isGuest()) {
        header('Location: ' . BASE_URL . '/auth/auth.php');
        exit;
    }
}

/**
 * Требует полноценный аккаунт (НЕ гостя): избранное, обратная связь, кабинет.
 */
function requireUser()
{
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/auth/auth.php');
        exit;
    }
}

/**
 * Требует права администратора. Иначе — на главную страницу.
 */
function requireAdmin()
{
    if (!isAdmin()) {
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
}
