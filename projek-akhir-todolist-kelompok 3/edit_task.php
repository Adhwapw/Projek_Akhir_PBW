<?php
require_once 'includes/auth_check.php'; 
require_once 'includes/config.php'; 

// Inisialisasi variabel pesan error untuk form ini
$formErrorMessage = '';
$formErrorType = ''; 

if (isset($_GET['error'])) {
    $error = $_GET['error'];
    $formErrorType = 'error'; 
    if ($error === 'task_invalid_category') {
        $formErrorMessage = 'Error: Kategori tugas harus dipilih.';
    } elseif ($error === 'task_invalid_desc_empty') {
        $formErrorMessage = 'Error: Deskripsi tugas tidak boleh kosong.';
    } elseif ($error === 'task_desc_too_long') {
        $maxLength = isset($_GET['max']) ? (int)$_GET['max'] : 255; // Default
        $formErrorMessage = 'Error: Deskripsi tugas terlalu panjang. Maksimal ' . $maxLength . ' karakter.';
    } elseif ($error === 'db_error') {
        $formErrorMessage = 'Error: Terjadi masalah saat menyimpan perubahan.';
        if (isset($_GET['msg']) && !empty(trim($_GET['msg']))) {
            error_log("DB Error on edit_task.php (from update_task.php): " . urldecode($_GET['msg']));
        }
    } elseif ($error === 'invalid_input') { // Menangkap error generic dari update_task.php 
         $formErrorMessage = 'Error: Input tidak valid. Pastikan semua field terisi dengan benar.';
    }
}


$taskData = null;
$categories = [];
$pageLoadErrorMessage = ''; 
$taskId = 0;

if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT) && $_GET['id'] > 0) {
    $taskId = (int)$_GET['id'];
    if (empty($formErrorMessage)) { 
        // Periksa apakah task ini milik user yang login
        $sql_task = "SELECT id, category_id, task_description, due_date FROM tasks WHERE id = ? AND user_id = ?"; 
        $stmt_task = $conn->prepare($sql_task);

        if ($stmt_task) {
            $stmt_task->bind_param("ii", $taskId, $_SESSION['user_id']); 
            $stmt_task->execute();
            $result_task = $stmt_task->get_result();

            if ($result_task->num_rows === 1) {
                $taskData = $result_task->fetch_assoc();
            } else {
                // Tugas tidak ditemukan atau bukan milik user ini
                $pageLoadErrorMessage = "Tugas dengan ID " . $taskId . " tidak ditemukan atau Anda tidak memiliki izin untuk mengeditnya.";
                $taskData = null; // Pastikan taskData null agar form tidak tampil
            }
            $stmt_task->close();
        } else {
            $pageLoadErrorMessage = "Gagal mempersiapkan query untuk mengambil data tugas: " . $conn->error;
        }
    } elseif ($taskId > 0) {
    }


    if ($taskId > 0) { 
        $sql_categories = "SELECT id, category_name FROM categories ORDER BY category_name ASC";
        $result_categories = $conn->query($sql_categories);
        if ($result_categories && $result_categories->num_rows > 0) {
            while ($row = $result_categories->fetch_assoc()) {
                $categories[] = $row;
            }
        } else {
            if(empty($formErrorMessage)) $pageLoadErrorMessage .= (empty($pageLoadErrorMessage) ? '' : ' ') . "Tidak dapat mengambil data kategori.";
        }
    }

} else {
    $pageLoadErrorMessage = "ID Tugas tidak valid atau tidak disediakan.";
}


?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Tugas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Edit Tugas</h1>

        <?php if (!empty($formErrorMessage)): ?>
            <div class="alert alert-<?php echo $formErrorType; ?>"><?php echo htmlspecialchars($formErrorMessage); ?></div>
        <?php endif; ?>

        <?php if (!empty($pageLoadErrorMessage)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($pageLoadErrorMessage); ?></div>
            <?php if (!$taskData):  ?>
            <p><a href="index.php">Kembali ke Daftar Tugas</a></p>
            <?php endif; ?>
        <?php endif; ?>
        

        <?php if ($taskId > 0 && !empty($categories) && $taskData): // Tampilkan form hanya jika data tugas berhasil diambil ?>
            <div class="form-section">
                <form action="actions/update_task.php" method="POST">
                    <input type="hidden" name="task_id" value="<?php echo htmlspecialchars($taskData['id']); ?>">

                    <label for="task_description">Deskripsi Tugas:</label>
                    <textarea id="task_description" name="task_description" rows="4" required><?php echo htmlspecialchars($taskData['task_description']); ?></textarea>

                    <label for="category_id">Pilih Kategori:</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php foreach ($categories as $category_item): ?>
                            <option value="<?php echo htmlspecialchars($category_item['id']); ?>"
                                <?php if ($taskData && $category_item['id'] === $taskData['category_id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($category_item['category_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label for="due_date">Tanggal Jatuh Tempo (Opsional):</label>
                    <input type="date" id="due_date" name="due_date" value="<?php echo htmlspecialchars($taskData['due_date'] ?? ''); ?>">
                    
                    <button type="submit">Simpan Perubahan</button>
                    <a href="index.php" style="margin-left: 15px; color: var(--dark-gray);">Batal</a>
                </form>
            </div>
        <?php elseif ($taskId > 0 && empty($pageLoadErrorMessage) && empty($formErrorMessage)): ?>
            <?php 
                  if(empty($taskData)) echo '<p><a href="index.php">Kembali ke Daftar Tugas</a></p>';
            ?>
        <?php endif; ?>

    </div>
</body>
</html>
<?php if ($conn) $conn->close(); // Tutup koneksi di akhir ?>