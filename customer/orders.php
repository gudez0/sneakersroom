<?php
$needLogin = true;
require '../includes/customer_header.php';
$st = $pdo->prepare('SELECT o.*, p.brand, p.model, p.size FROM orders o JOIN products p ON p.id = o.product_id WHERE o.user_id = ? ORDER BY o.id DESC');
$st->execute([$_SESSION['user']['id']]);
$rows = $st->fetchAll();
?>
<div class="card"><table>
<tr><th>Receipt</th><th>Date</th><th>Item</th><th>Total</th><th>Payment</th><th>Paid?</th><th>Delivery</th></tr>
<?php foreach ($rows as $r): ?>
<tr><td>#<?= str_pad($r['id'],4,'0',STR_PAD_LEFT) ?></td><td><?= e(substr($r['created_at'],0,10)) ?></td>
<td><?= e($r['brand'].' '.$r['model'].' ('.$r['size'].') ×'.$r['qty']) ?></td><td><?= peso($r['total']) ?></td>
<td><?= e($r['payment_method']) ?></td><td><?= e($r['payment_status']) ?></td><td><?= e($r['delivery_status']) ?></td></tr>
<?php endforeach; if (!$rows): ?><tr><td colspan="7">You have no orders yet. Visit the Shop to place one.</td></tr><?php endif; ?>
</table></div>
<?php require '../includes/footer.php'; ?>
