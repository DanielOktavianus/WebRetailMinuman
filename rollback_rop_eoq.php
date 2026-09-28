<?php
// Jalankan sekali via browser: http://localhost/SKRIPSIS8/rollback_rop_eoq.php
require_once __DIR__ . '/config/database.php';

$check = mysqli_query($conn, "SHOW COLUMNS FROM stok LIKE 'lead_time'");
if (mysqli_num_rows($check) > 0) {
    $sql = "ALTER TABLE stok DROP COLUMN lead_time, DROP COLUMN biaya_pesan, DROP COLUMN biaya_simpan";
    if (mysqli_query($conn, $sql)) {
        echo "<p style='color:green;font-family:sans-serif'>✅ Kolom lead_time, biaya_pesan, biaya_simpan berhasil dihapus dari tabel stok.</p>";
    } else {
        echo "<p style='color:red;font-family:sans-serif'>❌ Gagal: " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p style='color:#555;font-family:sans-serif'>ℹ️ Kolom tidak ditemukan, tidak perlu rollback.</p>";
}
?>
