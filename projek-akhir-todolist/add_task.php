<?php
require_once 'includes/auth_check.php';
$user_id = $_SESSION['user_id'];
require_once 'includes/config.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
    $task_description = isset($_POST['task_description']) ? trim($_POST['task_description']) : '';
    $due_date = isset($_POST['due_date']) && !empty($_POST['due_date']) ? $_POST['due_date'] : null;

    if ($category_id > 0 && !empty($task_description)) {

        $sql = "INSERT INTO tasks (category_id, user_id, task_description, due_date, is_done, created_at) VALUES (?, ?, ?, ?, 0, NOW())";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            // i = integer (category_id)
            // i = integer (user_id)
            // s = string (task_description)
            // s = string (due_date, bisa NULL jadi tetap 's')
            $types = "isss";

            // Bind parameter ke query (URUTAN HARUS SESUAI TANDA '?')
            $stmt->bind_param($types, $category_id, $user_id, $task_description, $due_date);

            // Eksekusi statement
            if ($stmt->execute()) {
                header("Location: index.php?status=task_success");
                exit;
            } else {
                $error_message = "Gagal menambahkan tugas: " . $stmt->error;
                header("Location: index.php?error=db_error&msg=" . urlencode($error_message));
                exit;
            }
            // Tutup statement
            $stmt->close();
        } else {
            // Gagal prepare statement
            $error_message = "Gagal mempersiapkan statement insert: " . $conn->error;
            header("Location: index.php?error=db_error&msg=" . urlencode($error_message));
            exit;
        }
    } else {
         // Data input tidak valid
         header("Location: index.php?error=task_invalid");
         exit;
    }
} else {
    // Jika bukan metode POST, redirect saja ke index
    header("Location: index.php");
    exit;
}

$conn->close();
?>