<?php
// Database config
define('DB_HOST', 'localhost');
define('DB_NAME', 'clubDB');
define('DB_USER', 'root');
define('DB_PASS', '');

// Auth / cookie settings
define('REMEMBER_COOKIE_NAME', 'dream_remember');
define('REMEMBER_COOKIE_EXPIRE', 30 * 24 * 60 * 60); // 30 days

// Other settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
?>
