<?php
require 'includes/layout.php';
$user = $_SESSION['user'] ?? null;

/* ---------- filters (all whitelisted / prepared) ---------- */
$brand = trim($_GET['brand'] ?? '');
$q     = trim($_GET['q'] ?? '');
$sort  = $_GET['sort'] ?? 'new';
$sorts = ['new' => 'id DESC', 'low' => 'price ASC', 'high' => 'price DESC', 'az' => 'brand ASC, model ASC'];
$orderBy = $sorts[$sort] ?? $sorts['new'];

$where = []; $args = [];
if ($brand !== '') { $where[] = 'brand = ?'; $args[] = $brand; }
if ($q !== '')     { $where[] = '(brand LIKE ? OR model LIKE ?)'; $args[] = "%$q%"; $args[] = "%$q%"; }
$sql = 'SELECT * FROM products' . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
     . ' ORDER BY (qty = 0), ' . $orderBy;           // sold-out pairs always sink to the bottom
$st = $pdo->prepare($sql); $st->execute($args);
$items = $st->fetchAll();

$pairs  = (int)$pdo->query('SELECT COALESCE(SUM(qty),0) FROM products')->fetchColumn();
$models = (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$brands = $pdo->query('SELECT brand, COUNT(*) AS n FROM products GROUP BY brand ORDER BY brand')->fetchAll();

function tab_url(string $b, string $q, string $sort): string {
    $p = array_filter(['brand' => $b, 'q' => $q, 'sort' => $sort === 'new' ? '' : $sort], 'strlen');
    return 'index.php' . ($p ? '?' . http_build_query($p) : '') . '#shop';
}

snr_page_start('Home', 'home');
?>
<div class="shop-h">
  <h1><?= $brand !== '' ? e($brand) : 'All sneakers' ?></h1>
  <div class="muted"><?= $pairs ?> pairs in stock across <?= $models ?> listings<?= $user ? ' &middot; Signed in as ' . e($user['name']) : '' ?></div>
</div>

<div class="coll" id="shop" style="margin-top:20px"><div class="tabs">
  <a href="<?= e(tab_url('', $q, $sort)) ?>" class="<?= $brand === '' ? 'on' : '' ?>">All</a>
  <?php foreach ($brands as $b): ?>
  <a href="<?= e(tab_url($b['brand'], $q, $sort)) ?>" class="<?= $brand === $b['brand'] ? 'on' : '' ?>"><?= e($b['brand']) ?><small><?= (int)$b['n'] ?></small></a>
  <?php endforeach; ?>
</div></div>

<div class="toolbar">
  <form method="get" action="#shop">
    <?php if ($brand !== ''): ?><input type="hidden" name="brand" value="<?= e($brand) ?>"><?php endif; ?>
    <input type="search" name="q" placeholder="Search brand or model" value="<?= e($q) ?>">
    <select name="sort" onchange="this.form.submit()">
      <option value="new"  <?= $sort === 'new'  ? 'selected' : '' ?>>Newest</option>
      <option value="low"  <?= $sort === 'low'  ? 'selected' : '' ?>>Price: low to high</option>
      <option value="high" <?= $sort === 'high' ? 'selected' : '' ?>>Price: high to low</option>
      <option value="az"   <?= $sort === 'az'   ? 'selected' : '' ?>>A &ndash; Z</option>
    </select>
    <button class="sm">Search</button>
    <?php if ($q !== '' || $brand !== ''): ?><a class="btn ghost sm" href="index.php#shop">Clear</a><?php endif; ?>
  </form>
  <div class="count"><?= count($items) ?> pair listing<?= count($items) === 1 ? '' : 's' ?></div>
</div>

<div class="pgrid">
<?php foreach ($items as $p): $sold = (int)$p['qty'] <= 0; $lowq = !$sold && (int)$p['qty'] <= 3; ?>
  <a class="pc <?= $sold ? 'sold' : '' ?>" href="customer/index.php">
    <div class="pc-img">
      <?= snr_img($p) ?>
      <?php if ($sold): ?><span class="pc-flag out">Out of stock</span>
      <?php elseif ($lowq): ?><span class="pc-flag">Only <?= (int)$p['qty'] ?> left</span><?php endif; ?>
    </div>
    <div class="pc-name"><?= e($p['brand'] . ' ' . $p['model']) ?></div>
    <div class="pc-meta">Size <?= e((string)$p['size']) ?></div>
    <div class="pc-price"><?= peso($p['price']) ?></div>
  </a>
<?php endforeach; if (!$items): ?>
  <div class="empty"><?= ($q !== '' || $brand !== '') ? 'No sneakers match your search.' : 'No sneakers listed yet. Check back soon.' ?></div>
<?php endif; ?>
</div>

<?php if (!$user): ?>
<section class="band"><div class="band-in">
  <h3>Browse as a guest.<br>Sign Up to order &amp; track delivery.</h3>
  <div><a class="btn" href="Sign Up.php">Create account</a> <a class="btn ghost" href="login.php">Sign In</a></div>
</div></section>
<?php endif; ?>

<?php snr_page_end(); ?>