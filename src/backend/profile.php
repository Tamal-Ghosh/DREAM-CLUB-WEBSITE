<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/session.php';

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function json_response(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}

if (!isLoggedIn()) {
    json_response(['success' => false, 'message' => 'Unauthorized'], 403);
}

$pdo = getPDO();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$action = $_GET['action'] ?? $_POST['action'] ?? 'me';
$userId = (int)($_SESSION['user_id'] ?? 0);

if ($method === 'GET' && in_array($action, ['me', 'profile'], true)) {
    $stmt = $pdo->prepare(
        'SELECT id, name, email, phone, blood_group, role, availability_status, status, created_at
         FROM users
         WHERE id = ?
         LIMIT 1'
    );
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        json_response(['success' => false, 'message' => 'Profile not found'], 404);
    }

    json_response(['success' => true, 'user' => $user]);
}

if ($method === 'POST' && $action === 'update') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $bloodGroup = trim($_POST['blood_group'] ?? '');
    $availability = trim($_POST['availability_status'] ?? 'Available');
    $oldPassword = (string)($_POST['old_password'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    if ($name === '' || $email === '') {
        json_response(['success' => false, 'message' => 'Name and email are required'], 422);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_response(['success' => false, 'message' => 'Invalid email address'], 422);
    }

    $stmt = $pdo->prepare('SELECT role FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$userId]);
    $currentUser = $stmt->fetch();
    if (!$currentUser) {
        json_response(['success' => false, 'message' => 'Profile not found'], 404);
    }

    $role = (string)$currentUser['role'];

    if ($password !== '') {
        if ($oldPassword === '') {
            json_response(['success' => false, 'message' => 'Old password is required to change password'], 422);
        }

        $stmt = $pdo->prepare('SELECT password FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $stored = $stmt->fetchColumn();
        if (!$stored || !password_verify($oldPassword, (string)$stored)) {
            json_response(['success' => false, 'message' => 'Old password is incorrect'], 422);
        }

        if (strlen($password) < 6) {
            json_response(['success' => false, 'message' => 'Password must be at least 6 characters'], 422);
        }
    }

    if ($role === 'donor' && !in_array($availability, ['Available', 'Unavailable'], true)) {
        json_response(['success' => false, 'message' => 'Invalid availability status'], 422);
    }

    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1');
    $stmt->execute([$email, $userId]);
    if ($stmt->fetch()) {
        json_response(['success' => false, 'message' => 'Email already exists'], 409);
    }

    $params = [
        $name,
        $email,
        $phone !== '' ? $phone : null,
        $bloodGroup !== '' ? $bloodGroup : null,
    ];

    if ($role === 'donor') {
        $params[] = $availability;
    }

    if ($password !== '') {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        if ($role === 'donor') {
            $stmt = $pdo->prepare(
                'UPDATE users
                 SET name = ?, email = ?, phone = ?, blood_group = ?, availability_status = ?, password = ?
                 WHERE id = ?'
            );
            $stmt->execute([
                $name,
                $email,
                $phone !== '' ? $phone : null,
                $bloodGroup !== '' ? $bloodGroup : null,
                $availability,
                $hash,
                $userId
            ]);
        } else {
            $stmt = $pdo->prepare(
                'UPDATE users
                 SET name = ?, email = ?, phone = ?, blood_group = ?, password = ?
                 WHERE id = ?'
            );
            $stmt->execute([
                $name,
                $email,
                $phone !== '' ? $phone : null,
                $bloodGroup !== '' ? $bloodGroup : null,
                $hash,
                $userId
            ]);
        }
    } else {
        if ($role === 'donor') {
            $stmt = $pdo->prepare(
                'UPDATE users
                 SET name = ?, email = ?, phone = ?, blood_group = ?, availability_status = ?
                 WHERE id = ?'
            );
            $stmt->execute([
                $name,
                $email,
                $phone !== '' ? $phone : null,
                $bloodGroup !== '' ? $bloodGroup : null,
                $availability,
                $userId
            ]);
        } else {
            $stmt = $pdo->prepare(
                'UPDATE users
                 SET name = ?, email = ?, phone = ?, blood_group = ?
                 WHERE id = ?'
            );
            $stmt->execute([
                $name,
                $email,
                $phone !== '' ? $phone : null,
                $bloodGroup !== '' ? $bloodGroup : null,
                $userId
            ]);
        }
    }

    $_SESSION['name'] = $name;
    $_SESSION['email'] = $email;

    json_response(['success' => true, 'message' => 'Profile updated']);
}

json_response(['success' => false, 'message' => 'Unknown action'], 400);
