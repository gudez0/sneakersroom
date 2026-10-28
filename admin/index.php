<?php
require '../includes/header.php';
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $a = $_POST['action'] ?? '';
    if ($a === 'add') {
        $st = $pdo->prepare('INSERT INTO products (brand,model,size,price,qty) VALUES (?,?,?,?,?)
            ON DUPLICATE KEY UPDATE qty = qty + VALUES(qty), price = VALUES(price)');
        $st->execute([$_POST['brand'], trim($_POST['model']), trim($_POST['size']), $_POST['price'], max(1,(int)$_POST['qty'])]);
        log_activity($pdo, 'Added/restocked stock', $_POST['brand'] . ' ' . trim($_POST['model']) . ' size ' . trim($_POST['size']) . ' +' . max(1,(int)$_POST['qty']));
        $msg = 'Stock saved.';
    } elseif ($a === 'update') {
        $pdo->prepare('UPDATE products SET price = ?, qty = ? WHERE id = ?')->execute([max(0,(float)$_POST['price']), max(0,(int)$_POST['qty']), $_POST['id']]);
        log_activity($pdo, 'Edited stock', shoe_label($pdo, $_POST['id']) . ' now qty ' . max(0,(int)$_POST['qty']) . ', price ' . peso(max(0,(float)$_POST['price'])));
        $msg = 'Item updated.';
    } elseif ($a === 'delete') {
        $label = shoe_label($pdo, $_POST['id']);
        try { $pdo->prepare('DELETE FROM products WHERE id = ?')->execute([$_POST['id']]); log_activity($pdo, 'Removed shoe', $label); }
        catch (PDOException $x) { $msg = 'Cannot remove a shoe that already has orders.'; }
    }
}
$q = trim($_GET['q'] ?? ''); $s = trim($_GET['s'] ?? '');
$st = $pdo->prepare('SELECT * FROM products WHERE CONCAT(brand," ",model) LIKE ? AND (? = "" OR size = ?) ORDER BY brand, model, size');
$st->execute(["%$q%", $s, $s]);
$rows = $st->fetchAll();
?>
<?php if ($msg): ?><div class="msg ok"><?= e($msg) ?></div><?php endif; ?>
<div class="card"><h3>Check availability</h3>
<form method="get" class="grid">
<label>Model or brand<input name="q" value="<?= e($q) ?>" placeholder="e.g. Air Force"></label>
<label>Size<input name="s" value="<?= e($s) ?>" placeholder="e.g. 42"></label>
<label>&nbsp;<button>Search</button></label>
</form></div>
<div class="card"><h3>Add or restock</h3>
<form method="post" class="grid"><input type="hidden" name="action" value="add">
<label>Brand<input name="brand" list="brand-list" placeholder="Type or pick a brand" required>
<datalist id="brand-list">
  <option value="Nike">
  <option value="Adidas">
  <option value="OnCloud">
  <option value="Asics">
</datalist></label>
<label>Model<input name="model" required></label>
<label>Size<input name="size" required></label>
<label>Quantity<input name="qty" type="number" min="1" value="1" required></label>
<label>Price (₱)<input name="price" type="number" min="0" step="0.01" required></label>
<label>&nbsp;<button>Save stock</button></label>
</form></div>
<div class="card"><table>
<tr><th>Shoe</th><th>Size</th><th>Availability</th><th>Edit quantity and price</th><th></th></tr>
<?php foreach ($rows as $r): ?>
<tr><td><?= e($r['brand'].' '.$r['model']) ?></td><td><?= e($r['size']) ?></td><td><?= stock_tag($r['qty']) ?></td>
<td><form method="post" class="inline"><input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?= $r['id'] ?>">
Qty <input name="qty" type="number" min="0" value="<?= (int)$r['qty'] ?>"> Price ₱ <input name="price" type="number" min="0" step="0.01" value="<?= e($r['price']) ?>" style="width:110px"> <button class="g">Save</button></form></td>
<td><form method="post" class="inline" onsubmit="return confirm('Remove this shoe?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="g">Remove</button></form></td></tr>
<?php endforeach; if (!$rows): ?><tr><td colspan="5">No shoes match your search.</td></tr><?php endif; ?>
</table></div>
<?php require '../includes/footer.php'; ?>
