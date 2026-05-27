<?php
declare(strict_types=1);

session_start();

header('Content-Type: application/json');

$role = $_SESSION['role'] ?? 'public';
$name = $_SESSION['name'] ?? '';

$response = [
    'role' => $role,
    'name' => $name,
    'loggedIn' => $role !== 'public'
];

echo json_encode($response);
