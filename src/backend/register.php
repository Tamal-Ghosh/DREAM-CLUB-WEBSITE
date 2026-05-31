<?php
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

$first = trim($_POST['firstName'] ?? '');
$last = trim($_POST['lastName'] ?? '');
$email = trim($_POST['email'] ?? '');
$role = $_POST['role'] ?? 'donor';
$bloodGroup = $_POST['bloodGroup'] ?? null;
$phone = $_POST['phone'] ?? null;
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirmPassword'] ?? '';
$next = $_GET['next'] ?? $_POST['next'] ?? '';

if (!$first || !$last || !$email || !$password) {
    header('Location: /project_club/src/frontend/register.php?error=missing');
    exit;
}

if ($password !== $confirm) {
    header('Location: /project_club/src/frontend/register.php?error=password_mismatch');
    exit;
}

if (!in_array($role, ['donor', 'patient'])) {
    $role = 'patient';
}

$name = $first . ' ' . $last;

$pdo = getPDO();
try {
    // check existing email
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        header('Location: /project_club/src/frontend/register.php?error=exists');
        exit;
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $insert = $pdo->prepare('INSERT INTO users (name, email, password, role, blood_group, phone) VALUES (?, ?, ?, ?, ?, ?)');
    $insert->execute([$name, $email, $passwordHash, $role, $bloodGroup, $phone]);

    if ($next === 'admin') {
        header('Location: /project_club/src/frontend/admin_dashboard.php?user_added=1');
        exit;
    }

    header('Location: /project_club/src/frontend/login.php?registered=1');
    exit;
} catch (Exception $e) {
    error_log($e->getMessage());
    header('Location: /project_club/src/frontend/register.php?error=server');
    exit;
}
