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

function require_admin(): void
{
    if (!isLoggedIn() || ($_SESSION['role'] ?? '') !== 'admin') {
        json_response(['success' => false, 'message' => 'Unauthorized'], 403);
    }
}

function fetch_users(PDO $pdo): array
{
    $stmt = $pdo->query(
        'SELECT id, name, email, role, status, blood_group, phone, availability_status, created_at
         FROM users
         ORDER BY created_at DESC, id DESC'
    );
    return $stmt->fetchAll();
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';
$pdo = getPDO();

require_admin();

if ($method === 'GET' && $action === 'list') {
    json_response(['success' => true, 'users' => fetch_users($pdo)]);
}

if ($method === 'POST' && $action === 'create-user') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $bloodGroup = trim($_POST['blood_group'] ?? '');
    $role = trim($_POST['role'] ?? 'patient');
    $status = trim($_POST['status'] ?? 'Active');
    $password = (string)($_POST['password'] ?? '');

    if ($name === '' || $email === '' || $password === '') {
        json_response(['success' => false, 'message' => 'Name, email, and password are required'], 422);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_response(['success' => false, 'message' => 'Invalid email address'], 422);
    }

    if (!in_array($role, ['donor', 'patient', 'volunteer', 'admin'], true)) {
        json_response(['success' => false, 'message' => 'Invalid role'], 422);
    }

    if (!in_array($status, ['Active', 'Blocked'], true)) {
        json_response(['success' => false, 'message' => 'Invalid status'], 422);
    }

    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        json_response(['success' => false, 'message' => 'Email already exists'], 409);
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare(
        'INSERT INTO users (name, email, password, role, blood_group, phone, availability_status, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $name,
        $email,
        $hash,
        $role,
        $bloodGroup !== '' ? $bloodGroup : null,
        $phone !== '' ? $phone : null,
        $role === 'donor' ? 'Available' : 'Unavailable',
        $status
    ]);

    json_response(['success' => true, 'message' => 'User created']);
}

if ($method === 'POST' && $action === 'update-user') {
    $userId = (int)($_POST['user_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $bloodGroup = trim($_POST['blood_group'] ?? '');
    $role = trim($_POST['role'] ?? 'patient');
    $availability = trim($_POST['availability_status'] ?? 'Available');
    $status = trim($_POST['status'] ?? 'Active');
    $password = (string)($_POST['password'] ?? '');

    if ($userId <= 0 || $name === '' || $email === '') {
        json_response(['success' => false, 'message' => 'Name and email are required'], 422);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_response(['success' => false, 'message' => 'Invalid email address'], 422);
    }

    if (!in_array($role, ['donor', 'patient', 'volunteer', 'admin'], true)) {
        json_response(['success' => false, 'message' => 'Invalid role'], 422);
    }

    if (!in_array($availability, ['Available', 'Unavailable'], true)) {
        json_response(['success' => false, 'message' => 'Invalid availability status'], 422);
    }

    if (!in_array($status, ['Active', 'Blocked'], true)) {
        json_response(['success' => false, 'message' => 'Invalid status'], 422);
    }

    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1');
    $stmt->execute([$email, $userId]);
    if ($stmt->fetch()) {
        json_response(['success' => false, 'message' => 'Email already exists'], 409);
    }

    if ($password !== '') {
        if (strlen($password) < 6) {
            json_response(['success' => false, 'message' => 'Password must be at least 6 characters'], 422);
        }
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare(
            'UPDATE users
             SET name = ?, email = ?, phone = ?, blood_group = ?, role = ?, availability_status = ?, status = ?, password = ?
             WHERE id = ?'
        );
        $stmt->execute([
            $name,
            $email,
            $phone !== '' ? $phone : null,
            $bloodGroup !== '' ? $bloodGroup : null,
            $role,
            $availability,
            $status,
            $hash,
            $userId
        ]);
    } else {
        $stmt = $pdo->prepare(
            'UPDATE users
             SET name = ?, email = ?, phone = ?, blood_group = ?, role = ?, availability_status = ?, status = ?
             WHERE id = ?'
        );
        $stmt->execute([
            $name,
            $email,
            $phone !== '' ? $phone : null,
            $bloodGroup !== '' ? $bloodGroup : null,
            $role,
            $availability,
            $status,
            $userId
        ]);
    }

    json_response(['success' => true, 'message' => 'User updated']);
}

if ($method === 'POST' && $action === 'update-role') {
    $userId = (int)($_POST['user_id'] ?? 0);
    $role = trim($_POST['role'] ?? '');

    if ($userId <= 0 || !in_array($role, ['donor', 'patient', 'volunteer', 'admin'], true)) {
        json_response(['success' => false, 'message' => 'Invalid role update'], 422);
    }

    $stmt = $pdo->prepare('UPDATE users SET role = ? WHERE id = ?');
    $stmt->execute([$role, $userId]);

    json_response(['success' => true, 'message' => 'Role updated']);
}

if ($method === 'POST' && $action === 'toggle-status') {
    $userId = (int)($_POST['user_id'] ?? 0);
    $status = trim($_POST['status'] ?? '');

    if ($userId <= 0 || !in_array($status, ['Active', 'Blocked'], true)) {
        json_response(['success' => false, 'message' => 'Invalid status update'], 422);
    }

    $stmt = $pdo->prepare('UPDATE users SET status = ? WHERE id = ?');
    $stmt->execute([$status, $userId]);

    json_response(['success' => true, 'message' => 'Status updated']);
}

json_response(['success' => false, 'message' => 'Unknown action'], 400);