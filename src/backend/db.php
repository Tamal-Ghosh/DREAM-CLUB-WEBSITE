<?php
declare(strict_types=1);

$config = require __DIR__ . '/config.php';

$connectionInfo = [
    'Database' => $config['database'],
    'UID' => $config['username'],
    'PWD' => $config['password'],
    'CharacterSet' => $config['options']['CharacterSet'] ?? 'UTF-8'
];

$connection = sqlsrv_connect($config['server'], $connectionInfo);

if ($connection === false) {
    $errors = sqlsrv_errors();
    $message = $errors ? $errors[0]['message'] : 'Unknown database error.';
    throw new RuntimeException($message);
}
