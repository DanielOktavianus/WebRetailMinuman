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
	<title>Detail Transaksi — Rekapan</title>
	<link rel="stylesheet" href="../../assets/css/main.css">
	<link rel="stylesheet" href="../../assets/css/components.css">
	<link rel="stylesheet" href="../../assets/css/modules.css">
	<style>
		.print-only { display: none; }
		@media print {
			html, body { background: white !important; min-height: 0 !important; }
			.sidebar, .hamburger-btn, .sidebar-overlay, .no-print { display: none !important; width: 0 !important; }
			.app-layout { display: block !important; background: white !important; }
			.main-content { display: block !important; margin: 0 !important; padding: 8px !important; width: 100% !important; max-width: 100% !important; background: white !important; }
			.container { max-width: 100% !important; padding: 0 !important; }
			.card { box-shadow: none !important; border: none !important; padding: 4px 0 !important; }
			.print-only { display: block !important; }
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
					<!-- Header cetak — hanya muncul saat print -->
					<div class="print-only" style="text-align:center;padding:12px 0 16px;border-bottom:2px solid #e5e7eb;margin-bottom:16px">
						<strong style="display:block;font-size:20px;color:#1f2937">Monitoring Penjualan</strong>
						<span style="font-size:13px;color:#6b7280">Jl. Sagan No.3 Terban, Gondokusuman, Kota Yogyakarta</span>
						<p style="margin:6px 0 0;font-size:14px;font-weight:600">Rekapan Transaksi</p>
					</div>

					<h2 class="no-print">Rekapan Detail Transaksi</h2>
					<?php
					// Display success/error messages
					if (isset($_GET['success']) && $_GET['success'] === 'deleted') {
						echo '<div class="alert" style="background:#d1fae5;border:1px solid #6ee7b7;color:#065f46;padding:12px;border-radius:4px;margin-bottom:16px">';
						echo 'Transaksi berhasil dihapus.';
						echo '</div>';
					}
					if (isset($_GET['error'])) {
						$errorMsg = htmlspecialchars($_GET['error']);
						echo '<div class="alert" style="background:#fee2e2;border:1px solid #fca5a5;color:#7f1d1d;padding:12px;border-radius:4px;margin-bottom:16px">';
						echo "Error: {$errorMsg}";
						echo '</div>';
					}

					// --- Filter Logic ---
					$filter  = isset($_GET['filter']) ? $_GET['filter'] : 'semua';
					$dari    = isset($_GET['dari'])   && $_GET['dari']   !== '' ? $_GET['dari']   : '';
					$sampai  = isset($_GET['sampai']) && $_GET['sampai'] !== '' ? $_GET['sampai'] : '';
					$metodeFilter = isset($_GET['metode']) && $_GET['metode'] !== '' ? (int)$_GET['metode'] : '';
					$karyawanFilter = isset($_GET['karyawan']) ? trim($_GET['karyawan']) : '';
					$sortBy = isset($_GET['sort_by']) && in_array($_GET['sort_by'], ['tanggal','total','karyawan','metode'], true) ? $_GET['sort_by'] : 'tanggal';
					$sortDir = isset($_GET['sort_dir']) && in_array($_GET['sort_dir'], ['asc','desc'], true) ? $_GET['sort_dir'] : 'desc';

					$whereConditions = [];
					$filterLabel = '';
					switch ($filter) {
						case 'minggu':
							$whereConditions[] = "DATE(t.tanggal) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
							$filterLabel = '7 Hari Terakhir';
							break;
						case 'bulan':
							$whereConditions[] = "MONTH(t.tanggal) = MONTH(CURDATE()) AND YEAR(t.tanggal) = YEAR(CURDATE())";
							$filterLabel = 'Bulan Ini';
							break;
						case '3bulan':
							$whereConditions[] = "DATE(t.tanggal) >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)";
							$filterLabel = '3 Bulan Terakhir';
							break;
						case 'tahun':
							$whereConditions[] = "YEAR(t.tanggal) = YEAR(CURDATE())";
							$filterLabel = 'Tahun Ini';
							break;
						case 'custom':
							if ($dari !== '' && $sampai !== '') {
								$dari_safe   = mysqli_real_escape_string($conn, $dari);
								$sampai_safe = mysqli_real_escape_string($conn, $sampai);
								$whereConditions[] = "DATE(t.tanggal) BETWEEN '{$dari_safe}' AND '{$sampai_safe}'";
								$filterLabel = $dari . ' s/d ' . $sampai;
							}
							break;
						default:
							$filter = 'semua';
							$filterLabel = 'Semua';
					}

					if ($metodeFilter !== '') {
						$whereConditions[] = "t.metode_pembayaranNo = " . (int)$metodeFilter;
					}

					if ($karyawanFilter !== '') {
						$whereConditions[] = "t.karyawanNo = " . (int)$karyawanFilter;
					}

					$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

					// Tombol filter aktif
					$btnBase   = 'display:inline-block;padding:8px 14px;border-radius:6px;font-size:13px;font-weight:600;text-decoration:none;border:none;cursor:pointer;margin:0 4px 6px 0;';
					$btnActive = $btnBase . 'background:#4f46e5;color:#fff;';
					$btnInact  = $btnBase . 'background:#e5e7eb;color:#374151;';

					$filters = [
						'semua'  => 'Semua',
						'minggu' => '7 Hari Terakhir',
						'bulan'  => 'Bulan Ini',
						'3bulan' => '3 Bulan Terakhir',
						'tahun'  => 'Tahun Ini',
					];
					echo '<div class="no-print" style="margin-bottom:14px;display:flex;flex-wrap:wrap;align-items:center;gap:4px">';
					foreach ($filters as $key => $label) {
						$params = ['filter' => $key];
						if ($metodeFilter !== '') { $params['metode'] = $metodeFilter; }
						if ($karyawanFilter !== '') { $params['karyawan'] = $karyawanFilter; }
						if ($sortBy !== 'tanggal') { $params['sort_by'] = $sortBy; }
						if ($sortDir !== 'desc') { $params['sort_dir'] = $sortDir; }
						if ($dari !== '') { $params['dari'] = $dari; }
						if ($sampai !== '') { $params['sampai'] = $sampai; }
						$style = ($filter === $key) ? $btnActive : $btnInact;
						echo "<a href=\"?" . http_build_query($params) . "\" style=\"{$style}\">{$label}</a>";
					}
					echo '</div>';

					$metodeResult = mysqli_query($conn, "SELECT metode_pembayaranNo, nama_metode FROM metode_pembayaran ORDER BY nama_metode ASC");
					$karyawanListResult = mysqli_query($conn, "SELECT karyawanNo, nama FROM karyawan ORDER BY nama ASC");

					echo '<div class="no-print" style="margin-bottom:14px;display:flex;flex-wrap:wrap;align-items:center;gap:10px">';
					echo '<button type="button" onclick="toggleCustomRange()" style="' . $btnActive . '">📅 Rentang Kustom</button>';
					if ($filterLabel && $filter !== 'semua') {
						echo '<span style="font-size:13px;color:#6b7280">Menampilkan: <strong>' . htmlspecialchars($filterLabel) . '</strong></span>';
					}
					echo '</div>';

					echo '<form method="get" action="" id="filterForm" class="no-print" style="background:#f9fafb;padding:12px;border-radius:8px;margin-bottom:14px;display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end">';
					echo '<input type="hidden" name="filter" value="' . htmlspecialchars($filter) . '">';
					echo '<input type="hidden" name="sort_by" value="' . htmlspecialchars($sortBy) . '">';
					echo '<input type="hidden" name="sort_dir" value="' . htmlspecialchars($sortDir) . '">';

					echo '<div><label style="font-size:13px;font-weight:600;display:block;margin-bottom:4px">Metode Pembayaran</label><select name="metode" style="padding:7px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;min-width:180px"><option value="">Semua Metode</option>';
					if ($metodeResult) {
						while ($mRow = mysqli_fetch_assoc($metodeResult)) {
							$selected = ($metodeFilter !== '' && (int)$mRow['metode_pembayaranNo'] === (int)$metodeFilter) ? 'selected' : '';
							echo '<option value="' . (int)$mRow['metode_pembayaranNo'] . '" ' . $selected . '>' . htmlspecialchars($mRow['nama_metode']) . '</option>';
						}
					}
					echo '</select></div>';

					echo '<div><label style="font-size:13px;font-weight:600;display:block;margin-bottom:4px">Nama Karyawan</label><select name="karyawan" style="padding:7px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;min-width:180px"><option value="">Semua Karyawan</option>';
					if ($karyawanListResult) {
						while ($kRow = mysqli_fetch_assoc($karyawanListResult)) {
							$selected = ($karyawanFilter !== '' && $kRow['karyawanNo'] == $karyawanFilter) ? 'selected' : '';
							echo '<option value="' . (int)$kRow['karyawanNo'] . '" ' . $selected . '>' . htmlspecialchars($kRow['nama']) . '</option>';
						}
					}
					echo '</select></div>';

					echo '<div><label style="font-size:13px;font-weight:600;display:block;margin-bottom:4px">Urutkan</label><select name="sort_by" style="padding:7px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:13px"><option value="tanggal" ' . (($sortBy === 'tanggal') ? 'selected' : '') . '>Tanggal</option><option value="total" ' . (($sortBy === 'total') ? 'selected' : '') . '>Total</option><option value="karyawan" ' . (($sortBy === 'karyawan') ? 'selected' : '') . '>Karyawan</option><option value="metode" ' . (($sortBy === 'metode') ? 'selected' : '') . '>Metode</option></select></div>';
					echo '<div><label style="font-size:13px;font-weight:600;display:block;margin-bottom:4px">Arah</label><select name="sort_dir" style="padding:7px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:13px"><option value="desc" ' . (($sortDir === 'desc') ? 'selected' : '') . '>Terbaru/terbesar</option><option value="asc" ' . (($sortDir === 'asc') ? 'selected' : '') . '>Terlama/terkecil</option></select></div>';
					echo '<button type="submit" style="' . $btnActive . 'margin:0">Terapkan</button>';

					echo '<div id="customRangeInputs" style="display:' . ($filter === 'custom' ? 'flex' : 'none') . ';flex-wrap:wrap;gap:10px;width:100%;margin-top:10px">';
					echo '<div><label style="font-size:13px;font-weight:600;display:block;margin-bottom:4px">Dari Tanggal</label>';
					echo '<input type="date" name="dari" value="' . htmlspecialchars($dari) . '" style="padding:7px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:13px"></div>';
					echo '<div><label style="font-size:13px;font-weight:600;display:block;margin-bottom:4px">Sampai Tanggal</label>';
					echo '<input type="date" name="sampai" value="' . htmlspecialchars($sampai) . '" style="padding:7px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:13px"></div>';
					echo '</div>';

					echo '</form>';
					echo '<script>function toggleCustomRange(){var f=document.getElementById("customRangeInputs");f.style.display=(f.style.display==="none"||f.style.display===""?"flex":"none");}</script>';

					// Query dengan filter
					$sortColumn = 't.tanggal';
					switch ($sortBy) {
						case 'total': $sortColumn = 't.total_harga'; break;
						case 'karyawan': $sortColumn = 'k.nama'; break;
						case 'metode': $sortColumn = 'm.nama_metode'; break;
						default: $sortColumn = 't.tanggal'; break;
					}
					$orderDir = ($sortDir === 'asc') ? 'ASC' : 'DESC';
					$query = "SELECT t.transaksiNo, t.tanggal, t.total_harga,
							 COALESCE(k.nama, '-') AS karyawan,
							 COALESCE(m.nama_metode, '-') AS metode,
							 COALESCE(v.nama_voucher, '-') AS voucher
						  FROM transaksi t
						  LEFT JOIN karyawan k ON t.karyawanNo = k.karyawanNo
						  LEFT JOIN metode_pembayaran m ON t.metode_pembayaranNo = m.metode_pembayaranNo
						  LEFT JOIN voucher v ON t.voucherNo = v.voucherNo
						  {$whereClause}
						  ORDER BY {$sortColumn} {$orderDir}, t.transaksiNo DESC";
					$res = mysqli_query($conn, $query);
					$totalRows = $res ? mysqli_num_rows($res) : 0;

					// Info bar: jumlah hasil + range aktif
					$rangeInfo = '';
					switch ($filter) {
						case 'minggu':  $rangeInfo = date('d M Y', strtotime('-7 days')) . ' – ' . date('d M Y'); break;
						case 'bulan':   $rangeInfo = date('01 M Y') . ' – ' . date('d M Y'); break;
						case '3bulan':  $rangeInfo = date('d M Y', strtotime('-3 months')) . ' – ' . date('d M Y'); break;
						case 'tahun':   $rangeInfo = '01 Jan ' . date('Y') . ' – ' . date('d M Y'); break;
						case 'custom':  $rangeInfo = $dari . ' – ' . $sampai; break;
						default:        $rangeInfo = 'Semua data';
					}
					echo '<div style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:6px;padding:10px 14px;margin-bottom:14px;font-size:13px;color:#0369a1;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px">';
					echo '<span>📊 Menampilkan <strong>' . $totalRows . ' transaksi</strong> &nbsp;|&nbsp; Periode: <strong>' . htmlspecialchars($rangeInfo) . '</strong></span>';
					echo '<button class="no-print" onclick="window.print()" style="padding:8px 16px;background:#4f46e5;color:white;border:none;border-radius:6px;cursor:pointer;font-size:13px;font-weight:600">🖨️ Cetak / Simpan PDF</button>';
					echo '</div>';

					// Summary stats queries
					$statQ  = "SELECT COUNT(*) as cnt, COALESCE(SUM(total_harga),0) as total FROM transaksi t {$whereClause}";
					$statR  = mysqli_query($conn, $statQ);
					$statRow = $statR ? mysqli_fetch_assoc($statR) : ['cnt' => 0, 'total' => 0];
					$statPenjualan = (float)$statRow['total'];
					$statTransaksi = (int)$statRow['cnt'];
					$statRataRata  = $statTransaksi > 0 ? $statPenjualan / $statTransaksi : 0;
					$periodeLabel  = ($filterLabel && $filterLabel !== 'Semua') ? htmlspecialchars($filterLabel) : 'Semua periode';

					// Summary boxes (visible on screen & in print)
					$boxStyle   = 'border:1px solid #d1d5db;border-radius:6px;padding:12px 10px;text-align:center;background:#fff';
					$labelStyle = 'font-size:11px;color:#6b7280;margin-bottom:4px';
					$valueStyle = 'font-size:18px;font-weight:700;color:#111827;word-break:break-all';
					$subStyle   = 'font-size:11px;color:#9ca3af;margin-top:2px';

					echo '<div style="margin-bottom:14px">';
					echo '<div style="font-size:14px;font-weight:700;color:#111827;margin-bottom:8px">Ringkasan Transaksi</div>';
					echo '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:10px;margin-bottom:16px">';

					echo '<div style="' . $boxStyle . '">';
					echo '<div style="' . $labelStyle . '">Total Pendapatan</div>';
					echo '<div style="' . $valueStyle . '">Rp ' . number_format($statPenjualan, 0, ',', '.') . '</div>';
					echo '<div style="' . $subStyle . '">' . $periodeLabel . '</div>';
					echo '</div>';

					echo '<div style="' . $boxStyle . '">';
					echo '<div style="' . $labelStyle . '">Total Transaksi</div>';
					echo '<div style="' . $valueStyle . '">' . $statTransaksi . '</div>';
					echo '<div style="' . $subStyle . '">' . $periodeLabel . '</div>';
					echo '</div>';

					echo '<div style="' . $boxStyle . '">';
					echo '<div style="' . $labelStyle . '">Rata-rata per Transaksi</div>';
					echo '<div style="' . $valueStyle . '">Rp ' . number_format($statRataRata, 0, ',', '.') . '</div>';
					echo '<div style="' . $subStyle . '">Berdasarkan filter aktif</div>';
					echo '</div>';

					echo '<div style="' . $boxStyle . '">';
					echo '<div style="' . $labelStyle . '">Periode Aktif</div>';
					echo '<div style="' . $valueStyle . '">' . htmlspecialchars($filterLabel ?: 'Semua') . '</div>';
					echo '<div style="' . $subStyle . '">' . htmlspecialchars($rangeInfo) . '</div>';
					echo '</div>';

					echo '</div>';
					echo '</div>';

					if ($res && $totalRows > 0) {
						echo '<table style="width:100%;border-collapse:collapse">';
						echo '<thead><tr style="background:#f0f0f0">';
						echo '<th style="text-align:left;padding:10px;border-bottom:1px solid #ccc">#</th>';
						echo '<th style="text-align:left;padding:10px;border-bottom:1px solid #ccc">TransaksiNo</th>';
						echo '<th style="text-align:left;padding:10px;border-bottom:1px solid #ccc">Tanggal</th>';
						echo '<th style="text-align:left;padding:10px;border-bottom:1px solid #ccc">Karyawan</th>';
						echo '<th style="text-align:left;padding:10px;border-bottom:1px solid #ccc">Metode Pembayaran</th>';
						echo '<th style="text-align:left;padding:10px;border-bottom:1px solid #ccc">Voucher</th>';
						echo '<th style="text-align:right;padding:10px;border-bottom:1px solid #ccc">Total</th>';
						echo '<th class="no-print" style="text-align:center;padding:10px;border-bottom:1px solid #ccc">Aksi</th>';
						echo '</tr></thead>';
						echo '<tbody>';
						
						$i = 1;
						while ($row = mysqli_fetch_assoc($res)) {
							$id = htmlspecialchars($row['transaksiNo']);
							$tgl = htmlspecialchars($row['tanggal'] ?? '-');
							$karyawan = htmlspecialchars($row['karyawan']);
							$metode = htmlspecialchars($row['metode']);
							$voucher = htmlspecialchars($row['voucher']);
							$total = rupiah($row['total_harga'] ?? 0);
							
							echo '<tr style="border-bottom:1px solid #eee">';
							echo "<td style=\"padding:10px;vertical-align:top\">" . ($i++) . "</td>";
							echo "<td style=\"padding:10px;vertical-align:top\">{$id}</td>";
							echo "<td style=\"padding:10px;vertical-align:top\">{$tgl}</td>";
							echo "<td style=\"padding:10px;vertical-align:top\">{$karyawan}</td>";
							echo "<td style=\"padding:10px;vertical-align:top\">{$metode}</td>";
							echo "<td style=\"padding:10px;vertical-align:top\">{$voucher}</td>";
							echo "<td style=\"padding:10px;vertical-align:top;text-align:right\">{$total}</td>";
							echo "<td class=\"no-print\" style=\"padding:10px;vertical-align:top;text-align:center\">";
							echo "<a class=\"btn\" href=\"transaksi_detail.php?transaksiNo={$id}\" style=\"padding:6px 10px;font-size:13px\">Detail</a> ";
							echo "<a class=\"btn\" href=\"transaksi_delete.php?transaksiNo={$id}\" onclick=\"return confirm('Hapus transaksi ini? Semua item dalam transaksi akan dihapus juga.')\" style=\"padding:6px 10px;font-size:13px;background:#ef4444\">Hapus</a>";
							echo "</td>";
							echo '</tr>';
						}
						echo '</tbody></table>';
					} else {
						echo '<p>Tidak ada transaksi.</p>';
					}
					?>
				</div>
			</div>
		</main>
	</div>
</body>
</html>
