<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/format_helper.php';

$successMessage = '';
$errorMessage   = '';
$missingList    = []; // menu yang belum punya resep/bahan

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $karyawanNo          = isset($_POST['karyawanNo'])          && is_numeric($_POST['karyawanNo'])          ? (int)   $_POST['karyawanNo']          : null;
    $metode_pembayaranNo = isset($_POST['metode_pembayaranNo']) && is_numeric($_POST['metode_pembayaranNo']) ? (int)   $_POST['metode_pembayaranNo'] : null;
    $voucherNo           = isset($_POST['voucherNo'])           && $_POST['voucherNo'] !== '' && is_numeric($_POST['voucherNo']) ? (int) $_POST['voucherNo'] : null;
    $tanggal             = isset($_POST['tanggal']) && trim($_POST['tanggal']) !== '' ? trim($_POST['tanggal']) : date('Y-m-d H:i:s');

    // datetime-local → MySQL format
    if (strpos($tanggal, 'T') !== false) {
        $tanggal = str_replace('T', ' ', $tanggal) . ':00';
    }

    // Parse items JSON
    $items = [];
    if (isset($_POST['items']) && trim($_POST['items']) !== '' && trim($_POST['items']) !== '[]') {
        $decoded = json_decode($_POST['items'], true);
        if (is_array($decoded)) $items = $decoded;
    }

    if ($karyawanNo === null || $karyawanNo <= 0) {
        $errorMessage = 'Karyawan harus diisi.';
    } elseif ($metode_pembayaranNo === null || $metode_pembayaranNo <= 0) {
        $errorMessage = 'Metode Pembayaran harus diisi.';
    } elseif (empty($items)) {
        $errorMessage = 'Minimal 1 item harus ditambahkan.';
    } else {
        // Hitung total dari items
        $totalHarga = 0;
        foreach ($items as $item) {
            if (!isset($item['varianMenuNo'], $item['jumlah'], $item['hargaSatuan'])) {
                $errorMessage = 'Data item tidak lengkap.'; break;
            }
            $item['jumlah']     = (int)   $item['jumlah'];
            $item['hargaSatuan'] = (float) $item['hargaSatuan'];
            if ($item['jumlah'] <= 0 || $item['hargaSatuan'] < 0) {
                $errorMessage = 'Jumlah harus > 0 dan harga >= 0.'; break;
            }
            $totalHarga += $item['jumlah'] * $item['hargaSatuan'];
        }

        if (!$errorMessage) {
            // ── PRE-VALIDASI: semua item harus punya resep & bahan per ukuran ──
            foreach ($items as $item) {
                $vmNo = (int) $item['varianMenuNo'];

                $stmtV = mysqli_prepare($conn,
                    "SELECT vm.MenuNo, vm.ukuranNo, m.nama_menu, u.nama_ukuran
                     FROM varian_menu vm
                     LEFT JOIN menu m   ON vm.MenuNo   = m.MenuNo
                     LEFT JOIN ukuran u ON vm.ukuranNo = u.UkuranNo
                     WHERE vm.VarianMenuNo = ? LIMIT 1");
                mysqli_stmt_bind_param($stmtV, 'i', $vmNo);
                mysqli_stmt_execute($stmtV);
                $vRow = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtV));
                mysqli_stmt_close($stmtV);

                if (!$vRow) { $missingList[] = 'Varian #' . $vmNo . ' tidak ditemukan'; continue; }

                $menuNo   = (int) $vRow['MenuNo'];
                $ukuranNo = (int) $vRow['ukuranNo'];
                $label    = htmlspecialchars(($vRow['nama_menu'] ?? '?') . ' – ' . ($vRow['nama_ukuran'] ?? '-'));

                // Cek resep
                $stmtR = mysqli_prepare($conn, "SELECT ResepNo FROM resep WHERE MenuNo = ? LIMIT 1");
                mysqli_stmt_bind_param($stmtR, 'i', $menuNo);
                mysqli_stmt_execute($stmtR);
                $rRow = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtR));
                mysqli_stmt_close($stmtR);

                if (!$rRow) { $missingList[] = $label . ': belum memiliki resep'; continue; }

                // Cek detail_resep untuk ukuran ini
                $resepNoCheck = (int) $rRow['ResepNo'];
                $stmtD = mysqli_prepare($conn,
                    "SELECT COUNT(*) AS cnt FROM detail_resep WHERE ResepNo = ? AND UkuranNo = ?");
                mysqli_stmt_bind_param($stmtD, 'ii', $resepNoCheck, $ukuranNo);
                mysqli_stmt_execute($stmtD);
                $dRow = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtD));
                mysqli_stmt_close($stmtD);

                if (!$dRow || (int) $dRow['cnt'] === 0) {
                    $missingList[] = $label . ': bahan belum diisi di Detail Resep untuk ukuran ini';
                }
            }

            if (empty($missingList)) {
            // Ambil diskon voucher
            $nilai_diskon = 0;
            if ($voucherNo) {
                $vStmt = mysqli_prepare($conn, "SELECT nilai_diskon FROM voucher WHERE voucherNo = ? LIMIT 1");
                if ($vStmt) {
                    mysqli_stmt_bind_param($vStmt, 'i', $voucherNo);
                    mysqli_stmt_execute($vStmt);
                    $vRow = mysqli_fetch_assoc(mysqli_stmt_get_result($vStmt));
                    mysqli_stmt_close($vStmt);
                    if ($vRow && $vRow['nilai_diskon'] > 0) {
                        $nilai_diskon = (float) $vRow['nilai_diskon'];
                        $totalHarga   = max(0, $totalHarga - $nilai_diskon);
                    }
                }
            }

            // ── TRANSAKSI ATOMIK ────────────────────────────────────────────
            mysqli_begin_transaction($conn);
            try {
                // 1. INSERT transaksi
                $stmt = mysqli_prepare($conn,
                    "INSERT INTO transaksi (karyawanNo, metode_pembayaranNo, voucherNo, tanggal, total_harga)
                     VALUES (?, ?, ?, ?, ?)");
                if (!$stmt) throw new Exception('Prepare insert transaksi gagal: ' . mysqli_error($conn));
                mysqli_stmt_bind_param($stmt, 'iiisd', $karyawanNo, $metode_pembayaranNo, $voucherNo, $tanggal, $totalHarga);
                if (!mysqli_stmt_execute($stmt)) throw new Exception('Execute insert transaksi gagal: ' . mysqli_stmt_error($stmt));
                $newTransaksiNo = mysqli_insert_id($conn);
                mysqli_stmt_close($stmt);

                // 2. INSERT detail_transaksi + kurangi stok per item
                foreach ($items as $item) {
                    $varianMenuNo = (int)   $item['varianMenuNo'];
                    $jumlah       = (int)   $item['jumlah'];
                    $hargaSatuan  = (float) $item['hargaSatuan'];

                    // INSERT detail
                    $stmt2 = mysqli_prepare($conn,
                        "INSERT INTO detail_transaksi (transaksiNo, VarianMenuNo, jumlah, harga_satuan)
                         VALUES (?, ?, ?, ?)");
                    if (!$stmt2) throw new Exception('Prepare insert detail gagal: ' . mysqli_error($conn));
                    mysqli_stmt_bind_param($stmt2, 'iiid', $newTransaksiNo, $varianMenuNo, $jumlah, $hargaSatuan);
                    if (!mysqli_stmt_execute($stmt2)) throw new Exception('Execute insert detail gagal: ' . mysqli_stmt_error($stmt2));
                    mysqli_stmt_close($stmt2);

                    // ── PENGURANGAN STOK ──────────────────────────────────
                    // 1. Ambil MenuNo + UkuranNo dari varian_menu
                    $stmtVarian = mysqli_prepare($conn,
                        "SELECT MenuNo, ukuranNo FROM varian_menu WHERE VarianMenuNo = ? LIMIT 1");
                    if (!$stmtVarian) throw new Exception('Prepare varian gagal: ' . mysqli_error($conn));
                    mysqli_stmt_bind_param($stmtVarian, 'i', $varianMenuNo);
                    mysqli_stmt_execute($stmtVarian);
                    $varianRow = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtVarian));
                    mysqli_stmt_close($stmtVarian);

                    if ($varianRow) {
                        $menuNo   = (int) $varianRow['MenuNo'];
                        $ukuranNo = (int) $varianRow['ukuranNo'];

                        // 2. Cari ResepNo berdasarkan MenuNo
                        $stmtResep = mysqli_prepare($conn,
                            "SELECT ResepNo FROM resep WHERE MenuNo = ? LIMIT 1");
                        if (!$stmtResep) throw new Exception('Prepare resep gagal: ' . mysqli_error($conn));
                        mysqli_stmt_bind_param($stmtResep, 'i', $menuNo);
                        mysqli_stmt_execute($stmtResep);
                        $resepRow = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtResep));
                        mysqli_stmt_close($stmtResep);

                        if ($resepRow) {
                            // 3. Ambil bahan untuk ResepNo ini + UkuranNo yang sesuai
                            $stmtBahan = mysqli_prepare($conn,
                                "SELECT StokNo, jumlah FROM detail_resep
                                 WHERE ResepNo = ? AND UkuranNo = ?");
                            if (!$stmtBahan) throw new Exception('Prepare detail_resep gagal: ' . mysqli_error($conn));
                            mysqli_stmt_bind_param($stmtBahan, 'ii', $resepRow['ResepNo'], $ukuranNo);
                            mysqli_stmt_execute($stmtBahan);
                            $bahanRes = mysqli_stmt_get_result($stmtBahan);

                            while ($bahan = mysqli_fetch_assoc($bahanRes)) {
                                $totalPakai = $bahan['jumlah'] * $jumlah; // takaran × qty order

                                $stmtKurang = mysqli_prepare($conn,
                                    "UPDATE stok SET jumlah_stok = jumlah_stok - ? WHERE StokNo = ?");
                                if (!$stmtKurang) throw new Exception('Prepare update stok gagal: ' . mysqli_error($conn));
                                mysqli_stmt_bind_param($stmtKurang, 'di', $totalPakai, $bahan['StokNo']);
                                if (!mysqli_stmt_execute($stmtKurang)) throw new Exception('Update stok gagal: ' . mysqli_stmt_error($stmtKurang));
                                mysqli_stmt_close($stmtKurang);
                            }
                            mysqli_stmt_close($stmtBahan);
                        }
                    }
                }

                mysqli_commit($conn);
                $successMessage = 'Transaksi #' . $newTransaksiNo . ' berhasil disimpan (' . count($items) . ' item). Stok telah dikurangi.';
                $_POST = [];

            } catch (Exception $e) {
                mysqli_rollback($conn);
                $errorMessage = 'Gagal menyimpan: ' . $e->getMessage();
            }
            } // end if (empty($missingList))
        }
    }
}

// Dropdown data
$karyawanResult = mysqli_query($conn, "SELECT karyawanNo, nama FROM karyawan ORDER BY nama ASC");
$metodeResult   = mysqli_query($conn, "SELECT metode_pembayaranNo, nama_metode FROM metode_pembayaran ORDER BY nama_metode ASC");
$voucherResult  = mysqli_query($conn, "SELECT voucherNo, nama_voucher, nilai_diskon FROM voucher ORDER BY nama_voucher ASC");
$varianResult   = mysqli_query($conn,
    "SELECT vm.VarianMenuNo, m.nama_menu, u.nama_ukuran, vm.Harga
     FROM varian_menu vm
     LEFT JOIN menu m   ON vm.MenuNo   = m.MenuNo
     LEFT JOIN ukuran u ON vm.ukuranNo = u.UkuranNo
     ORDER BY m.nama_menu ASC, u.UkuranNo ASC");
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Transaksi — Tambah</title>
    <link rel="stylesheet" href="../../assets/css/main.css">
    <link rel="stylesheet" href="../../assets/css/components.css">
    <link rel="stylesheet" href="../../assets/css/modules.css">
    <style>
        .items-list { margin-top:12px; padding:8px; background:#f3f4f6; border-radius:4px; }
        table.items-table { width:100%; border-collapse:collapse; }
        table.items-table th, table.items-table td { padding:6px; text-align:left; border-bottom:1px solid #d1d5db; }
        table.items-table th { background:#e5e7eb; font-weight:bold; }
    </style>
    <script>
        let itemsData = [];

        function addItem() {
            const sel    = document.getElementById('VarianMenuNo');
            const jumlah = parseInt(document.getElementById('jumlah').value) || 0;
            if (!sel.value || jumlah <= 0) { alert('Pilih varian dan masukkan jumlah > 0'); return; }

            const opt = sel.options[sel.selectedIndex];
            itemsData.push({
                varianMenuNo: parseInt(sel.value),
                jumlah:       jumlah,
                hargaSatuan:  parseFloat(opt.getAttribute('data-price')) || 0,
                text:         opt.text
            });
            document.getElementById('jumlah').value = 1;
            sel.value = '';
            updateDisplay();
        }

        function removeItem(i) { itemsData.splice(i, 1); updateDisplay(); }

        function updateDisplay() {
            const container = document.getElementById('itemsContainer');
            const hidden    = document.getElementById('items');
            let html = '', total = 0;

            if (itemsData.length > 0) {
                html += '<table class="items-table"><thead><tr>';
                html += '<th>Menu</th><th style="text-align:right">Qty</th><th style="text-align:right">Harga Satuan</th><th style="text-align:right">Subtotal</th><th style="text-align:center">Aksi</th>';
                html += '</tr></thead><tbody>';
                itemsData.forEach((item, i) => {
                    const sub = item.jumlah * item.hargaSatuan;
                    total += sub;
                    html += `<tr>
                        <td>${item.text}</td>
                        <td style="text-align:right">${item.jumlah}</td>
                        <td style="text-align:right">${fmt(item.hargaSatuan)}</td>
                        <td style="text-align:right">${fmt(sub)}</td>
                        <td style="text-align:center"><button type="button" onclick="removeItem(${i})" style="padding:4px 8px;background:#ef4444;color:white;border:none;border-radius:4px;cursor:pointer">Hapus</button></td>
                    </tr>`;
                });
                html += `<tr style="background:#e5e7eb;font-weight:bold">
                    <td colspan="3" style="text-align:right">TOTAL</td>
                    <td style="text-align:right">${fmt(total)}</td><td></td>
                </tr></tbody></table>`;
            } else {
                html = '<p style="color:#6b7280">Belum ada item.</p>';
            }
            container.innerHTML = html;
            hidden.value = JSON.stringify(itemsData);
        }

        function fmt(n) {
            return new Intl.NumberFormat('id-ID', { style:'currency', currency:'IDR' }).format(n);
        }

        function validateForm(e) {
            document.getElementById('items').value = JSON.stringify(itemsData);
            if (itemsData.length === 0) { e.preventDefault(); alert('Minimal 1 item harus ditambahkan.'); return false; }
        }

        window.addEventListener('load', function() {
            updateDisplay();
            document.querySelector('form').addEventListener('submit', validateForm);
        });
    </script>
</head>
<body>
    <div class="app-layout">
        <?php include __DIR__ . '/../../components/sidebar.php'; ?>
        <main class="main-content module-transaksi">
            <div class="container">
                <div class="card">
                    <h2>Tambah Transaksi</h2>

                    <?php if ($successMessage): ?>
                        <div class="alert success" style="background:#d1fae5;padding:10px;border-radius:4px;color:#065f46;margin-bottom:12px">
                            <?php echo htmlspecialchars($successMessage); ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($missingList)): ?>
                        <div class="alert error" style="background:#fee2e2;padding:12px 16px;border-radius:4px;color:#991b1b;margin-bottom:12px">
                            <strong>❌ Transaksi dibatalkan — menu berikut belum siap:</strong>
                            <ul style="margin:8px 0 0;padding-left:20px">
                                <?php foreach ($missingList as $m): ?>
                                    <li><?php echo $m; ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <p style="margin:10px 0 0;font-size:13px">
                                Lengkapi resep &amp; bahan di menu
                                <a href="../detail_resep/detail_resep_tambah.php" style="color:#991b1b;font-weight:600;text-decoration:underline">Detail Resep</a>
                                terlebih dahulu.
                            </p>
                        </div>
                    <?php endif; ?>
                    <?php if ($errorMessage): ?>
                        <div class="alert error" style="background:#fee2e2;padding:10px;border-radius:4px;color:#991b1b;margin-bottom:12px">
                            <?php echo htmlspecialchars($errorMessage); ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="">
                        <div class="row">
                            <div>
                                <label for="tanggal">Tanggal</label>
                                <input id="tanggal" name="tanggal" class="form-input" type="datetime-local" required
                                    value="<?php echo isset($_POST['tanggal']) ? htmlspecialchars($_POST['tanggal']) : date('Y-m-d\TH:i'); ?>">
                            </div>
                        </div>

                        <div class="row mt-12">
                            <div>
                                <label for="karyawanNo">Karyawan</label>
                                <select id="karyawanNo" name="karyawanNo" class="form-input" required>
                                    <option value="">-- Pilih Karyawan --</option>
                                    <?php if ($karyawanResult): while ($row = mysqli_fetch_assoc($karyawanResult)): ?>
                                        <option value="<?php echo $row['karyawanNo']; ?>"
                                            <?php echo (isset($_POST['karyawanNo']) && $_POST['karyawanNo'] == $row['karyawanNo']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($row['nama']); ?>
                                        </option>
                                    <?php endwhile; endif; ?>
                                </select>
                            </div>
                            <div>
                                <label for="metode_pembayaranNo">Metode Pembayaran</label>
                                <select id="metode_pembayaranNo" name="metode_pembayaranNo" class="form-input" required>
                                    <option value="">-- Pilih Metode --</option>
                                    <?php if ($metodeResult): while ($row = mysqli_fetch_assoc($metodeResult)): ?>
                                        <option value="<?php echo $row['metode_pembayaranNo']; ?>"
                                            <?php echo (isset($_POST['metode_pembayaranNo']) && $_POST['metode_pembayaranNo'] == $row['metode_pembayaranNo']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($row['nama_metode']); ?>
                                        </option>
                                    <?php endwhile; endif; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mt-12">
                            <label for="voucherNo">Voucher (Opsional)</label>
                            <select id="voucherNo" name="voucherNo" class="form-input">
                                <option value="">-- Tidak Ada Voucher --</option>
                                <?php if ($voucherResult): while ($row = mysqli_fetch_assoc($voucherResult)): ?>
                                    <option value="<?php echo $row['voucherNo']; ?>"
                                        <?php echo (isset($_POST['voucherNo']) && $_POST['voucherNo'] == $row['voucherNo']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($row['nama_voucher'] . ' (diskon: ' . rupiah($row['nilai_diskon']) . ')'); ?>
                                    </option>
                                <?php endwhile; endif; ?>
                            </select>
                        </div>

                        <!-- Tambah item -->
                        <div style="margin-top:20px;padding:12px;background:#f0fdf4;border-radius:8px">
                            <h3 style="margin-top:0">Tambah Item</h3>
                            <div class="row">
                                <div>
                                    <label for="VarianMenuNo">Menu / Varian</label>
                                    <select id="VarianMenuNo" class="form-input">
                                        <option value="">-- Pilih Menu --</option>
                                        <?php if ($varianResult): while ($row = mysqli_fetch_assoc($varianResult)): ?>
                                            <option value="<?php echo $row['VarianMenuNo']; ?>" data-price="<?php echo $row['Harga']; ?>">
                                                <?php echo htmlspecialchars($row['nama_menu'] . ' - ' . $row['nama_ukuran'] . ' (' . rupiah($row['Harga']) . ')'); ?>
                                            </option>
                                        <?php endwhile; endif; ?>
                                    </select>
                                </div>
                                <div>
                                    <label for="jumlah">Jumlah</label>
                                    <input id="jumlah" class="form-input" type="number" min="1" value="1">
                                </div>
                                <div style="display:flex;align-items:flex-end">
                                    <button type="button" onclick="addItem()" class="btn" style="padding:10px 14px">+ Tambah</button>
                                </div>
                            </div>
                        </div>

                        <!-- Daftar item -->
                        <div class="items-list">
                            <h3 style="margin-top:0">Item Transaksi</h3>
                            <div id="itemsContainer"></div>
                        </div>
                        <input type="hidden" id="items" name="items" value="">

                        <div class="form-actions mt-12">
                            <button class="btn" type="submit">Simpan Transaksi</button>
                            <a class="btn" href="transaksi_list.php" style="background:#6b7280">Batal</a>
                        </div>
                    </form>
                </div>

                <!-- Transaksi terbaru -->
                <div class="card" style="margin-top:18px">
                    <h2>Transaksi Terbaru</h2>
                    <?php
                    $recentRes = mysqli_query($conn,
                        "SELECT t.transaksiNo, t.tanggal, t.total_harga, COALESCE(k.nama,'-') AS karyawan
                         FROM transaksi t
                         LEFT JOIN karyawan k ON t.karyawanNo = k.karyawanNo
                         ORDER BY t.transaksiNo DESC LIMIT 10");
                    if ($recentRes && mysqli_num_rows($recentRes) > 0):
                        echo '<table style="width:100%;border-collapse:collapse">';
                        echo '<thead><tr style="background:#f0f0f0">
                            <th style="text-align:left;padding:8px">#</th>
                            <th style="text-align:left;padding:8px">No</th>
                            <th style="text-align:left;padding:8px">Tanggal</th>
                            <th style="text-align:left;padding:8px">Karyawan</th>
                            <th style="text-align:right;padding:8px">Total</th>
                            <th style="text-align:center;padding:8px">Aksi</th>
                        </tr></thead><tbody>';
                        $i = 1;
                        while ($row = mysqli_fetch_assoc($recentRes)):
                            $id  = htmlspecialchars($row['transaksiNo']);
                            echo '<tr style="border-bottom:1px solid #eee">';
                            echo "<td style=\"padding:8px\">" . ($i++) . "</td>";
                            echo "<td style=\"padding:8px\">{$id}</td>";
                            echo "<td style=\"padding:8px\">" . htmlspecialchars($row['tanggal']) . "</td>";
                            echo "<td style=\"padding:8px\">" . htmlspecialchars($row['karyawan']) . "</td>";
                            echo "<td style=\"padding:8px;text-align:right\">" . rupiah($row['total_harga']) . "</td>";
                            echo "<td style=\"padding:8px;text-align:center\"><a class=\"btn\" href=\"transaksi_detail.php?transaksiNo={$id}\" style=\"padding:6px 10px;font-size:13px\">Detail</a></td>";
                            echo '</tr>';
                        endwhile;
                        echo '</tbody></table>';
                    else:
                        echo '<p>Belum ada transaksi.</p>';
                    endif;
                    ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
