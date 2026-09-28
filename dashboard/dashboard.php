<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/format_helper.php';
?>
<!doctype html>
<html lang="id">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width,initial-scale=1" />
	<meta name="description" content="Monitoring Penjualan - Dashboard manajemen penjualan dan stok usaha minuman">
	<meta name="theme-color" content="#667eea">
	<meta name="mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
	<meta name="apple-mobile-web-app-title" content="Monitoring Penjualan">
	<link rel="manifest" href="<?php echo APP_BASE; ?>/manifest.php">
	<link rel="icon" type="image/svg+xml" href="../assets/img/icon-192.svg">
	<link rel="apple-touch-icon" href="../assets/img/icon-192.svg">
	<title>Dashboard</title>
	<link rel="stylesheet" href="../assets/css/main.css">
	<link rel="stylesheet" href="../assets/css/components.css">
	<link rel="stylesheet" href="../assets/css/modules.css">
	<style>
		.kpi-grid { 
			display: grid; 
			grid-template-columns: repeat(2, 1fr); 
			gap: 20px; 
			margin-bottom: 30px;
		}
		@media (max-width: 768px) {
			.kpi-grid {
				grid-template-columns: 1fr;
			}
		}
		.kpi-card { 
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
			color: white; 
			padding: 24px; 
			border-radius: 10px; 
			box-shadow: 0 4px 12px rgba(0,0,0,0.15);
			min-height: 140px;
			display: flex;
			flex-direction: column;
			justify-content: space-between;
		}
		.kpi-card.success { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
		.kpi-card.warning { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
		.kpi-card.info { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
		.kpi-value { font-size: 32px; font-weight: bold; margin: 12px 0; line-height: 1.2; }
		.kpi-label { font-size: 13px; opacity: 0.95; line-height: 1.3; }
		
		.section-title { font-size: 16px; font-weight: bold; margin-top: 24px; margin-bottom: 12px; color: #0f172a; }
		.dashboard-table { width: 100%; border-collapse: collapse; }
		.dashboard-table th { background: #f0f0f0; padding: 10px; text-align: left; font-weight: bold; border-bottom: 2px solid #ddd; }
		.dashboard-table td { padding: 10px; border-bottom: 1px solid #eee; }
		.dashboard-table tr:hover { background: #f9fafb; }
		
		.badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
		.badge-danger { background: #fee2e2; color: #991b1b; }
		.badge-warning { background: #fef3c7; color: #92400e; }
		.badge-success { background: #d1fae5; color: #065f46; }
		
		.dashboard-header {
			display: flex;
			justify-content: space-between;
			align-items: center;
			margin-bottom: 24px;
		}
		.refresh-btn {
			background: #667eea;
			color: white;
			border: none;
			padding: 10px 20px;
			border-radius: 6px;
			cursor: pointer;
			font-size: 14px;
			font-weight: 500;
			transition: all 0.3s ease;
		}
		.refresh-btn:hover {
			background: #764ba2;
			transform: scale(1.05);
		}
		.refresh-btn:active {
			transform: scale(0.95);
		}
	</style>
</head>
<body>
	<div class="app-layout">
		<?php include __DIR__ . '/../components/sidebar.php'; ?>
		<main class="main-content">
			<div class="container">
				<!-- Dashboard Header with Refresh Button -->
				<div class="dashboard-header">
					<h2 style="margin: 0; color: #0f172a;">Dashboard</h2>
					<button class="refresh-btn" onclick="location.reload()">🔄 Refresh</button>
				</div>
				
				<!-- KPI Cards -->
				<div class="kpi-grid">
					<?php
					// Filter periode penjualan
					$pf = isset($_GET['pf']) ? $_GET['pf'] : 'hari';
					$pfLabels = [
						'hari'   => 'Hari Ini',
						'minggu' => '7 Hari Terakhir',
						'3bulan' => '3 Bulan Terakhir',
						'tahun'  => 'Tahun Ini',
						'semua'  => 'Semua',
					];
					switch ($pf) {
						case 'minggu': $pfWhere = "AND DATE(t.tanggal) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";  $pfLabel = '7 Hari Terakhir'; break;
						case '3bulan': $pfWhere = "AND DATE(t.tanggal) >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)"; $pfLabel = '3 Bulan Terakhir'; break;
						case 'tahun':  $pfWhere = "AND YEAR(t.tanggal) = YEAR(CURDATE())";                       $pfLabel = 'Tahun Ini'; break;
						case 'semua':  $pfWhere = '';                                                             $pfLabel = 'Semua Waktu'; break;
						default:       $pf = 'hari'; $pfWhere = "AND DATE(t.tanggal) = CURDATE()";              $pfLabel = 'Hari Ini';
					}

					// 1. Total Penjualan (filter dinamis)
					$penjualanHari = mysqli_query($conn,
						"SELECT COALESCE(SUM(dt.jumlah * dt.harga_satuan), 0) AS total,
						        COUNT(DISTINCT dt.transaksiNo) AS jumlah
						 FROM detail_transaksi dt
						 LEFT JOIN transaksi t ON dt.transaksiNo = t.transaksiNo
						 WHERE 1=1 {$pfWhere}");
					$pjl = mysqli_fetch_assoc($penjualanHari);
					?>
					<div class="kpi-card success" style="grid-column:1/-1">
						<div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:10px">
							<div class="kpi-label" style="font-size:14px">Penjualan — <strong><?php echo $pfLabel; ?></strong></div>
							<div style="display:flex;flex-wrap:wrap;gap:5px">
								<?php foreach ($pfLabels as $key => $lbl):
									$href = '?' . http_build_query(array_merge($_GET, ['pf' => $key]));
									$active = ($pf === $key);
								?>
								<a href="<?php echo $href; ?>" style="padding:5px 10px;border-radius:5px;font-size:12px;font-weight:600;text-decoration:none;
									<?php echo $active
										? 'background:rgba(255,255,255,0.95);color:#065f46;'
										: 'background:rgba(255,255,255,0.25);color:white;'; ?>">
									<?php echo $lbl; ?>
								</a>
								<?php endforeach; ?>
							</div>
						</div>
						<div class="kpi-value"><?php echo rupiah($pjl['total']); ?></div>
						<div class="kpi-label"><?php echo $pjl['jumlah']; ?> transaksi</div>
					</div>
					
					<?php
					// 2. Total Stok Rendah
					$stokRendah = mysqli_query($conn, 
						"SELECT COUNT(StokNo) AS total FROM stok WHERE jumlah_stok < batas_minimum");
					$sr = mysqli_fetch_assoc($stokRendah);
					?>
					<div class="kpi-card warning">
						<div class="kpi-label">Stok Rendah</div>
						<div class="kpi-value"><?php echo $sr['total']; ?></div>
						<div class="kpi-label">Perlu restocking</div>
					</div>
					
	                    <?php
                    // 3. Total Transaksi mengikuti filter periode utama
                    $totalTransaksi = mysqli_query($conn, "SELECT COUNT(t.transaksiNo) AS total FROM transaksi t WHERE 1=1 {$pfWhere}");
                    $tt = mysqli_fetch_assoc($totalTransaksi);
                    ?>
                    <div class="kpi-card info">
                        <div class="kpi-label">Total Transaksi</div>
                        <div class="kpi-value"><?php echo $tt['total']; ?></div>
                        <div class="kpi-label">Periode: <?php echo htmlspecialchars($pfLabel); ?></div>
                    </div>
                    
                    <?php
                    // 4. Total Menu
                    $totalMenu = mysqli_query($conn, "SELECT COUNT(MenuNo) AS total FROM menu");
                    $tm = mysqli_fetch_assoc($totalMenu);
                    ?>
                    <div class="kpi-card" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                        <div class="kpi-label">Total Menu</div>
                        <div class="kpi-value"><?php echo $tm['total']; ?></div>
                        <div class="kpi-label">Menu tersedia</div>
                    </div>
                </div>

                <!-- Menu Terlaris -->
                <div class="card">
                    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;margin-bottom:10px">
                        <h3 class="section-title" style="margin:0">⭐ Menu Terlaris — <span style="color:#4f46e5"><?php echo htmlspecialchars($pfLabel); ?></span></h3>
                    </div>
                    <?php
                    // Menu Terlaris mengikuti filter periode penjualan utama
                    $menuTerlaris = mysqli_query($conn,
                        "SELECT m.nama_menu, u.nama_ukuran,
                            SUM(dt.jumlah) AS total_terjual,
                            SUM(dt.jumlah * dt.harga_satuan) AS total_harga
                         FROM detail_transaksi dt
                         LEFT JOIN varian_menu vm ON dt.VarianMenuNo = vm.VarianMenuNo
                         LEFT JOIN menu m          ON vm.MenuNo      = m.MenuNo
                         LEFT JOIN ukuran u         ON vm.ukuranNo   = u.UkuranNo
                         LEFT JOIN transaksi t      ON dt.transaksiNo = t.transaksiNo
                         WHERE 1=1 {$pfWhere}
                         GROUP BY m.MenuNo, vm.VarianMenuNo
                         ORDER BY total_terjual DESC
                         LIMIT 10");
                    if ($menuTerlaris && mysqli_num_rows($menuTerlaris) > 0) {
                        echo '<table class="dashboard-table">';
                        echo '<thead><tr>';
                        echo '<th>#</th><th>Menu</th><th>Ukuran</th>';
                        echo '<th style="text-align:right">Total Terjual</th>';
                        echo '<th style="text-align:right">Total Pendapatan</th>';
                        echo '</tr></thead><tbody>';
                        $rank = 1;
                        while ($m = mysqli_fetch_assoc($menuTerlaris)) {
                            $medal = $rank === 1 ? '🥇' : ($rank === 2 ? '🥈' : ($rank === 3 ? '🥉' : $rank));
                            echo '<tr>';
                            echo '<td style="text-align:center;font-weight:bold">' . $medal . '</td>';
                            echo '<td><strong>' . htmlspecialchars($m['nama_menu']) . '</strong></td>';
                            echo '<td>' . htmlspecialchars($m['nama_ukuran'] ?? '-') . '</td>';
                            echo '<td style="text-align:right">' . (int)$m['total_terjual'] . ' item</td>';
                            echo '<td style="text-align:right">' . rupiah($m['total_harga']) . '</td>';
                            echo '</tr>';
                            $rank++;
                        }
                        echo '</tbody></table>';
                    } else {
                        echo '<p style="color:#6b7280;text-align:center;padding:20px 0">Tidak ada data penjualan untuk periode ini.</p>';
                    }
                    ?>
                </div>
				<!-- Stok Rendah -->
				<div class="card">
					<h3 class="section-title">⚠️ Stok Rendah (Perhatian)</h3>
					<?php
					$stokRendahList = mysqli_query($conn,
						"SELECT s.StokNo, b.nama_bahan, s.jumlah_stok, s.batas_minimum,
						        sat.nama_satuan, p.Nama_Produsen
						 FROM stok s
						 LEFT JOIN bahan b ON s.BahanNo = b.BahanNo
						 LEFT JOIN satuan sat ON s.SatuanNo = sat.SatuanNo
						 LEFT JOIN produsen p ON s.ProdusenNo = p.ProdusenNo
						 WHERE s.jumlah_stok < s.batas_minimum
						 ORDER BY s.jumlah_stok ASC");
					
					if (mysqli_num_rows($stokRendahList) > 0) {
						echo '<table class="dashboard-table">';
						echo '<thead><tr><th>Nama Bahan</th><th>Stok Saat Ini</th><th>Satuan</th><th>Batas Min</th><th>Produsen</th><th>Status</th></tr></thead>';
						echo '<tbody>';
						while ($s = mysqli_fetch_assoc($stokRendahList)) {
							$status = $s['jumlah_stok'] == 0 ? '<span class="badge badge-danger">HABIS</span>' : '<span class="badge badge-warning">RENDAH</span>';
							echo '<tr>';
							echo '<td><strong>' . htmlspecialchars($s['nama_bahan']) . '</strong></td>';
							echo '<td>' . (float)$s['jumlah_stok'] . '</td>';
							echo '<td>' . htmlspecialchars($s['nama_satuan'] ?? '-') . '</td>';
							echo '<td>' . number_format($s['batas_minimum'], 2, ',', '.') . '</td>';
							echo '<td>' . htmlspecialchars($s['Nama_Produsen'] ?? '-') . '</td>';
							echo '<td>' . $status . '</td>';
							echo '</tr>';
						}
						echo '</tbody></table>';
					} else {
						echo '<p><span class="badge badge-success">✓ Semua stok aman</span></p>';
					}
					?>
				</div>

				<!-- Penjualan per Ukuran -->
				<div class="card">
					<h3 class="section-title">📏 Penjualan per Ukuran (Bulan Ini)</h3>
					<?php
					$perUkuran = mysqli_query($conn,
						"SELECT u.nama_ukuran,
						        SUM(dt.jumlah) AS total_terjual,
						        SUM(dt.jumlah * dt.harga_satuan) AS total_pendapatan
						 FROM detail_transaksi dt
						 LEFT JOIN varian_menu vm ON dt.VarianMenuNo = vm.VarianMenuNo
						 LEFT JOIN ukuran u        ON vm.ukuranNo    = u.UkuranNo
						 LEFT JOIN transaksi t     ON dt.transaksiNo  = t.transaksiNo
						 WHERE YEAR(t.tanggal) = YEAR(CURDATE())
						   AND MONTH(t.tanggal) = MONTH(CURDATE())
						 GROUP BY u.UkuranNo
						 ORDER BY total_terjual DESC");

					if ($perUkuran && mysqli_num_rows($perUkuran) > 0) {
						// Hitung total semua ukuran untuk persentase
						$totalSemua = 0;
						$rows = [];
						while ($r = mysqli_fetch_assoc($perUkuran)) { $rows[] = $r; $totalSemua += $r['total_terjual']; }

						echo '<table class="dashboard-table">';
						echo '<thead><tr>
							<th>Ukuran</th>
							<th style="text-align:right">Total Terjual</th>
							<th style="text-align:right">Pendapatan</th>
							<th>Proporsi</th>
						</tr></thead><tbody>';
						foreach ($rows as $r) {
							$pct    = $totalSemua > 0 ? round(($r['total_terjual'] / $totalSemua) * 100) : 0;
							$bar    = '<div style="background:#e5e7eb;border-radius:4px;height:10px;min-width:80px"><div style="background:#4f46e5;border-radius:4px;height:10px;width:' . $pct . '%"></div></div>';
							echo '<tr>';
							echo '<td><strong>' . htmlspecialchars($r['nama_ukuran'] ?? '-') . '</strong></td>';
							echo '<td style="text-align:right">' . (int)$r['total_terjual'] . ' item</td>';
							echo '<td style="text-align:right">' . rupiah($r['total_pendapatan']) . '</td>';
							echo '<td>' . $bar . ' <small style="color:#6b7280">' . $pct . '%</small></td>';
							echo '</tr>';
						}
						echo '</tbody></table>';
					} else {
						echo '<p style="color:#6b7280">Belum ada data penjualan bulan ini.</p>';
					}
					?>
				</div>

				<!-- Metode Pembayaran -->
				<div class="card">
					<h3 class="section-title">💳 Breakdown Metode Pembayaran (Hari Ini)</h3>
					<?php
					$metodePembayaran = mysqli_query($conn,
						"SELECT m.nama_metode, COUNT(t.transaksiNo) AS jumlah,
						        SUM(t.total_harga) AS total_harga
						 FROM transaksi t
						 LEFT JOIN metode_pembayaran m ON t.metode_pembayaranNo = m.metode_pembayaranNo
						 WHERE DATE(t.tanggal) = CURDATE()
						 GROUP BY m.metode_pembayaranNo
						 ORDER BY total_harga DESC");
					
					if (mysqli_num_rows($metodePembayaran) > 0) {
						echo '<table class="dashboard-table">';
						echo '<thead><tr><th>Metode Pembayaran</th><th style="text-align:right">Jumlah</th><th style="text-align:right">Total</th></tr></thead>';
						echo '<tbody>';
						while ($mp = mysqli_fetch_assoc($metodePembayaran)) {
							echo '<tr>';
							echo '<td><strong>' . htmlspecialchars($mp['nama_metode']) . '</strong></td>';
							echo '<td style="text-align:right">' . (int)$mp['jumlah'] . ' transaksi</td>';
							echo '<td style="text-align:right">' . rupiah($mp['total_harga']) . '</td>';
							echo '</tr>';
						}
						echo '</tbody></table>';
					} else {
						echo '<p>Belum ada transaksi hari ini.</p>';
					}
					?>
				</div>
			</div>
		</main>
	</div>
	<!-- PWA & Service Worker -->
	<script src="../assets/js/pwa.js"></script>
</body>
</html>
