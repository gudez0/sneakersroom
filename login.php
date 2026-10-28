<?php
require 'includes/auth.php';
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $st = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $st->execute([strtolower(trim($_POST['email']))]);
    $u = $st->fetch();
    if ($u && password_verify($_POST['password'], $u['password_hash'])) {
        $role = $u['role'];
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => $u['id'], 'name' => $u['name'], 'role' => $role];
        log_activity($pdo, 'Logged in', $u['email']);
        // Everyone (admin and customers) lands on the same home dashboard.
        // Admins open the admin dashboard from the nav link there.
        header('Location: index.php'); exit;
    }
    $err = 'Wrong email or password.';
}
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Log in - Sneakers Room</title><link rel="stylesheet" href="<?= BASE ?>assets/style.css"></head>
<body><div class="auth">
<h1>Sneakers Room</h1>
<?php if (isset($_GET['new'])): ?><div class="msg ok">Account created. Please log in.</div><?php endif; ?>
<?php if ($err): ?><div class="msg err"><?= e($err) ?></div><?php endif; ?>
<div class="card"><form method="post">
<label>Email<input name="email" type="email" required></label>
<label>Password<input name="password" type="password" required></label>
<button>Log in</button>
</form>
<p>No account yet? <a href="register.php">Register</a></p>
<p><a href="customer/index.php">Just browsing? View the shop</a></p></div>
</div></body></html>
