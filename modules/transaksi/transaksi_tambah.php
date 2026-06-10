<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/format_helper.php';

$successMessage = '';
$errorMessage   = '';
$warningStok    = [];
$missingList    = []; // menu yang belum punya resep/bahan

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $karyawanNo          = isset($_SESSION['karyawanNo']) && is_numeric($_SESSION['karyawanNo']) ? (int) $_SESSION['karyawanNo'] : null;
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
        $errorMessage = 'Sesi tidak valid, silakan login ulang.';
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
            $deductedStokNos = [];
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
                                $deductedStokNos[] = (int)$bahan['StokNo'];
                            }
                            mysqli_stmt_close($stmtBahan);
                        }
                    }
                }

                mysqli_commit($conn);
                $successMessage = 'Transaksi #' . $newTransaksiNo . ' berhasil disimpan (' . count($items) . ' item). Stok telah dikurangi.';

                // Cek bahan yang kini di bawah batas minimum
                if (!empty($deductedStokNos)) {
                    $inList = implode(',', array_unique($deductedStokNos));
                    $warnQ  = mysqli_query($conn,
                        "SELECT b.nama_bahan, s.jumlah_stok, s.batas_minimum
                         FROM stok s LEFT JOIN bahan b ON s.BahanNo = b.BahanNo
                         WHERE s.StokNo IN ({$inList}) AND s.jumlah_stok < s.batas_minimum
                         ORDER BY s.jumlah_stok ASC");
                    if ($warnQ) while ($w = mysqli_fetch_assoc($warnQ)) {
                        $warningStok[] = htmlspecialchars($w['nama_bahan'])
                            . ' (sisa: ' . ($w['jumlah_stok'] + 0) . ', min: ' . ($w['batas_minimum'] + 0) . ')';
                    }
                }
                $_POST = [];

            } catch (Exception $e) {
                mysqli_rollback($conn);
                $errorMessage = 'Gagal menyimpan: ' . $e->getMessage();
            }
            } // end if (empty($missingList))
        }
    }
}

// Ambil nama karyawan yang sedang login
$karyawanNama = '';
$karyawanNoSesi = isset($_SESSION['karyawanNo']) ? (int) $_SESSION['karyawanNo'] : 0;
if ($karyawanNoSesi > 0) {
    $kStmt = mysqli_prepare($conn, "SELECT nama FROM karyawan WHERE karyawanNo = ? LIMIT 1");
    mysqli_stmt_bind_param($kStmt, 'i', $karyawanNoSesi);
    mysqli_stmt_execute($kStmt);
    $kRow = mysqli_fetch_assoc(mysqli_stmt_get_result($kStmt));
    mysqli_stmt_close($kStmt);
    $karyawanNama = $kRow['nama'] ?? '';
}
$metodeResult   = mysqli_query($conn, "SELECT metode_pembayaranNo, nama_metode FROM metode_pembayaran ORDER BY nama_metode ASC");
$voucherResult  = mysqli_query($conn, "SELECT voucherNo, nama_voucher, nilai_diskon FROM voucher ORDER BY nama_voucher ASC");
$varianArr = [];
$_vQ = mysqli_query($conn,
    "SELECT vm.VarianMenuNo, m.nama_menu, u.nama_ukuran, vm.Harga
     FROM varian_menu vm
     LEFT JOIN menu m   ON vm.MenuNo   = m.MenuNo
     LEFT JOIN ukuran u ON vm.ukuranNo = u.UkuranNo
     ORDER BY m.nama_menu ASC, u.UkuranNo ASC");
if ($_vQ) while ($r = mysqli_fetch_assoc($_vQ)) $varianArr[] = $r;
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

        .menu-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(150px,1fr)); gap:10px; margin-top:10px; }
        .menu-card {
            border:2px solid #d1d5db; border-radius:10px; padding:12px 10px;
            text-align:center; cursor:pointer; background:#fff;
            transition:all .15s ease; user-select:none;
        }
        .menu-card:hover { border-color:#6366f1; background:#eef2ff; transform:translateY(-2px); box-shadow:0 4px 10px rgba(0,0,0,.1); }
        .menu-card:active { transform:translateY(0); }
        .menu-card.in-order { border-color:#10b981; background:#ecfdf5; }
        .menu-card-name { font-weight:700; font-size:13px; color:#111827; }
        .menu-card-size { font-size:12px; color:#6b7280; margin:2px 0; }
        .menu-card-price { font-size:13px; font-weight:600; color:#6366f1; }
        .menu-card-qty { margin-top:6px; font-size:12px; font-weight:700; color:#10b981; }
        .qty-btn { display:inline-flex; align-items:center; gap:6px; }
        .qty-btn button { width:24px; height:24px; border:none; border-radius:50%; cursor:pointer; font-size:14px; font-weight:700; line-height:1; }
        .qty-btn .btn-minus { background:#ef4444; color:#fff; }
        .qty-btn .btn-plus  { background:#10b981; color:#fff; }
    </style>
    <script>
        let itemsData = [];

        function addFromCard(varianMenuNo, hargaSatuan, text) {
            const existing = itemsData.find(i => i.varianMenuNo === varianMenuNo);
            if (existing) {
                existing.jumlah++;
            } else {
                itemsData.push({ varianMenuNo, jumlah: 1, hargaSatuan, text });
            }
            updateDisplay();
            updateCards();
        }

        function changeQty(varianMenuNo, delta) {
            const idx = itemsData.findIndex(i => i.varianMenuNo === varianMenuNo);
            if (idx === -1) return;
            itemsData[idx].jumlah += delta;
            if (itemsData[idx].jumlah <= 0) itemsData.splice(idx, 1);
            updateDisplay();
            updateCards();
        }

        function filterCards() {
            const q = document.getElementById('menuSearch').value.toLowerCase();
            document.querySelectorAll('.menu-card').forEach(card => {
                const name = card.querySelector('.menu-card-name').textContent.toLowerCase();
                const size = card.querySelector('.menu-card-size').textContent.toLowerCase();
                card.style.display = (name.includes(q) || size.includes(q)) ? '' : 'none';
            });
        }

        function updateCards() {
            document.querySelectorAll('.menu-card').forEach(card => {
                const vmNo = parseInt(card.dataset.id);
                const item = itemsData.find(i => i.varianMenuNo === vmNo);
                const qtyEl = card.querySelector('.menu-card-qty');
                if (item) {
                    card.classList.add('in-order');
                    qtyEl.innerHTML = `<span class="qty-btn">
                        <button class="btn-minus" type="button" onclick="event.stopPropagation();changeQty(${vmNo},-1)">−</button>
                        <span>${item.jumlah}</span>
                        <button class="btn-plus"  type="button" onclick="event.stopPropagation();changeQty(${vmNo},1)">+</button>
                    </span>`;
                } else {
                    card.classList.remove('in-order');
                    qtyEl.innerHTML = '';
                }
            });
        }

        function updateDisplay() {
            const container = document.getElementById('itemsContainer');
            const hidden    = document.getElementById('items');
            let html = '', total = 0;

            if (itemsData.length > 0) {
                html += '<table class="items-table"><thead><tr>';
                html += '<th>Menu</th><th style="text-align:center">Qty</th><th style="text-align:right">Harga Satuan</th><th style="text-align:right">Subtotal</th>';
                html += '</tr></thead><tbody>';
                itemsData.forEach(item => {
                    const sub = item.jumlah * item.hargaSatuan;
                    total += sub;
                    html += `<tr>
                        <td>${item.text}</td>
                        <td style="text-align:center">${item.jumlah}</td>
                        <td style="text-align:right">${fmt(item.hargaSatuan)}</td>
                        <td style="text-align:right">${fmt(sub)}</td>
                    </tr>`;
                });
                html += `<tr style="background:#e5e7eb;font-weight:bold">
                    <td colspan="3" style="text-align:right">TOTAL</td>
                    <td style="text-align:right">${fmt(total)}</td>
                </tr></tbody></table>`;
            } else {
                html = '<p style="color:#6b7280">Pilih menu di atas untuk menambahkan item.</p>';
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
                    <?php if (!empty($warningStok)): ?>
                        <div style="background:#fefce8;border:1px solid #fde047;border-radius:6px;padding:12px 14px;color:#713f12;margin-bottom:12px;font-size:13px">
                            <strong>⚠️ Peringatan Stok Rendah:</strong>
                            <ul style="margin:6px 0 0;padding-left:20px">
                                <?php foreach ($warningStok as $ws): ?>
                                    <li><?php echo $ws; ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <p style="margin:6px 0 0">Segera lakukan restock untuk bahan di atas.</p>
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
                                <label>Karyawan</label>
                                <input class="form-input" type="text" value="<?php echo htmlspecialchars($karyawanNama); ?>" readonly style="background:#f3f4f6;color:#6b7280;cursor:not-allowed;">
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

                        <!-- Pilih menu — card grid -->
                        <div style="margin-top:20px;padding:16px;background:#f0fdf4;border-radius:8px">
                            <h3 style="margin-top:0;margin-bottom:4px">Pilih Menu</h3>
                            <p style="margin:0 0 10px;font-size:13px;color:#6b7280">Klik kartu untuk menambah, gunakan tombol − / + untuk ubah jumlah.</p>
                            <input type="text" id="menuSearch" class="form-input" placeholder="🔍 Cari nama menu..." oninput="filterCards()" style="margin-bottom:12px;max-width:320px;">
                            <div class="menu-grid">
                                <?php foreach ($varianArr as $v):
                                    $vmNo  = (int)   $v['VarianMenuNo'];
                                    $harga = (float) $v['Harga'];
                                    $label = htmlspecialchars($v['nama_menu'] . ' - ' . $v['nama_ukuran']);
                                ?>
                                <div class="menu-card"
                                     data-id="<?php echo $vmNo; ?>"
                                     onclick="addFromCard(<?php echo $vmNo; ?>, <?php echo $harga; ?>, '<?php echo addslashes($label); ?>')">
                                    <div class="menu-card-name"><?php echo htmlspecialchars($v['nama_menu']); ?></div>
                                    <div class="menu-card-size"><?php echo htmlspecialchars($v['nama_ukuran']); ?></div>
                                    <div class="menu-card-price"><?php echo rupiah($harga); ?></div>
                                    <div class="menu-card-qty"></div>
                                </div>
                                <?php endforeach; ?>
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

            </div>
        </main>
    </div>
</body>
</html>
