<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If this file is requested directly, return JSON about the current session.
if (realpath($_SERVER['SCRIPT_FILENAME']) === realpath(__FILE__)) {
    header('Content-Type: application/json');
    $role = $_SESSION['role'] ?? 'public';
    $name = $_SESSION['name'] ?? '';
    echo json_encode(['role' => $role, 'name' => $name]);
    exit;
}

function isLoggedIn()
{
    return !empty($_SESSION['user_id']);
}

function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: /project_club/src/frontend/login.php');
        exit;
    }
}

// Auto-login via remember cookie
if (!isLoggedIn() && !empty($_COOKIE[REMEMBER_COOKIE_NAME])) {
    $token = $_COOKIE[REMEMBER_COOKIE_NAME];
    if ($token) {
        $pdo = getPDO();
        $tokenHash = hash('sha256', $token);
        $stmt = $pdo->prepare('SELECT id, name, email, role, status FROM users WHERE remember_token = ? LIMIT 1');
        $stmt->execute([$tokenHash]);
        $user = $stmt->fetch();
        if ($user && $user['status'] === 'Active') {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
        } else {
            // invalid token — clear cookie
            $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
            setcookie(REMEMBER_COOKIE_NAME, '', [
                'expires' => time() - 3600,
                'path' => '/',
                'secure' => $secure,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        }
    }
}
