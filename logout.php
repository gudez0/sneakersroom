<?php
session_start();

// Clear everything the visitor had in the session, then give them a fresh one.
$_SESSION = [];
session_regenerate_id(true);

// One-time message, shown on the login page.
$_SESSION['snr_flash'] = ['ok' => 'You have been logged out.', 'err' => ''];

header('Location: login.php');
exit;