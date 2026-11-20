<?php
// ONE-TIME SETUP: creates the first admin account. Delete this file after using it.
require 'includes/layout.php';

$exists = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn() > 0;
$err = ''; $done = false;

if (!$exists && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $pw    = (string)($_POST['password'] ?? '');
    if (!snr_csrf_ok())                                         $err = 'Your session expired. Please try again.';
    elseif ($name === '' || $email === '')                     $err = 'Please fill in all fields.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))        $err = 'Enter a valid email address.';
    elseif (strlen($pw) < 8)                                   $err = 'Password must be at least 8 characters.';
    elseif ($pw !== (string)($_POST['confirm'] ?? ''))         $err = 'The two passwords do not match.';
    else {
        $hash = password_hash($pw, PASSWORD_DEFAULT);
        $st = $pdo->prepare('SELECT id FROM users WHERE email = ?'); $st->execute([$email]);
        if ($id = $st->fetchColumn())
            $pdo->prepare("UPDATE users SET name = ?, password_hash = ?, role = 'admin' WHERE id = ?")->execute([$name, $hash, $id]);
        else
            $pdo->prepare("INSERT INTO users (name,email,mobile,address,password_hash,role) VALUES (?,?,'','',?, 'admin')")->execute([$name, $email, $hash]);
        log_activity($pdo, 'Created first admin account', $email);
        $done = true;
    }
}

snr_page_start('Admin setup');
?>
<main class="auth">
  <div class="eyebrow">One-time setup</div>
  <h1>Admin setup</h1>
<?php if ($done): ?>
  <div class="msg ok">Admin account created.</div>
  <div class="card flat"><p style="color:var(--ink);font-size:14px">Next: <b>delete the file <code>admin_setup.php</code></b> from your project folder, then Sign In.</p>
  <p><a href="admin/login.php">Go to the admin login</a></p></div>
<?php elseif ($exists): ?>
  <div class="msg err">An admin account already exists, so this setup page is locked.</div>
  <div class="card flat"><p><a href="admin/login.php">Go to the admin login</a></p></div>
<?php else: ?>
  <?php if ($err): ?><div class="msg err"><?= e($err) ?></div><?php endif; ?>
  <form method="post" class="card flat">
    <input type="hidden" name="csrf" value="<?= e(snr_csrf()) ?>">
    <label>Admin name<input name="name" required></label>
    <label>Admin email<input name="email" type="email" required></label>
    <label>Password (8+ characters)<input name="password" type="password" required></label>
    <label>Confirm password<input name="confirm" type="password" required></label>
    <button>Create admin account</button>
  </form>
<?php endif; ?>
</main>
<?php snr_page_end(); ?>
