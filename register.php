<?php
require 'includes/layout.php';
if (!empty($_SESSION['user'])) { header('Location: index.php'); exit; }

$err = '';
$old = ['name' => '', 'email' => '', 'mobile' => '', 'address' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($old as $k => $_) $old[$k] = trim($_POST[$k] ?? '');
    $old['email'] = strtolower($old['email']);
    $pw = (string)($_POST['password'] ?? '');

    if (!snr_csrf_ok())                                           $err = 'Your session expired. Please try again.';
    elseif (in_array('', $old, true))                            $err = 'Please fill in all fields.';
    elseif (!filter_var($old['email'], FILTER_VALIDATE_EMAIL))   $err = 'Enter a valid email address.';
    elseif (!preg_match('/^[0-9+()\-\s]{7,20}$/', $old['mobile'])) $err = 'Enter a valid mobile number.';
    elseif (strlen($pw) < 8)                                     $err = 'Password must be at least 8 characters.';
    elseif ($pw !== (string)($_POST['confirm'] ?? ''))           $err = 'The two passwords do not match.';
    else {
        try {
            $role = 'customer'; // admin accounts are created only through admin_setup.php
            $pdo->prepare('INSERT INTO users (name,email,mobile,address,password_hash,role) VALUES (?,?,?,?,?,?)')
                ->execute([$old['name'], $old['email'], $old['mobile'], $old['address'], password_hash($pw, PASSWORD_DEFAULT), $role]);
            log_activity($pdo, 'Sign Uped account', $old['name'] . ' (' . $old['email'] . ') as ' . $role);
            header('Location: login.php?new=1'); exit;
        } catch (PDOException $x) {
            $err = ($x->errorInfo[1] ?? 0) == 1062 ? 'That email is already Sign Uped.' : 'Could not create the account. Please try again.';
        }
    }
}

snr_page_start('Sign Up', 'Sign Up');
?>
<main class="auth">
  <div class="eyebrow">Join</div>
  <h1>Create account</h1>
  <?php if ($err): ?><div class="msg err"><?= e($err) ?></div><?php endif; ?>
  <form method="post" class="card flat">
    <input type="hidden" name="csrf" value="<?= e(snr_csrf()) ?>">
    <label>Full name<input name="name" value="<?= e($old['name']) ?>" autocomplete="name" required></label>
    <label>Email<input name="email" type="email" value="<?= e($old['email']) ?>" autocomplete="email" required></label>
    <label>Mobile number<input name="mobile" value="<?= e($old['mobile']) ?>" autocomplete="tel" required></label>
    <label>Delivery address<input name="address" value="<?= e($old['address']) ?>" autocomplete="street-address" required></label>
    <label>Password (8+ characters)<input name="password" type="password" autocomplete="new-password" required></label>
    <label>Confirm password<input name="confirm" type="password" autocomplete="new-password" required></label>
    <button>Sign Up</button>
    <p>Already Sign Uped? <a href="login.php">Sign In</a></p>
    <p><a href="index.php#shop">Just browsing? View the shop</a></p>
  </form>
</main>
<?php snr_page_end(); ?>
