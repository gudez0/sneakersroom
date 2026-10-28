<?php
require '../includes/header.php';
$one = function ($sql) use ($pdo) { return $pdo->query($sql)->fetchColumn() ?: 0; };
$paid = "payment_status='Paid'";
$stats = [
  [peso($one("SELECT SUM(total) FROM orders WHERE $paid AND DATE(created_at)=CURDATE()")), 'Sales today'],
  [peso($one("SELECT SUM(total) FROM orders WHERE $paid AND created_at >= CURDATE() - INTERVAL 6 DAY")), 'Last 7 days'],
  [peso($one("SELECT SUM(total) FROM orders WHERE $paid AND YEAR(created_at)=YEAR(CURDATE()) AND MONTH(created_at)=MONTH(CURDATE())")), 'This month'],
  [peso($one("SELECT SUM(total) FROM orders WHERE $paid")), 'All-time sales'],
  [$one('SELECT SUM(qty) FROM products'), 'Pairs in stock'],
  [$one('SELECT COUNT(*) FROM products WHERE qty = 0'), 'Sold-out items'],
  [$one("SELECT COUNT(*) FROM orders WHERE payment_status='Unpaid'"), 'Unpaid orders'],
  [$one("SELECT COUNT(*) FROM orders WHERE channel='Online' AND delivery_status <> 'Delivered'"), 'Deliveries in progress'],
  [$one("SELECT COUNT(*) FROM users WHERE role='customer'"), 'Registered customers'],
];

// Sales for the last 7 days (bar chart)
$days = [];
for ($i = 6; $i >= 0; $i--) $days[date('Y-m-d', strtotime("-$i day"))] = 0;
foreach ($pdo->query("SELECT DATE(created_at) d, SUM(total) t FROM orders WHERE $paid AND created_at >= CURDATE() - INTERVAL 6 DAY GROUP BY DATE(created_at)") as $r)
    if (isset($days[$r['d']])) $days[$r['d']] = (float)$r['t'];
$max = max($days) ?: 1;

$low  = $pdo->query('SELECT * FROM products WHERE qty <= 2 ORDER BY qty, brand, model LIMIT 8')->fetchAll();
$top  = $pdo->query('SELECT p.brand,p.model,SUM(o.qty) n FROM orders o JOIN products p ON p.id=o.product_id GROUP BY p.id ORDER BY n DESC LIMIT 5')->fetchAll();
$todo = $pdo->query("SELECT o.id,o.payment_status,o.delivery_status,o.channel,c.name FROM orders o JOIN customers c ON c.id=o.customer_id
  WHERE o.payment_status='Unpaid' OR (o.channel='Online' AND o.delivery_status<>'Delivered') ORDER BY o.id DESC LIMIT 8")->fetchAll();
$log  = $pdo->query('SELECT * FROM activity_log ORDER BY id DESC LIMIT 10')->fetchAll();
?>
<div class="stats">
<?php foreach ($stats as $s): ?><div class="stat"><b><?= e($s[0]) ?></b><span><?= e($s[1]) ?></span></div><?php endforeach; ?>
</div>

<div class="card"><h3>Sales, last 7 days (paid orders)</h3>
<div class="bars">
<?php foreach ($days as $d => $t): ?>
<div class="bar"><div class="col"><i style="height:<?= max(2, round($t / $max * 100)) ?>%"></i></div><span><?= $t ? '₱' . number_format($t) : '–' ?></span><small><?= date('D', strtotime($d)) ?></small></div>
<?php endforeach; ?>
</div></div>

<div class="two">
<div class="card"><h3>Stock alerts <a href="index.php" style="font-size:13px">Manage stock</a></h3><table>
<tr><th>Shoe</th><th>Size</th><th>Status</th></tr>
<?php foreach ($low as $r): ?><tr><td><?= e($r['brand'].' '.$r['model']) ?></td><td><?= e($r['size']) ?></td><td><?= stock_tag($r['qty']) ?></td></tr>
<?php endforeach; if (!$low): ?><tr><td colspan="3">All shoes are well stocked.</td></tr><?php endif; ?>
</table></div>

<div class="card"><h3>Best sellers</h3><table>
<tr><th>Shoe</th><th>Pairs sold</th></tr>
<?php foreach ($top as $r): ?><tr><td><?= e($r['brand'].' '.$r['model']) ?></td><td><?= (int)$r['n'] ?></td></tr>
<?php endforeach; if (!$top): ?><tr><td colspan="2">No sales yet.</td></tr><?php endif; ?>
</table></div>
</div>

<div class="card"><h3>Needs attention <a href="orders.php" style="font-size:13px">Open orders</a></h3><table>
<tr><th>Receipt</th><th>Customer</th><th>Payment</th><th>Delivery</th></tr>
<?php foreach ($todo as $r): ?><tr><td>#<?= str_pad($r['id'],4,'0',STR_PAD_LEFT) ?></td><td><?= e($r['name']) ?></td>
<td><?= $r['payment_status']==='Paid' ? '<span class="tag ok">Paid</span>' : '<span class="tag low">Unpaid</span>' ?></td><td><?= e($r['delivery_status']) ?></td></tr>
<?php endforeach; if (!$todo): ?><tr><td colspan="4">Nothing pending. Great job!</td></tr><?php endif; ?>
</table></div>

<div class="card"><h3>Latest activity <a href="activity.php" style="font-size:13px">See all</a></h3><table>
<tr><th>When</th><th>Who</th><th>Action</th><th>Details</th></tr>
<?php foreach ($log as $r): ?><tr><td><?= e(substr($r['created_at'],5,11)) ?></td><td><?= e($r['user_name']) ?> <small>(<?= e($r['role']) ?>)</small></td><td><?= e($r['action']) ?></td><td><?= e($r['details']) ?></td></tr>
<?php endforeach; if (!$log): ?><tr><td colspan="4">No activity recorded yet.</td></tr><?php endif; ?>
</table></div>
<?php require '../includes/footer.php'; ?>
