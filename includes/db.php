<?php
date_default_timezone_set('Asia/Manila');
// URL prefix of this project, worked out automatically (e.g. /sneakersroom_system/)
define('BASE', rtrim(substr(str_replace('\\', '/', dirname(__DIR__)), strlen(rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/'))), '/') . '/');

try {
    $pdo = new PDO('mysql:host=localhost;dbname=sneakers_db;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $x) {
    die('Database connection failed: ' . htmlspecialchars($x->getMessage()));
}

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function peso($n) { return '₱' . number_format((float)$n, 2); }
function stock_tag($q) {
    if ($q <= 0) return '<span class="tag out">Sold out</span>';
    if ($q == 1) return '<span class="tag low">Last pair</span>';
    return '<span class="tag ok">' . (int)$q . ' in stock</span>';
}
