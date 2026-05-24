<?php
require_once __DIR__ . '/../../config/database.php';

$ResepNo = isset($_GET['ResepNo']) && is_numeric($_GET['ResepNo']) ? (int) $_GET['ResepNo'] : 0;

if ($ResepNo <= 0) { header('Location: resep_tambah.php'); exit; }

$stmt = mysqli_prepare($conn, "SELECT ResepNo, MenuNo, Keterangan FROM resep WHERE ResepNo = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $ResepNo);
mysqli_stmt_execute($stmt);
$resep = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$resep) { die('Resep tidak ditemukan. <a href="resep_tambah.php">Kembali</a>'); }

$menuResult = mysqli_query($conn, "SELECT MenuNo, nama_menu FROM menu ORDER BY nama_menu ASC");
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Resep — Edit</title>
    <link rel="stylesheet" href="../../assets/css/main.css">
    <link rel="stylesheet" href="../../assets/css/components.css">
    <link rel="stylesheet" href="../../assets/css/modules.css">
</head>
<body>
    <div class="app-layout">
        <?php include __DIR__ . '/../../components/sidebar.php'; ?>
        <main class="main-content module-resep">
            <div class="container">
                <div class="card">
                    <h2>Edit Resep</h2>
                    <form method="post" action="resep_update.php">
                        <input type="hidden" name="ResepNo" value="<?php echo $resep['ResepNo']; ?>">

                        <div>
                            <label for="MenuNo">Menu</label>
                            <select id="MenuNo" name="MenuNo" class="form-input" required>
                                <option value="">-- Pilih Menu --</option>
                                <?php if ($menuResult): while ($row = mysqli_fetch_assoc($menuResult)): ?>
                                    <option value="<?php echo $row['MenuNo']; ?>"
                                        <?php echo ($row['MenuNo'] == $resep['MenuNo']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($row['nama_menu']); ?>
                                    </option>
                                <?php endwhile; endif; ?>
                            </select>
                        </div>

                        <div class="mt-12">
                            <label for="keterangan">Keterangan</label>
                            <textarea id="keterangan" name="keterangan" class="form-input" rows="3" required><?php
                                echo htmlspecialchars($resep['Keterangan']);
                            ?></textarea>
                        </div>

                        <div class="form-actions">
                            <button class="btn" type="submit">Update</button>
                            <a class="btn" href="resep_tambah.php" style="background:#6b7280">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
