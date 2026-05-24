<?php
require_once __DIR__ . '/../../config/database.php';

if (!isset($_GET['ResepNo']) || !is_numeric($_GET['ResepNo'])) {
    header('Location: resep_tambah.php');
    exit;
}

$ResepNo = (int) $_GET['ResepNo'];

try {
    // Step 1: Delete detail_resep yang mereferensi resep ini
    $stmt1 = mysqli_prepare($conn, "DELETE FROM detail_resep WHERE ResepNo = ?");
    if (!$stmt1) throw new Exception('Prepare delete detail_resep gagal: ' . mysqli_error($conn));
    mysqli_stmt_bind_param($stmt1, 'i', $ResepNo);
    if (!mysqli_stmt_execute($stmt1)) throw new Exception('Delete detail_resep gagal: ' . mysqli_stmt_error($stmt1));
    mysqli_stmt_close($stmt1);
    
    // Step 2: Delete resep itu sendiri
    $stmt2 = mysqli_prepare($conn, "DELETE FROM resep WHERE ResepNo = ?");
    if (!$stmt2) throw new Exception('Prepare delete resep gagal: ' . mysqli_error($conn));
    mysqli_stmt_bind_param($stmt2, 'i', $ResepNo);
    if (!mysqli_stmt_execute($stmt2)) throw new Exception('Delete resep gagal: ' . mysqli_stmt_error($stmt2));
    mysqli_stmt_close($stmt2);
    
    header('Location: resep_tambah.php');
    exit;
} catch (Exception $e) {
    die('Error saat menghapus resep: ' . htmlspecialchars($e->getMessage()));
}
?>
