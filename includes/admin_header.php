<?php
/**
 * Header for every page inside admin/. It gives the admin pages the Sneakers Room look
 * (ticker, header, tab bar) and also blocks anyone who is not logged in as an admin.
 * Your old includes/header.php is NOT touched, so the customer pages keep working as before.
 */
require_once __DIR__ . '/layout.php';

/* Used by the stock / order pages. Only defined here if your other includes don't already have it. */
if (!function_exists('shoe_label')) {
    function shoe_label($pdo, $id) {
        $st = $pdo->prepare('SELECT brand, model, size FROM products WHERE id = ?');
        $st->execute([(int)$id]);
        $p = $st->fetch();
        return $p ? $p['brand'] . ' ' . $p['model'] . ' size ' . $p['size'] : 'Shoe #' . (int)$id;
    }
}

/* Admin-only gate (runs before any output, so the redirect works). */
$snr_user = $_SESSION['user'] ?? null;
if (!$snr_user || ($snr_user['role'] ?? '') !== 'admin') {
    header('Location: ' . BASE . 'login.php');
    exit;
}

$snr_file = basename($_SERVER['SCRIPT_NAME']);
$snr_nav  = [
    'dashboard.php' => 'Dashboard',
    'index.php'     => 'Stock',
    'order_new.php' => 'New order',
    'sales.php'     => 'Sales',
    'soldout.php'   => 'Sold out',
    'payouts.php'   => 'Payouts',
    'activity.php'  => 'Activity log',
    'admins.php'    => 'Admins',
];
$pageTitle = $pageTitle ?? ($snr_nav[$snr_file] ?? 'Admin');

snr_flash_begin();
snr_page_start($pageTitle . ' · Admin', 'admin');
?>
<div class="coll"><div class="tabs">
<?php foreach ($snr_nav as $f => $label): ?>
  <a href="<?= e(BASE . 'admin/' . $f) ?>" class="<?= $snr_file === $f ? 'on' : '' ?>"><?= e($label) ?></a>
<?php endforeach; ?>
  <a href="<?= e(BASE) ?>customer/index.php">View shop</a>
  <a href="<?= e(BASE) ?>logout.php">Log out</a>
</div></div>
<main class="page">
  <div class="page-h">
    <div><div class="eyebrow">Admin &middot; <?= e($snr_user['name']) ?></div><h1><?= e($pageTitle) ?></h1></div>
  </div>
<?php snr_flash_show(); ?>