<?php
require '../includes/header.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id'];
    if (($_POST['action'] ?? '') === 'pay') {
        $pdo->prepare("UPDATE orders SET payment_status='Paid' WHERE id=?")->execute([$id]);
        log_activity($pdo, 'Marked order paid', '#' . str_pad($id, 4, '0', STR_PAD_LEFT));
    }
    if (($_POST['action'] ?? '') === 'delivery') {
        $pdo->prepare('UPDATE orders SET delivery_status=? WHERE id=?')->execute([$_POST['status'], $id]);
        log_activity($pdo, 'Updated delivery', '#' . str_pad($id, 4, '0', STR_PAD_LEFT) . ' to ' . $_POST['status']);
        $pdo->prepare("UPDATE orders SET payment_status='Paid' WHERE id=? AND delivery_status='Delivered' AND payment_method='COD (J&T)'")->execute([$id]);
    }
}
$rows = $pdo->query('SELECT o.*, c.name, c.mobile, c.address, p.brand, p.model, p.size
  FROM orders o JOIN customers c ON c.id=o.customer_id JOIN products p ON p.id=o.product_id ORDER BY o.id DESC')->fetchAll();
?>
<div class="card"><table>
<tr><th>Receipt</th><th>Date</th><th>Customer</th><th>Item</th><th>Total</th><th>Payment</th><th>Status</th><th>Delivery</th></tr>
<?php foreach ($rows as $r): ?>
<tr><td>#<?= str_pad($r['id'],4,'0',STR_PAD_LEFT) ?></td><td><?= e(substr($r['created_at'],0,10)) ?></td>
<td><?= e($r['name']) ?><br><small><?= e($r['mobile'].($r['address']?' · '.$r['address']:'')) ?></small></td>
<td><?= e($r['brand'].' '.$r['model'].' ('.$r['size'].') ×'.$r['qty']) ?></td><td><?= peso($r['total']) ?></td><td><?= e($r['payment_method']) ?></td>
<td><?php if ($r['payment_status']==='Paid'): ?><span class="tag ok">Paid</span><?php else: ?>
<form method="post" class="inline"><input type="hidden" name="action" value="pay"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="g">Mark paid</button></form><?php endif; ?></td>
<td><?php if ($r['channel']==='Online'): ?>
<form method="post" class="inline"><input type="hidden" name="action" value="delivery"><input type="hidden" name="id" value="<?= $r['id'] ?>">
<select name="status" onchange="this.form.submit()">
<?php foreach (['Preparing','At J&T hub','In transit','Delivered'] as $s): ?><option <?= $s===$r['delivery_status']?'selected':'' ?>><?= e($s) ?></option><?php endforeach; ?>
</select></form><?php else: ?>Released in store<?php endif; ?></td></tr>
<?php endforeach; if (!$rows): ?><tr><td colspan="8">No orders yet.</td></tr><?php endif; ?>
</table></div>
<?php require '../includes/footer.php'; ?>
