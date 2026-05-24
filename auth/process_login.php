<?php
require_once '../config/database.php';

// Session already started in database.php (safe check)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Basic input validation
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if ($username === '' || $password === '') {
    echo "Username atau password kosong.";
    exit;
}

// Prepared statement to avoid SQL injection
$stmt = mysqli_prepare($conn, "SELECT usernameNo, username, password, role FROM data_user WHERE username = ? LIMIT 1");
if (!$stmt) {
    die('Prepare failed: ' . mysqli_error($conn));
}
mysqli_stmt_bind_param($stmt, 's', $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if (!$result) {
    die('Query failed: ' . mysqli_error($conn));
}
$user = mysqli_fetch_assoc($result);

if ($user) {
    $stored = $user['password'];
    $authenticated = false;

    // Prefer verifying hashed passwords. If DB currently stores plain-text,
    // allow fallback and migrate the password to a secure hash.
    if (password_verify($password, $stored)) {
        $authenticated = true;
    } elseif ($password === $stored) {
        $authenticated = true;
        // Re-hash plain-text password and update DB
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $upd = mysqli_prepare($conn, "UPDATE data_user SET password = ? WHERE username = ?");
        if ($upd) {
            mysqli_stmt_bind_param($upd, 'ss', $newHash, $username);
            mysqli_stmt_execute($upd);
            mysqli_stmt_close($upd);
        }
    }

    if ($authenticated) {
        $_SESSION['login'] = true;
        $_SESSION['username'] = $user['username'];
        $_SESSION['usernameNo'] = $user['usernameNo'];
        $_SESSION['role'] = $user['role'] ?? 'karyawan'; // Default role: karyawan
        header('Location: ../dashboard/dashboard.php');
        exit;
    }
}

echo "Login gagal, silakan ulangi.";
?>
