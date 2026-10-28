<?php
require '../includes/auth.php';
if (($_SESSION['user']['role'] ?? '') === 'admin') { header('Location: dashboard.php'); exit; }
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $st = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin'");
    $st->execute([strtolower(trim($_POST['email']))]);
    $u = $st->fetch();
    if ($u && password_verify($_POST['password'], $u['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => $u['id'], 'name' => $u['name'], 'role' => 'admin'];
        log_activity($pdo, 'Admin logged in', $u['email']);
        header('Location: dashboard.php'); exit;
    }
    $err = 'Wrong email or password, or this is not an admin account.';
}
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin login - Sneakers Room</title><link rel="stylesheet" href="<?= BASE ?>assets/style.css"></head>
<body><div class="auth">
<h1>Admin login</h1>
<?php if ($err): ?><div class="msg err"><?= e($err) ?></div><?php endif; ?>
<div class="card"><form method="post">
<label>Admin Gmail<input name="email" type="email" required></label>
<label>Password<input name="password" type="password" required></label>
<button>Log in</button>
</form>
<p><a href="../customer/index.php">Back to the shop</a></p></div>
</div></body></html>
