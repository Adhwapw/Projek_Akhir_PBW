<?php
require_once 'includes/config.php'; 


$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$password_confirm = isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '';


if (empty($username) || empty($email) || empty($password) || empty($password_confirm)) {
    header("Location: register.php?error=empty");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: register.php?error=invalid_email");
    exit;
}

if ($password !== $password_confirm) {
    header("Location: register.php?error=password_mismatch");
    exit;
}

$sql_check = "SELECT id FROM users WHERE username = ? OR email = ?";
$stmt_check = $conn->prepare($sql_check);
if (!$stmt_check) {
    // Error saat prepare statement (masalah query/koneksi)
    header("Location: register.php?error=db_error&msg=prepare_check");
    exit;
}

$stmt_check->bind_param("ss", $username, $email);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    // Username atau Email sudah ada
    $existing_user = $result_check->fetch_assoc();
    if ($existing_user['username'] === $username) {
        header("Location: register.php?error=username_exists");
    } else {
        header("Location: register.php?error=email_exists");
    }
    $stmt_check->close();
    $conn->close();
    exit;
}
$stmt_check->close();

$password_hash = password_hash($password, PASSWORD_DEFAULT);
if ($password_hash === false) {
    // Error saat hashing
    header("Location: register.php?error=hash_error");
    exit;
}

$sql_insert = "INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)";
$stmt_insert = $conn->prepare($sql_insert);
if (!$stmt_insert) {
     header("Location: register.php?error=db_error&msg=prepare_insert");
     exit;
}

$stmt_insert->bind_param("sss", $username, $email, $password_hash);

if ($stmt_insert->execute()) {
    $stmt_insert->close();
    $conn->close();
    header("Location: login.php?status=register_success");
    exit;
} else {
    // Gagal menyimpan ke DB
    $stmt_insert->close();
    $conn->close();
    header("Location: register.php?error=db_error&msg=execute_insert");
    exit;
}

?>