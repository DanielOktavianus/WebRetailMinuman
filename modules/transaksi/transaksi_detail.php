<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/format_helper.php';
?>
<!doctype html>
<html lang="id">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width,initial-scale=1" />
	<title>Transaksi — Detail</title>
	<link rel="stylesheet" href="../../assets/css/main.css">
	<link rel="stylesheet" href="../../assets/css/components.css">
	<link rel="stylesheet" href="../../assets/css/modules.css">
	<style>
		@media print {
			/* Reset background — INI penyebab gradien ungu ikut tercetak */
			html, body {
				background: white !important;
				min-height: 0 !important;
			}
			/* Sembunyikan sidebar, hamburger button, overlay, dan tombol aksi */
			.sidebar,
			.hamburger-btn,
			.sidebar-overlay,
			.no-print {
				display: none !important;
				width: 0 !important;
			}
			/* Override flex → block supaya konten full width */
			.app-layout {
				display: block !important;
				background: white !important;
			}
			.main-content {
				display: block !important;
				margin: 0 !important;
				padding: 8px !important;
				width: 100% !important;
				max-width: 100% !important;
				background: white !important;
			}
			.container {
				max-width: 100% !important;
				padding: 0 !important;
			}
			/* Bersihkan card */
			.card {
				box-shadow: none !important;
				border: none !important;
				padding: 4px 0 !important;
			}
			/* Paksa warna background tabel ikut tercetak */
			* { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
		}
	</style>
</head>
<body>
	<div class="app-layout">
		<?php include __DIR__ . '/../../components/sidebar.php'; ?>
		<main class="main-content module-transaksi">
			<div class="container">
				<div class="card">
					<?php
					$transaksiNo = isset($_GET['transaksiNo']) ? (int) $_GET['transaksiNo'] : 0;
					
					if ($transaksiNo <= 0) {
						echo '<p style="color:#dc2626">Transaksi tidak valid.</p>';
					} else {
						// Ambil header transaksi dengan semua FK (customer table sudah dihapus)
						$stmt = mysqli_prepare($conn,
							"SELECT t.transaksiNo, t.tanggal, t.total_harga,
									t.voucherNo, t.metode_pembayaranNo, t.karyawanNo,
									COALESCE(k.nama, '-') AS karyawan, k.kontak AS kontak_karyawan,
									COALESCE(m.nama_metode, '-') AS metode,
									COALESCE(v.nama_voucher, '-') AS voucher,
									COALESCE(v.nilai_diskon, 0) AS nilai_diskon
							FROM transaksi t
							LEFT JOIN karyawan k ON t.karyawanNo = k.karyawanNo
							LEFT JOIN metode_pembayaran m ON t.metode_pembayaranNo = m.metode_pembayaranNo
							LEFT JOIN voucher v ON t.voucherNo = v.voucherNo
							WHERE t.transaksiNo = ? LIMIT 1");
						
						if (!$stmt) {
							die('Query prepare error: ' . mysqli_error($conn));
						}
						
						mysqli_stmt_bind_param($stmt, 'i', $transaksiNo);
						mysqli_stmt_execute($stmt);
						$res = mysqli_stmt_get_result($stmt);
						$tx = mysqli_fetch_assoc($res);
						mysqli_stmt_close($stmt);
						
						if (!$tx) {
							echo '<p style="color:#dc2626">Transaksi tidak ditemukan.</p>';
						} else {
							// Header toko
							echo '<div style="text-align:center;padding:12px 0 16px;border-bottom:2px solid #e5e7eb;margin-bottom:16px">';
							echo '<strong style="display:block;font-size:20px;color:#1f2937;letter-spacing:0.5px">Monitoring Penjualan</strong>';
							echo '<span style="font-size:13px;color:#6b7280">Jl. Sagan No.3 Terban, Gondokusuman, Kota Yogyakarta</span>';
							echo '<p style="margin:6px 0 0;font-size:12px;color:#9ca3af">No. Transaksi: #' . htmlspecialchars($tx['transaksiNo']) . '</p>';
							echo '</div>';

							// Info header dengan FK
							echo '<div style="background:#f9fafb;padding:12px;border-radius:8px;margin-bottom:16px">';
							echo '<table style="width:100%">';
							echo '<tr><td style="width:20%"><strong>Tanggal:</strong></td><td>' . htmlspecialchars($tx['tanggal'] ?? '-') . '</td></tr>';
							echo '<tr><td><strong>Karyawan:</strong></td><td>' . htmlspecialchars($tx['karyawan']) . 
								 (trim($tx['kontak_karyawan']) ? ' (' . htmlspecialchars($tx['kontak_karyawan']) . ')' : '') . '</td></tr>';
							echo '<tr><td><strong>Metode Pembayaran:</strong></td><td>' . htmlspecialchars($tx['metode']) . '</td></tr>';
							$diskonInfo = '';
							if ($tx['voucherNo'] && $tx['nilai_diskon'] > 0) {
								$diskonInfo = ' <span style="color:#b91c1c;font-weight:600">— Potongan: ' . rupiah($tx['nilai_diskon']) . '</span>';
							}
							echo '<tr><td><strong>Voucher:</strong></td><td>' . htmlspecialchars($tx['voucher']) .
								 $diskonInfo . '</td></tr>';
							echo '</table>';
							echo '</div>';
							
							// Detail item transaksi
							echo '<h3>Item Transaksi</h3>';
							$stmt2 = mysqli_prepare($conn,
								"SELECT dt.detailNo, dt.jumlah, dt.harga_satuan, dt.VarianMenuNo,
										COALESCE(m.nama_menu, '?') AS menu_name,
										COALESCE(u.nama_ukuran, '-') AS ukuran
								FROM detail_transaksi dt
								LEFT JOIN varian_menu vm ON dt.VarianMenuNo = vm.VarianMenuNo
								LEFT JOIN menu m         ON vm.MenuNo       = m.MenuNo
								LEFT JOIN ukuran u       ON vm.ukuranNo     = u.UkuranNo
								WHERE dt.transaksiNo = ?");
							
							if (!$stmt2) {
								die('Query detail prepare error: ' . mysqli_error($conn));
							}
							
							mysqli_stmt_bind_param($stmt2, 'i', $transaksiNo);
							mysqli_stmt_execute($stmt2);
							$res2 = mysqli_stmt_get_result($stmt2);
							
							if (mysqli_num_rows($res2) > 0) {
								echo '<table style="width:100%;border-collapse:collapse;margin-bottom:12px">';
								echo '<thead><tr style="background:#e5e7eb;border-bottom:2px solid #999">';
								echo '<th style="text-align:left;padding:10px">Menu</th>';
								echo '<th style="text-align:left;padding:10px">Ukuran</th>';
								echo '<th style="text-align:right;padding:10px">Jumlah</th>';
								echo '<th style="text-align:right;padding:10px">Harga Satuan</th>';
								echo '<th style="text-align:right;padding:10px">Subtotal</th>';
								echo '</tr></thead>';
								echo '<tbody>';
								
								$sum = 0;
								while ($r = mysqli_fetch_assoc($res2)) {
									$menu = htmlspecialchars($r['menu_name']);
									$ukuran = htmlspecialchars($r['ukuran']);
									$qty = (int)$r['jumlah'];
									$harga = (float)$r['harga_satuan'];
									$sub = $qty * $harga;
									$sum += $sub;
									
									echo '<tr style="border-bottom:1px solid #ddd">';
									echo "<td style=\"padding:10px\">{$menu}</td>";
									echo "<td style=\"padding:10px\">{$ukuran}</td>";
									echo "<td style=\"padding:10px;text-align:right\">{$qty}</td>";
									echo "<td style=\"padding:10px;text-align:right\">" . rupiah($harga) . "</td>";
									echo "<td style=\"padding:10px;text-align:right\">" . rupiah($sub) . "</td>";
									echo '</tr>';
								}
								
								$nilai_diskon = (float)($tx['nilai_diskon'] ?? 0);

								echo '<tr style="background:#f3f4f6;font-weight:bold;border-top:2px solid #999">';
								echo '<td colspan="4" style="padding:10px;text-align:right">Total Hitung:</td>';
								echo '<td style="padding:10px;text-align:right">' . rupiah($sum) . '</td>';
								echo '</tr>';

								if ($nilai_diskon > 0 && $tx['voucherNo']) {
									echo '<tr style="background:#fee2e2">';
									echo '<td colspan="4" style="padding:10px;text-align:right;color:#b91c1c">Potongan Voucher (' . htmlspecialchars($tx['voucher']) . '):</td>';
									echo '<td style="padding:10px;text-align:right;color:#b91c1c;font-weight:bold">- ' . rupiah($nilai_diskon) . '</td>';
									echo '</tr>';
								}

								$totalBayar = max(0, $sum - $nilai_diskon);
								echo '<tr style="background:#fef3c7">';
								echo '<td colspan="4" style="padding:10px;text-align:right">Total Tercatat:</td>';
								echo '<td style="padding:10px;text-align:right;font-weight:bold">' . rupiah($totalBayar) . '</td>';
								echo '</tr>';
								
								echo '</tbody></table>';
							} else {
								echo '<p>Tidak ada item transaksi.</p>';
							}
							
							mysqli_stmt_close($stmt2);
							
							// Tombol aksi
							echo '<div class="no-print" style="margin-top:20px;display:flex;gap:10px">';
							echo '<button onclick="window.print()" style="padding:10px 20px;background:#4f46e5;color:white;border:none;border-radius:6px;cursor:pointer;font-size:14px;font-weight:600">🖨️ Cetak / Simpan PDF</button>';
							echo '<a href="transaksi_list.php" style="padding:10px 20px;background:#6b7280;color:white;text-decoration:none;border-radius:6px;font-size:14px;font-weight:600;display:inline-flex;align-items:center">← Kembali ke Daftar</a>';
							echo '</div>';
						}
					}
					?>
				</div>
			</div>
		</main>
	</div>
</body>
</html>
