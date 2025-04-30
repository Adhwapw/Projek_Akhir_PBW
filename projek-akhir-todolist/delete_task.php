<?php
require_once 'includes/auth_check.php';
$user_id = $_SESSION['user_id'];
require_once 'includes/config.php';
$task_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($task_id > 0) {
    $sql = "DELETE FROM tasks WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        // i = integer (task_id)
        // i = integer (user_id)
        $types = "ii";

        // Bind parameter ke query (URUTAN SESUAI '?')
        $stmt->bind_param($types, $task_id, $user_id);

        // Eksekusi statement
        if ($stmt->execute()) {
            header("Location: index.php?status=delete_success");
            exit;
        } else {
            $error_message = "Gagal menghapus tugas: " . $stmt->error;
            header("Location: index.php?error=db_error&msg=" . urlencode($error_message));
            exit;
        }
        $stmt->close();
    } else {
        // Gagal prepare statement
        $error_message = "Gagal mempersiapkan statement delete: " . $conn->error;
        header("Location: index.php?error=db_error&msg=" . urlencode($error_message));
        exit;
    }
} else {
    header("Location: index.php?error=invalid_id");
    exit;
}
$conn->close();
?>