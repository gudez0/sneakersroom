<?php
$bare = true;
$b = trim($_GET['b'] ?? ''); $m = trim($_GET['m'] ?? ''); $wantColor = trim($_GET['c'] ?? '');
$pageTitle = trim($b . ' ' . $m) ?: 'Shop';
require '../includes/customer_header.php';
$msg = ''; $err = '';
if ($isCustomer) {
    $me = $pdo->prepare('SELECT * FROM users WHERE id = ?'); $me->execute([$user['id']]); $me = $me->fetch();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$isCustomer) {
        $err = 'Log in or register as a customer to place an order.';
    } elseif (!snr_csrf_ok()) {
        $err = 'Your session expired. Please try again.';
    } else {
        $pid = (int)($_POST['product_id'] ?? 0); $qty = max(1, (int)($_POST['qty'] ?? 1)); $pay = $_POST['payment'] ?? '';
        try {
            if ($pid <= 0) throw new Exception('Select a size first.');
            if (!in_array($pay, ['GCash', 'Maya', 'COD (J&T)'], true)) throw new Exception('Choose a payment method.');
            $pdo->beginTransaction();
            $p = $pdo->prepare('SELECT price FROM products WHERE id = ? FOR UPDATE'); $p->execute([$pid]);
            $price = $p->fetchColumn();
            $u = $pdo->prepare('UPDATE products SET qty = qty - ? WHERE id = ? AND qty >= ?'); $u->execute([$qty, $pid, $qty]);
            if ($price === false || $u->rowCount() === 0) throw new Exception('Not enough stock for that quantity.');
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

$st = $pdo->prepare('SELECT * FROM products WHERE brand = ? AND model = ? ORDER BY color, size');
$st->execute([$b, $m]);
$rows = $st->fetchAll();
if (!$rows): ?>
  <div class="card"><p>We could not find that shoe.</p><p><a class="btn" href="index.php">Back to the shop</a></p></div>
<?php require '../includes/footer.php'; exit; endif;

$colors = [];                                       // colour => its rows (every size)
foreach ($rows as $r) $colors[(string)$r['color']][] = $r;
$cur = null;                                        // the colour being shown
if ($wantColor !== '' && isset($colors[$wantColor])) $cur = $wantColor;
if ($cur === null) foreach ($colors as $c => $cr) { foreach ($cr as $r) if ((int)$r['qty'] > 0) { $cur = (string)$c; break 2; } }
if ($cur === null) $cur = (string)array_key_first($colors);
$curRows = $colors[$cur];
usort($curRows, function ($x, $y) { return strnatcmp((string)$x['size'], (string)$y['size']); });
$tot = array_sum(array_map(function ($r) { return (int)$r['qty']; }, $rows));
$imgOf = function (array $cr) { foreach ($cr as $r) if ((string)($r['image'] ?? '') !== '') return $r; return $cr[0]; };
$bySize = [];                                       // every size 36-47 is listed, in stock or not
foreach ($curRows as $r) $bySize[(string)$r['size']] = $r;
foreach (range(36, 47) as $n) if (!isset($bySize[(string)$n]))
    $bySize[(string)$n] = ['id' => 0, 'size' => (string)$n, 'qty' => 0, 'price' => $curRows[0]['price']];
uksort($bySize, 'strnatcmp');
$avail = array_values(array_filter($curRows, function ($r) { return (int)$r['qty'] > 0; }));
$prices = array_map(function ($r) { return (float)$r['price']; }, $avail ?: $curRows);
$priceText = (count(array_unique($prices)) > 1 ? 'From ' : '') . peso(min($prices));
$buy = $tot > 0 && $isCustomer;                     // customers with stock can order
?>
<div class="pd">
  <div class="pd-crumbs"><a href="index.php">&larr; Back</a> &nbsp;/&nbsp; <a href="<?= e(BASE) ?>index.php">Home</a> &nbsp;/&nbsp; <a href="index.php?q=<?= e(rawurlencode($b)) ?>"><?= e($b) ?></a></div>

  <div class="pd-gallery zoom pd-zoom" data-zoom="1.9"><?= snr_img($imgOf($curRows)) ?></div>

  <div class="pd-info">
    <div class="pd-brand"><?= e($b) ?></div>
    <?php if ($tot <= 0): ?><span class="pd-badge out">Out of stock</span>
    <?php elseif ($tot <= 3): ?><span class="pd-badge">Low stock</span><?php endif; ?>
    <h1 class="pd-title"><?= e($m) ?></h1>
    <?php if ($cur !== ''): ?><div class="pd-sub"><b><?= e($cur) ?></b></div><?php endif; ?>
    <div class="pd-price" id="pdPrice"><?= e($priceText) ?></div>

    <?php if (count($colors) > 1): ?>
    <div class="pd-h"><span>Colors</span></div>
    <div class="pd-colors">
      <?php foreach ($colors as $c => $cr): ?>
        <a class="cth <?= (string)$c === $cur ? 'on' : '' ?>" href="<?= e(snr_product_url($b, $m, (string)$c)) ?>" title="<?= e((string)$c) ?>" aria-label="<?= e((string)$c) ?>"><?= snr_img($imgOf($cr)) ?></a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if ($buy): ?><form method="post" class="pd-form"><input type="hidden" name="csrf" value="<?= e(snr_csrf()) ?>"><?php endif; ?>
    <div class="pd-h"><span>Sizes</span></div>
    <div class="pd-sizes">
      <?php foreach ($bySize as $r): $q = (int)$r['qty']; ?>
        <?php if ($buy): ?>
          <label class="sz<?= $q <= 0 ? ' off' : '' ?>"><input type="radio" name="product_id" value="<?= (int)$r['id'] ?>" data-size="<?= e($r['size']) ?>" data-qty="<?= $q ?>" data-price="<?= e((string)(float)$r['price']) ?>" required><span><?php if ($q <= 0): ?><b><?= e($r['size']) ?></b><small>Out of stock</small><?php else: ?><?= e($r['size']) ?><?= $q <= 3 ? '<small>' . $q . ' left</small>' : '' ?><?php endif; ?></span></label>
        <?php elseif ($q <= 0): ?>
          <span class="sz off" data-size="<?= e($r['size']) ?>" tabindex="0" role="button" aria-disabled="true" title="Size <?= e($r['size']) ?> is out of stock"><span><b><?= e($r['size']) ?></b><small>Out of stock</small></span></span>
        <?php else: ?>
          <span class="sz"><span><?= e($r['size']) ?><?= $q <= 3 ? '<small>' . $q . ' left</small>' : '' ?></span></span>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
    <div class="pd-hint"><?= $avail ? ($buy ? 'Select a size' : '') : 'This color is sold out' ?></div>

    <?php if ($tot <= 0): ?>
      <div class="pd-buy"><button type="button" disabled>Out of stock</button></div>
    <?php elseif ($buy): ?>
      <div class="pd-buy">
        <input name="qty" class="pv-qty" type="number" min="1" value="1" aria-label="Quantity">
        <select name="payment" class="pd-pay" aria-label="Payment method"><option>GCash</option><option>Maya</option><option>COD (J&amp;T)</option></select>
        <button>Order now</button>
      </div>
    <?php elseif ($user): ?>
      <div class="pd-buy"><div class="pc-note">Admins cannot place orders.</div></div>
    <?php else: ?>
      <div class="pd-buy"><a class="btn" href="<?= e(BASE) ?>login.php">Log in to order</a></div>
    <?php endif; ?>
    <?php if ($buy): ?></form><?php endif; ?>
  </div>
</div>
<?php require '../includes/footer.php'; ?>