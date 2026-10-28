<?php
// SOLD OUT: every shoe with 0 pairs left, with a quick restock button.
require '../includes/header.php';

if (($_SESSION['user']['role'] ?? '') !== 'admin') {
    echo '<div class="msg err">This page is for admins only.</div>';
    require '../includes/footer.php'; exit;
}

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'restock') {
    $id  = (int)($_POST['id'] ?? 0);
    $add = max(1, (int)($_POST['qty'] ?? 1));
    $pdo->prepare('UPDATE products SET qty = qty + ? WHERE id = ?')->execute([$add, $id]);

    $st = $pdo->prepare('SELECT brand, model, size FROM products WHERE id = ?');
    $st->execute([$id]);
    $p = $st->fetch();
    $label = $p ? $p['brand'] . ' ' . $p['model'] . ' size ' . $p['size'] : 'Product #' . $id;
    log_activity($pdo, 'Restocked sold-out shoe', $label . ' +' . $add);
    $msg = 'Restocked: ' . $label . ' (+' . $add . ').';
}

$rows = $pdo->query('SELECT * FROM products WHERE qty = 0 ORDER BY brand, model, size')->fetchAll();
?>
<?php if ($msg): ?><div class="msg ok"><?= e($msg) ?></div><?php endif; ?>

<div class="card"><h3>Sold out (<?= count($rows) ?>)</h3>
<table>
<tr><th>Shoe</th><th>Size</th><th>Price</th><th>Availability</th><th>Restock</th></tr>
<?php foreach ($rows as $r): ?>
<tr>
  <td><?= e($r['brand'] . ' ' . $r['model']) ?></td>
  <td><?= e($r['size']) ?></td>
  <td><?= peso($r['price']) ?></td>
  <td><?= stock_tag($r['qty']) ?></td>
  <td><form method="post" class="inline">
    <input type="hidden" name="action" value="restock">
    <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
    Add <input name="qty" type="number" min="1" value="1"> pairs <button class="g">Restock</button>
  </form></td>
</tr>
<?php endforeach; if (!$rows): ?>
<tr><td colspan="5">Nothing is sold out right now.</td></tr>
<?php endif; ?>
</table></div>
<?php require '../includes/footer.php'; ?>
