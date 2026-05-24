<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth_helper.php';

requireRole('admin');

$karyawanNo = isset($_GET['karyawanNo']) && is_numeric($_GET['karyawanNo']) ? (int) $_GET['karyawanNo'] : null;

if ($karyawanNo === null) {
    header('Location: karyawan_tambah.php');
    exit;
}

try {
    // Step 1: Delete detail_transaksi yang mereferensi transaksi yang mereferensi karyawan ini
    $stmt1 = mysqli_prepare($conn, 
        "DELETE FROM detail_transaksi WHERE transaksiNo IN 
         (SELECT transaksiNo FROM transaksi WHERE karyawanNo = ?)");
    if (!$stmt1) throw new Exception('Prepare delete detail_transaksi gagal: ' . mysqli_error($conn));
    mysqli_stmt_bind_param($stmt1, 'i', $karyawanNo);
    if (!mysqli_stmt_execute($stmt1)) throw new Exception('Delete detail_transaksi gagal: ' . mysqli_stmt_error($stmt1));
    mysqli_stmt_close($stmt1);
    
    // Step 2: Delete transaksi yang mereferensi karyawan ini
    $stmt2 = mysqli_prepare($conn, "DELETE FROM transaksi WHERE karyawanNo = ?");
    if (!$stmt2) throw new Exception('Prepare delete transaksi gagal: ' . mysqli_error($conn));
    mysqli_stmt_bind_param($stmt2, 'i', $karyawanNo);
    if (!mysqli_stmt_execute($stmt2)) throw new Exception('Delete transaksi gagal: ' . mysqli_stmt_error($stmt2));
    mysqli_stmt_close($stmt2);
    
    // Step 3: Delete data_user yang mereferensi karyawan ini
    $stmt3 = mysqli_prepare($conn, "DELETE FROM data_user WHERE karyawanNo = ?");
    if (!$stmt3) throw new Exception('Prepare delete data_user gagal: ' . mysqli_error($conn));
    mysqli_stmt_bind_param($stmt3, 'i', $karyawanNo);
    if (!mysqli_stmt_execute($stmt3)) throw new Exception('Delete data_user gagal: ' . mysqli_stmt_error($stmt3));
    mysqli_stmt_close($stmt3);
    
    // Step 4: Delete karyawan itu sendiri
    $stmt4 = mysqli_prepare($conn, "DELETE FROM karyawan WHERE karyawanNo = ?");
    if (!$stmt4) throw new Exception('Prepare delete karyawan gagal: ' . mysqli_error($conn));
    mysqli_stmt_bind_param($stmt4, 'i', $karyawanNo);
    if (!mysqli_stmt_execute($stmt4)) throw new Exception('Delete karyawan gagal: ' . mysqli_stmt_error($stmt4));
    mysqli_stmt_close($stmt4);
    
    header('Location: karyawan_tambah.php');
    exit;
} catch (Exception $e) {
    die('Error saat menghapus karyawan: ' . htmlspecialchars($e->getMessage()));
}
?>
