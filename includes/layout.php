<?php
/**
 * Shared page shell: announcement ticker, header, footer and small helpers.
 * Include this INSTEAD of includes/auth.php (it loads auth.php for you).
 */
require_once __DIR__ . '/auth.php';
if (session_status() === PHP_SESSION_NONE) session_start();

const SNR_STORE  = 'Sneakers Room';
// Edit these lines to change the scrolling bar at the very top of every page.
const SNR_TICKER = [
    'Delivery via J&T Express',
    'Cash on delivery (J&T), GCash and Maya accepted',
    'New pairs added regularly',
    'Register to order and track your delivery',
];

/* ---------- self-healing database tweaks (runs once per session) ---------- */
function snr_schema(PDO $pdo): void {
    if (!empty($_SESSION['snr_schema'])) return;
    try {
        $cols = array_column($pdo->query('SHOW COLUMNS FROM products')->fetchAll(PDO::FETCH_ASSOC), 'Field');
        if (!in_array('image', $cols, true)) $pdo->exec('ALTER TABLE products ADD image VARCHAR(255) NULL');
    } catch (PDOException $x) {}
    try {
        $cols = array_column($pdo->query('SHOW COLUMNS FROM orders')->fetchAll(PDO::FETCH_ASSOC), 'Field');
        if (!in_array('user_id', $cols, true)) $pdo->exec('ALTER TABLE orders ADD user_id INT NULL');
    } catch (PDOException $x) {}
    $_SESSION['snr_schema'] = 1;
}

/* ---------- CSRF ---------- */
function snr_csrf(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}
function snr_csrf_ok(): bool {
    return hash_equals($_SESSION['csrf'] ?? '', (string)($_POST['csrf'] ?? ''));
}

/* ---------- messages: shown once after a save, so refreshing the page never repeats them ---------- */
function snr_flash_begin(): void { $GLOBALS['snr_pg'] = true; ob_start(); }
function snr_flash_show(): void {
    $f = $_SESSION['snr_flash'] ?? null; unset($_SESSION['snr_flash']);
    if (!$f) return;
    if (!empty($f['ok']))  echo '<div class="msg ok">'  . e($f['ok'])  . '</div>';
    if (!empty($f['err'])) echo '<div class="msg err">' . e($f['err']) . '</div>';
}

/* ---------- product helpers ---------- */
function snr_stock($qty): string {
    $q = (int)$qty;
    if ($q <= 0) return '<span class="tag out">Sold out</span>';
    if ($q <= 3) return '<span class="tag low">Low &middot; ' . $q . ' left</span>';
    return '<span class="tag in">In stock</span>';
}

function snr_upload_dir(): string { return dirname(__DIR__) . '/uploads'; }

/** Product photo, or a black monogram tile when there is no photo. */
function snr_img(array $p): string {
    $f = (string)($p['image'] ?? '');
    if ($f !== '' && is_file(snr_upload_dir() . '/' . basename($f))) {
        return '<img src="' . e(BASE . 'uploads/' . rawurlencode(basename($f))) . '" alt="'
             . e($p['brand'] . ' ' . $p['model']) . '" loading="lazy">';
    }
    $b = trim((string)$p['brand']); $m = trim((string)$p['model']);
    $ini = function_exists('mb_substr') ? mb_substr($b, 0, 1) . mb_substr($m, 0, 1) : substr($b, 0, 1) . substr($m, 0, 1);
    return '<div class="ph" aria-hidden="true">' . e(strtoupper($ini)) . '</div>';
}

/** Validates and stores an uploaded JPG/PNG/WebP. Returns the file name, or null (see $err). */
function snr_save_image(array $f, string &$err): ?string {
    if (!$f || ($f['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    if ($f['error'] !== UPLOAD_ERR_OK) { $err = 'The image could not be uploaded (error ' . (int)$f['error'] . ').'; return null; }
    if ($f['size'] > 3 * 1024 * 1024)  { $err = 'Image must be 3 MB or smaller.'; return null; }
    $info = @getimagesize($f['tmp_name']);
    $ext  = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$info['mime'] ?? ''] ?? null;
    if (!$ext) { $err = 'Use a JPG, PNG or WebP image.'; return null; }
    $dir = snr_upload_dir();
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) { $err = 'Could not create the uploads folder.'; return null; }
    $ht = $dir . '/.htaccess'; // never let anything in uploads/ run as PHP
    if (!is_file($ht)) @file_put_contents($ht, '<FilesMatch "\.(php|phtml|phar)$">' . "\n  Require all denied\n</FilesMatch>\n");
    $name = bin2hex(random_bytes(8)) . '.' . $ext;
    if (!move_uploaded_file($f['tmp_name'], $dir . '/' . $name)) { $err = 'Could not save the image.'; return null; }
    return $name;
}
function snr_delete_image(?string $name): void {
    if (!$name) return;
    $p = snr_upload_dir() . '/' . basename($name);
    if (is_file($p)) @unlink($p);
}

/* ---------- page shell ---------- */
function snr_page_start(string $title, string $active = ''): void {
    global $pdo;
    if (isset($pdo) && $pdo instanceof PDO) snr_schema($pdo);
    $u   = $_SESSION['user'] ?? null;
    $ver = @filemtime(dirname(__DIR__) . '/assets/style.css') ?: 1;
    $a = function ($href, $label, $key) use ($active) {
        return '<a class="nl' . ($active === $key ? ' on' : '') . '" href="' . e(BASE . $href) . '">' . e($label) . '</a>';
    };
    $set = str_repeat(implode('', array_map(function ($t) { return '<span>' . e($t) . '</span>'; }, SNR_TICKER)), 3);
?>
<!DOCTYPE html>
<html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> &ndash; <?= e(SNR_STORE) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="<?= e(BASE) ?>assets/style.css?v=<?= (int)$ver ?>">
<script src="<?= e(BASE) ?>assets/script.js" defer></script>
</head><body class="p-<?= e($active) ?>">

<div class="ticker" aria-label="Announcements"><div class="ticker-track"><?= $set . $set ?></div></div>

<header class="site" id="site">
  <div class="site-in">
    <button class="menu-btn" type="button" aria-label="Menu" onclick="document.getElementById('site').classList.toggle('open')"><i></i><i></i></button>
    <nav class="nav-l">
      <?= $a('index.php', 'Home', 'home') ?>
      <?= $a('customer/index.php', 'Shop', 'shop') ?>
      <?php if ($u && ($u['role'] ?? '') === 'admin'): ?><?= $a('admin/dashboard.php', 'Admin', 'admin') ?>
      <?php elseif ($u): ?><?= $a('customer/orders.php', 'My orders', 'orders') ?><?php endif; ?>
    </nav>
    <a class="brand" href="<?= e(BASE) ?>index.php"><?= e(SNR_STORE) ?></a>
    <nav class="nav-r">
      <?php if ($u): ?>
        <span class="who"><?= e($u['name']) ?></span><a class="nl" href="<?= e(BASE) ?>logout.php">Log out</a>
      <?php else: ?>
        <?= $a('login.php', 'Log in', 'login') ?><?= $a('register.php', 'Register', 'register') ?>
      <?php endif; ?>
    </nav>
  </div>
</header>
<?php
}

function snr_page_end(): void {
    global $msg, $err;
    // After a form is saved: keep the message, redirect back to the same page (GET) and show it there once.
    if (!empty($GLOBALS['snr_pg']) && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        while (ob_get_level() > 0) ob_end_clean();
        if (!empty($msg) || !empty($err)) $_SESSION['snr_flash'] = ['ok' => (string)($msg ?? ''), 'err' => (string)($err ?? '')];
        header('Location: ' . $_SERVER['REQUEST_URI']); exit;
    }
    $u = $_SESSION['user'] ?? null;
?>
<footer class="foot">
  <div class="foot-in">
    <div><div class="foot-brand"><?= e(SNR_STORE) ?></div><p>Sneakers in stock. Order online, pay by GCash, Maya or cash on delivery.</p></div>
    <div><h4>Shop</h4><a href="<?= e(BASE) ?>index.php">Home</a><a href="<?= e(BASE) ?>customer/index.php">All sneakers</a></div>
    <div><h4>Account</h4>
      <?php if ($u): ?><a href="<?= e(BASE) ?>logout.php">Log out</a>
      <?php else: ?><a href="<?= e(BASE) ?>login.php">Log in</a><a href="<?= e(BASE) ?>register.php">Register</a><?php endif; ?></div>
  </div>
  <div class="foot-copy">&copy; <?= date('Y') ?> <?= e(SNR_STORE) ?></div>
</footer>
</body></html>
<?php
}