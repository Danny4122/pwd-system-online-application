<?php
// config/paths.php
$proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host  = $_SERVER['HTTP_HOST'] ?? 'localhost';

// For Render deployment - use environment variable if set
if (!empty($_SERVER['RENDER_EXTERNAL_URL'])) {
    define('APP_BASE_URL', $_SERVER['RENDER_EXTERNAL_URL']);
} else {
    define('APP_BASE_URL', $proto . '://' . rtrim($host, '/'));
}

define('ADMIN_BASE', rtrim(APP_BASE_URL, '/') . '/src/admin_side');


