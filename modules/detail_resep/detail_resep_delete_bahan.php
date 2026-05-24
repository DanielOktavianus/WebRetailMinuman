<?php
require_once __DIR__ . '/../../config/database.php';

// Ambil dan validasi daftar IDs (comma-separated integer)
$raw = isset($_GET['ids']) ? trim($_GET['ids']) : '';
if ($raw === '') {
    header('Location: detail_resep_tambah.php');
    exit;
}

// Parse: pisahkan, buang yang bukan integer positif
$idParts = explode(',', $raw);
$ids = [];
foreach ($idParts as $part) {
    $v = trim($part);
    if (is_numeric($v) && (int)$v > 0) {
        $ids[] = (int)$v;
    }
}

if (empty($ids)) {
    header('Location: detail_resep_tambah.php');
    exit;
}

// Ambil ResepNo dari salah satu ID → untuk redirect balik
$firstId   = $ids[0];
$stmtCheck = mysqli_prepare($conn, "SELECT ResepNo FROM detail_resep WHERE DetailResepNo = ? LIMIT 1");
$ResepNo   = null;
if ($stmtCheck) {
    mysqli_stmt_bind_param($stmtCheck, 'i', $firstId);
    mysqli_stmt_execute($stmtCheck);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCheck));
    $ResepNo = $row ? (int)$row['ResepNo'] : null;
    mysqli_stmt_close($stmtCheck);
}

// Hapus semua ID yang sudah divalidasi
// Bangun placeholders: ?,?,? sejumlah $ids
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$types        = str_repeat('i', count($ids));

$stmtDel = mysqli_prepare($conn, "DELETE FROM detail_resep WHERE DetailResepNo IN ({$placeholders})");
if ($stmtDel) {
    mysqli_stmt_bind_param($stmtDel, $types, ...$ids);
    mysqli_stmt_execute($stmtDel);
    mysqli_stmt_close($stmtDel);
}

// Redirect kembali ke halaman detail resep
$backUrl = 'detail_resep_tambah.php' . ($ResepNo ? '?ResepNo=' . $ResepNo : '');
header('Location: ' . $backUrl);
exit;
?>
