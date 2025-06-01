<?php
// Mulai session di awal untuk bisa akses $_SESSION dan menampilkan pesan
session_start();

// Jika sudah login, redirect ke index.php (tidak perlu ke halaman login lagi)
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Aplikasi ToDo List</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Style tambahan khusus halaman login/register (bisa disatukan di style.css nanti) */
        body { background-color: var(--light-gray); }
        .auth-container { max-width: 450px; margin: 50px auto; padding: 30px; background-color: var(--card-bg); border-radius: 8px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); }
        .auth-container h1 { text-align: center; margin-bottom: 25px; font-size: 1.8em; }
        .form-group { margin-bottom: 20px; }
        .form-group label { margin-bottom: 8px; }
        .text-center { text-align: center; margin-top: 20px; }
        .text-center a { color: var(--primary-color); text-decoration: underline; }
    </style>
</head>
<body>
    <div class="auth-container">
        <h1>Login</h1>

        <?php
        // Tampilkan pesan sukses atau error
        if (isset($_GET['status']) && $_GET['status'] === 'register_success') {
            echo '<div class="alert alert-success">Registrasi berhasil! Silakan login dengan akun baru Anda.</div>';
        }
        if (isset($_GET['logout']) && $_GET['logout'] === 'success') {
            echo '<div class="alert alert-success">Anda telah berhasil logout.</div>';
        }
        if (isset($_GET['error'])) {
            $errorMessage = '';
            switch ($_GET['error']) {
                case 'empty': $errorMessage = 'Username/Email dan Password harus diisi.'; break;
                case 'invalid_credentials': $errorMessage = 'Username/Email atau Password salah.'; break;
                case 'db_error': $errorMessage = 'Terjadi masalah saat login. Coba lagi nanti.'; break;
                default: $errorMessage = 'Terjadi kesalahan tidak diketahui.';
            }
            echo '<div class="alert alert-error">' . htmlspecialchars($errorMessage) . '</div>';
        }
         if (isset($_GET['restricted'])) {
             echo '<div class="alert alert-error">Anda harus login untuk mengakses halaman tersebut.</div>';
         }
        ?>

        <form action="actions/do_login.php" method="POST">
            <div class="form-group">
                <label for="identifier">Username atau Email:</label>
                <input type="text" id="identifier" name="identifier" required>
                </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>

        <p class="text-center">
            Belum punya akun? <a href="register.php">Daftar di sini</a>
        </p>
        
    </div>
</body>
</html>