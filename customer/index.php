<?php
require '../includes/customer_header.php';
$msg = ''; $err = '';
if ($isCustomer) {
    $me = $pdo->prepare('SELECT * FROM users WHERE id = ?'); $me->execute([$user['id']]); $me = $me->fetch();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$isCustomer) {
        $err = 'Please log in or register as a customer to place an order.';
    } else {
        $pid = (int)$_POST['product_id']; $qty = max(1, (int)$_POST['qty']); $pay = $_POST['payment'];
        try {
            if (!in_array($pay, ['GCash', 'Maya', 'COD (J&T)'])) throw new Exception('Choose a payment method.');
            $pdo->beginTransaction();
            $p = $pdo->prepare('SELECT price FROM products WHERE id = ? FOR UPDATE'); $p->execute([$pid]);
            $price = $p->fetchColumn();
            $u = $pdo->prepare('UPDATE products SET qty = qty - ? WHERE id = ? AND qty >= ?'); $u->execute([$qty, $pid, $qty]);
            if ($price === false || $u->rowCount() === 0) throw new Exception('Sorry, not enough stock for that quantity.');
            $pdo->prepare('INSERT INTO customers (name,mobile,address,email) VALUES (?,?,?,?)')
                ->execute([$me['name'], $me['mobile'], $me['address'], $me['email']]);
            $cid = $pdo->lastInsertId();
            $pdo->prepare("INSERT INTO orders (customer_id,product_id,qty,total,channel,payment_method,payment_status,delivery_status,user_id) VALUES (?,?,?,?,'Online',?,'Unpaid','Preparing',?)")
                ->execute([$cid, $pid, $qty, $price * $qty, $pay, $me['id']]);
            $oid = $pdo->lastInsertId();
            $pdo->commit();
            log_activity($pdo, 'Placed order', '#' . str_pad($oid, 4, '0', STR_PAD_LEFT) . ' ' . shoe_label($pdo, $pid) . ' x' . $qty . ' = ' . peso($price * $qty) . ' via ' . $pay);
            $msg = 'Order #' . str_pad($oid, 4, '0', STR_PAD_LEFT) . ' placed. We will deliver to ' . $me['address'] . '.';
        } catch (Exception $x) { if ($pdo->inTransaction()) $pdo->rollBack(); $err = $x->getMessage(); }
    }
}
$q = trim($_GET['q'] ?? '');
$st = $pdo->prepare('SELECT * FROM products WHERE CONCAT(brand," ",model) LIKE ? ORDER BY brand, model, size');
$st->execute(["%$q%"]);
$rows = $st->fetchAll();
?>
<?php if (!$user): ?><div class="msg ok">You are browsing as a guest. <a href="../login.php">Log in</a> or <a href="../register.php">register</a> to place an order.</div><?php endif; ?>
<?php if ($msg): ?><div class="msg ok"><?= e($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="msg err"><?= e($err) ?></div><?php endif; ?>
<div class="card"><form method="get" class="grid">
<label>Search shoes<input name="q" value="<?= e($q) ?>" placeholder="e.g. Air Force"></label>
<label>&nbsp;<button>Search</button></label>
</form></div>
<div class="card"><table>
<tr><th>Shoe</th><th>Size</th><th>Price</th><th>Availability</th><th>Order</th></tr>
<?php foreach ($rows as $r): ?>
<tr><td><?= e($r['brand'].' '.$r['model']) ?></td><td><?= e($r['size']) ?></td><td><?= peso($r['price']) ?></td><td><?= stock_tag($r['qty']) ?></td>
<td>
<?php if ($r['qty'] <= 0): ?>Unavailable
<?php elseif ($isCustomer): ?>
<form method="post" class="inline"><input type="hidden" name="product_id" value="<?= $r['id'] ?>">
<input name="qty" type="number" min="1" max="<?= $r['qty'] ?>" value="1">
<select name="payment"><option>GCash</option><option>Maya</option><option>COD (J&amp;T)</option></select>
<button>Order</button></form>
<?php elseif ($user): ?>Admins cannot place orders
<?php else: ?><a href="../login.php">Log in to order</a>
<?php endif; ?>
</td></tr>
<?php endforeach; if (!$rows): ?><tr><td colspan="5">No shoes found.</td></tr><?php endif; ?>
</table></div>
<?php require '../includes/footer.php'; ?>
