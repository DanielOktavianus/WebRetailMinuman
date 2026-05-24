<?php
require_once __DIR__ . '/../../config/database.php';

$UkuranNo = isset($_GET['UkuranNo']) && is_numeric($_GET['UkuranNo']) ? (int) $_GET['UkuranNo'] : 0;
$ukuran   = null;
$errorMessage = '';

if ($UkuranNo <= 0) {
    header('Location: ukuran_tambah.php');
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT UkuranNo, nama_ukuran FROM ukuran WHERE UkuranNo = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $UkuranNo);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$ukuran = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$ukuran) {
    $errorMessage = 'Data ukuran tidak ditemukan.';
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Ukuran — Edit</title>
    <link rel="stylesheet" href="../../assets/css/main.css">
    <link rel="stylesheet" href="../../assets/css/components.css">
    <link rel="stylesheet" href="../../assets/css/modules.css">
</head>
<body>
    <div class="app-layout">
        <?php include __DIR__ . '/../../components/sidebar.php'; ?>
        <main class="main-content">
            <div class="container">
                <div class="card">
                    <h2>Edit Ukuran</h2>
                    <?php if ($errorMessage): ?>
                        <div class="alert error"><?php echo htmlspecialchars($errorMessage); ?></div>
                    <?php elseif ($ukuran): ?>
                        <form method="post" action="ukuran_update.php">
                            <input type="hidden" name="UkuranNo" value="<?php echo $ukuran['UkuranNo']; ?>">

                            <div class="mt-12">
                                <label for="nama_ukuran">Nama Ukuran</label>
                                <input id="nama_ukuran" name="nama_ukuran" class="form-input" type="text"
                                    required value="<?php echo htmlspecialchars($ukuran['nama_ukuran']); ?>">
                            </div>

                            <div class="form-actions">
                                <button class="btn" type="submit">Simpan Perubahan</button>
                                <a class="btn" href="ukuran_tambah.php" style="background:#6b7280">Batal</a>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
