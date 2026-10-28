<?php
require_once __DIR__ . '/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
function require_role($role, $loginUrl) {
    if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== $role) {
        header('Location: ' . $loginUrl); exit;
    }
}

// Record who did what (shown to admins in the Activity log)
function log_activity($pdo, $action, $details = '') {
    try {
        $u = $_SESSION['user'] ?? null;
        $pdo->prepare('INSERT INTO activity_log (user_id,user_name,role,action,details) VALUES (?,?,?,?,?)')
            ->execute([$u['id'] ?? null, $u['name'] ?? 'Guest', $u['role'] ?? 'guest', $action, $details]);
    } catch (Exception $x) { /* never break the page because of logging */ }
}
function shoe_label($pdo, $id) {
    $s = $pdo->prepare('SELECT CONCAT(brand," ",model," size ",size) FROM products WHERE id = ?');
    $s->execute([$id]);
    return $s->fetchColumn() ?: ('#' . (int)$id);
}
