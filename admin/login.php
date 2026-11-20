<?php
// There is one login page for everyone now. Old links to the admin login land here and are forwarded.
require_once '../includes/db.php';
header('Location: ' . BASE . 'login.php');
exit;