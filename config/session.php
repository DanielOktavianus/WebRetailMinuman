<?php
require_once __DIR__ . '/app.php';
session_start();

if(!isset($_SESSION['user'])) {
    header("Location: " . APP_BASE . "/auth/login.php");
    exit;
}
?>
