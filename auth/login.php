<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/auth_helper.php';
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Login — Aplikasi</title>
    <link rel="stylesheet" href="../assets/css/auth.css">
    <style>
        .demo-role-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
            margin-top: 16px;
            margin-bottom: 8px;
        }
        .demo-role-btn {
            background: #e2e8f0;
            color: #0f172a;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 10px 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .demo-role-btn:hover {
            background: #cbd5e1;
        }
        .demo-role-btn.primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border-color: transparent;
        }
        .demo-role-btn.primary:hover {
            opacity: 0.96;
        }
    </style>
</head>
<body class="auth-page">
    <div class="auth-hero">
        <div class="wrap">
            <div class="login-card">
                <div class="brand">
                    <h1>Selamat Datang</h1>
                    <p class="text-muted">Di Website Monitoring Pencatatan Penjualan</p>
                </div>

                <div class="demo-role-grid">
                    <form method="POST" action="process_login.php">
                        <input type="hidden" name="demo_role" value="admin">
                        <button class="demo-role-btn primary" type="submit">Masuk sebagai Admin</button>
                    </form>
                    <form method="POST" action="process_login.php">
                        <input type="hidden" name="demo_role" value="karyawan">
                        <button class="demo-role-btn" type="submit">Masuk sebagai Karyawan</button>
                    </form>
                </div>

                <form method="POST" action="process_login.php" autocomplete="off">
                    <label for="username">Username</label>
                    <input id="username" type="text" name="username" placeholder="Isi Username" required>

                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" placeholder="Isi Password" required>

                    <div class="actions">
                        <button type="submit">Masuk</button>
                    </div>
                    <p class="note">Jika lupa password, hubungi administrator.</p>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
