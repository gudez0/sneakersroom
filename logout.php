<?php
session_start();
$wasAdmin = ($_SESSION['user']['role'] ?? '') === 'admin';

// 1. Empty the session data
$_SESSION = [];

// 2. Delete the session cookie in the browser
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}

// 3. Destroy the session on the server
session_destroy();

header('Location: ' . ($wasAdmin ? 'admin/login.php' : 'login.php'));
exit;
