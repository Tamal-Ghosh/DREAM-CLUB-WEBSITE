<?php
declare(strict_types=1);

session_start();

$redirectBase = '../frontend';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: {$redirectBase}/login.html");
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    header("Location: {$redirectBase}/login.html?error=missing");
    exit;
}

try {
    require __DIR__ . '/db.php';
} catch (Throwable $error) {
    header("Location: {$redirectBase}/login.html?error=db");
    exit;
}

$sql = 'SELECT id, full_name, role, password_hash FROM dbo.users WHERE email = ?';
$stmt = sqlsrv_prepare($connection, $sql, [$email]);

if (!$stmt || !sqlsrv_execute($stmt)) {
    header("Location: {$redirectBase}/login.html?error=db");
    exit;
}

$matched = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

if (!$matched || !password_verify($password, $matched['password_hash'] ?? '')) {
    header("Location: {$redirectBase}/login.html?error=invalid");
    exit;
}

$_SESSION['userId'] = (int) ($matched['id'] ?? 0);
$_SESSION['role'] = $matched['role'] ?? 'public';
$_SESSION['name'] = $matched['full_name'] ?? '';

$role = $_SESSION['role'];
$target = "{$redirectBase}/home.html";

$roleMap = [
    'admin' => "{$redirectBase}/admin_dashboard.html",
    'donor' => "{$redirectBase}/donor_dashboard.html",
    'patient' => "{$redirectBase}/patient_dashboard.html",
    'volunteer' => "{$redirectBase}/volunteer_dashboard.html"
];

if (isset($roleMap[$role])) {
    $target = $roleMap[$role];
}

header("Location: {$target}");
exit;
