<?php
// Customer pages now share the same shell (announcement bar, header, footer) as the rest of the site.
require_once __DIR__ . '/layout.php';
if (!empty($needLogin)) require_role('customer', BASE . 'login.php');
$user = $_SESSION['user'] ?? null;
$isCustomer = $user && $user['role'] === 'customer';
$page = basename($_SERVER['PHP_SELF']);
$titles = ['index.php' => 'Shop', 'dashboard.php' => 'My dashboard', 'orders.php' => 'My orders'];
$pageTitle = $pageTitle ?? ($titles[$page] ?? 'Shop');
snr_flash_begin();
snr_page_start($pageTitle, $page === 'orders.php' ? 'orders' : 'shop');
?>
<?php if ($isCustomer): ?>
<div class="coll"><div class="tabs">
  <a href="<?= e(BASE) ?>customer/index.php" class="<?= $page === 'index.php' ? 'on' : '' ?>">Shop</a>
  <a href="<?= e(BASE) ?>customer/dashboard.php" class="<?= $page === 'dashboard.php' ? 'on' : '' ?>">Dashboard</a>
  <a href="<?= e(BASE) ?>customer/orders.php" class="<?= $page === 'orders.php' ? 'on' : '' ?>">My orders</a>
</div></div>
<?php endif; ?>
<main class="page">
  <div class="page-h"><h1><?= e($pageTitle) ?></h1></div>
<?php snr_flash_show(); ?>