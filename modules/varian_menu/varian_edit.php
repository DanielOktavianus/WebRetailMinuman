<?php
require_once __DIR__ . '/../../config/database.php';

$VarianMenuNo = isset($_GET['VarianMenuNo']) && is_numeric($_GET['VarianMenuNo']) ? (int) $_GET['VarianMenuNo'] : 0;

if ($VarianMenuNo <= 0) {
    header('Location: varian_tambah.php');
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT VarianMenuNo, MenuNo, ukuranNo, Harga FROM varian_menu WHERE VarianMenuNo = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $VarianMenuNo);
mysqli_stmt_execute($stmt);
$res  = mysqli_stmt_get_result($stmt);
$row  = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$row) {
    die('Data tidak ditemukan. <a href="varian_tambah.php">Kembali</a>');
}

$menuResult   = mysqli_query($conn, "SELECT MenuNo, nama_menu FROM menu ORDER BY nama_menu ASC");
$ukuranResult = mysqli_query($conn, "SELECT UkuranNo, nama_ukuran FROM ukuran ORDER BY UkuranNo ASC");
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Varian Menu — Edit</title>
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
                    <h2>Edit Varian Menu</h2>
                    <form method="post" action="varian_update.php">
                        <input type="hidden" name="VarianMenuNo" value="<?php echo $row['VarianMenuNo']; ?>">

                        <div class="row">
                            <div>
                                <label>VarianMenuNo</label>
                                <input class="form-input" type="text" value="<?php echo $row['VarianMenuNo']; ?>" readonly>
                            </div>
                            <div>
                                <label for="MenuNo">Menu</label>
                                <select id="MenuNo" name="MenuNo" class="form-input" required>
                                    <option value="">-- Pilih Menu --</option>
                                    <?php if ($menuResult): while ($m = mysqli_fetch_assoc($menuResult)): ?>
                                        <option value="<?php echo $m['MenuNo']; ?>"
                                            <?php echo ($m['MenuNo'] == $row['MenuNo']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($m['nama_menu']); ?>
                                        </option>
                                    <?php endwhile; endif; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row mt-12">
                            <div>
                                <label for="ukuranNo">Ukuran</label>
                                <select id="ukuranNo" name="ukuranNo" class="form-input" required>
                                    <option value="">-- Pilih Ukuran --</option>
                                    <?php if ($ukuranResult): while ($u = mysqli_fetch_assoc($ukuranResult)): ?>
                                        <option value="<?php echo $u['UkuranNo']; ?>"
                                            <?php echo ($u['UkuranNo'] == $row['ukuranNo']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($u['nama_ukuran']); ?>
                                        </option>
                                    <?php endwhile; endif; ?>
                                </select>
                            </div>
                            <div>
                                <label for="Harga">Harga</label>
                                <input id="Harga" name="Harga" class="form-input" type="number"
                                    step="500" min="0" required value="<?php echo htmlspecialchars($row['Harga']); ?>">
                            </div>
                        </div>

                        <div class="form-actions">
                            <button class="btn" type="submit">Simpan Perubahan</button>
                            <a class="btn" href="varian_tambah.php" style="background:#6b7280">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
