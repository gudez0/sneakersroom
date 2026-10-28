<?php
require '../includes/header.php';
$msg = ''; $err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ch = $_POST['channel']; $pay = $_POST['payment'];
    $name = trim($_POST['name']); $mobile = trim($_POST['mobile']); $addr = trim($_POST['address']);
    $pid = (int)$_POST['product_id']; $qty = max(1, (int)$_POST['qty']);
    if ($name === '' || $mobile === '') $err = 'Enter the customer name and mobile number.';
    elseif ($ch === 'Online' && $addr === '') $err = 'Online orders need a delivery address.';
    else {
        try {
            $pdo->beginTransaction();
            $p = $pdo->prepare('SELECT price FROM products WHERE id = ? FOR UPDATE'); $p->execute([$pid]);
            $price = $p->fetchColumn();
            $u = $pdo->prepare('UPDATE products SET qty = qty - ? WHERE id = ? AND qty >= ?');
            $u->execute([$qty, $pid, $qty]);
            if ($price === false || $u->rowCount() === 0) throw new Exception('Not enough stock for that quantity.');
            $pdo->prepare('INSERT INTO customers (name,mobile,address,email) VALUES (?,?,?,?)')
                ->execute([$name, $mobile, $addr, trim($_POST['email'])]);
            $cid = $pdo->lastInsertId();
            $paid = ($pay === 'Cash') ? 'Paid' : 'Unpaid';
            $del = ($ch === 'Online') ? 'Preparing' : 'Released';
            $pdo->prepare('INSERT INTO orders (customer_id,product_id,qty,total,channel,payment_method,payment_status,delivery_status) VALUES (?,?,?,?,?,?,?,?)')
                ->execute([$cid, $pid, $qty, $price * $qty, $ch, $pay, $paid, $del]);
            $oid = $pdo->lastInsertId();
            $pdo->commit();
            log_activity($pdo, 'Recorded ' . strtolower($ch) . ' order', '#' . str_pad($oid, 4, '0', STR_PAD_LEFT) . ' ' . shoe_label($pdo, $pid) . ' x' . $qty . ' = ' . peso($price * $qty) . ' via ' . $pay);
            $msg = 'Order #' . str_pad($oid, 4, '0', STR_PAD_LEFT) . ' saved. Stock updated.';
        } catch (Exception $x) { if ($pdo->inTransaction()) $pdo->rollBack(); $err = $x->getMessage(); }
    }
}
$shoes = $pdo->query('SELECT * FROM products WHERE qty > 0 ORDER BY brand, model, size')->fetchAll();
?>
<?php if ($msg): ?><div class="msg ok"><?= e($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="msg err"><?= e($err) ?></div><?php endif; ?>
<div class="card"><h3>Record an order</h3>
<form method="post" class="grid">
<label>Channel<select name="channel"><option>Walk-in</option><option>Online</option></select></label>
<label>Customer name<input name="name" required></label>
<label>Mobile number<input name="mobile" required></label>
<label>Address (online orders)<input name="address"></label>
<label>Email<input name="email" type="email"></label>
<label>Shoe<select name="product_id">
<?php foreach ($shoes as $s): ?><option value="<?= $s['id'] ?>"><?= e($s['brand'].' '.$s['model'].', size '.$s['size'].' ('.$s['qty'].' left, '.peso($s['price']).')') ?></option><?php endforeach; ?>
</select></label>
<label>Quantity<input name="qty" type="number" min="1" value="1"></label>
<label>Payment<select name="payment"><option>Cash</option><option>GCash</option><option>Maya</option><option>COD (J&amp;T)</option></select></label>
<label>&nbsp;<button>Save order</button></label>
</form></div>
<?php require '../includes/footer.php'; ?>
