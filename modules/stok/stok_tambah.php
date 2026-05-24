<?php
require_once __DIR__ . '/../../config/database.php';

// Handle form submit for insert
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ProdusenNo = isset($_POST['ProdusenNo']) && is_numeric($_POST['ProdusenNo']) ? (int) $_POST['ProdusenNo'] : null;
    $BahanNo = isset($_POST['BahanNo']) && is_numeric($_POST['BahanNo']) ? (int) $_POST['BahanNo'] : null;
    $SatuanNo = isset($_POST['SatuanNo']) && is_numeric($_POST['SatuanNo']) ? (int) $_POST['SatuanNo'] : null;
    $jumlah_stok = isset($_POST['jumlah_stok']) && is_numeric($_POST['jumlah_stok']) ? (int) $_POST['jumlah_stok'] : null;
    $batas_minimum = isset($_POST['batas_minimum']) && is_numeric($_POST['batas_minimum']) ? (float) $_POST['batas_minimum'] : null;
    
    if ($BahanNo === null || $SatuanNo === null || $jumlah_stok === null || $batas_minimum === null) {
        $errorMessage = 'Bahan, Satuan, Jumlah Stok, dan Batas Minimum harus diisi.';
    } else {
        // Cek apakah bahan ini sudah ada di stok
        $cekStmt = mysqli_prepare($conn, "SELECT s.StokNo, b.nama_bahan FROM stok s LEFT JOIN bahan b ON s.BahanNo = b.BahanNo WHERE s.BahanNo = ? LIMIT 1");
        mysqli_stmt_bind_param($cekStmt, 'i', $BahanNo);
        mysqli_stmt_execute($cekStmt);
        $cekRow = mysqli_fetch_assoc(mysqli_stmt_get_result($cekStmt));
        mysqli_stmt_close($cekStmt);

        if ($cekRow) {
            $namaBahan = htmlspecialchars($cekRow['nama_bahan'] ?? 'Bahan ini');
            $stokNo    = (int) $cekRow['StokNo'];
            $errorMessage = "{$namaBahan} sudah ada di stok. Gunakan tombol <a href=\"stok_restock.php?StokNo={$stokNo}\" style=\"color:#991b1b;font-weight:700;text-decoration:underline\">+ Restock</a> untuk menambah jumlahnya.";
        } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO stok (ProdusenNo, BahanNo, SatuanNo, jumlah_stok, batas_minimum) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            $errorMessage = 'Prepare gagal: ' . mysqli_error($conn);
        } else {
            mysqli_stmt_bind_param($stmt, 'iiiid', $ProdusenNo, $BahanNo, $SatuanNo, $jumlah_stok, $batas_minimum);
            if (mysqli_stmt_execute($stmt)) {
                $successMessage = 'Stok berhasil ditambahkan.';
                $_POST = [];
            } else {
                $errorMessage = 'Gagal menyimpan: ' . mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt);
        }
        } // end else (bahan belum ada)
    }
}

// Ambil daftar produsen untuk dropdown
$produsenResult = mysqli_query($conn, "SELECT ProdusenNo, Nama_Produsen FROM produsen ORDER BY Nama_Produsen ASC");

// Ambil daftar bahan untuk dropdown
$bahanResult = mysqli_query($conn, "SELECT BahanNo, nama_bahan FROM bahan ORDER BY nama_bahan ASC");

// Ambil daftar satuan untuk dropdown
$satuanResult = mysqli_query($conn, "SELECT SatuanNo, nama_satuan FROM satuan ORDER BY nama_satuan ASC");
?>
<!doctype html>
<html lang="id">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width,initial-scale=1" />
	<title>Stok — Tambah</title>
	<link rel="stylesheet" href="../../assets/css/main.css">
	<link rel="stylesheet" href="../../assets/css/components.css">
	<link rel="stylesheet" href="../../assets/css/modules.css">
</head>
<body>
	<div class="app-layout">
		<?php include __DIR__ . '/../../components/sidebar.php'; ?>
		<main class="main-content module-stok">
			<div class="container">
				<div class="card">
					<h2>Tambah Stok</h2>
					<?php if ($successMessage): ?>
						<div class="alert success"><?php echo htmlspecialchars($successMessage); ?></div>
					<?php endif; ?>
					<?php if ($errorMessage): ?>
						<div class="alert error"><?php echo $errorMessage; ?></div>
					<?php endif; ?>
					
					<form method="post" action="">
						<div class="row">
							<div>
								<label for="ProdusenNo">Produsen</label>
								<select id="ProdusenNo" name="ProdusenNo" class="form-input">
									<option value="">-- Pilih Produsen (Optional) --</option>
									<?php if ($produsenResult): while ($row = mysqli_fetch_assoc($produsenResult)): ?>
										<option value="<?php echo $row['ProdusenNo']; ?>" <?php echo (isset($_POST['ProdusenNo']) && $_POST['ProdusenNo'] == $row['ProdusenNo']) ? 'selected' : ''; ?>>
											<?php echo htmlspecialchars($row['Nama_Produsen']); ?>
										</option>
									<?php endwhile; endif; ?>
								</select>
							</div>
							<div>
								<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px">
									<label for="BahanNo" style="margin:0">Bahan</label>
									<a href="../bahan/bahan_tambah.php"
									   style="font-size:12px;color:#3b82f6;text-decoration:none;font-weight:600">
									   + Tambah Bahan Baru
									</a>
								</div>
								<select id="BahanNo" name="BahanNo" class="form-input" required>
									<option value="">-- Pilih Bahan --</option>
									<?php if ($bahanResult): while ($row = mysqli_fetch_assoc($bahanResult)): ?>
										<option value="<?php echo $row['BahanNo']; ?>" <?php echo (isset($_POST['BahanNo']) && $_POST['BahanNo'] == $row['BahanNo']) ? 'selected' : ''; ?>>
											<?php echo htmlspecialchars($row['nama_bahan']); ?>
										</option>
									<?php endwhile; endif; ?>
								</select>
							</div>
						</div>

						<div class="row mt-12">
							<div>
								<label for="SatuanNo">Satuan</label>
								<select id="SatuanNo" name="SatuanNo" class="form-input" required>
									<option value="">-- Pilih Satuan --</option>
									<?php if ($satuanResult): while ($row = mysqli_fetch_assoc($satuanResult)): ?>
										<option value="<?php echo $row['SatuanNo']; ?>" <?php echo (isset($_POST['SatuanNo']) && $_POST['SatuanNo'] == $row['SatuanNo']) ? 'selected' : ''; ?>>
											<?php echo htmlspecialchars($row['nama_satuan']); ?>
										</option>
									<?php endwhile; endif; ?>
								</select>
							</div>
							<div>
								<label for="jumlah_stok">Jumlah Stok</label>
								<input id="jumlah_stok" name="jumlah_stok" class="form-input" type="number" required placeholder="Contoh: 100, 500, 1000" value="<?php echo isset($_POST['jumlah_stok']) ? htmlspecialchars($_POST['jumlah_stok']) : ''; ?>">
							</div>
						</div>

						<div class="row mt-12">
							<div>
								<label for="batas_minimum">Batas Minimum</label>
								<input id="batas_minimum" name="batas_minimum" class="form-input" type="number" step="0.01" required placeholder="Contoh: 5, 10.5, 100" value="<?php echo isset($_POST['batas_minimum']) ? htmlspecialchars($_POST['batas_minimum']) : ''; ?>">
							</div>
						</div>

						<div class="form-actions">
							<button class="btn" type="submit">Simpan</button>
						</div>
					</form>
				</div>

				<?php if (isset($_GET['restock']) && $_GET['restock'] === 'ok'): ?>
					<div class="alert success" style="margin-top:12px">✅ Stok berhasil ditambahkan.</div>
				<?php endif; ?>

				<div class="card" style="margin-top:18px">
					<h2>Daftar Stok</h2>
					<?php
					$query = "SELECT s.StokNo, p.Nama_Produsen, b.nama_bahan, sat.nama_satuan,
					                 s.jumlah_stok, s.batas_minimum
					          FROM stok s
					          LEFT JOIN produsen p  ON s.ProdusenNo = p.ProdusenNo
					          LEFT JOIN bahan b     ON s.BahanNo   = b.BahanNo
					          LEFT JOIN satuan sat  ON s.SatuanNo  = sat.SatuanNo
					          ORDER BY s.jumlah_stok ASC, b.nama_bahan ASC";
					$res = mysqli_query($conn, $query);
					if ($res && mysqli_num_rows($res) > 0) {
						echo '<table style="width:100%;border-collapse:collapse">';
						echo '<thead><tr style="background:#f0f0f0">';
						foreach (['#','Bahan','Produsen','Satuan','Jumlah Stok','Batas Min','Status','Aksi'] as $th) {
							$align = in_array($th, ['Jumlah Stok','Batas Min','Status','Aksi']) ? 'right' : 'left';
							echo "<th style=\"text-align:{$align};padding:10px;border-bottom:1px solid #ccc\">{$th}</th>";
						}
						echo '</tr></thead><tbody>';

						$i = 1;
						while ($row = mysqli_fetch_assoc($res)) {
							$id       = (int)   $row['StokNo'];
							$jumlah   = (float) $row['jumlah_stok'];
							$batas    = (float) $row['batas_minimum'];
							$produsen = htmlspecialchars($row['Nama_Produsen'] ?? '-');
							$bahan    = htmlspecialchars($row['nama_bahan']    ?? '-');
							$satuan   = htmlspecialchars($row['nama_satuan']   ?? '-');

							// Tentukan status berdasarkan angka (bukan string)
							if ($jumlah <= 0) {
								$badge    = '<span style="display:inline-block;padding:3px 8px;border-radius:4px;font-size:12px;font-weight:600;background:#fee2e2;color:#991b1b">🔴 HABIS</span>';
								$rowStyle = 'background:#fff5f5';
							} elseif ($jumlah < $batas) {
								$badge    = '<span style="display:inline-block;padding:3px 8px;border-radius:4px;font-size:12px;font-weight:600;background:#fef3c7;color:#92400e">🟡 RENDAH</span>';
								$rowStyle = 'background:#fffbeb';
							} else {
								$badge    = '<span style="display:inline-block;padding:3px 8px;border-radius:4px;font-size:12px;font-weight:600;background:#d1fae5;color:#065f46">🟢 AMAN</span>';
								$rowStyle = '';
							}

							echo "<tr style=\"border-bottom:1px solid #eee;{$rowStyle}\">";
							echo "<td style=\"padding:10px\">" . ($i++) . "</td>";
							echo "<td style=\"padding:10px\"><strong>{$bahan}</strong></td>";
							echo "<td style=\"padding:10px\">{$produsen}</td>";
							echo "<td style=\"padding:10px\">{$satuan}</td>";
							echo "<td style=\"padding:10px;text-align:right\">" . ($jumlah + 0) . "</td>";
							echo "<td style=\"padding:10px;text-align:right\">" . ($batas + 0) . "</td>";
							echo "<td style=\"padding:10px;text-align:right\">{$badge}</td>";
							echo "<td style=\"padding:10px;text-align:right;white-space:nowrap\">";
							echo "<a class=\"btn\" href=\"stok_restock.php?StokNo={$id}\" style=\"padding:6px 10px;font-size:13px;background:#16a34a;color:white;text-decoration:none;border-radius:4px;margin-right:4px\">+ Restock</a>";
							echo "<a class=\"btn\" href=\"stok_edit.php?StokNo={$id}\" style=\"padding:6px 10px;font-size:13px;background:#f59e0b;color:white;text-decoration:none;border-radius:4px;margin-right:4px\">Edit</a>";
							echo "<a class=\"btn\" href=\"stok_delete.php?StokNo={$id}\" onclick=\"return confirm('Yakin hapus stok ini?')\" style=\"padding:6px 10px;font-size:13px;background:#ef4444;color:white;text-decoration:none;border-radius:4px\">Hapus</a>";
							echo "</td>";
							echo '</tr>';
						}
						echo '</tbody></table>';
					} else {
						echo '<p>Belum ada stok.</p>';
					}
					?>
				</div>
			</div>
		</main>
	</div>
</body>
</html>
