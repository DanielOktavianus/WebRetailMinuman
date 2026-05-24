<?php
require_once __DIR__ . '/../../config/database.php';

$errorMessage = '';
$satuan = null;
$SatuanNo = isset($_GET['SatuanNo']) ? (int) $_GET['SatuanNo'] : 0;

// Ambil data satuan
if ($SatuanNo > 0) {
    $stmt = mysqli_prepare($conn, "SELECT SatuanNo, nama_satuan FROM satuan WHERE SatuanNo = ? LIMIT 1");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $SatuanNo);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $satuan = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);
        
        if (!$satuan) {
            $errorMessage = 'Satuan tidak ditemukan.';
        }
    }
} else {
    $errorMessage = 'ID satuan tidak valid.';
}
?>
<!doctype html>
<html lang="id">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width,initial-scale=1" />
	<title>Satuan — Edit</title>
	<link rel="stylesheet" href="../../assets/css/main.css">
	<link rel="stylesheet" href="../../assets/css/components.css">
	<link rel="stylesheet" href="../../assets/css/modules.css">
</head>
<body>
	<div class="app-layout">
		<?php include __DIR__ . '/../../components/sidebar.php'; ?>
		<main class="main-content module-satuan">
			<div class="container">
				<div class="card">
					<h2>Edit Satuan</h2>
					<?php if ($errorMessage): ?>
						<div class="alert error"><?php echo htmlspecialchars($errorMessage); ?></div>
					<?php endif; ?>
					
					<?php if ($satuan): ?>
						<form method="post" action="satuan_update.php">
							<input type="hidden" name="SatuanNo" value="<?php echo htmlspecialchars($satuan['SatuanNo']); ?>">
							
							<div class="mt-12">
								<label for="nama_satuan">Nama Satuan</label>
								<input id="nama_satuan" name="nama_satuan" class="form-input" type="text" required placeholder="Contoh: Gram, Mili Liter, Butir, Siung, Kg" value="<?php echo htmlspecialchars($satuan['nama_satuan']); ?>">
							</div>

							<div class="form-actions">
								<button class="btn" type="submit">Update</button>
								<a class="btn" href="satuan_tambah.php" style="background:#6b7280;color:white;padding:10px 16px;text-decoration:none;border-radius:4px;display:inline-block">Batal</a>
							</div>
						</form>
					<?php endif; ?>
				</div>
			</div>
		</main>
	</div>
</body>
</html>
