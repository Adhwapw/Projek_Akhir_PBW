<?php
session_start(); // Mulai session (walaupun belum login, mungkin perlu untuk pesan error)

// Jika sudah login, redirect ke index.php
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
    <title>Registrasi - Aplikasi ToDo List</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Style tambahan khusus halaman login/register */
        body {
            background-color: var(--light-gray); /* Latar abu */
        }
        .auth-container {
            max-width: 450px; /* Lebar form lebih kecil */
            margin: 50px auto; /* Margin atas lebih besar */
            padding: 30px;
            background-color: var(--card-bg);
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .auth-container h1 {
            text-align: center;
            margin-bottom: 25px;
            font-size: 1.8em;
        }
        .form-group {
            margin-bottom: 20px; /* Jarak antar field */
        }
        .form-group label {
            margin-bottom: 8px; /* Jarak label ke input */
        }
        .text-center {
            text-align: center;
            margin-top: 20px;
        }
        .text-center a {
            color: var(--primary-color);
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <h1>Registrasi Akun Baru</h1>

        <?php
        // Tampilkan pesan error jika ada dari proses sebelumnya
        if (isset($_GET['error'])) {
            $errorMessage = '';
            switch ($_GET['error']) {
                case 'empty': $errorMessage = 'Semua field harus diisi.'; break;
                case 'invalid_email': $errorMessage = 'Format email tidak valid.'; break;
                case 'password_mismatch': $errorMessage = 'Password dan konfirmasi password tidak cocok.'; break;
                case 'username_exists': $errorMessage = 'Username sudah digunakan, silakan pilih yang lain.'; break;
                case 'email_exists': $errorMessage = 'Email sudah terdaftar, silakan gunakan email lain.'; break;
                case 'db_error': $errorMessage = 'Terjadi masalah pada database. Coba lagi nanti.'; break;
                default: $errorMessage = 'Terjadi kesalahan tidak diketahui.';
            }
            echo '<div class="alert alert-error">' . htmlspecialchars($errorMessage) . '</div>';
        }
        ?>

        <form action="do_register.php" method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="password_confirm">Konfirmasi Password:</label>
                <input type="password" id="password_confirm" name="password_confirm" required>
            </div>
            <button type="submit">Daftar</button>
        </form>

        <p class="text-center">
            Sudah punya akun? <a href="login.php">Login di sini</a>
        </p>
    </div>
</body>
</html>