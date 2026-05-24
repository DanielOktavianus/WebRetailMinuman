<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Login — Aplikasi</title>
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body class="auth-page">
    <div class="auth-hero">
        <div class="wrap">
            <div class="login-card">
                <div class="brand">
                    <h1>Selamat Datang</h1>
                    <p class="text-muted">Di Website Monitoring Pencatatan Penjualan</p>
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
