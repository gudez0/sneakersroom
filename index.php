<?php
require 'includes/auth.php';
$user = $_SESSION['user'] ?? null;
$pairs  = (int)$pdo->query('SELECT COALESCE(SUM(qty),0) FROM products')->fetchColumn();
$models = (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$out    = (int)$pdo->query('SELECT COUNT(*) FROM products WHERE qty = 0')->fetchColumn();
$feat   = $pdo->query('SELECT * FROM products WHERE qty > 0 ORDER BY id DESC LIMIT 6')->fetchAll();
$btn = 'text-decoration:none;display:inline-block;color:#fff';
?>
<!DOCTYPE html>
<html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sneakers Room System</title>
<link rel="stylesheet" href="<?= BASE ?>assets/style.css">
</head><body><div class="wrap">
<h1>Sneakers Room</h1>
<nav>
<a href="index.php" class="on">Home</a>
<a href="customer/index.php">Shop</a>
<?php if (!$user): ?>
<a href="login.php">Log in</a>
<a href="register.php">Register</a>
<?php elseif ($user['role'] === 'admin'): ?>
<a href="admin/dashboard.php">Admin dashboard</a>
<a href="logout.php">Log out</a>
<?php else: ?>
<a href="customer/orders.php">My orders</a>
<a href="logout.php">Log out (<?= e($user['name']) ?>)</a>
<?php endif; ?>
</nav>

<?php if ($user): ?>
<div class="msg ok">Welcome back, <?= e($user['name']) ?> (<?= e($user['role']) ?>).</div>
<?php endif; ?>

<div class="stats">
  <div class="stat"><b><?= $pairs ?></b><span>Pairs in stock</span></div>
  <div class="stat"><b><?= $models ?></b><span>Shoe listings</span></div>
  <div class="stat"><b><?= $out ?></b><span>Sold out</span></div>
</div>

<div class="card"><h3>Newly stocked</h3><table>
<tr><th>Shoe</th><th>Size</th><th>Price</th><th>Availability</th></tr>
<?php foreach ($feat as $r): ?>
<tr><td><?= e($r['brand'].' '.$r['model']) ?></td><td><?= e($r['size']) ?></td><td><?= peso($r['price']) ?></td><td><?= stock_tag($r['qty']) ?></td></tr>
<?php endforeach; if (!$feat): ?><tr><td colspan="4">No shoes in stock yet.</td></tr><?php endif; ?>
</table>
<p><a class="btn" style="<?= $btn ?>" href="customer/index.php">Browse the full shop</a></p></div>

<div class="stats">
  <div class="card"><h3>Customers</h3><p>Browse as a guest, then register to order and track deliveries.</p>
    <a class="btn" style="<?= $btn ?>" href="customer/index.php">Open shop</a>
    <?php if (!$user): ?><a class="btn" style="<?= $btn ?>" href="register.php">Register</a><?php endif; ?></div>
  <div class="card"><h3>Admin</h3><p>Manage stock, orders, payments, J&amp;T deliveries and sales.</p>
    <a class="btn" style="<?= $btn ?>" href="<?= ($user && $user['role']==='admin') ? 'admin/dashboard.php' : 'admin/login.php' ?>">Admin dashboard</a></div>
</div>
</div></body></html>