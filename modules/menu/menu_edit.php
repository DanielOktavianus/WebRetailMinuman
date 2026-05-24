<?php
require_once __DIR__ . '/../../config/database.php';

$errorMessage = '';
$menu = null;
$MenuNo = isset($_GET['MenuNo']) ? (int) $_GET['MenuNo'] : 0;

// Ambil data menu
if ($MenuNo > 0) {
    $stmt = mysqli_prepare($conn, "SELECT MenuNo, nama_menu FROM menu WHERE MenuNo = ? LIMIT 1");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $MenuNo);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $menu = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);
        
        if (!$menu) {
            $errorMessage = 'Menu tidak ditemukan.';
        }
    }
} else {
    $errorMessage = 'ID menu tidak valid.';
}
?>
<!doctype html>
<html lang="id">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width,initial-scale=1" />
	<title>Menu — Edit</title>
	<link rel="stylesheet" href="../../assets/css/main.css">
	<link rel="stylesheet" href="../../assets/css/components.css">
	<link rel="stylesheet" href="../../assets/css/modules.css">
</head>
<body>
	<div class="app-layout">
		<?php include __DIR__ . '/../../components/sidebar.php'; ?>
		<main class="main-content module-menu">
			<div class="container">
				<div class="card">
					<h2>Edit Menu</h2>
					<?php if ($errorMessage): ?>
						<div class="alert error"><?php echo htmlspecialchars($errorMessage); ?></div>
					<?php endif; ?>
					
					<?php if ($menu): ?>
						<form method="post" action="menu_update.php">
							<input type="hidden" name="MenuNo" value="<?php echo htmlspecialchars($menu['MenuNo']); ?>">
							
							<div class="mt-12">
								<label for="nama_menu">Nama Menu</label>
								<input id="nama_menu" name="nama_menu" class="form-input" type="text" required placeholder="Contoh: Nasi Goreng, Mie Goreng, dll" value="<?php echo htmlspecialchars($menu['nama_menu']); ?>">
							</div>

							<div class="form-actions">
								<button class="btn" type="submit">Update</button>
								<a class="btn" href="menu_tambah.php" style="background:#6b7280;color:white;padding:10px 16px;text-decoration:none;border-radius:4px;display:inline-block">Batal</a>
							</div>
						</form>
					<?php endif; ?>
				</div>
			</div>
		</main>
	</div>
</body>
</html>
