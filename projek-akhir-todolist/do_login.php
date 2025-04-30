<?php
require_once 'includes/config.php'; 

session_start();

$identifier = isset($_POST['identifier']) ? trim($_POST['identifier']) : ''; // Bisa username atau email
$password = isset($_POST['password']) ? $_POST['password'] : '';

if (empty($identifier) || empty($password)) {
    header("Location: login.php?error=empty");
    exit;
}


$sql = "SELECT id, username, password_hash FROM users WHERE username = ? OR email = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    // Error saat prepare statement
    header("Location: login.php?error=db_error&msg=prepare");
    exit;
}

$stmt->bind_param("ss", $identifier, $identifier);
$stmt->execute();
$result = $stmt->get_result();


if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();


    if (password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
   
        $stmt->close();
        $conn->close();

        header("Location: index.php");
        exit;

    } else {
        // Password tidak cocok
        $stmt->close();
        $conn->close();
        header("Location: login.php?error=invalid_credentials");
        exit;
    }
} else {
    // Pengguna tidak ditemukan (username/email salah)
    $stmt->close();
    $conn->close();
    header("Location: login.php?error=invalid_credentials");
    exit;
}

?>