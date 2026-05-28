<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$remember = !empty($_POST['remember']);

if (!$email || !$password) {
    header('Location: ../frontend/login.php?error=missing');
    exit;
}

$pdo = getPDO();
$stmt = $pdo->prepare('SELECT id, name, email, password, role, status FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: ../frontend/login.php?error=invalid');
    exit;
}

if ($user['status'] !== 'Active') {
    header('Location: ../frontend/login.php?error=blocked');
    exit;
}

if (!password_verify($password, $user['password'])) {
    header('Location: ../frontend/login.php?error=invalid');
    exit;
}

// successful login
if (session_status() === PHP_SESSION_NONE) session_start();
// regenerate session id to prevent fixation
session_regenerate_id(true);
$_SESSION['user_id'] = $user['id'];
$_SESSION['name'] = $user['name'];
$_SESSION['email'] = $user['email'];
$_SESSION['role'] = $user['role'];

// handle remember me
if ($remember) {
    $token = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $token);
    $update = $pdo->prepare('UPDATE users SET remember_token = ? WHERE id = ?');
    $update->execute([$tokenHash, $user['id']]);
    // secure cookie options
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    setcookie(REMEMBER_COOKIE_NAME, $token, [
        'expires' => time() + REMEMBER_COOKIE_EXPIRE,
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

// redirect based on role — use PHP-protected dashboard pages
switch ($user['role']) {
    case 'donor':
        $loc = '../frontend/donor_dashboard.php';
        break;
    case 'patient':
        $loc = '../frontend/patient_dashboard.php';
        break;
    case 'volunteer':
        $loc = '../frontend/volunteer_dashboard.php';
        break;
    case 'admin':
        $loc = '../frontend/admin_dashboard.php';
        break;
    default:
        $loc = '../frontend/home.html';
}

header('Location: ' . $loc);
exit;
