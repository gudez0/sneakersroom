<?php
// ADMIN DASHBOARD: manage stock (with photos), monitor orders and activity, compute totals.
require __DIR__ . '/../includes/layout.php';

$user = $_SESSION['user'] ?? null;
if (!$user || ($user['role'] ?? '') !== 'admin') { header('Location: ../login.php'); exit; }

$ok  = $_SESSION['flash_ok'] ?? ''; unset($_SESSION['flash_ok']);
$err = '';
$form = null;

/* ---------- ADD / UPDATE / DELETE shoes ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['action'] ?? '';
    if (!snr_csrf_ok()) {
        $err = 'Your session expired. Please try again.';
    } else {
        try {
            if ($act === 'save') {
                $id    = (int)($_POST['id'] ?? 0);
                $brand = trim($_POST['brand'] ?? '');
                $model = trim($_POST['model'] ?? '');
                $size  = trim($_POST['size'] ?? '');
                $price = (float)($_POST['price'] ?? 0);
                $qty   = (int)($_POST['qty'] ?? 0);
                $form  = ['id' => $id, 'brand' => $brand, 'model' => $model, 'size' => $size, 'price' => $price, 'qty' => $qty, 'image' => null];
                if ($id) {
                    $o = $pdo->prepare('SELECT image FROM products WHERE id=?'); $o->execute([$id]);
                    $form['image'] = $o->fetchColumn() ?: null;
                }
                if ($brand === '' || $model === '' || $size === '') {
                    $err = 'Brand, model and size are required.';
                } elseif ($price < 0 || $qty < 0) {
                    $err = 'Price and qsnrntity cannot be negative.';
                } else {
                    $imgErr = '';
                    $new = snr_save_image($_FILES['image'] ?? [], $imgErr);
                    if ($imgErr) {
                        $err = $imgErr;
                    } else {
                        $old = $form['image'];
                        $img = $old;
                        if (!empty($_POST['remove_image'])) $img = null;
                        if ($new !== null) $img = $new;
                        if ($id) {
                            $pdo->prepare('UPDATE products SET brand=?, model=?, size=?, price=?, qty=?, image=? WHERE id=?')
                                ->execute([$brand, $model, $size, $price, $qty, $img, $id]);
                            if ($old && $old !== $img) snr_delete_image($old);
                            log_activity($pdo, 'Updated shoe', "$brand $model (size $size, qty $qty, price $price)");
                            $_SESSION['flash_ok'] = 'Shoe updated.';
                        } else {
                            $pdo->prepare('INSERT INTO products (brand,model,size,price,qty,image) VALUES (?,?,?,?,?,?)')
                                ->execute([$brand, $model, $size, $price, $qty, $new]);
                            log_activity($pdo, 'Added shoe', "$brand $model (size $size, qty $qty)");
                            $_SESSION['flash_ok'] = 'Shoe added.';
                        }
                        header('Location: dashboard.php#stock'); exit;
                    }
                }
            } elseif ($act === 'delete') {
                $id = (int)($_POST['id'] ?? 0);
                $o = $pdo->prepare('SELECT brand, model, image FROM products WHERE id=?'); $o->execute([$id]);
                if ($row = $o->fetch()) {
                    $pdo->prepare('DELETE FROM products WHERE id=?')->execute([$id]);
                    snr_delete_image($row['image'] ?? null);
                    log_activity($pdo, 'Deleted shoe', $row['brand'] . ' ' . $row['model'] . ' (#' . $id . ')');
                    $_SESSION['flash_ok'] = 'Shoe deleted.';
                }
                header('Location: dashboard.php#stock'); exit;
            }
        } catch (PDOException $x) {
            $err = ($x->getCode() === '23000')
                ? 'This shoe is linked to existing orders, so it cannot be deleted. Set its qsnrntity to 0 to mark it sold out instead.'
                : 'Database error: ' . $x->getMessage();
        }
    }
}

/* ---------- form state (add vs edit) ---------- */
if ($form === null && isset($_GET['edit'])) {
    $st = $pdo->prepare('SELECT * FROM products WHERE id=?'); $st->execute([(int)$_GET['edit']]);
    $form = $st->fetch() ?: null;
}
$f0 = $form ?: ['id' => 0, 'brand' => '', 'model' => '', 'size' => '', 'price' => '', 'qty' => '', 'image' => null];

/* ---------- COMPUTE: stock figures ---------- */
$pairs     = (int)$pdo->query('SELECT COALESCE(SUM(qty),0) FROM products')->fetchColumn();
$models    = (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$out       = (int)$pdo->query('SELECT COUNT(*) FROM products WHERE qty = 0')->fetchColumn();
$low       = (int)$pdo->query('SELECT COUNT(*) FROM products WHERE qty BETWEEN 1 AND 3')->fetchColumn();
$invValue  = (float)$pdo->query('SELECT COALESCE(SUM(qty*price),0) FROM products')->fetchColumn();
$avgPrice  = (float)$pdo->query('SELECT COALESCE(AVG(price),0) FROM products')->fetchColumn();
$customers = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn();
$brandList = $pdo->query('SELECT DISTINCT brand FROM products ORDER BY brand')->fetchAll(PDO::FETCH_COLUMN);

/* ---------- stock list with search + quick filters ---------- */
$s = trim($_GET['s'] ?? ''); $fl = $_GET['f'] ?? '';
$w = []; $a = [];
if ($s !== '') { $w[] = '(brand LIKE ? OR model LIKE ?)'; $a[] = "%$s%"; $a[] = "%$s%"; }
if ($fl === 'low') $w[] = 'qty BETWEEN 1 AND 3'; elseif ($fl === 'out') $w[] = 'qty = 0';
$st = $pdo->prepare('SELECT * FROM products' . ($w ? ' WHERE ' . implode(' AND ', $w) : '') . ' ORDER BY id DESC');
$st->execute($a);
$products = $st->fetchAll();

/* ---------- MONITOR + COMPUTE: orders (columns are detected, not assumed) ---------- */
function pick(array $have, array $want) { foreach ($want as $w) if (in_array($w, $have, true)) return $w; return null; }
$ocols = [];
try { foreach ($pdo->query('SHOW COLUMNS FROM orders') as $c) $ocols[] = $c['Field']; } catch (PDOException $x) {}
$tcol = pick($ocols, ['total', 'total_amount', 'amount', 'grand_total', 'total_price']);
$scol = pick($ocols, ['status', 'order_status']);
$dcol = pick($ocols, ['created_at', 'order_date', 'ordered_at', 'date']);

$orderCount = 0; $sales = null; $monthSales = null; $byStatus = []; $recent = [];
if ($ocols) {
    $orderCount   = (int)$pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
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
        $sum = $tcol ? ", COALESCE(SUM(`$tcol`),0) AS amt" : ', 0 AS amt';
        $byStatus = $pdo->query("SELECT `$scol` AS st, COUNT(*) AS n $sum FROM orders GROUP BY `$scol`")->fetchAll();
    }
    $order  = in_array('id', $ocols, true) ? 'ORDER BY id DESC' : '';
    $recent = $pdo->query("SELECT * FROM orders $order LIMIT 10")->fetchAll();
}

/* ---------- MONITOR: activity log ---------- */
$log = [];
try { $log = $pdo->query('SELECT * FROM activity_log ORDER BY id DESC LIMIT 15')->fetchAll(); } catch (PDOException $x) {}

function f_url(string $s, string $fl): string {
    $p = array_filter(['s' => $s, 'f' => $fl], 'strlen');
    return 'dashboard.php' . ($p ? '?' . http_build_query($p) : '') . '#stock';
}

snr_page_start('Admin', 'admin');
?>
<main class="page">
  <div class="page-h">
    <div><div class="eyebrow">Signed in as <?= e($user['name']) ?></div><h1>Admin</h1></div>
    <a class="btn" href="#form"><?= $f0['id'] ? 'Edit shoe' : '+ Add shoe' ?></a>
  </div>
  <div class="sub"><div class="tabs">
    <a href="#totals">Totals</a><a href="#stock">Stock</a><a href="#orders">Orders</a><a href="#activity">Activity</a>
  </div></div>

  <?php if ($ok):  ?><div class="msg ok"><?= e($ok) ?></div><?php endif; ?>
  <?php if ($err): ?><div class="msg err"><?= e($err) ?></div><?php endif; ?>

  <!-- TOTALS -->
  <h2 id="totals" style="margin-top:8px">Totals</h2>
  <div class="stats">
    <div class="stat"><b><?= $sales === null ? 'n/a' : peso($sales) ?></b><span>Total sales (excl. cancelled)</span></div>
    <div class="stat"><b><?= $monthSales === null ? 'n/a' : peso($monthSales) ?></b><span>Sales this month</span></div>
    <div class="stat"><b><?= $orderCount ?></b><span>Orders</span></div>
    <div class="stat"><b><?= $customers ?></b><span>Customers</span></div>
  </div>
  <div class="stats">
    <div class="stat"><b><?= $pairs ?></b><span>Pairs in stock</span></div>
    <div class="stat"><b><?= $models ?></b><span>Shoe listings</span></div>
    <div class="stat"><b><?= $low ?></b><span>Low stock (1-3 left)</span></div>
    <div class="stat"><b><?= $out ?></b><span>Sold out</span></div>
    <div class="stat"><b><?= peso($invValue) ?></b><span>Stock value</span></div>
    <div class="stat"><b><?= peso($avgPrice) ?></b><span>Average price</span></div>
  </div>
  <?php if ($ocols && $sales === null): ?>
    <div class="msg err">Could not find a total/amount column in the orders table, so sales cannot be computed. Columns found: <?= e(implode(', ', $ocols)) ?>.</div>
  <?php elseif (!$ocols): ?>
    <div class="msg err">No orders table found, so order monitoring and sales are unavailable.</div>
  <?php endif; ?>

  <div class="card"><h3>Profit calculator</h3>
    <div class="calc">
      <label>Cost per pair<input id="c-cost" type="number" step="0.01" min="0" value="0"></label>
      <label>Selling price<input id="c-sell" type="number" step="0.01" min="0" value="0"></label>
      <label>Pairs sold<input id="c-qty" type="number" step="1" min="0" value="1"></label>
    </div>
    <p style="margin:0">Revenue: <b id="c-rev">0.00</b> &nbsp;&middot;&nbsp; Profit: <b id="c-profit">0.00</b> &nbsp;&middot;&nbsp; Margin: <b id="c-margin">0.0%</b></p>
  </div>

  <!-- STOCK: add / edit -->
  <h2 id="stock">Stock</h2>
  <div class="card flat" id="form">
    <h3><?= $f0['id'] ? 'Edit shoe #' . (int)$f0['id'] : 'Add a shoe' ?></h3>
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="csrf" value="<?= e(snr_csrf()) ?>">
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= (int)$f0['id'] ?>">
      <div class="formgrid">
        <label>Brand<input name="brand" list="brands" value="<?= e((string)$f0['brand']) ?>" required></label>
        <label>Model<input name="model" value="<?= e((string)$f0['model']) ?>" required></label>
        <label>Size<input name="size" value="<?= e((string)$f0['size']) ?>" required></label>
        <label>Price<input name="price" type="number" step="0.01" min="0" value="<?= e((string)$f0['price']) ?>" required></label>
        <label>Qsnrntity<input name="qty" type="number" min="0" value="<?= e((string)$f0['qty']) ?>" required></label>
        <label>Photo (JPG, PNG, WebP &middot; max 3 MB)<input name="image" type="file" accept="image/jpeg,image/png,image/webp"></label>
      </div>
      <datalist id="brands"><?php foreach ($brandList as $b): ?><option value="<?= e($b) ?>"><?php endforeach; ?></datalist>
      <?php if ($f0['id'] && !empty($f0['image'])): ?>
        <div style="display:flex;gap:16px;align-items:center;margin-bottom:16px">
          <div class="thumb"><?= snr_img($f0) ?></div>
          <label style="margin:0"><input type="checkbox" name="remove_image" value="1">Remove current photo</label>
        </div>
      <?php endif; ?>
      <button><?= $f0['id'] ? 'Save changes' : 'Add shoe' ?></button>
      <?php if ($f0['id']): ?><a class="btn ghost" href="dashboard.php#stock">Cancel</a><?php endif; ?>
    </form>
  </div>

  <div class="tabs" style="margin-bottom:16px">
    <a href="<?= e(f_url($s, '')) ?>"    class="<?= $fl === ''    ? 'on' : '' ?>">All<small><?= $models ?></small></a>
    <a href="<?= e(f_url($s, 'low')) ?>" class="<?= $fl === 'low' ? 'on' : '' ?>">Low stock<small><?= $low ?></small></a>
    <a href="<?= e(f_url($s, 'out')) ?>" class="<?= $fl === 'out' ? 'on' : '' ?>">Sold out<small><?= $out ?></small></a>
  </div>
  <form method="get" class="filters">
    <?php if ($fl !== ''): ?><input type="hidden" name="f" value="<?= e($fl) ?>"><?php endif; ?>
    <input type="search" name="s" placeholder="Search brand or model" value="<?= e($s) ?>">
    <button class="sm">Search</button>
    <?php if ($s !== ''): ?><a class="btn ghost sm" href="<?= e(f_url('', $fl)) ?>">Clear</a><?php endif; ?>
  </form>

  <div class="card" style="padding:0"><div class="scroll"><table>
    <tr><th></th><th>Brand</th><th>Model</th><th>Size</th><th>Price</th><th>Qty</th><th>Status</th><th></th></tr>
    <?php foreach ($products as $p): ?>
    <tr>
      <td><div class="thumb"><?= snr_img($p) ?></div></td>
      <td><b><?= e($p['brand']) ?></b></td>
      <td><?= e($p['model']) ?></td>
      <td><?= e((string)$p['size']) ?></td>
      <td><?= peso($p['price']) ?></td>
      <td><?= (int)$p['qty'] ?></td>
      <td><?= snr_stock($p['qty']) ?></td>
      <td class="row-actions">
        <a class="btn ghost sm" href="dashboard.php?edit=<?= (int)$p['id'] ?>#form">Edit</a>
        <form method="post" onsubmit="return confirm('Delete this shoe?')">
          <input type="hidden" name="csrf" value="<?= e(snr_csrf()) ?>">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
          <button class="sm danger">Delete</button>
        </form>
      </td>
    </tr>
    <?php endforeach; if (!$products): ?><tr><td colspan="8" class="muted" style="text-align:center;padding:40px">No shoes found.</td></tr><?php endif; ?>
  </table></div></div>

  <!-- ORDERS -->
  <h2 id="orders">Orders</h2>
  <?php if ($byStatus): ?>
  <div class="card" style="padding:0"><table>
    <tr><th>Status</th><th>Orders</th><?php if ($tcol): ?><th>Amount</th><?php endif; ?></tr>
    <?php foreach ($byStatus as $r): ?>
    <tr><td><span class="tag"><?= e((string)$r['st']) ?></span></td><td><?= (int)$r['n'] ?></td><?php if ($tcol): ?><td><?= peso($r['amt']) ?></td><?php endif; ?></tr>
    <?php endforeach; ?>
  </table></div>
  <?php endif; ?>

  <div class="card" style="padding:0"><div class="scroll"><table>
    <?php if ($recent): ?>
    <tr><?php foreach (array_keys($recent[0]) as $col): ?><th><?= e($col) ?></th><?php endforeach; ?></tr>
    <?php foreach ($recent as $r): ?><tr>
      <?php foreach ($r as $col => $val): ?>
        <td><?php if ($col === $tcol && is_numeric($val)): ?><?= peso($val) ?>
            <?php elseif ($col === $scol): ?><span class="tag"><?= e((string)$val) ?></span>
            <?php else: ?><?= e((string)$val) ?><?php endif; ?></td>
      <?php endforeach; ?>
    </tr><?php endforeach; else: ?>
    <tr><td class="muted" style="text-align:center;padding:40px">No orders yet.</td></tr>
    <?php endif; ?>
  </table></div></div>

  <!-- ACTIVITY -->
  <h2 id="activity">Activity log</h2>
  <div class="card" style="padding:0"><div class="scroll"><table>
    <tr><th>When</th><th>User</th><th>Role</th><th>Action</th><th>Details</th></tr>
    <?php foreach ($log as $l): ?>
    <tr><td class="muted"><?= e($l['created_at']) ?></td><td><?= e($l['user_name']) ?></td><td><?= e($l['role']) ?></td>
        <td><b><?= e($l['action']) ?></b></td><td><?= e($l['details']) ?></td></tr>
    <?php endforeach; if (!$log): ?><tr><td colspan="5" class="muted" style="text-align:center;padding:40px">No activity yet.</td></tr><?php endif; ?>
  </table></div></div>
</main>

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
<?php snr_page_end(); ?>
