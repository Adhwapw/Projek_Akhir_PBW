<?php
require_once 'includes/config.php'; 

$taskData = null;
$categories = [];
$errorMessage = '';
$taskId = 0;

if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT) && $_GET['id'] > 0) {
    $taskId = (int)$_GET['id'];

    $sql_task = "SELECT id, category_id, task_description, due_date FROM tasks WHERE id = ?"; // Ambil due_date jika ada
    $stmt_task = $conn->prepare($sql_task);

    if ($stmt_task) {
        $stmt_task->bind_param("i", $taskId);
        $stmt_task->execute();
        $result_task = $stmt_task->get_result();

        if ($result_task->num_rows === 1) {
            $taskData = $result_task->fetch_assoc();
        } else {
            $errorMessage = "Tugas dengan ID " . $taskId . " tidak ditemukan.";
        }
        $stmt_task->close();
    } else {
         $errorMessage = "Gagal mempersiapkan query untuk mengambil data tugas: " . $conn->error;
    }

    // 3. Jika tugas ditemukan, ambil semua kategori untuk dropdown
    if ($taskData) {
        $sql_categories = "SELECT id, category_name FROM categories ORDER BY category_name ASC";
        $result_categories = $conn->query($sql_categories);
        if ($result_categories && $result_categories->num_rows > 0) {
            while ($row = $result_categories->fetch_assoc()) {
                $categories[] = $row;
            }
        } else {
            $errorMessage = "Tidak dapat mengambil data kategori.";
        }
    }

} else {
    $errorMessage = "ID Tugas tidak valid atau tidak disediakan.";
}

if (!$taskData && !empty($errorMessage)) {

}

$conn->close(); // Tutup koneksi setelah selesai query
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

        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($errorMessage); ?></div>
            <p><a href="index.php">Kembali ke Daftar Tugas</a></p>
        <?php elseif ($taskData): // Hanya tampilkan form jika data tugas berhasil diambil ?>

            <div class="form-section">
                <form action="update_task.php" method="POST">
                    <input type="hidden" name="task_id" value="<?php echo htmlspecialchars($taskData['id']); ?>">

                    <label for="task_description">Deskripsi Tugas:</label>
                    <textarea id="task_description" name="task_description" rows="4" required><?php echo htmlspecialchars($taskData['task_description']); ?></textarea>

                    <label for="category_id">Pilih Kategori:</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo htmlspecialchars($category['id']); ?>"
                                <?php if ($category['id'] === $taskData['category_id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($category['category_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <?php 
                    if (isset($taskData['due_date'])): ?>
                        <label for="due_date">Tanggal Jatuh Tempo (Opsional):</label>
                        <input type="date" id="due_date" name="due_date" value="<?php echo htmlspecialchars($taskData['due_date']); ?>">
                    <?php endif; ?>

                    <button type="submit">Simpan Perubahan</button>
                    <a href="index.php" style="margin-left: 15px; color: var(--dark-gray);">Batal</a>
                </form>
            </div>

        <?php endif; ?>

    </div>
</body>
</html>