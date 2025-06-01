<?php
require_once '../includes/config.php'; 

define('MAX_CATEGORY_NAME_LENGTH', 50); 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_name = isset($_POST['category_name']) ? trim($_POST['category_name']) : '';

    if (empty($category_name)) {
        header("Location: ../index.php?error=category_empty");
        exit;
    } elseif (mb_strlen($category_name) > MAX_CATEGORY_NAME_LENGTH) { 
        header("Location: ../index.php?error=category_too_long&max=" . MAX_CATEGORY_NAME_LENGTH);
        exit;
    } else {
        // Nama kategori valid, lanjutkan proses insert
        $sql = "INSERT INTO categories (category_name) VALUES (?)";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("s", $category_name);

            if ($stmt->execute()) {
                header("Location: ../index.php?status=category_success");
                exit;
            } else {
                header("Location: ../index.php?error=db_error&msg=" . urlencode("Gagal menambahkan kategori: " . $stmt->error));
                exit;
            }
            $stmt->close();
        } else {
            header("Location: ../index.php?error=db_error&msg=" . urlencode("Gagal mempersiapkan statement: " . $conn->error));
            exit;
        }
    }
} else {
    header("Location: ../index.php");
    exit;
}

$conn->close(); 
?>