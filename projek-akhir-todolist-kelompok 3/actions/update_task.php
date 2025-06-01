<?php
require_once '../includes/auth_check.php';
$user_id = $_SESSION['user_id'];
require_once '../includes/config.php';

define('MAX_TASK_DESC_LENGTH', 255); 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_id = isset($_POST['task_id']) ? (int)$_POST['task_id'] : 0;
    $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
    $task_description = isset($_POST['task_description']) ? trim($_POST['task_description']) : '';
    $due_date = isset($_POST['due_date']) && !empty($_POST['due_date']) ? $_POST['due_date'] : null;

    // Validasi input
    if ($task_id <= 0) {
        header("Location: ../index.php?error=invalid_id");
        exit;
    } elseif ($category_id <= 0) {
        header("Location: ../edit_task.php?id=" . $task_id . "&error=task_invalid_category");
        exit;
    } elseif (empty($task_description)) {
        header("Location: ../edit_task.php?id=" . $task_id . "&error=task_invalid_desc_empty");
        exit;
    } elseif (mb_strlen($task_description) > MAX_TASK_DESC_LENGTH) {
        header("Location: ../edit_task.php?id=" . $task_id . "&error=task_desc_too_long&max=" . MAX_TASK_DESC_LENGTH);
        exit;
    } else {
        if ($due_date !== null) {
             $sql = "UPDATE tasks SET task_description = ?, category_id = ?, due_date = ? WHERE id = ? AND user_id = ?";
             $types = "sisii";
        } else {
             $sql = "UPDATE tasks SET task_description = ?, category_id = ?, due_date = NULL WHERE id = ? AND user_id = ?";
             $types = "siii";
        }

        $stmt = $conn->prepare($sql);

        if ($stmt) {
             if ($due_date !== null) {
                 $stmt->bind_param($types, $task_description, $category_id, $due_date, $task_id, $user_id);
             } else {
                 $stmt->bind_param($types, $task_description, $category_id, $task_id, $user_id);
             }

            if ($stmt->execute()) {
                header("Location: ../index.php?status=update_success");
                exit;
            } else {
                // Gagal eksekusi query
                $error_message = "Gagal memperbarui tugas: " . $stmt->error;
                // Redirect kembali ke form edit dengan pesan error database
                header("Location: ../edit_task.php?id=" . $task_id . "&error=db_error&msg=" . urlencode($error_message));
                exit;
            }
            $stmt->close();
        } else {
             // Gagal prepare statement
             $error_message = "Gagal mempersiapkan statement update: " . $conn->error;
             header("Location: ../edit_task.php?id=" . $task_id . "&error=db_error&msg=" . urlencode($error_message));
             exit;
        }
    }
} else {
    header("Location: ../index.php");
    exit;
}

$conn->close();
?>