<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek jika pengguna belum login, redirect ke login.php
if (!isset($_SESSION['user_id'])) {
    $loginPage = '/projek-akhir-todolist/login.php?restricted=1';
    header("Location: " . $loginPage);
    exit;
}
