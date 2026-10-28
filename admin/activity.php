<?php
require '../includes/header.php';
$who = $_GET['who'] ?? '';
$sql = 'SELECT * FROM activity_log' . (in_array($who, ['admin','customer','guest']) ? ' WHERE role = ?' : '') . ' ORDER BY id DESC LIMIT 300';
$st = $pdo->prepare($sql);
$st->execute(in_array($who, ['admin','customer','guest']) ? [$who] : []);
$rows = $st->fetchAll();
?>
<div class="card"><form method="get" class="grid">
<label>Show activity by<select name="who">
<option value="">Everyone</option>
<?php foreach (['admin'=>'Admins','customer'=>'Customers','guest'=>'Guests'] as $k=>$l): ?><option value="<?= $k ?>" <?= $who===$k?'selected':'' ?>><?= $l ?></option><?php endforeach; ?>
</select></label>
<label>&nbsp;<button>Filter</button></label>
</form></div>
<div class="card"><h3>Activity log (latest 300)</h3><table>
<tr><th>Date and time</th><th>Who</th><th>Role</th><th>Action</th><th>Details</th></tr>
<?php foreach ($rows as $r): ?>
<tr><td><?= e($r['created_at']) ?></td><td><?= e($r['user_name']) ?></td><td><?= e($r['role']) ?></td><td><?= e($r['action']) ?></td><td><?= e($r['details']) ?></td></tr>
<?php endforeach; if (!$rows): ?><tr><td colspan="5">No activity recorded yet.</td></tr><?php endif; ?>
</table></div>
<?php require '../includes/footer.php'; ?>
