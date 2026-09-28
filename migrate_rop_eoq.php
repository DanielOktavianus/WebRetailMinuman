<?php
// Jalankan PERTAMA via browser: http://localhost/SKRIPSIS8/migrate_rop_eoq.php
require_once __DIR__ . '/config/database.php';

$check = mysqli_query($conn, "SHOW COLUMNS FROM stok LIKE 'lead_time'");
if (mysqli_num_rows($check) === 0) {
    $sql = "ALTER TABLE stok
        ADD COLUMN lead_time    INT           NOT NULL DEFAULT 1 COMMENT 'Hari pengiriman supplier',
        ADD COLUMN biaya_pesan  DECIMAL(12,2) NOT NULL DEFAULT 0 COMMENT 'Biaya sekali order (Rp)',
        ADD COLUMN biaya_simpan DECIMAL(12,2) NOT NULL DEFAULT 0 COMMENT 'Biaya simpan per unit per bulan (Rp)'";
    if (mysqli_query($conn, $sql)) {
        echo "<p style='color:green;font-family:sans-serif;font-size:16px'>✅ Berhasil! Kolom <strong>lead_time</strong>, <strong>biaya_pesan</strong>, <strong>biaya_simpan</strong> ditambahkan ke tabel stok.</p>";
        echo "<p style='font-family:sans-serif'><a href='modules/stok/stok_rop_eoq.php'>→ Buka halaman ROP & EOQ</a></p>";
    } else {
        echo "<p style='color:red;font-family:sans-serif'>❌ Gagal: " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p style='color:#555;font-family:sans-serif'>ℹ️ Kolom sudah ada, tidak perlu migrasi.</p>";
    echo "<p style='font-family:sans-serif'><a href='modules/stok/stok_rop_eoq.php'>→ Buka halaman ROP & EOQ</a></p>";
}
?>
