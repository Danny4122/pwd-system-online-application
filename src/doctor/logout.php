<?php
// src/doctor/logout.php
require_once __DIR__ . '/../../config/paths.php';
session_start();

// Unset all session variables
$_SESSION = [];

// Delete session cookie if present
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'] ?? '/',
        $params['domain'] ?? '',
        $params['secure'] ?? false,
        $params['httponly'] ?? true
    );
}

// Destroy the session
session_destroy();

// Redirect to login page
header('Location: ' . APP_BASE_URL . '/src/admin_side/signin.php');
exit;
