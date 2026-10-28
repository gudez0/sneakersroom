<?php
// ONE-TIME SETUP: creates the first admin account. Delete this file after using it.
require 'includes/auth.php';
$exists = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn() > 0;
$err = ''; $done = false;
if (!$exists && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']); $email = strtolower(trim($_POST['email'])); $pw = $_POST['password'];
    if ($name === '' || $email === '') $err = 'Please fill in all fields.';
    elseif (!preg_match('/^[a-z0-9._+-]+@gmail\.com$/', $email)) $err = 'Use a Gmail address, for example name@gmail.com.';
    elseif (strlen($pw) < 8) $err = 'Password must be at least 8 characters.';
    elseif ($pw !== $_POST['confirm']) $err = 'The two passwords do not match.';
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
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin setup - Sneakers Room</title><link rel="stylesheet" href="<?= BASE ?>assets/style.css"></head>
<body><div class="auth">
<h1>Admin setup</h1>
<?php if ($done): ?>
  <div class="msg ok">Admin account created.</div>
  <div class="card"><p>Next: <b>delete the file <code>admin_setup.php</code></b> from your project folder, then log in.</p>
  <p><a href="admin/login.php">Go to the admin login</a></p></div>
<?php elseif ($exists): ?>
  <div class="msg err">An admin account already exists, so this setup page is locked.</div>
  <div class="card"><p><a href="admin/login.php">Go to the admin login</a></p></div>
<?php else: ?>
  <?php if ($err): ?><div class="msg err"><?= e($err) ?></div><?php endif; ?>
  <div class="card"><form method="post">
  <label>Admin name<input name="name" required></label>
  <label>Admin Gmail<input name="email" type="email" placeholder="name@gmail.com" required></label>
  <label>Password (8+ characters)<input name="password" type="password" required></label>
  <label>Confirm password<input name="confirm" type="password" required></label>
  <button>Create admin account</button>
  </form></div>
<?php endif; ?>
</div></body></html>
