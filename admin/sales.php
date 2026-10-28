<?php
require '../includes/header.php';
$by = $pdo->query("SELECT payment_method, SUM(total) t, COUNT(*) n FROM orders WHERE payment_status='Paid' GROUP BY payment_method")->fetchAll();
$all = $pdo->query("SELECT SUM(total) FROM orders WHERE payment_status='Paid'")->fetchColumn();
$today = $pdo->query("SELECT SUM(total) FROM orders WHERE payment_status='Paid' AND DATE(created_at)=CURDATE()")->fetchColumn();
$rows = $pdo->query("SELECT o.id,o.created_at,o.qty,o.total,o.payment_method,p.brand,p.model,p.size FROM orders o JOIN products p ON p.id=o.product_id WHERE o.payment_status='Paid' ORDER BY o.id DESC")->fetchAll();
?>
<div class="card"><h3>Totals</h3>
<p>Sales today: <b><?= peso($today ?: 0) ?></b> &nbsp; All paid sales: <b><?= peso($all ?: 0) ?></b></p>
<?php foreach ($by as $b): ?><span class="tag ok"><?= e($b['payment_method']) ?>: <?= peso($b['t']) ?> (<?= $b['n'] ?>)</span> <?php endforeach; ?>
</div>
<div class="card"><table>
<tr><th>Receipt</th><th>Date</th><th>Item</th><th>Qty</th><th>Amount</th><th>Method</th></tr>
<?php foreach ($rows as $r): ?>
<tr><td>#<?= str_pad($r['id'],4,'0',STR_PAD_LEFT) ?></td><td><?= e(substr($r['created_at'],0,10)) ?></td><td><?= e($r['brand'].' '.$r['model'].' ('.$r['size'].')') ?></td><td><?= $r['qty'] ?></td><td><?= peso($r['total']) ?></td><td><?= e($r['payment_method']) ?></td></tr>
<?php endforeach; if (!$rows): ?><tr><td colspan="6">Paid orders appear here as sales records.</td></tr><?php endif; ?>
</table></div>
<?php require '../includes/footer.php'; ?>
