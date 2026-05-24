<?php
require_once __DIR__ . '/../config/app.php';
session_start();
session_destroy();
header('Location: ' . APP_BASE . '/auth/login.php?logged_out=1');
exit;
?>
