<?php
require_once 'includes/config.php'; 


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_name = isset($_POST['category_name']) ? trim($_POST['category_name']) : '';

    if (!empty($category_name)) {
        // Gunakan Prepared Statements untuk keamanan
        $sql = "INSERT INTO categories (category_name) VALUES (?)";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("s", $category_name);

            // Eksekusi statement
            if ($stmt->execute()) {
                header("Location: index.php?status=category_success");
                exit; // Penting untuk menghentikan eksekusi setelah redirect
            } else {
                echo "Error: Gagal menambahkan kategori. " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Error: Gagal mempersiapkan statement. " . $conn->error;
        }
    } else {
        echo "Error: Nama kategori tidak boleh kosong.";
    }
} else {
    header("Location: index.php");
    exit;
}

$conn->close();
?>