<?php
require_once __DIR__ . '/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
// Colour support: adds products.color and makes one row = brand + model + colour + size (runs once per session).
function snr_variant_schema(PDO $pdo): void {
    if (!empty($_SESSION['snr_variants'])) return;
    try {
        $cols = array_column($pdo->query('SHOW COLUMNS FROM products')->fetchAll(PDO::FETCH_ASSOC), 'Field');
        if (!in_array('color', $cols, true)) $pdo->exec("ALTER TABLE products ADD color VARCHAR(40) NOT NULL DEFAULT '' AFTER model");
        $old = [];
        foreach ($pdo->query('SHOW INDEX FROM products WHERE Non_unique = 0')->fetchAll(PDO::FETCH_ASSOC) as $k)
            if ($k['Key_name'] !== 'PRIMARY') $old[$k['Key_name']] = true;
        if (!isset($old['uq_variant'])) {
            $pdo->exec('ALTER TABLE products ADD UNIQUE KEY uq_variant (brand, model, color, size)');
            foreach (array_keys($old) as $n) $pdo->exec('ALTER TABLE products DROP INDEX `' . str_replace('`', '', $n) . '`');
        }
        $_SESSION['snr_variants'] = 1;
    } catch (PDOException $x) { /* tried again on the next visit */ }
}
snr_variant_schema($pdo);

/** "Nike Air Force 1 - White": brand, model and colour (colour only if there is one). */
function snr_item($r) {
    $c = trim((string)($r['color'] ?? ''));
    return trim(($r['brand'] ?? '') . ' ' . ($r['model'] ?? '') . ($c !== '' ? ' - ' . $c : ''));
}

/** Rows of products -> one entry per brand + model (its colours and sizes stay in ['rows']). */
function snr_group_products(array $rows): array {
    $g = [];
    foreach ($rows as $r) {
        $k = $r['brand'] . "\0" . $r['model'];
        $g[$k]['brand'] = $r['brand']; $g[$k]['model'] = $r['model']; $g[$k]['rows'][] = $r;
    }
    return $g;
}
function snr_product_url(string $brand, string $model, string $color = ''): string {
    return BASE . 'customer/product.php?b=' . rawurlencode($brand) . '&m=' . rawurlencode($model) . ($color !== '' ? '&c=' . rawurlencode($color) : '');
}
/** One shoe card for the home page and the shop grid. It opens the product page. */
function snr_model_card(array $g): string {
    $rows = $g['rows']; $tot = 0; $sizes = []; $colors = []; $prices = []; $imgRow = $rows[0];
    foreach ($rows as $r) {
        $sz = (string)$r['size'];
        $sizes[$sz] = !empty($sizes[$sz]) || (int)$r['qty'] > 0;   // size => still available?
        if ((int)$r['qty'] > 0) { $tot += (int)$r['qty']; $prices[] = (float)$r['price']; }
        if ($r['color'] !== '') $colors[$r['color']] = 1;
    }
    foreach ($rows as $r) if ((string)($r['image'] ?? '') !== '' && (int)$r['qty'] > 0) { $imgRow = $r; break; }
    if (!$prices) foreach ($rows as $r) $prices[] = (float)$r['price'];
    $sold = $tot <= 0; $lowq = !$sold && $tot <= 3;
    uksort($sizes, 'strnatcmp');
    $chips = [];
    foreach (array_slice($sizes, 0, 6, true) as $sz => $in)
        $chips[] = $in ? e((string)$sz) : '<s class="szo" title="Out of stock">' . e((string)$sz) . '</s>';
    $from = count(array_unique($prices)) > 1 ? 'From ' : '';
    $flag = $sold ? '<span class="pc-flag out">Out of stock</span>' : ($lowq ? '<span class="pc-flag">Only ' . $tot . ' left</span>' : '');
    $meta = (count($colors) > 1 ? count($colors) . ' colors &middot; ' : '')
          . ($chips ? 'Size ' . implode(', ', $chips) . (count($sizes) > 6 ? '&hellip;' : '') : 'No sizes');
    return '<a class="pc' . ($sold ? ' sold' : '') . '" href="' . e(snr_product_url($g['brand'], $g['model'])) . '">'
         . '<div class="pc-img zoom" data-zoom="1.3">' . snr_img($imgRow) . $flag . '</div>'
         . '<div class="pc-name">' . e($g['brand'] . ' ' . $g['model']) . '</div>'
         . '<div class="pc-meta">' . $meta . '</div>'
         . '<div class="pc-price">' . $from . peso(min($prices)) . '</div></a>' . "\n";
}

function require_role($role, $loginUrl) {
    if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== $role) {
        header('Location: ' . $loginUrl); exit;
    }
}

// Record who did what (shown to admins in the Activity log)
function log_activity($pdo, $action, $details = '') {
    try {
        $u = $_SESSION['user'] ?? null;
        $pdo->prepare('INSERT INTO activity_log (user_id,user_name,role,action,details) VALUES (?,?,?,?,?)')
            ->execute([$u['id'] ?? null, $u['name'] ?? 'Guest', $u['role'] ?? 'guest', $action, $details]);
    } catch (Exception $x) { /* never break the page because of logging */ }
}
function shoe_label($pdo, $id) {
    $s = $pdo->prepare('SELECT brand, model, color, size FROM products WHERE id = ?');
    $s->execute([$id]);
    $p = $s->fetch();
    return $p ? snr_item($p) . ' size ' . $p['size'] : ('#' . (int)$id);
}