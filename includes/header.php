<?php require_once __DIR__ . '/auth.php'; require_role('admin', 'login.php'); $page = basename($_SERVER['PHP_SELF']); ?>
<!DOCTYPE html>
<html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sneakers Room - Admin</title>
<link rel="stylesheet" href="<?= BASE ?>assets/style.css">
</head><body><div class="wrap">
<h1>Sneakers Room <small>Admin</small></h1>
<nav>
<?php foreach (['dashboard.php'=>'Dashboard','index.php'=>'Stock','order_new.php'=>'New order','orders.php'=>'Orders','sales.php'=>'Sales','soldout.php'=>'Sold out','activity.php'=>'Activity log','admins.php'=>'Admins'] as $f=>$l): ?>
<a href="<?= $f ?>" class="<?= $page===$f?'on':'' ?>"><?= $l ?></a>
<?php endforeach; ?>
<a href="../customer/index.php">View shop</a>
<a href="../logout.php">Log out</a>
</nav>
