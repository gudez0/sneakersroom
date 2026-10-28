<?php
require 'includes/auth.php';
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']); $email = strtolower(trim($_POST['email']));
    $mobile = trim($_POST['mobile']); $addr = trim($_POST['address']); $pw = $_POST['password'];
    if ($name === '' || $email === '' || $mobile === '' || $addr === '') $err = 'Please fill in all fields.';
    elseif (strlen($pw) < 6) $err = 'Password must be at least 6 characters.';
    else {
        try {
            $role = 'customer'; // admin accounts are created only through admin_setup.php or the Admins page
            $pdo->prepare('INSERT INTO users (name,email,mobile,address,password_hash,role) VALUES (?,?,?,?,?,?)')
                ->execute([$name, $email, $mobile, $addr, password_hash($pw, PASSWORD_DEFAULT), $role]);
            log_activity($pdo, 'Registered account', $name . ' (' . $email . ') as ' . $role);
            header('Location: login.php?new=1'); exit;
        } catch (PDOException $x) { $err = 'That email is already registered.'; }
    }
}
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Register - Sneakers Room</title><link rel="stylesheet" href="<?= BASE ?>assets/style.css"></head>
<body><div class="auth">
<h1>Create account</h1>
<?php if ($err): ?><div class="msg err"><?= e($err) ?></div><?php endif; ?>
<div class="card"><form method="post">
<label>Full name<input name="name" required></label>
<label>Email<input name="email" type="email" required></label>
<label>Mobile number<input name="mobile" required></label>
<label>Delivery address<input name="address" required></label>
<label>Password (6+ characters)<input name="password" type="password" required></label>
<button>Register</button>
</form>
<p>Already registered? <a href="login.php">Log in</a></p>
<p><a href="customer/index.php">Just browsing? View the shop</a></p></div>
</div></body></html>
