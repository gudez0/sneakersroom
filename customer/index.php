<?php
$pageTitle = 'Shop';
require '../includes/customer_header.php';
$q = trim($_GET['q'] ?? '');
$st = $pdo->prepare('SELECT * FROM products WHERE CONCAT(brand," ",model) LIKE ? ORDER BY (qty = 0), brand, model, color, size');
$st->execute(["%$q%"]);
$groups = snr_group_products($st->fetchAll());
?>
<?php if (!$user): ?><div class="msg">Browsing as a guest. <a href="<?= e(BASE) ?>login.php"><b>Log in</b></a> or <a href="<?= e(BASE) ?>register.php"><b>register</b></a> to place an order.</div><?php endif; ?>
<form method="get" class="toolbar" style="padding:0 0 18px">
  <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search brand or model">
  <button class="sm">Search</button>
  <?php if ($q !== ''): ?><a class="btn ghost sm" href="index.php">Clear</a><?php endif; ?>
  <span class="count"><?= count($groups) ?> shoe<?= count($groups) === 1 ? '' : 's' ?></span>
</form>
<div class="pgrid" style="padding:0">
<?php foreach ($groups as $g) echo snr_model_card($g); ?>
<?php if (!$groups): ?><div class="empty">No sneakers match your search.</div><?php endif; ?>
</div>
<?php require '../includes/footer.php'; ?>