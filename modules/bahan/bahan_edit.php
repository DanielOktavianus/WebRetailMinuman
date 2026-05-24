<?php
require_once __DIR__ . '/../../config/database.php';

$errorMessage = '';
$bahan = null;
$BahanNo = isset($_GET['BahanNo']) ? (int) $_GET['BahanNo'] : 0;

// Ambil data bahan
if ($BahanNo > 0) {
    $stmt = mysqli_prepare($conn, "SELECT BahanNo, nama_bahan FROM bahan WHERE BahanNo = ? LIMIT 1");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $BahanNo);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $bahan = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);
        
        if (!$bahan) {
            $errorMessage = 'Bahan tidak ditemukan.';
        }
    }
} else {
    $errorMessage = 'ID bahan tidak valid.';
}
?>
<!doctype html>
<html lang="id">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width,initial-scale=1" />
	<title>Bahan — Edit</title>
	<link rel="stylesheet" href="../../assets/css/main.css">
	<link rel="stylesheet" href="../../assets/css/components.css">
	<link rel="stylesheet" href="../../assets/css/modules.css">
</head>
<body>
	<div class="app-layout">
		<?php include __DIR__ . '/../../components/sidebar.php'; ?>
		<main class="main-content module-bahan">
			<div class="container">
				<div class="card">
					<h2>Edit Bahan</h2>
					<?php if ($errorMessage): ?>
						<div class="alert error"><?php echo htmlspecialchars($errorMessage); ?></div>
					<?php endif; ?>
					
					<?php if ($bahan): ?>
						<form method="post" action="bahan_update.php">
							<input type="hidden" name="BahanNo" value="<?php echo htmlspecialchars($bahan['BahanNo']); ?>">
							
							<div class="mt-12">
								<label for="nama_bahan">Nama Bahan</label>
								<input id="nama_bahan" name="nama_bahan" class="form-input" type="text" required placeholder="Contoh: Beras, Telur, Bawang Merah, Minyak" value="<?php echo htmlspecialchars($bahan['nama_bahan']); ?>">
							</div>

							<div class="form-actions">
								<button class="btn" type="submit">Update</button>
								<a class="btn" href="bahan_tambah.php" style="background:#6b7280;color:white;padding:10px 16px;text-decoration:none;border-radius:4px;display:inline-block">Batal</a>
							</div>
						</form>
					<?php endif; ?>
				</div>
			</div>
		</main>
	</div>
</body>
</html>
