<?php
require_once '../includes/auth_check.php';
$user_id = $_SESSION['user_id'];
require_once '../includes/config.php';

// Ambil ID tugas dan status baru
$task_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$new_status = isset($_GET['status']) ? (int)$_GET['status'] : 0;

// Validasi dasar ID tugas
if ($task_id > 0) {
    // Pastikan status hanya 0 atau 1
    $new_status = ($new_status === 1) ? 1 : 0;

    $sql = "UPDATE tasks SET is_done = ? WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        // i = integer (new_status)
        // i = integer (task_id)
        // i = integer (user_id)
        $types = "iii";

        $stmt->bind_param($types, $new_status, $task_id, $user_id);

        // Eksekusi statement
        if ($stmt->execute()) {
            header("Location: ../index.php?status=update_success");
            exit;
        } else {
            // Gagal eksekusi query
            $error_message = "Gagal memperbarui status tugas: " . $stmt->error;
            header("Location: ../index.php?error=db_error&msg=" . urlencode($error_message));
            exit;
        }
        // Tutup statement
        $stmt->close();
    } else {
        $error_message = "Gagal mempersiapkan statement update status: " . $conn->error;
        header("Location: ../index.php?error=db_error&msg=" . urlencode($error_message));
        exit;
    }
} else {
    header("Location: ../index.php?error=invalid_id");
    exit;
}

// Tutup koneksi
$conn->close();
?>