<?php
// ONE-TIME SETUP SCRIPT — DELETE AFTER USE
// Visit: https://skripsi8-production.up.railway.app/_setup.php

require_once __DIR__ . '/config/database.php';

$sql = <<<'SQL'
CREATE TABLE IF NOT EXISTS `bahan` (
  `BahanNo` int(11) NOT NULL AUTO_INCREMENT,
  `nama_bahan` varchar(100) NOT NULL,
  PRIMARY KEY (`BahanNo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `karyawan` (
  `karyawanNo` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) DEFAULT NULL,
  `kontak` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`karyawanNo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `data_user` (
  `usernameNo` int(11) NOT NULL AUTO_INCREMENT,
  `karyawanNo` int(11) DEFAULT NULL,
  `jabatan` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` varchar(20) DEFAULT 'karyawan',
  `username` varchar(50) NOT NULL,
  PRIMARY KEY (`usernameNo`),
  KEY `karyawanNo` (`karyawanNo`),
  CONSTRAINT `data_user_ibfk_1` FOREIGN KEY (`karyawanNo`) REFERENCES `karyawan` (`karyawanNo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `menu` (
  `MenuNo` int(11) NOT NULL AUTO_INCREMENT,
  `nama_menu` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`MenuNo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `ukuran` (
  `UkuranNo` int(11) NOT NULL AUTO_INCREMENT,
  `nama_ukuran` varchar(50) NOT NULL,
  PRIMARY KEY (`UkuranNo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `satuan` (
  `SatuanNo` int(11) NOT NULL AUTO_INCREMENT,
  `nama_satuan` varchar(50) NOT NULL,
  PRIMARY KEY (`SatuanNo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `produsen` (
  `ProdusenNo` int(11) NOT NULL AUTO_INCREMENT,
  `Nama_Produsen` varchar(100) DEFAULT NULL,
  `Alamat` text DEFAULT NULL,
  `Kontak` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`ProdusenNo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `stok` (
  `StokNo` int(11) NOT NULL AUTO_INCREMENT,
  `ProdusenNo` int(11) DEFAULT NULL,
  `jumlah_stok` int(11) DEFAULT NULL,
  `BahanNo` int(11) DEFAULT NULL,
  `SatuanNo` int(11) DEFAULT NULL,
  `batas_minimum` decimal(10,2) DEFAULT 0.00,
  PRIMARY KEY (`StokNo`),
  KEY `ProdusenNo` (`ProdusenNo`),
  KEY `fk_stok_bahan` (`BahanNo`),
  KEY `fk_stok_satuan` (`SatuanNo`),
  CONSTRAINT `fk_stok_bahan` FOREIGN KEY (`BahanNo`) REFERENCES `bahan` (`BahanNo`) ON DELETE CASCADE,
  CONSTRAINT `fk_stok_satuan` FOREIGN KEY (`SatuanNo`) REFERENCES `satuan` (`SatuanNo`) ON DELETE CASCADE,
  CONSTRAINT `stok_ibfk_1` FOREIGN KEY (`ProdusenNo`) REFERENCES `produsen` (`ProdusenNo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `resep` (
  `ResepNo` int(11) NOT NULL AUTO_INCREMENT,
  `MenuNo` int(11) DEFAULT NULL,
  `Keterangan` text DEFAULT NULL,
  PRIMARY KEY (`ResepNo`),
  UNIQUE KEY `uq_resep_menu` (`MenuNo`),
  CONSTRAINT `fk_resep_menu` FOREIGN KEY (`MenuNo`) REFERENCES `menu` (`MenuNo`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `detail_resep` (
  `DetailResepNo` int(11) NOT NULL AUTO_INCREMENT,
  `ResepNo` int(11) NOT NULL,
  `UkuranNo` int(11) DEFAULT NULL,
  `StokNo` int(11) NOT NULL,
  `SatuanNo` int(11) NOT NULL,
  `jumlah` decimal(10,2) NOT NULL,
  PRIMARY KEY (`DetailResepNo`),
  UNIQUE KEY `uq_detail_resep` (`ResepNo`,`StokNo`,`UkuranNo`),
  KEY `fk_detailresep_resep` (`ResepNo`),
  KEY `fk_detailresep_stok` (`StokNo`),
  KEY `fk_detailresep_satuan` (`SatuanNo`),
  KEY `fk_detailresep_ukuran` (`UkuranNo`),
  CONSTRAINT `fk_detailresep_resep` FOREIGN KEY (`ResepNo`) REFERENCES `resep` (`ResepNo`) ON DELETE CASCADE,
  CONSTRAINT `fk_detailresep_satuan` FOREIGN KEY (`SatuanNo`) REFERENCES `satuan` (`SatuanNo`) ON DELETE CASCADE,
  CONSTRAINT `fk_detailresep_stok` FOREIGN KEY (`StokNo`) REFERENCES `stok` (`StokNo`) ON DELETE CASCADE,
  CONSTRAINT `fk_detailresep_ukuran` FOREIGN KEY (`UkuranNo`) REFERENCES `ukuran` (`UkuranNo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `metode_pembayaran` (
  `metode_pembayaranNo` int(11) NOT NULL AUTO_INCREMENT,
  `nama_metode` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`metode_pembayaranNo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `voucher` (
  `voucherNo` int(11) NOT NULL AUTO_INCREMENT,
  `nama_voucher` varchar(100) NOT NULL,
  `nilai_diskon` decimal(12,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`voucherNo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `varian_menu` (
  `VarianMenuNo` int(11) NOT NULL AUTO_INCREMENT,
  `MenuNo` int(11) DEFAULT NULL,
  `ukuranNo` int(11) DEFAULT NULL,
  `Harga` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`VarianMenuNo`),
  KEY `MenuNo` (`MenuNo`),
  KEY `fk_varian_ukuran` (`ukuranNo`),
  CONSTRAINT `fk_varian_ukuran` FOREIGN KEY (`ukuranNo`) REFERENCES `ukuran` (`UkuranNo`),
  CONSTRAINT `varian_menu_ibfk_1` FOREIGN KEY (`MenuNo`) REFERENCES `menu` (`MenuNo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `transaksi` (
  `transaksiNo` int(11) NOT NULL AUTO_INCREMENT,
  `voucherNo` int(11) DEFAULT NULL,
  `metode_pembayaranNo` int(11) DEFAULT NULL,
  `karyawanNo` int(11) DEFAULT NULL,
  `total_harga` decimal(10,2) DEFAULT NULL,
  `tanggal` datetime DEFAULT NULL,
  PRIMARY KEY (`transaksiNo`),
  KEY `voucherNo` (`voucherNo`),
  KEY `metode_pembayaranNo` (`metode_pembayaranNo`),
  KEY `karyawanNo` (`karyawanNo`),
  CONSTRAINT `fk_transaksi_voucher` FOREIGN KEY (`voucherNo`) REFERENCES `voucher` (`voucherNo`) ON DELETE SET NULL,
  CONSTRAINT `transaksi_ibfk_2` FOREIGN KEY (`metode_pembayaranNo`) REFERENCES `metode_pembayaran` (`metode_pembayaranNo`),
  CONSTRAINT `transaksi_ibfk_3` FOREIGN KEY (`karyawanNo`) REFERENCES `karyawan` (`karyawanNo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `detail_transaksi` (
  `detailNo` int(11) NOT NULL AUTO_INCREMENT,
  `transaksiNo` int(11) DEFAULT NULL,
  `VarianMenuNo` int(11) DEFAULT NULL,
  `jumlah` int(11) DEFAULT NULL,
  `harga_satuan` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`detailNo`),
  KEY `transaksiNo` (`transaksiNo`),
  KEY `VarianMenuNo` (`VarianMenuNo`),
  CONSTRAINT `detail_transaksi_ibfk_1` FOREIGN KEY (`transaksiNo`) REFERENCES `transaksi` (`transaksiNo`),
  CONSTRAINT `detail_transaksi_ibfk_2` FOREIGN KEY (`VarianMenuNo`) REFERENCES `varian_menu` (`VarianMenuNo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `karyawan` (`karyawanNo`,`nama`,`kontak`) VALUES
(2,'Daniel Oktavianus','087749955620'),
(4,'david','098767'),
(5,'grace','0987655');

INSERT IGNORE INTO `data_user` (`usernameNo`,`karyawanNo`,`jabatan`,`password`,`role`,`username`) VALUES
(1,NULL,NULL,'$2y$10$IelwcQshCM5IkwUMBObP6O720Rcl4m1cK5lB9bGGwiOYM9PKNeHW2','admin','admin'),
(2,4,'kasir','$2y$10$wmGQvez9CbnDtY1RNEJ97O7KiYftrJka9fMDiY6G8N4cyL.B.HHjq','karyawan','karyawan1'),
(3,5,'kasir','$2y$10$hPKAK3YPz7CdO7V94DQ6Fuz/n4RtEDmb.YNKeC8oitk8tG4rz5gEK','karyawan','karyawan2');

INSERT IGNORE INTO `satuan` (`SatuanNo`,`nama_satuan`) VALUES (2,'kg'),(3,'gram'),(4,'pcs');

INSERT IGNORE INTO `ukuran` (`UkuranNo`,`nama_ukuran`) VALUES (1,'Kecil'),(2,'Besar');

INSERT IGNORE INTO `metode_pembayaran` (`metode_pembayaranNo`,`nama_metode`) VALUES (1,'QRIS'),(4,'TUNAI');

INSERT IGNORE INTO `voucher` (`voucherNo`,`nama_voucher`,`nilai_diskon`) VALUES (4,'Diskon 1',5000.00);

INSERT IGNORE INTO `produsen` (`ProdusenNo`,`Nama_Produsen`,`Alamat`,`Kontak`) VALUES
(2,'Pt teh poci indonesia','jalan jendral sudirman','08777777'),
(3,'Pt teh poci indonesia','Jalan jendral sudirman','08777777'),
(5,'PT teh poci surabaya','jalan jendral sudirman','0987867');

INSERT IGNORE INTO `bahan` (`BahanNo`,`nama_bahan`) VALUES
(9,'daun teh poci original'),(10,'gula'),(11,'bubuk teh pucuk apel');

INSERT IGNORE INTO `stok` (`StokNo`,`ProdusenNo`,`jumlah_stok`,`BahanNo`,`SatuanNo`,`batas_minimum`) VALUES
(15,2,640,9,3,100.00),(17,2,92,11,4,50.00),(18,NULL,960,10,3,1000.00);

INSERT IGNORE INTO `menu` (`MenuNo`,`nama_menu`) VALUES (16,'Teh poci original'),(17,'Teh poci apel');

INSERT IGNORE INTO `resep` (`ResepNo`,`MenuNo`,`Keterangan`) VALUES
(1,16,'tambahkan gula dan daun teh'),
(2,17,'daun teh pucuk apel dengan 10 gram gula');

INSERT IGNORE INTO `detail_resep` (`DetailResepNo`,`ResepNo`,`UkuranNo`,`StokNo`,`SatuanNo`,`jumlah`) VALUES
(1,1,1,15,3,100.00),(7,1,2,15,3,120.00),(11,2,1,17,4,1.00),(12,2,1,18,3,20.00);

INSERT IGNORE INTO `varian_menu` (`VarianMenuNo`,`MenuNo`,`ukuranNo`,`Harga`) VALUES
(2,16,2,10000.00),(3,17,2,7000.00),(4,17,1,10000.00),(5,16,1,10000.00);

INSERT IGNORE INTO `transaksi` (`transaksiNo`,`voucherNo`,`metode_pembayaranNo`,`karyawanNo`,`total_harga`,`tanggal`) VALUES
(7,4,1,2,15000.00,'2026-05-24 09:13:00');

INSERT IGNORE INTO `detail_transaksi` (`detailNo`,`transaksiNo`,`VarianMenuNo`,`jumlah`,`harga_satuan`) VALUES
(8,7,4,2,10000.00);
SQL;

$statements = array_filter(array_map('trim', explode(';', $sql)));
$success = 0;
$errors  = [];

foreach ($statements as $stmt) {
    if (empty($stmt)) continue;
    if (mysqli_query($conn, $stmt)) {
        $success++;
    } else {
        $errors[] = mysqli_error($conn) . '<br><small>' . htmlspecialchars(substr($stmt, 0, 120)) . '</small>';
    }
}

echo '<h2>Setup Result</h2>';
echo "<p>✅ <strong>{$success}</strong> statements OK</p>";
if ($errors) {
    echo '<p>⚠️ ' . count($errors) . ' error(s):</p><ul>';
    foreach ($errors as $e) echo "<li style='color:red'>{$e}</li>";
    echo '</ul>';
} else {
    echo '<p style="color:green">✅ Semua tabel berhasil dibuat! Silakan <a href="/auth/login.php">Login</a></p>';
    echo '<p style="color:orange">⚠️ Segera hapus file _setup.php dari server!</p>';
}
?>
