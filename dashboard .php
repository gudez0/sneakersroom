<?php
// ADMIN DASHBOARD: edit stock, monitor orders and activity, compute totals.
require __DIR__ . '/../includes/auth.php';

$user = $_SESSION['user'] ?? null;
if (!$user || ($user['role'] ?? '') !== 'admin') {
    header('Location: ../login.php'); exit;
}

if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
$csrf = $_SESSION['csrf'];

$msg = ''; $err = '';

/* ---------- EDIT: add / update / delete shoes ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($csrf, $_POST['csrf'] ?? '')) {
        $err = 'Your session expired. Please try again.';
    } else {
        try {
            $act = $_POST['action'] ?? '';
            if ($act === 'add' || $act === 'update') {
                $brand = trim($_POST['brand'] ?? '');
                $model = trim($_POST['model'] ?? '');
                $size  = trim($_POST['size'] ?? '');
                $price = (float)($_POST['price'] ?? 0);
                $qty   = (int)($_POST['qty'] ?? 0);
                if ($brand === '' || $model === '' || $size === '') {
                    $err = 'Brand, model and size are required.';
                } elseif ($price < 0 || $qty < 0) {
                    $err = 'Price and quantity cannot be negative.';
                } elseif ($act === 'add') {
                    $pdo->prepare('INSERT INTO products (brand,model,size,price,qty) VALUES (?,?,?,?,?)')
                        ->execute([$brand, $model, $size, $price, $qty]);
                    log_activity($pdo, 'Added shoe', "$brand $model (size $size, qty $qty)");
                    $msg = 'Shoe added.';
                } else {
                    $pdo->prepare('UPDATE products SET brand=?, model=?, size=?, price=?, qty=? WHERE id=?')
                        ->execute([$brand, $model, $size, $price, $qty, (int)$_POST['id']]);
                    log_activity($pdo, 'Updated shoe', "$brand $model (size $size, qty $qty, price $price)");
                    $msg = 'Shoe updated.';
                }
            } elseif ($act === 'delete') {
                $pdo->prepare('DELETE FROM products WHERE id=?')->execute([(int)$_POST['id']]);
                log_activity($pdo, 'Deleted shoe', 'Product #' . (int)$_POST['id']);
                $msg = 'Shoe deleted.';
            }
        } catch (PDOException $x) {
            $err = 'Database error: ' . $x->getMessage();
        }
    }
}

/* ---------- COMPUTE: stock figures ---------- */
$pairs    = (int)$pdo->query('SELECT COALESCE(SUM(qty),0) FROM products')->fetchColumn();
$models   = (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$out      = (int)$pdo->query('SELECT COUNT(*) FROM products WHERE qty = 0')->fetchColumn();
$low      = (int)$pdo->query('SELECT COUNT(*) FROM products WHERE qty BETWEEN 1 AND 3')->fetchColumn();
$invValue = (float)$pdo->query('SELECT COALESCE(SUM(qty*price),0) FROM products')->fetchColumn();
$avgPrice = (float)$pdo->query('SELECT COALESCE(AVG(price),0) FROM products')->fetchColumn();
$customers = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn();
$products = $pdo->query('SELECT * FROM products ORDER BY id DESC')->fetchAll();

/* ---------- MONITOR + COMPUTE: orders (columns are detected, not assumed) ---------- */
function pick(array $have, array $want) {
    foreach ($want as $w) if (in_array($w, $have, true)) return $w;
    return null;
}
$ocols = [];
try { foreach ($pdo->query('SHOW COLUMNS FROM orders') as $c) $ocols[] = $c['Field']; } catch (PDOException $x) {}
$tcol = pick($ocols, ['total', 'total_amount', 'amount', 'grand_total', 'total_price']);
$scol = pick($ocols, ['status', 'order_status']);
$dcol = pick($ocols, ['created_at', 'order_date', 'ordered_at', 'date']);

$orderCount = 0; $sales = null; $monthSales = null; $byStatus = []; $recent = [];
if ($ocols) {
    $orderCount = (int)$pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
    $notCancelled = $scol ? "WHERE LOWER(`$scol`) NOT IN ('cancelled','canceled','rejected')" : '';
    if ($tcol) {
        $sales = (float)$pdo->query("SELECT COALESCE(SUM(`$tcol`),0) FROM orders $notCancelled")->fetchColumn();
        if ($dcol) {
            $and = $notCancelled ? str_replace('WHERE', 'AND', $notCancelled) : '';
            $monthSales = (float)$pdo->query(
                "SELECT COALESCE(SUM(`$tcol`),0) FROM orders
                 WHERE DATE_FORMAT(`$dcol`,'%Y-%m') = DATE_FORMAT(NOW(),'%Y-%m') $and")->fetchColumn();
        }
    }
    if ($scol) {
        $sum = $tcol ? ", COALESCE(SUM(`$tcol`),0) AS amt" : ", 0 AS amt";
        $byStatus = $pdo->query("SELECT `$scol` AS st, COUNT(*) AS n $sum FROM orders GROUP BY `$scol`")->fetchAll();
    }
    $order = in_array('id', $ocols, true) ? 'ORDER BY id DESC' : '';
    $recent = $pdo->query("SELECT * FROM orders $order LIMIT 10")->fetchAll();
}

/* ---------- MONITOR: activity log ---------- */
$log = [];
try { $log = $pdo->query('SELECT * FROM activity_log ORDER BY id DESC LIMIT 15')->fetchAll(); } catch (PDOException $x) {}

$btn = 'text-decoration:none;display:inline-block;color:#fff';
?>
<!DOCTYPE html>
<html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin dashboard - Sneakers Room</title>
<link rel="stylesheet" href="<?= BASE ?>assets/style.css">
<style>
  .scroll { overflow-x: auto; }
  td input { width: 100%; min-width: 70px; box-sizing: border-box; }
  td .row-actions { white-space: nowrap; }
  .calc { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 10px; }
</style>
</head><body><div class="wrap">
<h1>Sneakers Room &middot; Admin</h1>
<nav>
  <a href="../index.php">Home dashboard</a>
  <a href="../customer/index.php">Shop</a>
  <a href="dashboard.php" class="on">Admin dashboard</a>
  <a href="../logout.php">Log out</a>
</nav>

<div class="msg ok">Signed in as <?= e($user['name']) ?> (admin).</div>
<?php if ($msg): ?><div class="msg ok"><?= e($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="msg err"><?= e($err) ?></div><?php endif; ?>

<!-- COMPUTE -->
<h2>Totals</h2>
<div class="stats">
  <div class="stat"><b><?= $pairs ?></b><span>Pairs in stock</span></div>
  <div class="stat"><b><?= $models ?></b><span>Shoe listings</span></div>
  <div class="stat"><b><?= $out ?></b><span>Sold out</span></div>
  <div class="stat"><b><?= $low ?></b><span>Low stock (1-3 left)</span></div>
</div>
<div class="stats">
  <div class="stat"><b><?= peso($invValue) ?></b><span>Stock value (qty &times; price)</span></div>
  <div class="stat"><b><?= peso($avgPrice) ?></b><span>Average price</span></div>
  <div class="stat"><b><?= $orderCount ?></b><span>Orders</span></div>
  <div class="stat"><b><?= $customers ?></b><span>Customers</span></div>
</div>
<div class="stats">
  <div class="stat"><b><?= $sales === null ? 'n/a' : peso($sales) ?></b><span>Total sales (excl. cancelled)</span></div>
  <div class="stat"><b><?= $monthSales === null ? 'n/a' : peso($monthSales) ?></b><span>Sales this month</span></div>
</div>
<?php if ($ocols && $sales === null): ?>
  <div class="msg err">Could not find a total/amount column in the orders table, so sales cannot be computed.
  Columns found: <?= e(implode(', ', $ocols)) ?>.</div>
<?php elseif (!$ocols): ?>
  <div class="msg err">No orders table found, so order monitoring and sales are unavailable.</div>
<?php endif; ?>

<div class="card"><h3>Profit calculator</h3>
  <div class="calc">
    <label>Cost per pair<input id="c-cost" type="number" step="0.01" min="0" value="0"></label>
    <label>Selling price<input id="c-sell" type="number" step="0.01" min="0" value="0"></label>
    <label>Pairs sold<input id="c-qty" type="number" step="1" min="0" value="1"></label>
  </div>
  <p>Revenue: <b id="c-rev">0.00</b> &nbsp; Profit: <b id="c-profit">0.00</b> &nbsp; Margin: <b id="c-margin">0.0%</b></p>
</div>

<!-- EDIT -->
<h2>Stock</h2>
<div class="card"><h3>Add a shoe</h3>
<form method="post" class="calc">
  <input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="action" value="add">
  <label>Brand<input name="brand" required></label>
  <label>Model<input name="model" required></label>
  <label>Size<input name="size" required></label>
  <label>Price<input name="price" type="number" step="0.01" min="0" required></label>
  <label>Quantity<input name="qty" type="number" min="0" required></label>
  <label>&nbsp;<button>Add shoe</button></label>
</form></div>

<div class="card"><h3>Edit or remove shoes</h3><div class="scroll"><table>
<tr><th>Brand</th><th>Model</th><th>Size</th><th>Price</th><th>Qty</th><th>Status</th><th></th></tr>
<?php foreach ($products as $p): $f = 'f' . (int)$p['id']; ?>
<tr>
  <td><input form="<?= $f ?>" name="brand" value="<?= e($p['brand']) ?>" required></td>
  <td><input form="<?= $f ?>" name="model" value="<?= e($p['model']) ?>" required></td>
  <td><input form="<?= $f ?>" name="size"  value="<?= e($p['size']) ?>" required></td>
  <td><input form="<?= $f ?>" name="price" type="number" step="0.01" min="0" value="<?= e($p['price']) ?>" required></td>
  <td><input form="<?= $f ?>" name="qty"   type="number" min="0" value="<?= e($p['qty']) ?>" required></td>
  <td><?= stock_tag($p['qty']) ?></td>
  <td class="row-actions">
    <form id="<?= $f ?>" method="post" style="display:inline">
      <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
      <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
      <button name="action" value="update">Save</button>
    </form>
    <form method="post" style="display:inline" onsubmit="return confirm('Delete this shoe?')">
      <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
      <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
      <button name="action" value="delete">Delete</button>
    </form>
  </td>
</tr>
<?php endforeach; if (!$products): ?><tr><td colspan="7">No shoes yet. Add one above.</td></tr><?php endif; ?>
</table></div></div>

<!-- MONITOR -->
<h2>Orders</h2>
<?php if ($byStatus): ?>
<div class="card"><h3>Orders by status</h3><table>
<tr><th>Status</th><th>Orders</th><?php if ($tcol): ?><th>Amount</th><?php endif; ?></tr>
<?php foreach ($byStatus as $s): ?>
<tr><td><?= e($s['st']) ?></td><td><?= (int)$s['n'] ?></td><?php if ($tcol): ?><td><?= peso($s['amt']) ?></td><?php endif; ?></tr>
<?php endforeach; ?>
</table></div>
<?php endif; ?>

<div class="card"><h3>Latest orders</h3><div class="scroll"><table>
<?php if ($recent): ?>
<tr><?php foreach (array_keys($recent[0]) as $col): ?><th><?= e($col) ?></th><?php endforeach; ?></tr>
<?php foreach ($recent as $r): ?><tr>
  <?php foreach ($r as $col => $val): ?>
    <td><?= ($col === $tcol && is_numeric($val)) ? peso($val) : e((string)$val) ?></td>
  <?php endforeach; ?>
</tr><?php endforeach; else: ?>
<tr><td>No orders yet.</td></tr>
<?php endif; ?>
</table></div></div>

<h2>Activity log</h2>
<div class="card"><div class="scroll"><table>
<tr><th>When</th><th>User</th><th>Role</th><th>Action</th><th>Details</th></tr>
<?php foreach ($log as $l): ?>
<tr><td><?= e($l['created_at']) ?></td><td><?= e($l['user_name']) ?></td><td><?= e($l['role']) ?></td>
    <td><?= e($l['action']) ?></td><td><?= e($l['details']) ?></td></tr>
<?php endforeach; if (!$log): ?><tr><td colspan="5">No activity yet.</td></tr><?php endif; ?>
</table></div></div>

</div>
<script>
(function () {
  var cost = document.getElementById('c-cost'), sell = document.getElementById('c-sell'), qty = document.getElementById('c-qty');
  function fmt(n) { return n.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2}); }
  function calc() {
    var c = parseFloat(cost.value) || 0, s = parseFloat(sell.value) || 0, q = parseFloat(qty.value) || 0;
    var rev = s * q, profit = (s - c) * q;
    document.getElementById('c-rev').textContent = fmt(rev);
    document.getElementById('c-profit').textContent = fmt(profit);
    document.getElementById('c-margin').textContent = (rev > 0 ? (profit / rev * 100).toFixed(1) : '0.0') + '%';
  }
  [cost, sell, qty].forEach(function (el) { el.addEventListener('input', calc); });
})();
</script>
</body></html>
