<?php
$pageTitle = 'Shop';
require '../includes/customer_header.php';
$msg = ''; $err = '';
if ($isCustomer) {
    $me = $pdo->prepare('SELECT * FROM users WHERE id = ?'); $me->execute([$user['id']]); $me = $me->fetch();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$isCustomer) {
        $err = 'Sign In or Sign Up as a customer to place an order.';
    } elseif (!snr_csrf_ok()) {
        $err = 'Your session expired. Please try again.';
    } else {
        $pid = (int)$_POST['product_id']; $qty = max(1, (int)$_POST['qty']); $pay = $_POST['payment'] ?? '';
        try {
            if (!in_array($pay, ['GCash', 'Maya', 'COD (J&T)'], true)) throw new Exception('Choose a payment method.');
            $pdo->beginTransaction();
            $p = $pdo->prepare('SELECT price FROM products WHERE id = ? FOR UPDATE'); $p->execute([$pid]);
            $price = $p->fetchColumn();
            $u = $pdo->prepare('UPDATE products SET qty = qty - ? WHERE id = ? AND qty >= ?'); $u->execute([$qty, $pid, $qty]);
            if ($price === false || $u->rowCount() === 0) throw new Exception('Not enough stock for that qsnrntity.');
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
$st = $pdo->prepare('SELECT * FROM products WHERE CONCAT(brand," ",model) LIKE ? ORDER BY (qty = 0), brand, model, size');
$st->execute(["%$q%"]);
$rows = $st->fetchAll();
?>
<?php if (!$user): ?><div class="msg">Browsing as a guest. <a href="<?= e(BASE) ?>login.php"><b>Sign In</b></a> or <a href="<?= e(BASE) ?>Sign Up.php"><b>Sign Up</b></a> to place an order.</div><?php endif; ?>
<?php if ($msg): ?><div class="msg ok"><?= e($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="msg err"><?= e($err) ?></div><?php endif; ?>

<form method="get" class="toolbar" style="padding:0 0 18px">
  <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search brand or model">
  <button class="sm">Search</button>
  <?php if ($q !== ''): ?><a class="btn ghost sm" href="index.php">Clear</a><?php endif; ?>
  <span class="count"><?= count($rows) ?> listing<?= count($rows) === 1 ? '' : 's' ?></span>
</form>

<div class="pgrid" style="padding:0">
<?php foreach ($rows as $r): $sold = (int)$r['qty'] <= 0; ?>
  <div class="pc <?= $sold ? 'sold' : '' ?>">
    <div class="pc-img"><?= snr_img($r) ?>
      <?php if ($sold): ?><span class="pc-flag out">Out of stock</span>
      <?php elseif ((int)$r['qty'] <= 3): ?><span class="pc-flag">Only <?= (int)$r['qty'] ?> left</span><?php endif; ?>
    </div>
    <div class="pc-name"><?= e($r['brand'] . ' ' . $r['model']) ?></div>
    <div class="pc-meta">Size <?= e($r['size']) ?></div>
    <div class="pc-price"><?= peso($r['price']) ?></div>
    <?php if ($sold): ?>
      <div class="pc-note">Currently unavailable</div>
    <?php elseif ($isCustomer): ?>
      <form method="post" class="pc-buy">
        <input type="hidden" name="csrf" value="<?= e(snr_csrf()) ?>">
        <input type="hidden" name="product_id" value="<?= (int)$r['id'] ?>">
        <input name="qty" type="number" min="1" max="<?= (int)$r['qty'] ?>" value="1" aria-label="Qsnrntity">
        <select name="payment" aria-label="Payment method"><option>GCash</option><option>Maya</option><option>COD (J&amp;T)</option></select>
        <button>Order now</button>
      </form>
    <?php elseif ($user): ?><div class="pc-note">Admins cannot place orders</div>
    <?php else: ?><div class="pc-note"><a href="<?= e(BASE) ?>login.php">Sign In to order</a></div>
    <?php endif; ?>
  </div>
<?php endforeach; if (!$rows): ?><div class="empty">No sneakers match your search.</div><?php endif; ?>
</div>
<?php require '../includes/footer.php'; ?>