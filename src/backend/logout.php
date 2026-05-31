<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// clear remember token in DB
if (!empty($_SESSION['user_id'])) {
    try {
        $pdo = getPDO();
        $stmt = $pdo->prepare('UPDATE users SET remember_token = NULL WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
    } catch (Exception $e) {
        error_log($e->getMessage());
    }
}

// clear session
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();

// clear remember cookie
// clear remember cookie securely
$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
setcookie(REMEMBER_COOKIE_NAME, '', [
    'expires' => time() - 3600,
    'path' => '/',
    'secure' => $secure,
    'httponly' => true,
    'samesite' => 'Lax'
]);

header('Location: /project_club/src/frontend/login.php');
exit;
