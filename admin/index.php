<?php
require '../includes/admin_header.php';
$msg = ''; $err = '';

/* Saves a new photo for a product. The photo belongs to the colour, so every size of the same brand + model + colour gets it. */
function stock_photo(PDO $pdo, int $id, ?string $new): void {
    if ($new === null) return;
    $r = $pdo->prepare('SELECT brand, model, color FROM products WHERE id = ?'); $r->execute([$id]); $p = $r->fetch();
    if (!$p) return;
    $k = [$p['brand'], $p['model'], $p['color']];
    $o = $pdo->prepare("SELECT DISTINCT image FROM products WHERE brand = ? AND model = ? AND color = ? AND image IS NOT NULL AND image <> ''");
    $o->execute($k); $olds = $o->fetchAll(PDO::FETCH_COLUMN);
    $pdo->prepare('UPDATE products SET image = ? WHERE brand = ? AND model = ? AND color = ?')->execute(array_merge([$new], $k));
    foreach ($olds as $old) snr_delete_image($old);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $a = $_POST['action'] ?? '';
    if ($a === 'add') {
        $imgErr = ''; $img = snr_save_image($_FILES['image'] ?? [], $imgErr);
        if ($imgErr) {
            $err = $imgErr;
        } else {
            $brand = trim($_POST['brand'] ?? ''); $model = trim($_POST['model'] ?? ''); $color = trim($_POST['color'] ?? '');
            $price = max(0, (float)($_POST['price'] ?? 0));
            // size => pairs, from the size boxes plus the "other sizes" box
            $list = [];
            foreach ((array)($_POST['sizes'] ?? []) as $sz => $q) {
                $sz = substr(trim((string)$sz), 0, 20); $q = (int)$q;
                if ($sz !== '' && $q > 0) $list[$sz] = $q;
            }
            $oq = (int)($_POST['other_qty'] ?? 0);
            if ($oq > 0) foreach (preg_split('/[,\s]+/', trim($_POST['other_sizes'] ?? ''), -1, PREG_SPLIT_NO_EMPTY) as $sz) {
                $sz = substr($sz, 0, 20); $list[$sz] = ($list[$sz] ?? 0) + $oq;
            }
            if ($brand === '' || $model === '') $err = 'Brand and model are required.';
            elseif (!$list)                     $err = 'Type how many stocks you have for at least one size.';
            if ($err) {
                if ($img !== null) snr_delete_image($img);       // nothing was saved, so drop the uploaded photo
            } else {
                uksort($list, function ($x, $y) { return strnatcmp((string)$x, (string)$y); });
                $st = $pdo->prepare('INSERT INTO products (brand,model,color,size,price,qty) VALUES (?,?,?,?,?,?)
                    ON DUPLICATE KEY UPDATE qty = qty + VALUES(qty), price = VALUES(price)');
                foreach ($list as $sz => $q) $st->execute([$brand, $model, $color, (string)$sz, $price, $q]);
                if ($img !== null) {
                    $f = $pdo->prepare('SELECT id FROM products WHERE brand = ? AND model = ? AND color = ? LIMIT 1');
                    $f->execute([$brand, $model, $color]);
                    if ($pid = $f->fetchColumn()) stock_photo($pdo, (int)$pid, $img);   // the photo is shared by every size of this color
                }
                if ($img === null) {   // sizes added to an existing colour use the photo that colour already has
                    $pdo->prepare("UPDATE products SET image = (SELECT image FROM (SELECT image FROM products WHERE brand = ? AND model = ? AND color = ? AND image IS NOT NULL AND image <> '' LIMIT 1) t)
                        WHERE brand = ? AND model = ? AND color = ? AND (image IS NULL OR image = '')")
                        ->execute([$brand, $model, $color, $brand, $model, $color]);
                }
                $parts = []; foreach ($list as $sz => $q) $parts[] = $sz . ' +' . $q;
                log_activity($pdo, 'Added/restocked stock', snr_item(['brand' => $brand, 'model' => $model, 'color' => $color]) . ' sizes ' . implode(', ', $parts));
                $msg = 'Stock saved for size' . (count($list) === 1 ? ' ' : 's ') . implode(', ', array_map('strval', array_keys($list))) . '.';
            }
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
    } elseif ($a === 'delete_group') {
        $k = [trim($_POST['brand'] ?? ''), trim($_POST['model'] ?? ''), trim($_POST['color'] ?? '')];
        $o = $pdo->prepare('SELECT DISTINCT image FROM products WHERE brand = ? AND model = ? AND color = ? AND image IS NOT NULL AND image <> ""');
        $o->execute($k); $olds = $o->fetchAll(PDO::FETCH_COLUMN);
        try {
            $pdo->prepare('DELETE FROM products WHERE brand = ? AND model = ? AND color = ?')->execute($k);
            foreach ($olds as $old) snr_delete_image($old);
            log_activity($pdo, 'Removed shoe (all sizes)', snr_item(['brand' => $k[0], 'model' => $k[1], 'color' => $k[2]]));
            $msg = 'Shoe removed.';
        } catch (PDOException $x) { $err = 'Cannot remove this shoe because one of its sizes already has orders. Set the quantities to 0 instead.'; }
    } elseif ($a === 'restock') {
        $id = (int)($_POST['id'] ?? 0); $add = max(1, (int)($_POST['add'] ?? 1));
        $pdo->prepare('UPDATE products SET qty = qty + ? WHERE id = ?')->execute([$add, $id]);
        log_activity($pdo, 'Restocked shoe', shoe_label($pdo, $id) . ' +' . $add);
        $msg = 'Added ' . $add . ' pair' . ($add === 1 ? '' : 's') . '.';
    } elseif ($a === 'delete') {
        $id = (int)$_POST['id'];
        $label = shoe_label($pdo, $id);
        $o = $pdo->prepare('SELECT image FROM products WHERE id = ?'); $o->execute([$id]);
        $oldImg = $o->fetchColumn();
        try {
            $pdo->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
            if ($oldImg) {
                $c = $pdo->prepare('SELECT COUNT(*) FROM products WHERE image = ?'); $c->execute([$oldImg]);
                if (!(int)$c->fetchColumn()) snr_delete_image($oldImg);   // other sizes may still use this photo
            }
            log_activity($pdo, 'Removed shoe', $label);
            $msg = 'Shoe removed.';
        } catch (PDOException $x) { $err = 'Cannot remove a shoe that already has orders. Set its quantity to 0 instead.'; }
    }
}
$pre = ['brand' => '', 'model' => '', 'color' => '', 'price' => '']; $have = [];
if (isset($_GET['add'])) {                       // "Add sizes" on a row: fill the form with that shoe and list the sizes it already has
    $g = $pdo->prepare('SELECT brand, model, color, price FROM products WHERE id = ?'); $g->execute([(int)$_GET['add']]);
    if ($row = $g->fetch()) {
        $pre = $row;
        $h = $pdo->prepare('SELECT size, qty FROM products WHERE brand = ? AND model = ? AND color = ?'); $h->execute([$row['brand'], $row['model'], $row['color']]);
        $have = $h->fetchAll();
        usort($have, function ($x, $y) { return strnatcmp((string)$x['size'], (string)$y['size']); });
    }
}
$q = trim($_GET['q'] ?? ''); $s = trim($_GET['s'] ?? '');
$st = $pdo->prepare('SELECT * FROM products WHERE CONCAT(brand," ",model," ",color) LIKE ? AND (? = "" OR size = ?) ORDER BY brand, model, color, size');
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
<div class="card" id="add-stock"><h3>Add or restock</h3>
<?php if ($pre['brand'] !== ''): ?>
<div class="addnote">
  Adding sizes to <?= e(snr_item($pre)) ?>.
  Already in stock: <?php foreach ($have as $i => $hv) echo ($i ? ', ' : '') . e($hv['size']) . ' (' . (int)$hv['qty'] . ')'; ?>.
  Type stocks for any size below: a new size is added, and a size you already have gets the stocks added to it.
  <a href="index.php">Clear</a>
</div>
<?php endif; ?>
<form method="post" enctype="multipart/form-data" class="grid"><input type="hidden" name="action" value="add">
<label>Brand<input name="brand" list="brand-list" placeholder="Type or pick a brand" value="<?= e($pre['brand']) ?>" required>
<datalist id="brand-list">
  <option value="Nike">
  <option value="Adidas">
  <option value="OnCloud">
  <option value="Asics">
</datalist></label>
<label>Model<input name="model" value="<?= e($pre['model']) ?>" required></label>
<label>Color<input name="color" list="color-list" placeholder="e.g. White (leave empty if only one)" value="<?= e($pre['color']) ?>">
<datalist id="color-list"><option value="Black"><option value="White"><option value="Grey"><option value="Red"><option value="Blue"><option value="Navy"><option value="Green"><option value="Brown"><option value="Beige"><option value="Pink"><option value="Orange"><option value="Yellow"></datalist></label>
<label>Price (₱, same for every size)<input name="price" type="number" min="0" step="0.01" value="<?= e($pre['price']) ?>" required></label>
<label>Photo of this color (optional)<input name="image" type="file" accept="image/jpeg,image/png,image/webp"></label>
<div class="sizebox">
  <div class="sb-h">Sizes available: type how many stocks you have in each size. Leave a size empty if you don't have it.</div>
  <div class="sb-grid">
<?php foreach (range(36, 47) as $n): ?>
    <label class="sb"><span><?= $n ?></span><input type="number" name="sizes[<?= $n ?>]" min="0" placeholder="0"></label>
<?php endforeach; ?>
  </div>
  <div class="grid" style="margin-top:14px">
    <label>Other sizes (separate with commas, e.g. 7.5, 8, 8.5)<input name="other_sizes" placeholder="optional"></label>
    <label>Stocks for each of those sizes<input name="other_qty" type="number" min="1" placeholder="e.g. 2"></label>
  </div>
</div>
<div class="sizebox" style="margin:0"><button>Save stock</button></div>
</form></div>
<?php
/* One block per brand + model + color. Click a block to open its sizes. */
$grouped = [];
foreach ($rows as $r) $grouped[$r['brand'] . "\0" . $r['model'] . "\0" . $r['color']][] = $r;
?>
<div class="card sg-card">
<?php foreach ($grouped as $gk => $gr):
    usort($gr, function ($x, $y) { return strnatcmp((string)$x['size'], (string)$y['size']); });
    $first = $gr[0]; $gid = substr(md5($gk), 0, 8);
    $tot = 0; $prices = []; $im = $first; $avail = 0;
    foreach ($gr as $x) { $tot += (int)$x['qty']; if ((int)$x['qty'] > 0) $avail++; $prices[(string)(float)$x['price']] = (float)$x['price']; if (empty($im['image']) && !empty($x['image'])) $im = $x; }
    $n = count($gr);
?>
<details class="sg" data-g="<?= $gid ?>">
  <summary>
    <div class="thumb"><?= snr_img($im) ?></div>
    <div class="sg-name"><b><?= e($first['brand'] . ' ' . $first['model']) ?></b><span><?= e($first['color'] !== '' ? $first['color'] : 'No color') ?></span>
      <div class="sg-chips"><?php foreach ($gr as $x): $qq = (int)$x['qty']; ?><i class="c <?= $qq > 0 ? 'on' : 'off' ?>" title="<?= $qq > 0 ? $qq . ' in stock' : 'Sold out' ?>"><?= e($x['size']) ?></i><?php endforeach; ?></div>
    </div>
    <div class="sg-n"><?= $avail ?> of <?= $n ?> size<?= $n === 1 ? '' : 's' ?> available</div>
    <div class="sg-av"><?= stock_tag($tot) ?></div>
    <div class="sg-pr"><?= (count($prices) > 1 ? 'From ' : '') . peso(min($prices)) ?></div>
    <span class="sg-ar" aria-hidden="true">&#9662;</span>
  </summary>
  <div class="sg-body">
    <div class="sg-tools">
      <form method="post" enctype="multipart/form-data"><input type="hidden" name="action" value="photo"><input type="hidden" name="id" value="<?= (int)$first['id'] ?>">
        <input type="file" name="image" accept="image/jpeg,image/png,image/webp" required><button class="g"><?= empty($im['image']) ? 'Add photo' : 'Change photo' ?></button></form>
      <form method="post" onsubmit="return confirm('Remove this shoe with ALL its sizes?')"><input type="hidden" name="action" value="delete_group">
        <input type="hidden" name="brand" value="<?= e($first['brand']) ?>"><input type="hidden" name="model" value="<?= e($first['model']) ?>"><input type="hidden" name="color" value="<?= e($first['color']) ?>">
        <button class="g">Remove all sizes</button></form>
    </div>
<?php foreach ($gr as $r): ?>
    <div class="sg-row">
      <div class="sg-sz">Size <b><?= e($r['size']) ?></b></div>
      <div><?= stock_tag($r['qty']) ?></div>
      <form method="post" class="u"><input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?= $r['id'] ?>">
        <label class="f"><span>Stocks</span><input name="qty" type="number" min="0" value="<?= (int)$r['qty'] ?>" readonly tabindex="-1"></label>
        <label class="f"><span>Price &#8369;</span><input name="price" type="number" min="0" step="0.01" value="<?= e($r['price']) ?>" readonly tabindex="-1"></label>
        <button type="button" class="g js-edit" style="margin-left:auto">Edit</button></form>
      <form method="post" onsubmit="return confirm('Remove this size?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="g">Remove</button></form>
    </div>
<?php endforeach; ?>
    <form method="post" class="sg-add"><input type="hidden" name="action" value="add">
      <input type="hidden" name="brand" value="<?= e($first['brand']) ?>"><input type="hidden" name="model" value="<?= e($first['model']) ?>"><input type="hidden" name="color" value="<?= e($first['color']) ?>">
      <b>Add a new size</b>
      <span>Size</span><input name="other_sizes" placeholder="e.g. 48" required>
      <span>Stocks</span><input name="other_qty" type="number" min="1" placeholder="e.g. 2" required>
      <span>Price &#8369;</span><input name="price" type="number" min="0" step="0.01" value="<?= e($first['price']) ?>" required>
      <button>Add size</button>
    </form>
  </div>
</details>
<?php endforeach; if (!$rows): ?><div class="sg-empty">No shoes match your search.</div><?php endif; ?>
</div>
<?php require '../includes/admin_footer.php'; ?>