<?php
require_once 'includes/auth_check.php';
$user_id = $_SESSION['user_id'];
require_once 'includes/config.php';

// Pastikan request adalah POST (dari form edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Ambil data dari form dan validasi dasar
    $task_id = isset($_POST['task_id']) ? (int)$_POST['task_id'] : 0;
    $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
    $task_description = isset($_POST['task_description']) ? trim($_POST['task_description']) : '';
    // Ambil due_date jika ada, set ke NULL jika dikosongkan di form
    $due_date = isset($_POST['due_date']) && !empty($_POST['due_date']) ? $_POST['due_date'] : null;

    // 2. Validasi lebih lanjut (ID > 0, kategori dipilih, deskripsi tidak kosong)
    if ($task_id > 0 && $category_id > 0 && !empty($task_description)) {

        // 3. Siapkan Query UPDATE dengan Prepared Statements
        // Query disesuaikan tergantung apakah due_date diisi atau tidak
        if ($due_date !== null) {
             // Query jika due_date diisi
             $sql = "UPDATE tasks SET task_description = ?, category_id = ?, due_date = ? WHERE id = ? AND user_id = ?"; // <<< Tambah AND user_id = ?
             // Tipe data untuk bind_param: s(desc), i(cat_id), s(date), i(task_id), i(user_id)
             $types = "sisii";
        } else {
             // Query jika due_date dikosongkan (set ke NULL)
             $sql = "UPDATE tasks SET task_description = ?, category_id = ?, due_date = NULL WHERE id = ? AND user_id = ?"; // <<< Tambah AND user_id = ?
             // Tipe data untuk bind_param: s(desc), i(cat_id), i(task_id), i(user_id)
             $types = "siii";
        }

        $stmt = $conn->prepare($sql);

        if ($stmt) {
             // Bind parameter sesuai urutan '?' dalam query dan tipe data
             if ($due_date !== null) {
                 // !!! MODIFIKASI BIND PARAM: Tambahkan user_id di akhir !!!
                 $stmt->bind_param($types, $task_description, $category_id, $due_date, $task_id, $user_id);
             } else {
                 // !!! MODIFIKASI BIND PARAM: Tambahkan user_id di akhir !!!
                 $stmt->bind_param($types, $task_description, $category_id, $task_id, $user_id);
             }

            // 4. Eksekusi statement
            if ($stmt->execute()) {
                // Berhasil. Jika ID tidak cocok DENGAN user_id, tidak ada error,
                // hanya saja tidak ada baris yang terupdate (affected_rows = 0).
                // Redirect tetap dianggap sukses dari sisi pengguna.
                header("Location: index.php?status=update_success");
                exit;
            } else {
                // Gagal eksekusi query
                $error_message = "Gagal memperbarui tugas: " . $stmt->error;
                header("Location: index.php?error=db_error&msg=" . urlencode($error_message));
                exit;
            }
            // Tutup statement
            $stmt->close();
        } else {
             // Gagal prepare statement
             $error_message = "Gagal mempersiapkan statement update: " . $conn->error;
             header("Location: index.php?error=db_error&msg=" . urlencode($error_message));
             exit;
        }
    } else {
         // Data input tidak valid (misal deskripsi dikosongkan)
         // Kembalikan ke form edit dengan pesan error
         header("Location: edit_task.php?id=" . $task_id . "&error=invalid_input"); // Sertakan ID agar form bisa load ulang
         exit;
    }
} else {
    // Jika bukan metode POST, redirect ke index
    header("Location: index.php");
    exit;
}

// Tutup koneksi
$conn->close();
?>