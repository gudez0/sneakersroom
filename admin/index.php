<?php
require '../includes/admin_header.php';
$msg = ''; $err = '';

/* Saves a new photo for a product and removes the old file. */
function stock_photo(PDO $pdo, int $id, ?string $new): void {
    if ($new === null) return;
    $o = $pdo->prepare('SELECT image FROM products WHERE id = ?'); $o->execute([$id]);
    $old = $o->fetchColumn();
    $pdo->prepare('UPDATE products SET image = ? WHERE id = ?')->execute([$new, $id]);
    if ($old) snr_delete_image($old);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $a = $_POST['action'] ?? '';
    if ($a === 'add') {
        $imgErr = ''; $img = snr_save_image($_FILES['image'] ?? [], $imgErr);
        if ($imgErr) {
            $err = $imgErr;
        } else {
            $brand = trim($_POST['brand']); $model = trim($_POST['model']); $size = trim($_POST['size']);
            $qty = max(1, (int)$_POST['qty']);
            $st = $pdo->prepare('INSERT INTO products (brand,model,size,price,qty) VALUES (?,?,?,?,?)
                ON DUPLICATE KEY UPDATE qty = qty + VALUES(qty), price = VALUES(price)');
            $st->execute([$brand, $model, $size, $_POST['price'], $qty]);
            if ($img !== null) {
                $f = $pdo->prepare('SELECT id FROM products WHERE brand = ? AND model = ? AND size = ?');
                $f->execute([$brand, $model, $size]);
                if ($pid = $f->fetchColumn()) stock_photo($pdo, (int)$pid, $img);
            }
            log_activity($pdo, 'Added/restocked stock', $brand . ' ' . $model . ' size ' . $size . ' +' . $qty);
            $msg = 'Stock saved.';
        }
    } elseif ($a === 'update') {
        $pdo->prepare('UPDATE products SET price = ?, qty = ? WHERE id = ?')->execute([max(0,(float)$_POST['price']), max(0,(int)$_POST['qty']), $_POST['id']]);
        log_activity($pdo, 'Edited stock', shoe_label($pdo, $_POST['id']) . ' now qty ' . max(0,(int)$_POST['qty']) . ', price ' . peso(max(0,(float)$_POST['price'])));
        $msg = 'Item updated.';
    } elseif ($a === 'photo') {
        $id = (int)($_POST['id'] ?? 0);
        $imgErr = ''; $img = snr_save_image($_FILES['image'] ?? [], $imgErr);
        if ($imgErr)           $err = $imgErr;
        elseif ($img === null) $err = 'Choose a photo first.';
        else {
            stock_photo($pdo, $id, $img);
            log_activity($pdo, 'Updated shoe photo', shoe_label($pdo, $id));
            $msg = 'Photo saved.';
        }
    } elseif ($a === 'delete') {
        $id = (int)$_POST['id'];
        $label = shoe_label($pdo, $id);
        $o = $pdo->prepare('SELECT image FROM products WHERE id = ?'); $o->execute([$id]);
        $oldImg = $o->fetchColumn();
        try {
            $pdo->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
            if ($oldImg) snr_delete_image($oldImg);
            log_activity($pdo, 'Removed shoe', $label);
            $msg = 'Shoe removed.';
        } catch (PDOException $x) { $err = 'Cannot remove a shoe that already has orders. Set its qsnrntity to 0 instead.'; }
    }
}
$q = trim($_GET['q'] ?? ''); $s = trim($_GET['s'] ?? '');
$st = $pdo->prepare('SELECT * FROM products WHERE CONCAT(brand," ",model) LIKE ? AND (? = "" OR size = ?) ORDER BY brand, model, size');
$st->execute(["%$q%", $s, $s]);
$rows = $st->fetchAll();
?>
<?php if ($msg): ?><div class="msg ok"><?= e($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="msg err"><?= e($err) ?></div><?php endif; ?>
<div class="card"><h3>Check availability</h3>
<form method="get" class="grid">
<label>Model or brand<input name="q" value="<?= e($q) ?>" placeholder="e.g. Air Force"></label>
<label>Size<input name="s" value="<?= e($s) ?>" placeholder="e.g. 42"></label>
<label>&nbsp;<button>Search</button></label>
</form></div>
<div class="card"><h3>Add or restock</h3>
<form method="post" enctype="multipart/form-data" class="grid"><input type="hidden" name="action" value="add">
<label>Brand<input name="brand" list="brand-list" placeholder="Type or pick a brand" required>
<datalist id="brand-list">
  <option value="Nike">
  <option value="Adidas">
  <option value="OnCloud">
  <option value="Asics">
</datalist></label>
<label>Model<input name="model" required></label>
<label>Size<input name="size" required></label>
<label>Qsnrntity<input name="qty" type="number" min="1" value="1" required></label>
<label>Price (₱)<input name="price" type="number" min="0" step="0.01" required></label>
<label>Photo (optional)<input name="image" type="file" accept="image/jpeg,image/png,image/webp"></label>
<label>&nbsp;<button>Save stock</button></label>
</form></div>
<div class="card"><table>
<tr><th></th><th>Shoe</th><th>Size</th><th>Availability</th><th>Edit qsnrntity, price and photo</th><th></th></tr>
<?php foreach ($rows as $r): ?>
<tr><td><div class="thumb"><?= snr_img($r) ?></div></td>
<td><?= e($r['brand'].' '.$r['model']) ?></td><td><?= e($r['size']) ?></td><td><?= stock_tag($r['qty']) ?></td>
<td><form method="post" class="inline"><input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?= $r['id'] ?>">
Qty <input name="qty" type="number" min="0" value="<?= (int)$r['qty'] ?>"> Price ₱ <input name="price" type="number" min="0" step="0.01" value="<?= e($r['price']) ?>" style="width:110px"> <button class="g">Save</button></form>
<form method="post" enctype="multipart/form-data" class="inline" style="margin-top:8px"><input type="hidden" name="action" value="photo"><input type="hidden" name="id" value="<?= $r['id'] ?>">
<input type="file" name="image" accept="image/jpeg,image/png,image/webp" required> <button class="g"><?= empty($r['image']) ? 'Add photo' : 'Change photo' ?></button></form></td>
<td><form method="post" class="inline" onsubmit="return confirm('Remove this shoe?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="g">Remove</button></form></td></tr>
<?php endforeach; if (!$rows): ?><tr><td colspan="6">No shoes match your search.</td></tr><?php endif; ?>
</table></div>
<?php require '../includes/admin_footer.php'; ?>