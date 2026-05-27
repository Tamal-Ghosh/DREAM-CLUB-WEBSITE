<?php
declare(strict_types=1);

session_start();

$redirectBase = '../frontend';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: {$redirectBase}/register.html");
    exit;
}

$firstName = trim($_POST['firstName'] ?? '');
$lastName = trim($_POST['lastName'] ?? '');
$email = trim($_POST['email'] ?? '');
$role = trim($_POST['role'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';

if ($firstName === '' || $lastName === '' || $email === '' || $role === '' || $password === '' || $confirmPassword === '') {
    header("Location: {$redirectBase}/register.html?error=missing");
    exit;
}

if ($password !== $confirmPassword) {
    header("Location: {$redirectBase}/register.html?error=nomatch");
    exit;
}

$allowedRoles = ['donor', 'patient'];
if (!in_array($role, $allowedRoles, true)) {
    header("Location: {$redirectBase}/register.html?error=role");
    exit;
}

try {
    require __DIR__ . '/db.php';
} catch (Throwable $error) {
    header("Location: {$redirectBase}/register.html?error=db");
    exit;
}

$checkSql = 'SELECT id FROM dbo.users WHERE email = ?';
$checkStmt = sqlsrv_prepare($connection, $checkSql, [$email]);

if (!$checkStmt || !sqlsrv_execute($checkStmt)) {
    header("Location: {$redirectBase}/register.html?error=db");
    exit;
}

if (sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC)) {
    header("Location: {$redirectBase}/register.html?error=exists");
    exit;
}

$name = trim("{$firstName} {$lastName}");
$hash = password_hash($password, PASSWORD_DEFAULT);

$insertSql = 'INSERT INTO dbo.users (full_name, email, role, password_hash) OUTPUT INSERTED.id VALUES (?, ?, ?, ?)';
$insertStmt = sqlsrv_prepare($connection, $insertSql, [$name, $email, $role, $hash]);

if (!$insertStmt || !sqlsrv_execute($insertStmt)) {
    header("Location: {$redirectBase}/register.html?error=db");
    exit;
}

$inserted = sqlsrv_fetch_array($insertStmt, SQLSRV_FETCH_ASSOC);

$_SESSION['userId'] = (int) ($inserted['id'] ?? 0);
$_SESSION['role'] = $role;
$_SESSION['name'] = $name;

$target = $role === 'patient'
    ? "{$redirectBase}/patient_dashboard.html"
    : "{$redirectBase}/donor_dashboard.html";

header("Location: {$target}");
exit;
