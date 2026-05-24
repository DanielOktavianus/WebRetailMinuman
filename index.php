<?php require_once __DIR__ . '/config/app.php'; $b = APP_BASE; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SKRIPSIS8 - Progressive Web App untuk manajemen bisnis restoran">
    <meta name="theme-color" content="#667eea">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="SKRIPSIS8">
    <link rel="manifest" href="<?php echo $b; ?>/manifest.php">
    <link rel="icon" type="image/svg+xml" href="<?php echo $b; ?>/assets/img/icon-192.svg">
    <link rel="apple-touch-icon" href="<?php echo $b; ?>/assets/img/icon-192.svg">
    <title>SKRIPSIS8 - Management System</title>
    <link rel="stylesheet" href="<?php echo $b; ?>/assets/css/main.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .container {
            text-align: center;
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(2, 6, 23, 0.04);
            max-width: 600px;
        }
        .container h1 {
            font-size: 32px;
            margin-bottom: 12px;
            color: #667eea;
        }
        .container p {
            color: #6b7280;
            font-size: 16px;
            margin-bottom: 24px;
        }
        .btn-group {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            justify-content: center;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 8px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        .btn-secondary {
            background: #e5e7eb;
            color: #111827;
        }
        .btn-secondary:hover {
            background: #d1d5db;
        }
        #pwa-install-banner {
            position: fixed;
            bottom: 20px;
            left: 20px;
            right: 20px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            color: white;
            padding: 16px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            display: none;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        #pwa-install-banner p {
            margin: 0;
            flex: 1;
        }
        #pwa-install-banner button {
            background: white;
            color: #667eea;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            white-space: nowrap;
        }
        #pwa-install-banner button:hover {
            background: #f3f4f6;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 SKRIPSIS8</h1>
        <p>Progressive Web App untuk Manajemen Bisnis Restoran</p>

        <div class="btn-group">
            <a href="<?php echo $b; ?>/dashboard/dashboard.php" class="btn">Masuk ke Dashboard</a>
            <a href="<?php echo $b; ?>/auth/login.php" class="btn btn-secondary">Login</a>
        </div>

        <div style="margin-top: 32px; padding-top: 32px; border-top: 1px solid #e5e7eb;">
            <p style="color: #9ca3af; font-size: 13px;">
                💡 Tip: Aplikasi ini adalah PWA. Klik tombol menu untuk "Add to Home Screen" agar dapat diakses seperti aplikasi native.
            </p>
        </div>
    </div>

    <!-- PWA Install Banner -->
    <div id="pwa-install-banner">
        <p>Instal SKRIPSIS8 di perangkat Anda untuk akses lebih cepat!</p>
        <div>
            <button id="pwa-install-btn">Instal</button>
            <button id="pwa-close-banner" style="background: rgba(255,255,255,0.2); color: white; margin-left: 8px;">Tutup</button>
        </div>
    </div>

    <script>window.APP_BASE = "<?php echo APP_BASE; ?>";</script>
    <script src="<?php echo $b; ?>/assets/js/pwa.js"></script>
</body>
</html>
