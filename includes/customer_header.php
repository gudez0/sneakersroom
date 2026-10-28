<?php
require_once __DIR__ . '/auth.php';
if (!empty($needLogin)) require_role('customer', '../login.php');
$user = $_SESSION['user'] ?? null;
$isCustomer = $user && $user['role'] === 'customer';
$page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sneakers Room</title>
<link rel="stylesheet" href="<?= BASE ?>assets/style.css">
</head><body><div class="wrap">
<h1>Sneakers Room</h1>
<nav>
<?php if ($isCustomer): ?><a href="dashboard.php" class="<?= $page==='dashboard.php'?'on':'' ?>">Dashboard</a><?php endif; ?>
<a href="index.php" class="<?= $page==='index.php'?'on':'' ?>">Shop</a>
<?php if ($isCustomer): ?>
<a href="orders.php" class="<?= $page==='orders.php'?'on':'' ?>">My orders</a>
<a href="../logout.php">Log out (<?= e($user['name']) ?>)</a>
<?php elseif ($user): ?>
<a href="../admin/dashboard.php">Admin dashboard</a>
<a href="../logout.php">Log out</a>
<?php else: ?>
<a href="../login.php">Log in</a>
<a href="../register.php">Register</a>
<?php endif; ?>
</nav>
