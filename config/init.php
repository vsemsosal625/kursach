<?php
// config/init.php — единая инициализация проекта: сессия, базовый URL, подключение к БД и роли пользователей.
// Подключайте этот файл первым в каждом скрипте.

if (session_status() === PHP_SESSION_NONE) {
    // Кука сессии живёт только до закрытия браузера: при новом запуске проекта
    // старый вход или гостевой режим не подхватываются, и человек снова попадает на окно входа,
    // где сам выбирает — войти, зарегистрироваться или продолжить как гость.
    @ini_set('session.cookie_lifetime', '0');
    session_set_cookie_params(0);
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

/* =====================================================================
 *  ВАЛИДАЦИЯ ПОЛЬЗОВАТЕЛЬСКИХ ДАННЫХ
 *  Единые правила для регистрации, личного кабинета и админ-панели,
 *  чтобы данные нельзя было ввести «как попало».
 * ===================================================================== */

/** Имя/Фамилия/Отчество: только русские и латинские буквы, пробел и дефис (от 2 до 50 символов). */
function isValidName($value)
{
    $value = trim($value);
    if (mb_strlen($value) < 2 || mb_strlen($value) > 50) return false;
    return (bool)preg_match('/^[A-Za-zА-Яа-яЁё]+([ -][A-Za-zА-Яа-яЁё]+)*$/u', $value);
}

/** Логин: строго цифры, длина от 3 до 20 символов. */
function isValidLogin($value)
{
    return (bool)preg_match('/^[0-9]{3,20}$/', trim($value));
}

/** Телефон (необязательное поле): цифры, +, пробел, скобки, дефис. */
function isValidPhone($value)
{
    $value = trim($value);
    if ($value === '') return true;
    if (mb_strlen($value) > 20) return false;
    return (bool)preg_match('/^[0-9+() -]{5,20}$/', $value);
}

/**
 * Корректность email: правильный формат + реально существующий домен.
 * Благодаря проверке DNS адреса вида user@ail.com (несуществующий домен)
 * отклоняются, хотя по формату они выглядят допустимыми.
 * Если DNS-функции на сервере недоступны — остаётся только проверка формата.
 */
function isValidEmail($email)
{
    $email = trim($email);
    if ($email === '' || mb_strlen($email) > 150) return false;
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return false;
    $at = strrchr($email, '@');
    if ($at === false) return false;
    $domain = rtrim(substr($at, 1), '.');
    if ($domain === '' || strpos($domain, '.') === false) return false;
    if (function_exists('checkdnsrr')) {
        if (checkdnsrr($domain, 'MX') || checkdnsrr($domain, 'A') || checkdnsrr($domain, 'AAAA')) {
            return true;
        }
        return false;
    }
    return true;
}

/** Гарантирует наличие колонки в таблице (мягкая миграция без потери данных). */
function ensureColumn($pdo, $table, $column, $definition)
{
    try {
        $exists = false;
        foreach ($pdo->query("SHOW COLUMNS FROM `$table`") as $c) {
            if (($c['Field'] ?? '') === $column) { $exists = true; break; }
        }
        if (!$exists) {
            $pdo->exec("ALTER TABLE `$table` ADD COLUMN $definition");
        }
    } catch (Exception $e) {
        // Нет прав на ALTER — молча игнорируем, чтобы не ронять сайт.
    }
}

/** Заблокирован ли пользователь в обратной связи. */
function isFeedbackBlocked($pdo, $userId)
{
    try {
        ensureColumn($pdo, 'user', 'feedback_blocked', 'feedback_blocked TINYINT(1) NOT NULL DEFAULT 0');
        $stmt = $pdo->prepare("SELECT feedback_blocked FROM `user` WHERE id_user = ?");
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn() === 1;
    } catch (Exception $e) {
        return false;
    }
}
