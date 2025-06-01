<?php
require_once '../includes/auth_check.php';
$user_id = $_SESSION['user_id'];
require_once '../includes/config.php';

define('MAX_TASK_DESC_LENGTH', 255); 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
    $task_description = isset($_POST['task_description']) ? trim($_POST['task_description']) : '';
    $due_date = isset($_POST['due_date']) && !empty($_POST['due_date']) ? $_POST['due_date'] : null;

    // Validasi input
    if ($category_id <= 0) {
        header("Location: ../index.php?error=task_invalid_category");
        exit;
    } elseif (empty($task_description)) {
        header("Location: ../index.php?error=task_invalid_desc_empty");
        exit;
    } elseif (mb_strlen($task_description) > MAX_TASK_DESC_LENGTH) {
        header("Location: ../index.php?error=task_desc_too_long&max=" . MAX_TASK_DESC_LENGTH);
        exit;
    } else {
        // Semua validasi lolos, lanjutkan proses insert
        $sql = "INSERT INTO tasks (category_id, user_id, task_description, due_date, is_done, created_at) VALUES (?, ?, ?, ?, 0, NOW())";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $types = "isss";
            $stmt->bind_param($types, $category_id, $user_id, $task_description, $due_date);

            if ($stmt->execute()) {
                header("Location: ../index.php?status=task_success");
                exit;
            } else {
                header("Location: ../index.php?error=db_error&msg=" . urlencode("Gagal menambahkan tugas: " . $stmt->error));
                exit;
            }
            $stmt->close();
        } else {
            header("Location: ../index.php?error=db_error&msg=" . urlencode("Gagal mempersiapkan statement insert: " . $conn->error));
            exit;
        }
    }
} else {
    header("Location: ../index.php");
    exit;
}

$conn->close();
?>