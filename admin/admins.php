<?php
require '../includes/header.php';
$msg = ''; $err = ''; $me = (int)$_SESSION['user']['id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['action'] ?? '') === 'add') {
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
            log_activity($pdo, 'Added admin account', $email);
            $msg = 'Admin account saved for ' . $email . '.';
        }
    } elseif (($_POST['action'] ?? '') === 'remove') {
        $id = (int)$_POST['id'];
        if ($id === $me) $err = 'You cannot remove your own account.';
        else {
            $st = $pdo->prepare("SELECT email FROM users WHERE id = ? AND role = 'admin'"); $st->execute([$id]);
            if ($email = $st->fetchColumn()) {
                $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'admin'")->execute([$id]);
                log_activity($pdo, 'Removed admin account', $email);
                $msg = 'Admin account removed.';
            }
        }
    }
}
$admins = $pdo->query("SELECT id,name,email FROM users WHERE role='admin' ORDER BY id")->fetchAll();
?>
<?php if ($msg): ?><div class="msg ok"><?= e($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="msg err"><?= e($err) ?></div><?php endif; ?>
<div class="card"><h3>Admin accounts</h3><table>
<tr><th>Name</th><th>Gmail</th><th></th></tr>
<?php foreach ($admins as $a): ?>
<tr><td><?= e($a['name']) ?></td><td><?= e($a['email']) ?></td>
<td><?php if ((int)$a['id'] === $me): ?><span class="tag ok">You</span><?php else: ?>
<form method="post" class="inline" onsubmit="return confirm('Remove this admin?')"><input type="hidden" name="action" value="remove"><input type="hidden" name="id" value="<?= $a['id'] ?>"><button class="g">Remove</button></form><?php endif; ?></td></tr>
<?php endforeach; ?>
</table></div>
<div class="card"><h3>Add an admin</h3>
<form method="post" class="grid"><input type="hidden" name="action" value="add">
<label>Name<input name="name" required></label>
<label>Gmail<input name="email" type="email" placeholder="name@gmail.com" required></label>
<label>Password (8+ characters)<input name="password" type="password" required></label>
<label>Confirm password<input name="confirm" type="password" required></label>
<label>&nbsp;<button>Save admin</button></label>
</form></div>
<?php require '../includes/footer.php'; ?>
