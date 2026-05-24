<?php
require_once __DIR__ . '/../../config/database.php';

$successMessage = '';
$errorMessage   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $MenuNo   = isset($_POST['MenuNo'])   && is_numeric($_POST['MenuNo'])   ? (int)   $_POST['MenuNo']   : null;
    $ukuranNo = isset($_POST['ukuranNo']) && is_numeric($_POST['ukuranNo']) ? (int)   $_POST['ukuranNo'] : null;
    $harga    = isset($_POST['Harga'])    && is_numeric($_POST['Harga'])    ? (float) $_POST['Harga']    : null;

    if ($MenuNo === null || $ukuranNo === null || $harga === null) {
        $errorMessage = 'Menu, Ukuran, dan Harga harus diisi.';
    } else {
        // Cek duplikat menu + ukuran
        $cek = mysqli_prepare($conn,
            "SELECT vm.VarianMenuNo, m.nama_menu, u.nama_ukuran
             FROM varian_menu vm
             LEFT JOIN menu m   ON vm.MenuNo   = m.MenuNo
             LEFT JOIN ukuran u ON vm.ukuranNo = u.UkuranNo
             WHERE vm.MenuNo = ? AND vm.ukuranNo = ? LIMIT 1");
        mysqli_stmt_bind_param($cek, 'ii', $MenuNo, $ukuranNo);
        mysqli_stmt_execute($cek);
        $cekRow = mysqli_fetch_assoc(mysqli_stmt_get_result($cek));
        mysqli_stmt_close($cek);

        if ($cekRow) {
            $namaMenu   = htmlspecialchars($cekRow['nama_menu']   ?? '');
            $namaUkuran = htmlspecialchars($cekRow['nama_ukuran'] ?? '');
            $vmNo       = (int) $cekRow['VarianMenuNo'];
            $errorMessage = "Varian <strong>{$namaMenu} — {$namaUkuran}</strong> sudah ada. "
                . "Gunakan <a href=\"varian_edit.php?VarianMenuNo={$vmNo}\" style=\"color:#991b1b;font-weight:700;text-decoration:underline\">Edit</a> untuk mengubah harganya.";
        } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO varian_menu (MenuNo, ukuranNo, Harga) VALUES (?, ?, ?)");
        if (!$stmt) {
            $errorMessage = 'Prepare gagal: ' . mysqli_error($conn);
        } else {
            mysqli_stmt_bind_param($stmt, 'iid', $MenuNo, $ukuranNo, $harga);
            if (mysqli_stmt_execute($stmt)) {
                $successMessage = 'Varian menu berhasil disimpan.';
                $_POST = [];
            } else {
                $errorMessage = 'Gagal menyimpan: ' . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        }
        } // end else (varian belum ada)
    }
}

// Dropdown data
$menuResult   = mysqli_query($conn, "SELECT MenuNo, nama_menu FROM menu ORDER BY nama_menu ASC");
$ukuranResult = mysqli_query($conn, "SELECT UkuranNo, nama_ukuran FROM ukuran ORDER BY UkuranNo ASC");
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Varian Menu — Tambah</title>
    <link rel="stylesheet" href="../../assets/css/main.css">
    <link rel="stylesheet" href="../../assets/css/components.css">
    <link rel="stylesheet" href="../../assets/css/modules.css">
</head>
<body>
    <div class="app-layout">
        <?php include __DIR__ . '/../../components/sidebar.php'; ?>
        <main class="main-content module-varian-menu">
            <div class="container">
                <div class="card">
                    <h2>Tambah Varian Menu</h2>
                    <?php if ($successMessage): ?>
                        <div class="alert success"><?php echo htmlspecialchars($successMessage); ?></div>
                    <?php endif; ?>
                    <?php if ($errorMessage): ?>
                        <div class="alert error"><?php echo $errorMessage; ?></div>
                    <?php endif; ?>

                    <form method="post" action="">
                        <div class="row">
                            <div>
                                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px">
                                    <label for="MenuNo" style="margin:0">Menu</label>
                                    <a href="../menu/menu_tambah.php"
                                       style="font-size:12px;color:#3b82f6;text-decoration:none;font-weight:600">
                                       + Tambah Menu Baru
                                    </a>
                                </div>
                                <select id="MenuNo" name="MenuNo" class="form-input" required>
                                    <option value="">-- Pilih Menu --</option>
                                    <?php if ($menuResult): while ($row = mysqli_fetch_assoc($menuResult)): ?>
                                        <option value="<?php echo $row['MenuNo']; ?>"
                                            <?php echo (isset($_POST['MenuNo']) && $_POST['MenuNo'] == $row['MenuNo']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($row['nama_menu']); ?>
                                        </option>
                                    <?php endwhile; endif; ?>
                                </select>
                            </div>
                            <div>
                                <label for="ukuranNo">Ukuran</label>
                                <select id="ukuranNo" name="ukuranNo" class="form-input" required>
                                    <option value="">-- Pilih Ukuran --</option>
                                    <?php if ($ukuranResult): while ($row = mysqli_fetch_assoc($ukuranResult)): ?>
                                        <option value="<?php echo $row['UkuranNo']; ?>"
                                            <?php echo (isset($_POST['ukuranNo']) && $_POST['ukuranNo'] == $row['UkuranNo']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($row['nama_ukuran']); ?>
                                        </option>
                                    <?php endwhile; endif; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mt-12">
                            <label for="Harga">Harga</label>
                            <input id="Harga" name="Harga" class="form-input" type="number"
                                step="500" min="0" required placeholder="0"
                                value="<?php echo isset($_POST['Harga']) ? htmlspecialchars($_POST['Harga']) : ''; ?>">
                        </div>

                        <div class="form-actions">
                            <button class="btn" type="submit">Simpan</button>
                        </div>
                    </form>
                </div>

                <div class="card" style="margin-top:18px">
                    <h2>Daftar Varian Menu</h2>
                    <?php
                    $sql = "SELECT vm.VarianMenuNo, vm.MenuNo, m.nama_menu, u.nama_ukuran, vm.Harga,
                                   (SELECT COUNT(*) FROM resep r WHERE r.MenuNo = vm.MenuNo) AS punya_resep
                            FROM varian_menu vm
                            LEFT JOIN menu m    ON vm.MenuNo   = m.MenuNo
                            LEFT JOIN ukuran u  ON vm.ukuranNo = u.UkuranNo
                            ORDER BY m.nama_menu ASC, u.UkuranNo ASC";
                    $varianRes = mysqli_query($conn, $sql);
                    if ($varianRes && mysqli_num_rows($varianRes) > 0):
                        echo '<table style="width:100%;border-collapse:collapse">';
                        echo '<thead><tr style="background:#f0f0f0">
                            <th style="text-align:left;padding:8px">#</th>
                            <th style="text-align:left;padding:8px">Menu</th>
                            <th style="text-align:left;padding:8px">Ukuran</th>
                            <th style="text-align:right;padding:8px">Harga</th>
                            <th style="text-align:center;padding:8px">Resep</th>
                            <th style="text-align:right;padding:8px">Aksi</th>
                        </tr></thead><tbody>';
                        $i = 1;
                        while ($row = mysqli_fetch_assoc($varianRes)):
                            $id     = htmlspecialchars($row['VarianMenuNo']);
                            $menu   = htmlspecialchars($row['nama_menu'] ?? '-');
                            $ukuran = htmlspecialchars($row['nama_ukuran'] ?? '-');
                            $harga  = 'Rp ' . number_format($row['Harga'], 0, ',', '.');
                            $menuNo = htmlspecialchars($row['MenuNo']);
                            $resepBadge = $row['punya_resep']
                                ? '<span style="color:#16a34a;font-weight:600">✔ Ada</span>'
                                : '<a href="../resep/resep_tambah.php?MenuNo=' . $menuNo . '" style="color:#dc2626;font-size:13px;font-weight:600">+ Isi Resep</a>';
                            echo "<tr style=\"border-bottom:1px solid #eee\">";
                            echo "<td style=\"padding:8px\">" . ($i++) . "</td>";
                            echo "<td style=\"padding:8px\">{$menu}</td>";
                            echo "<td style=\"padding:8px\">{$ukuran}</td>";
                            echo "<td style=\"padding:8px;text-align:right\">{$harga}</td>";
                            echo "<td style=\"padding:8px;text-align:center\">{$resepBadge}</td>";
                            echo "<td style=\"padding:8px;text-align:right\">";
                            echo "<a class=\"btn\" href=\"varian_edit.php?VarianMenuNo={$id}\" style=\"padding:6px 10px;font-size:13px;background:#f59e0b;color:white;text-decoration:none;border-radius:4px;margin-right:4px\">Edit</a>";
                            echo "<a class=\"btn\" href=\"varian_delete.php?VarianMenuNo={$id}\" onclick=\"return confirm('Hapus varian ini? Resep & bahan terkait juga akan dihapus.')\" style=\"padding:6px 10px;font-size:13px;background:#ef4444;color:white;text-decoration:none;border-radius:4px\">Hapus</a>";
                            echo "</td></tr>";
                        endwhile;
                        echo '</tbody></table>';
                    else:
                        echo '<p>Tidak ada data varian menu.</p>';
                    endif;
                    ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
