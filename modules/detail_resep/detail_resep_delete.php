<?php
require_once __DIR__ . '/../../config/database.php';

$DetailResepNo = isset($_GET['DetailResepNo']) ? (int) $_GET['DetailResepNo'] : 0;

if ($DetailResepNo > 0) {
    // Ambil ResepNo terlebih dahulu untuk redirect kembali
    $stmt = mysqli_prepare($conn, "SELECT ResepNo FROM detail_resep WHERE DetailResepNo = ? LIMIT 1");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $DetailResepNo);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $detail = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);
        
        if ($detail) {
            $ResepNo = $detail['ResepNo'];
            
            // Hapus detail resep
            $deleteStmt = mysqli_prepare($conn, "DELETE FROM detail_resep WHERE DetailResepNo = ? LIMIT 1");
            if ($deleteStmt) {
                mysqli_stmt_bind_param($deleteStmt, 'i', $DetailResepNo);
                mysqli_stmt_execute($deleteStmt);
                mysqli_stmt_close($deleteStmt);
            }
            
            header('Location: detail_resep_tambah.php?ResepNo=' . $ResepNo . '&deleted=1');
            exit();
        }
    }
}

header('Location: detail_resep_tambah.php?deleted=1');
exit();
?>
