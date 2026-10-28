<?php
$needLogin = true;
require '../includes/customer_header.php';
$uid = $_SESSION['user']['id'];
$one = function ($sql) use ($pdo, $uid) { $s = $pdo->prepare($sql); $s->execute([$uid]); return $s->fetchColumn() ?: 0; };
$stats = [
  [$one('SELECT COUNT(*) FROM orders WHERE user_id = ?'), 'Total orders'],
  [$one("SELECT COUNT(*) FROM orders WHERE user_id = ? AND delivery_status <> 'Delivered'"), 'On the way'],
  [$one("SELECT COUNT(*) FROM orders WHERE user_id = ? AND payment_status = 'Unpaid'"), 'Unpaid'],
  [peso($one('SELECT SUM(total) FROM orders WHERE user_id = ?')), 'Total ordered'],
];
$st = $pdo->prepare('SELECT o.*, p.brand, p.model, p.size FROM orders o JOIN products p ON p.id = o.product_id WHERE o.user_id = ? ORDER BY o.id DESC LIMIT 5');
$st->execute([$uid]); $recent = $st->fetchAll();
$new = $pdo->query('SELECT * FROM products WHERE qty > 0 ORDER BY id DESC LIMIT 4')->fetchAll();
?>
<div class="msg ok">Welcome, <?= e($_SESSION['user']['name']) ?>!</div>
<div class="stats">
<?php foreach ($stats as $s): ?><div class="stat"><b><?= e($s[0]) ?></b><span><?= e($s[1]) ?></span></div><?php endforeach; ?>
</div>
<div class="card"><h3>My latest orders</h3><table>
<tr><th>Receipt</th><th>Item</th><th>Total</th><th>Payment</th><th>Delivery</th></tr>
<?php foreach ($recent as $r): ?>
<tr><td>#<?= str_pad($r['id'],4,'0',STR_PAD_LEFT) ?></td><td><?= e($r['brand'].' '.$r['model'].' ('.$r['size'].') ×'.$r['qty']) ?></td>
<td><?= peso($r['total']) ?></td><td><?= e($r['payment_status']) ?></td><td><?= e($r['delivery_status']) ?></td></tr>
<?php endforeach; if (!$recent): ?><tr><td colspan="5">No orders yet.</td></tr><?php endif; ?>
</table><p><a href="orders.php">View all orders</a></p></div>
<div class="card"><h3>New arrivals</h3><table>
<tr><th>Shoe</th><th>Size</th><th>Price</th><th>Availability</th></tr>
<?php foreach ($new as $r): ?>
<tr><td><?= e($r['brand'].' '.$r['model']) ?></td><td><?= e($r['size']) ?></td><td><?= peso($r['price']) ?></td><td><?= stock_tag($r['qty']) ?></td></tr>
<?php endforeach; ?>
</table><p><a class="btn" style="text-decoration:none;display:inline-block;color:#fff" href="index.php">Go to the shop</a></p></div>
<?php require '../includes/footer.php'; ?>
