<?php
// PAYOUTS: how much paid-order money is waiting to be moved to GCash / a bank account,
// and a record of every transfer already made. This page RECORDS transfers; it does not send money.
require '../includes/header.php';

if (($_SESSION['user']['role'] ?? '') !== 'admin') {
    echo '<div class="msg err">This page is for admins only.</div>';
    require '../includes/footer.php'; exit;
}

/* Creates the payouts table the first time this page opens, so no manual SQL is needed. */
$pdo->exec("CREATE TABLE IF NOT EXISTS payouts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  amount DECIMAL(12,2) NOT NULL,
  method VARCHAR(20) NOT NULL,
  bank_name VARCHAR(60) NOT NULL DEFAULT '',
  account_name VARCHAR(100) NOT NULL,
  account_number VARCHAR(40) NOT NULL,
  reference_no VARCHAR(60) NOT NULL DEFAULT '',
  note VARCHAR(255) NOT NULL DEFAULT '',
  created_by VARCHAR(100) NOT NULL DEFAULT '',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
$csrf = $_SESSION['csrf'];

/* ---------- Work out total of paid orders (column names are detected, not assumed) ---------- */
$cols = [];
try { foreach ($pdo->query('SHOW COLUMNS FROM orders') as $c) $cols[] = $c['Field']; } catch (PDOException $x) {}
$has = function (array $names) use ($cols) {
    foreach ($names as $n) if (in_array($n, $cols, true)) return $n;
    return null;
};
$tcol = $has(['total', 'total_amount', 'amount', 'grand_total', 'total_price']);

$paidWhere = null;
if     ($c = $has(['payment_status', 'payment'])) $paidWhere = "LOWER(`$c`) IN ('paid','completed','verified')";
elseif ($c = $has(['is_paid', 'paid']))           $paidWhere = "LOWER(`$c`) IN ('1','paid','yes','true')";
elseif ($c = $has(['paid_at']))                   $paidWhere = "`$c` IS NOT NULL";

$paidTotal = null;
if ($tcol && $paidWhere) {
    $paidTotal = (float)$pdo->query("SELECT COALESCE(SUM(`$tcol`),0) FROM orders WHERE $paidWhere")->fetchColumn();
}
$sentOut   = (float)$pdo->query('SELECT COALESCE(SUM(amount),0) FROM payouts')->fetchColumn();
$available = $paidTotal === null ? null : round($paidTotal - $sentOut, 2);

/* ---------- Save / delete a transfer ---------- */
$msg = ''; $err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($csrf, $_POST['csrf'] ?? '')) {
        $err = 'Your session expired. Please try again.';
    } elseif (($_POST['action'] ?? '') === 'add') {
        $method = $_POST['method'] ?? '';
        $bank   = trim($_POST['bank_name'] ?? '');
        $name   = trim($_POST['account_name'] ?? '');
        $num    = preg_replace('/[\s-]+/', '', $_POST['account_number'] ?? '');
        $amount = round((float)($_POST['amount'] ?? 0), 2);
        $ref    = trim($_POST['reference_no'] ?? '');
        $note   = trim($_POST['note'] ?? '');

        if (!in_array($method, ['GCash', 'Bank transfer'], true)) $err = 'Choose GCash or Bank transfer.';
        elseif ($name === '' || $num === '')                      $err = 'Account name and number are required.';
        elseif ($method === 'GCash' && !preg_match('/^09\d{9}$/', $num)) $err = 'GCash number must look like 09171234567.';
        elseif ($method === 'Bank transfer' && ($bank === '' || !preg_match('/^\d{6,20}$/', $num))) $err = 'Enter the bank name and a valid account number (digits only).';
        elseif ($amount <= 0)                                     $err = 'Amount must be more than zero.';
        elseif ($available !== null && $amount > $available + 0.005) $err = 'That is more than the ' . peso($available) . ' available to transfer.';
        else {
            if ($method === 'GCash') $bank = '';
            $pdo->prepare('INSERT INTO payouts (amount,method,bank_name,account_name,account_number,reference_no,note,created_by) VALUES (?,?,?,?,?,?,?,?)')
                ->execute([$amount, $method, $bank, $name, $num, $ref, $note, $_SESSION['user']['name'] ?? '']);
            log_activity($pdo, 'Recorded transfer', peso($amount) . ' to ' . ($method === 'GCash' ? 'GCash' : $bank) . ' ****' . substr($num, -4));
            $sentOut += $amount;
            $available = $paidTotal === null ? null : round($paidTotal - $sentOut, 2);
            $msg = 'Transfer recorded.';
        }
    } elseif (($_POST['action'] ?? '') === 'delete') {
        $pdo->prepare('DELETE FROM payouts WHERE id = ?')->execute([(int)$_POST['id']]);
        log_activity($pdo, 'Deleted transfer record', 'Payout #' . (int)$_POST['id']);
        $sentOut = (float)$pdo->query('SELECT COALESCE(SUM(amount),0) FROM payouts')->fetchColumn();
        $available = $paidTotal === null ? null : round($paidTotal - $sentOut, 2);
        $msg = 'Transfer record deleted.';
    }
}

$history = $pdo->query('SELECT * FROM payouts ORDER BY id DESC LIMIT 50')->fetchAll();
$last = $history[0] ?? null;
$mask = function ($n) { return str_repeat('*', max(0, strlen($n) - 4)) . substr($n, -4); };
$val  = function ($k, $default = '') { return e($_POST[$k] ?? $default); };
$method0 = $_POST['method'] ?? ($last['method'] ?? 'GCash');
?>
<?php if ($msg): ?><div class="msg ok"><?= e($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="msg err"><?= e($err) ?></div><?php endif; ?>

<div class="stats">
  <div class="stat"><b><?= $paidTotal === null ? 'n/a' : peso($paidTotal) ?></b><span>Total paid orders</span></div>
  <div class="stat"><b><?= peso($sentOut) ?></b><span>Already transferred</span></div>
  <div class="stat"><b><?= $available === null ? 'n/a' : peso($available) ?></b><span>Available to transfer</span></div>
</div>

<?php if ($paidTotal === null): ?>
<div class="msg err">I could not work out which orders are paid, so the totals show n/a.
  Columns in the orders table: <?= e($cols ? implode(', ', $cols) : 'none found') ?>.
  You can still record transfers below, but without a limit.</div>
<?php endif; ?>

<div class="card"><h3>Record a transfer</h3>
<p style="color:#6b7280">Send the money from your own GCash or banking app first, then record it here so the balance stays correct.
This page does not move any money.</p>
<form method="post" class="grid">
  <input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="action" value="add">
  <label>Send to
    <select name="method" id="method">
      <option <?= $method0 === 'GCash' ? 'selected' : '' ?>>GCash</option>
      <option <?= $method0 === 'Bank transfer' ? 'selected' : '' ?>>Bank transfer</option>
    </select></label>
  <label id="bank-field">Bank name<input name="bank_name" value="<?= $val('bank_name', $last['bank_name'] ?? '') ?>" placeholder="e.g. BDO, BPI"></label>
  <label>Account name<input name="account_name" value="<?= $val('account_name', $last['account_name'] ?? '') ?>" required></label>
  <label id="num-label">GCash number<input name="account_number" value="<?= $val('account_number', $last['account_number'] ?? '') ?>" inputmode="numeric" required></label>
  <label>Amount (₱)<input name="amount" id="amount" type="number" min="0.01" step="0.01" value="<?= $val('amount') ?>" required></label>
  <label>Reference no.<input name="reference_no" value="<?= $val('reference_no') ?>" placeholder="optional"></label>
  <label>Note<input name="note" value="<?= $val('note') ?>" placeholder="optional"></label>
  <label>&nbsp;<button>Save transfer</button></label>
</form>
<?php if ($available !== null && $available > 0): ?>
  <p style="margin-top:12px"><button type="button" class="g" id="fill-all" data-amount="<?= number_format($available, 2, '.', '') ?>">Use all available (<?= peso($available) ?>)</button></p>
<?php endif; ?>
</div>

<div class="card"><h3>Transfer history</h3>
<table>
<tr><th>Date</th><th>Sent to</th><th>Account</th><th>Amount</th><th>Reference</th><th>By</th><th></th></tr>
<?php foreach ($history as $h): ?>
<tr>
  <td><?= e($h['created_at']) ?></td>
  <td><?= e($h['method'] === 'GCash' ? 'GCash' : ($h['bank_name'] ?: 'Bank')) ?></td>
  <td><?= e($h['account_name']) ?><br><small style="color:#6b7280"><?= e($mask($h['account_number'])) ?></small></td>
  <td><b><?= peso($h['amount']) ?></b></td>
  <td><?= e($h['reference_no'] ?: ($h['note'] ?: '-')) ?></td>
  <td><?= e($h['created_by']) ?></td>
  <td><form method="post" class="inline" onsubmit="return confirm('Delete this transfer record? The amount goes back to Available.')">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" value="<?= (int)$h['id'] ?>"><button class="g">Delete</button></form></td>
</tr>
<?php endforeach; if (!$history): ?><tr><td colspan="7">No transfers recorded yet.</td></tr><?php endif; ?>
</table></div>

<script>
(function () {
  var method = document.getElementById('method'),
      bank = document.getElementById('bank-field'),
      label = document.getElementById('num-label'),
      input = label.querySelector('input');
  function sync() {
    var gcash = method.value === 'GCash';
    bank.style.display = gcash ? 'none' : '';
    label.firstChild.nodeValue = gcash ? 'GCash number' : 'Account number';
    input.placeholder = gcash ? '09171234567' : 'digits only';
  }
  method.addEventListener('change', sync); sync();
  var fill = document.getElementById('fill-all');
  if (fill) fill.addEventListener('click', function () { document.getElementById('amount').value = fill.dataset.amount; });
})();
</script>
<?php require '../includes/footer.php'; ?>
