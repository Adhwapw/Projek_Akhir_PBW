<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

//koneksi database
$db_host = 'localhost'; 
$db_user = 'root';      
$db_pass = '';          
$db_name = 'todo_list_db'; 

// koneksi  MySQLi
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Cek koneksi
if ($conn->connect_error) {
    // Hentikan script dan tampilkan pesan error jika koneksi gagal
    die("Koneksi ke database gagal: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

?>