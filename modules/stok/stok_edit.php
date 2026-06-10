<?php
require_once __DIR__ . '/../../config/database.php';

$errorMessage = '';
$stok = null;
$StokNo = isset($_GET['StokNo']) ? (int) $_GET['StokNo'] : 0;

// Ambil data stok
if ($StokNo > 0) {
    $stmt = mysqli_prepare($conn, "SELECT StokNo, ProdusenNo, BahanNo, SatuanNo, jumlah_stok, batas_minimum FROM stok WHERE StokNo = ? LIMIT 1");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $StokNo);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $stok = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);
        
        if (!$stok) {
            $errorMessage = 'Stok tidak ditemukan.';
        }
    }
} else {
    $errorMessage = 'ID stok tidak valid.';
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
	<title>Stok — Edit</title>
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
					<h2>Edit Stok</h2>
					<?php if ($errorMessage): ?>
						<div class="alert error"><?php echo htmlspecialchars($errorMessage); ?></div>
					<?php endif; ?>
					
					<?php if ($stok): ?>
						<form method="post" action="stok_update.php">
							<input type="hidden" name="StokNo" value="<?php echo htmlspecialchars($stok['StokNo']); ?>">
							
							<div class="row">
								<div>
									<label for="ProdusenNo">Produsen</label>
									<select id="ProdusenNo" name="ProdusenNo" class="form-input">
										<option value="">-- Pilih Produsen (Optional) --</option>
										<?php if ($produsenResult): while ($row = mysqli_fetch_assoc($produsenResult)): ?>
											<option value="<?php echo $row['ProdusenNo']; ?>" <?php echo ($stok['ProdusenNo'] == $row['ProdusenNo']) ? 'selected' : ''; ?>>
												<?php echo htmlspecialchars($row['Nama_Produsen']); ?>
											</option>
										<?php endwhile; endif; ?>
									</select>
								</div>
								<div>
									<label for="BahanNo">Bahan</label>
									<select id="BahanNo" name="BahanNo" class="form-input" required>
										<option value="">-- Pilih Bahan --</option>
										<?php 
										mysqli_data_seek($bahanResult, 0);
										while ($row = mysqli_fetch_assoc($bahanResult)): 
										?>
											<option value="<?php echo $row['BahanNo']; ?>" <?php echo ($stok['BahanNo'] == $row['BahanNo']) ? 'selected' : ''; ?>>
												<?php echo htmlspecialchars($row['nama_bahan']); ?>
											</option>
										<?php endwhile; ?>
									</select>
								</div>
							</div>

							<div class="row mt-12">
								<div>
									<label for="SatuanNo">Satuan</label>
									<select id="SatuanNo" name="SatuanNo" class="form-input" required>
										<option value="">-- Pilih Satuan --</option>
										<?php if ($satuanResult): while ($row = mysqli_fetch_assoc($satuanResult)): ?>
											<option value="<?php echo $row['SatuanNo']; ?>" <?php echo ($stok['SatuanNo'] == $row['SatuanNo']) ? 'selected' : ''; ?>>
												<?php echo htmlspecialchars($row['nama_satuan']); ?>
											</option>
										<?php endwhile; endif; ?>
									</select>
								</div>
								<div>
									<label for="jumlah_stok">Jumlah Stok</label>
									<input id="jumlah_stok" name="jumlah_stok" class="form-input" type="number" required placeholder="Contoh: 100, 500, 1000" value="<?php echo htmlspecialchars($stok['jumlah_stok']); ?>">
								</div>
							</div>

							<div class="row mt-12">
								<div>
									<label for="batas_minimum">Batas Minimum</label>
									<input id="batas_minimum" name="batas_minimum" class="form-input" type="number" step="0.01" required placeholder="Contoh: 5, 10, 100" value="<?php echo htmlspecialchars($stok['batas_minimum']); ?>">
								</div>
							</div>

							<div class="form-actions">
								<button class="btn" type="submit">Update</button>
								<a class="btn" href="stok_tambah.php" style="background:#6b7280;color:white;padding:10px 16px;text-decoration:none;border-radius:4px;display:inline-block">Batal</a>
							</div>
						</form>
					<?php endif; ?>
				</div>
			</div>
		</main>
	</div>
</body>
</html>
