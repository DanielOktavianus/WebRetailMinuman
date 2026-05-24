<?php
require_once __DIR__ . '/../../config/database.php';

$successMessage = '';
$errorMessage   = '';

// Pre-select MenuNo dari URL (datang dari tombol "Isi Resep" di menu / varian)
$selectedMenuNo = isset($_GET['MenuNo']) && is_numeric($_GET['MenuNo'])
    ? (int) $_GET['MenuNo']
    : (isset($_POST['MenuNo']) && is_numeric($_POST['MenuNo']) ? (int) $_POST['MenuNo'] : null);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $MenuNo     = isset($_POST['MenuNo'])     && is_numeric($_POST['MenuNo'])     ? (int) $_POST['MenuNo']     : null;
    $keterangan = isset($_POST['keterangan']) ? trim($_POST['keterangan'])         : '';

    if ($MenuNo === null || empty($keterangan)) {
        $errorMessage = 'Menu dan keterangan harus diisi.';
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO resep (MenuNo, Keterangan) VALUES (?, ?)");
        if (!$stmt) {
            $errorMessage = 'Prepare gagal: ' . mysqli_error($conn);
        } else {
            mysqli_stmt_bind_param($stmt, 'is', $MenuNo, $keterangan);
            if (mysqli_stmt_execute($stmt)) {
                $newResepNo = mysqli_insert_id($conn);
                $successMessage = 'Resep berhasil ditambahkan. <a href="../detail_resep/detail_resep_tambah.php?ResepNo=' . $newResepNo . '">→ Isi Bahan Sekarang</a>';
                $_POST = [];
                $selectedMenuNo = null;
            } else {
                if (strpos(mysqli_stmt_error($stmt), '1062') !== false || strpos(mysqli_stmt_error($stmt), 'Duplicate') !== false) {
                    // Cari ResepNo yang sudah ada
                    $cek = mysqli_prepare($conn, "SELECT ResepNo FROM resep WHERE MenuNo = ? LIMIT 1");
                    mysqli_stmt_bind_param($cek, 'i', $MenuNo);
                    mysqli_stmt_execute($cek);
                    $cekRow = mysqli_fetch_assoc(mysqli_stmt_get_result($cek));
                    mysqli_stmt_close($cek);
                    $editLink = $cekRow ? ' <a href="resep_edit.php?ResepNo=' . $cekRow['ResepNo'] . '">Edit resep →</a>' : '';
                    $errorMessage = 'Menu ini sudah memiliki resep.' . $editLink;
                } else {
                    $errorMessage = 'Gagal menyimpan: ' . mysqli_stmt_error($stmt);
                }
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// Dropdown: menu yang BELUM punya resep
$menuResult = mysqli_query($conn,
    "SELECT MenuNo, nama_menu FROM menu
     WHERE MenuNo NOT IN (SELECT MenuNo FROM resep WHERE MenuNo IS NOT NULL)
     ORDER BY nama_menu ASC");

// Jika MenuNo dari URL sudah punya resep, cek dulu
if ($selectedMenuNo) {
    $cek = mysqli_prepare($conn, "SELECT ResepNo FROM resep WHERE MenuNo = ? LIMIT 1");
    mysqli_stmt_bind_param($cek, 'i', $selectedMenuNo);
    mysqli_stmt_execute($cek);
    $cekRow = mysqli_fetch_assoc(mysqli_stmt_get_result($cek));
    mysqli_stmt_close($cek);
    if ($cekRow) {
        $errorMessage = 'Menu ini sudah punya resep. <a href="resep_edit.php?ResepNo=' . $cekRow['ResepNo'] . '">Edit resep →</a>';
        $selectedMenuNo = null;
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Resep — Tambah</title>
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
                    <h2>Tambah Resep</h2>
                    <?php if ($successMessage): ?>
                        <div class="alert success"><?php echo $successMessage; ?></div>
                    <?php endif; ?>
                    <?php if ($errorMessage): ?>
                        <div class="alert error"><?php echo $errorMessage; ?></div>
                    <?php endif; ?>

                    <form method="post" action="">
                        <div>
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px">
                                <label for="MenuNo" style="margin:0">Menu</label>
                                <a href="../menu/menu_tambah.php"
                                   style="font-size:12px;color:#3b82f6;text-decoration:none;font-weight:600">
                                   + Tambah Menu Baru
                                </a>
                            </div>
                            <select id="MenuNo" name="MenuNo" class="form-input" required
                                <?php echo $selectedMenuNo ? 'disabled' : ''; ?>>
                                <option value="">-- Pilih Menu --</option>
                                <?php
                                if ($selectedMenuNo):
                                    $sv = mysqli_prepare($conn, "SELECT MenuNo, nama_menu FROM menu WHERE MenuNo = ? LIMIT 1");
                                    mysqli_stmt_bind_param($sv, 'i', $selectedMenuNo);
                                    mysqli_stmt_execute($sv);
                                    $svRow = mysqli_fetch_assoc(mysqli_stmt_get_result($sv));
                                    mysqli_stmt_close($sv);
                                    if ($svRow):
                                        echo '<option value="' . $svRow['MenuNo'] . '" selected>'
                                           . htmlspecialchars($svRow['nama_menu']) . '</option>';
                                    endif;
                                elseif ($menuResult):
                                    while ($row = mysqli_fetch_assoc($menuResult)):
                                        echo '<option value="' . $row['MenuNo'] . '">'
                                           . htmlspecialchars($row['nama_menu']) . '</option>';
                                    endwhile;
                                endif;
                                ?>
                            </select>
                            <?php if ($selectedMenuNo): ?>
                                <input type="hidden" name="MenuNo" value="<?php echo $selectedMenuNo; ?>">
                            <?php endif; ?>
                        </div>

                        <div class="mt-12">
                            <label for="keterangan">Keterangan Resep</label>
                            <textarea id="keterangan" name="keterangan" class="form-input" rows="3" required
                                placeholder="Contoh: Seduh teh dengan air 80°C, tambahkan gula sesuai ukuran"><?php
                                echo isset($_POST['keterangan']) ? htmlspecialchars($_POST['keterangan']) : '';
                            ?></textarea>
                        </div>

                        <div class="form-actions">
                            <button class="btn" type="submit">Simpan</button>
                            <a class="btn" href="../varian_menu/varian_tambah.php" style="background:#6b7280">← Kembali ke Varian</a>
                        </div>
                    </form>
                </div>

                <div class="card" style="margin-top:18px">
                    <h2>Daftar Resep</h2>
                    <?php
                    $res = mysqli_query($conn,
                        "SELECT r.ResepNo, m.nama_menu, r.Keterangan,
                                (SELECT COUNT(*) FROM detail_resep dr WHERE dr.ResepNo = r.ResepNo) AS jml_bahan
                         FROM resep r
                         LEFT JOIN menu m ON r.MenuNo = m.MenuNo
                         ORDER BY m.nama_menu ASC");
                    if ($res && mysqli_num_rows($res) > 0):
                        echo '<table style="width:100%;border-collapse:collapse">';
                        echo '<thead><tr style="background:#f0f0f0">
                            <th style="text-align:left;padding:10px;border-bottom:1px solid #ccc">#</th>
                            <th style="text-align:left;padding:10px;border-bottom:1px solid #ccc">Menu</th>
                            <th style="text-align:left;padding:10px;border-bottom:1px solid #ccc">Keterangan</th>
                            <th style="text-align:center;padding:10px;border-bottom:1px solid #ccc">Bahan</th>
                            <th style="text-align:right;padding:10px;border-bottom:1px solid #ccc">Aksi</th>
                        </tr></thead><tbody>';
                        $i = 1;
                        while ($row = mysqli_fetch_assoc($res)):
                            $id     = htmlspecialchars($row['ResepNo']);
                            $menu   = htmlspecialchars($row['nama_menu'] ?? '-');
                            $ket    = htmlspecialchars(mb_substr($row['Keterangan'], 0, 60)) . (mb_strlen($row['Keterangan']) > 60 ? '...' : '');
                            $jml    = (int) $row['jml_bahan'];
                            $badge  = $jml > 0
                                ? "<span style=\"color:#16a34a;font-weight:600\">{$jml} bahan</span>"
                                : "<a href=\"../detail_resep/detail_resep_tambah.php?ResepNo={$id}\" style=\"color:#dc2626;font-size:13px;font-weight:600\">+ Isi Bahan</a>";
                            echo '<tr style="border-bottom:1px solid #eee">';
                            echo "<td style=\"padding:10px\">" . ($i++) . "</td>";
                            echo "<td style=\"padding:10px\"><strong>{$menu}</strong></td>";
                            echo "<td style=\"padding:10px\">{$ket}</td>";
                            echo "<td style=\"padding:10px;text-align:center\">{$badge}</td>";
                            echo "<td style=\"padding:10px;text-align:right\">";
                            echo "<a class=\"btn\" href=\"../detail_resep/detail_resep_tambah.php?ResepNo={$id}\" style=\"padding:6px 10px;font-size:13px;background:#06b6d4;color:white;text-decoration:none;border-radius:4px;margin-right:4px\">Isi Bahan</a>";
                            echo "<a class=\"btn\" href=\"resep_edit.php?ResepNo={$id}\" style=\"padding:6px 10px;font-size:13px;background:#f59e0b;color:white;text-decoration:none;border-radius:4px;margin-right:4px\">Edit</a>";
                            echo "<a class=\"btn\" href=\"resep_delete.php?ResepNo={$id}\" onclick=\"return confirm('Yakin hapus resep ini? Semua bahan terkait juga akan dihapus.')\" style=\"padding:6px 10px;font-size:13px;background:#ef4444;color:white;text-decoration:none;border-radius:4px\">Hapus</a>";
                            echo "</td></tr>";
                        endwhile;
                        echo '</tbody></table>';
                    else:
                        echo '<p>Belum ada resep.</p>';
                    endif;
                    ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
