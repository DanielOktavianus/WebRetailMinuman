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
</head>
<body>
	<div class="app-layout">
		<?php include __DIR__ . '/../../components/sidebar.php'; ?>
		<main class="main-content module-transaksi">
			<div class="container">
				<div class="card">
					<h2>Rekapan Detail Transaksi</h2>
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

					$whereClause = '';
					$filterLabel = '';
					switch ($filter) {
						case 'minggu':
							$whereClause = "WHERE DATE(t.tanggal) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
							$filterLabel = '7 Hari Terakhir';
							break;
						case 'bulan':
							$whereClause = "WHERE MONTH(t.tanggal) = MONTH(CURDATE()) AND YEAR(t.tanggal) = YEAR(CURDATE())";
							$filterLabel = 'Bulan Ini';
							break;
						case '3bulan':
							$whereClause = "WHERE DATE(t.tanggal) >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)";
							$filterLabel = '3 Bulan Terakhir';
							break;
						case 'tahun':
							$whereClause = "WHERE YEAR(t.tanggal) = YEAR(CURDATE())";
							$filterLabel = 'Tahun Ini';
							break;
						case 'custom':
							if ($dari !== '' && $sampai !== '') {
								$dari_safe   = mysqli_real_escape_string($conn, $dari);
								$sampai_safe = mysqli_real_escape_string($conn, $sampai);
								$whereClause = "WHERE DATE(t.tanggal) BETWEEN '{$dari_safe}' AND '{$sampai_safe}'";
								$filterLabel = $dari . ' s/d ' . $sampai;
							}
							break;
						default:
							$filter = 'semua';
							$filterLabel = 'Semua';
					}

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
					echo '<div style="margin-bottom:14px;display:flex;flex-wrap:wrap;align-items:center;gap:4px">';
					foreach ($filters as $key => $label) {
						$style = ($filter === $key) ? $btnActive : $btnInact;
						echo "<a href=\"?filter={$key}\" style=\"{$style}\">{$label}</a>";
					}
					echo '</div>';

					// Form custom range
					$customOpen = ($filter === 'custom') ? '' : 'style="display:none"';
					echo '<form method="get" action="" id="customForm" ' . $customOpen . ' style="background:#f9fafb;padding:12px;border-radius:8px;margin-bottom:14px;display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end">';
					echo '<input type="hidden" name="filter" value="custom">';
					echo '<div><label style="font-size:13px;font-weight:600;display:block;margin-bottom:4px">Dari Tanggal</label>';
					echo '<input type="date" name="dari" value="' . htmlspecialchars($dari) . '" style="padding:7px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:13px"></div>';
					echo '<div><label style="font-size:13px;font-weight:600;display:block;margin-bottom:4px">Sampai Tanggal</label>';
					echo '<input type="date" name="sampai" value="' . htmlspecialchars($sampai) . '" style="padding:7px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:13px"></div>';
					echo '<button type="submit" style="' . $btnActive . 'margin:0">Terapkan</button>';
					echo '</form>';

					// Tombol Pilih Rentang Kustom (toggle form)
					$toggleStyle = ($filter === 'custom') ? $btnActive : $btnInact;
					echo '<div style="margin-bottom:14px">';
					echo '<button onclick="toggleCustomForm()" style="' . $toggleStyle . '">📅 Rentang Kustom</button>';
					if ($filterLabel && $filter !== 'semua') {
						echo '<span style="font-size:13px;color:#6b7280;margin-left:8px">Menampilkan: <strong>' . htmlspecialchars($filterLabel) . '</strong></span>';
					}
					echo '</div>';
					echo '<script>function toggleCustomForm(){var f=document.getElementById("customForm");f.style.display=(f.style.display==="none"||f.style.display===""?"flex":"none");}</script>';

					// Query dengan filter
					$query = "SELECT t.transaksiNo, t.tanggal, t.total_harga,
						         COALESCE(k.nama, '-') AS karyawan,
						         COALESCE(m.nama_metode, '-') AS metode,
						         COALESCE(v.nama_voucher, '-') AS voucher
						  FROM transaksi t
						  LEFT JOIN karyawan k ON t.karyawanNo = k.karyawanNo
						  LEFT JOIN metode_pembayaran m ON t.metode_pembayaranNo = m.metode_pembayaranNo
						  LEFT JOIN voucher v ON t.voucherNo = v.voucherNo
						  {$whereClause}
						  ORDER BY t.transaksiNo DESC";

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
					echo '<div style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:6px;padding:10px 14px;margin-bottom:14px;font-size:13px;color:#0369a1">';
					echo '📊 Menampilkan <strong>' . $totalRows . ' transaksi</strong>';
					echo ' &nbsp;|&nbsp; Periode: <strong>' . htmlspecialchars($rangeInfo) . '</strong>';
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
						echo '<th style="text-align:center;padding:10px;border-bottom:1px solid #ccc">Aksi</th>';
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
							echo "<td style=\"padding:10px;vertical-align:top;text-align:center\">";
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
