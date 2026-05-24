<?php
/**
 * ADMIN-ONLY PAGE TEMPLATE
 * 
 * Copy code block below to top of any admin-only page:
 * Replace __DIR__ path sesuai dengan lokasi file relative to project root
 */

// ===== COPY FROM HERE =====
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth_helper.php';

// Ensure user is logged in
requireLogin();

// Ensure user is admin
requireRole('admin');
// ===== COPY UNTIL HERE =====

// Your page code di bawah ini...
?>
