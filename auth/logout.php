<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../helpers/auth_helper.php';
session_start();
session_destroy();

if (isPublicDemoMode()) {
    header('Location: ' . APP_BASE . '/dashboard/dashboard.php');
} else {
    header('Location: ' . APP_BASE . '/auth/login.php?logged_out=1');
}
exit;
?>
