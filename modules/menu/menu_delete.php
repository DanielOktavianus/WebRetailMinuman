<?php
require_once __DIR__ . '/../../config/database.php';

if (!isset($_GET['MenuNo']) || !is_numeric($_GET['MenuNo'])) {
    header('Location: menu_tambah.php');
    exit;
}

$MenuNo = (int) $_GET['MenuNo'];

try {
    // Step 1: Hapus detail_transaksi yang mereferensi varian_menu milik menu ini
    // (tidak ada CASCADE dari varian_menu ke detail_transaksi)
    $stmt1 = mysqli_prepare($conn,
        "DELETE FROM detail_transaksi WHERE VarianMenuNo IN
         (SELECT VarianMenuNo FROM varian_menu WHERE MenuNo = ?)");
    if (!$stmt1) throw new Exception('Prepare delete detail_transaksi gagal: ' . mysqli_error($conn));
    mysqli_stmt_bind_param($stmt1, 'i', $MenuNo);
    if (!mysqli_stmt_execute($stmt1)) throw new Exception('Delete detail_transaksi gagal: ' . mysqli_stmt_error($stmt1));
    mysqli_stmt_close($stmt1);

    // Step 2: Hapus varian_menu → otomatis CASCADE ke resep → CASCADE ke detail_resep
    $stmt2 = mysqli_prepare($conn, "DELETE FROM varian_menu WHERE MenuNo = ?");
    if (!$stmt2) throw new Exception('Prepare delete varian_menu gagal: ' . mysqli_error($conn));
    mysqli_stmt_bind_param($stmt2, 'i', $MenuNo);
    if (!mysqli_stmt_execute($stmt2)) throw new Exception('Delete varian_menu gagal: ' . mysqli_stmt_error($stmt2));
    mysqli_stmt_close($stmt2);

    // Step 3: Hapus menu itu sendiri
    $stmt3 = mysqli_prepare($conn, "DELETE FROM menu WHERE MenuNo = ?");
    if (!$stmt3) throw new Exception('Prepare delete menu gagal: ' . mysqli_error($conn));
    mysqli_stmt_bind_param($stmt3, 'i', $MenuNo);
    if (!mysqli_stmt_execute($stmt3)) throw new Exception('Delete menu gagal: ' . mysqli_stmt_error($stmt3));
    mysqli_stmt_close($stmt3);

    header('Location: menu_tambah.php?deleted=1');
    exit;
} catch (Exception $e) {
    die('Error saat menghapus menu: ' . htmlspecialchars($e->getMessage()));
}
?>
