<?php
require 'includes/layout.php';
function snr_home_for($role) { return $role === 'admin' ? 'admin/dashboard.php' : 'customer/index.php'; }
if (!empty($_SESSION['user'])) { header('Location: ' . BASE . snr_home_for($_SESSION['user']['role'])); exit; }

$err = '';
$locked = ($_SESSION['login_lock'] ?? 0) > time();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!snr_csrf_ok()) {
        $err = 'Your session expired. Please try again.';
    } elseif ($locked) {
        $err = 'Too many attempts. Please wait a minute and try again.';
    } else {
        $email = strtolower(trim($_POST['email'] ?? ''));
        $pw    = (string)($_POST['password'] ?? '');
        $st = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $st->execute([$email]);
        $u = $st->fetch();
        if ($u && password_verify($pw, $u['password_hash'])) {
            session_regenerate_id(true);
            unset($_SESSION['login_fail'], $_SESSION['login_lock']);
            $_SESSION['user'] = ['id' => $u['id'], 'name' => $u['name'], 'role' => $u['role']];
            log_activity($pdo, 'Logged in', $u['email']);
            // The account's role decides where it lands: admins go to the dashboard, customers to the shop.
            header('Location: ' . BASE . snr_home_for($u['role'])); exit;
        }
        $_SESSION['login_fail'] = ($_SESSION['login_fail'] ?? 0) + 1;
        if ($_SESSION['login_fail'] >= 5) { $_SESSION['login_lock'] = time() + 60; $_SESSION['login_fail'] = 0; }
        $err = 'Wrong email or password.';
    }
}

snr_page_start('Sign In', 'login');
?>
<main class="auth">
  <div class="eyebrow">Account</div>
  <h1>Sign In</h1>
  <?php if (isset($_GET['new'])): ?><div class="msg ok">Account created. Please Sign In.</div><?php endif; ?>
  <?php if ($err): ?><div class="msg err"><?= e($err) ?></div><?php endif; ?>
  <form method="post" class="card flat">
    <input type="hidden" name="csrf" value="<?= e(snr_csrf()) ?>">
    <label>Email<input name="email" type="email" autocomplete="username" required autofocus></label>
    <label>Password<input name="password" type="password" autocomplete="current-password" required></label>
    <button>Sign In</button>
    <p>No account yet? <a href="Sign Up.php">Sign Up</a></p>
    <p><a href="index.php#shop">View the shop</a></p>
  </form>
</main>
<?php snr_page_end(); ?>