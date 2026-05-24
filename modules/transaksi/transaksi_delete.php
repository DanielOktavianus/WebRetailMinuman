<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth_helper.php';

// Validate user is logged in
requireLogin();

// Get transaksiNo from URL
$transaksiNo = isset($_GET['transaksiNo']) && is_numeric($_GET['transaksiNo']) ? (int) $_GET['transaksiNo'] : null;

if ($transaksiNo === null) {
    header('Location: transaksi_list.php');
    exit;
}

// Check if transaksi exists
$checkStmt = mysqli_prepare($conn, "SELECT transaksiNo FROM transaksi WHERE transaksiNo = ?");
if (!$checkStmt) {
    die('Prepare failed: ' . mysqli_error($conn));
}
mysqli_stmt_bind_param($checkStmt, 'i', $transaksiNo);
mysqli_stmt_execute($checkStmt);
$checkResult = mysqli_stmt_get_result($checkStmt);
mysqli_stmt_close($checkStmt);

if (mysqli_num_rows($checkResult) === 0) {
    // Transaksi tidak ditemukan, redirect
    header('Location: transaksi_list.php?error=not_found');
    exit;
}

// Start transaction
mysqli_begin_transaction($conn);

try {
    // Step 1: Delete all detail_transaksi records for this transaksi
    $deleteDetailStmt = mysqli_prepare($conn, "DELETE FROM detail_transaksi WHERE transaksiNo = ?");
    if (!$deleteDetailStmt) {
        throw new Exception('Prepare detail delete failed: ' . mysqli_error($conn));
    }
    mysqli_stmt_bind_param($deleteDetailStmt, 'i', $transaksiNo);
    if (!mysqli_stmt_execute($deleteDetailStmt)) {
        throw new Exception('Execute detail delete failed: ' . mysqli_error($conn));
    }
    mysqli_stmt_close($deleteDetailStmt);

    // Step 2: Delete the transaksi record
    $deleteTransaksiStmt = mysqli_prepare($conn, "DELETE FROM transaksi WHERE transaksiNo = ?");
    if (!$deleteTransaksiStmt) {
        throw new Exception('Prepare transaksi delete failed: ' . mysqli_error($conn));
    }
    mysqli_stmt_bind_param($deleteTransaksiStmt, 'i', $transaksiNo);
    if (!mysqli_stmt_execute($deleteTransaksiStmt)) {
        throw new Exception('Execute transaksi delete failed: ' . mysqli_error($conn));
    }
    mysqli_stmt_close($deleteTransaksiStmt);

    // Commit transaction
    mysqli_commit($conn);

    // Redirect with success message
    header('Location: transaksi_list.php?success=deleted');
    exit;

} catch (Exception $e) {
    // Rollback on error
    mysqli_rollback($conn);
    
    // Redirect with error message
    header('Location: transaksi_list.php?error=' . urlencode($e->getMessage()));
    exit;
}
?>
