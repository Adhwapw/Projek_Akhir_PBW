<?php
require_once 'includes/auth_check.php';
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
require_once 'includes/config.php';

// Ambil Data Categories
$categories = [];
$sql_categories = "SELECT id, category_name FROM categories ORDER BY category_name ASC";
$result_categories = $conn->query($sql_categories);
if ($result_categories && $result_categories->num_rows > 0) {
    while ($row = $result_categories->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Ambil Data Tasks 
$tasks = [];

$sql_tasks = "SELECT tasks.id, tasks.category_id, tasks.task_description, tasks.is_done, tasks.created_at, tasks.due_date, categories.category_name
              FROM tasks
              JOIN categories ON tasks.category_id = categories.id
              WHERE tasks.user_id = ?  
              ORDER BY categories.category_name ASC, tasks.created_at DESC";

$stmt_tasks = $conn->prepare($sql_tasks);

if (!$stmt_tasks) {
    echo '<div class="alert alert-error">Gagal mempersiapkan query tugas: ' . htmlspecialchars($conn->error) . '</div>';
} else {
    $stmt_tasks->bind_param("i", $user_id);

    // Eksekusi query yang sudah diprepare
    if (!$stmt_tasks->execute()) {
        echo '<div class="alert alert-error">Gagal menjalankan query tugas: ' . htmlspecialchars($stmt_tasks->error) . '</div>';
        // error_log("Execute failed: (" . $stmt_tasks->errno . ") " . $stmt_tasks->error);
    } else {
        $result_tasks = $stmt_tasks->get_result();

        // Cek jika ada hasil
        if ($result_tasks->num_rows > 0) {
            while ($row = $result_tasks->fetch_assoc()) {
                $tasks[$row['category_name']][] = $row;
            }
        }
    }
    $stmt_tasks->close();
}

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Tugas Sederhana</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <h1>Daftar Tugas Sederhana</h1>
        <div style="text-align: right; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid var(--border-color);">
            Selamat datang, <strong><?php echo htmlspecialchars($username); ?></strong>!
            <a href="logout.php" class="logout-button">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-box-arrow-right" viewBox="0 0 16 16" style="vertical-align: -2px; margin-right: 4px;">
                    <path fill-rule="evenodd" d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0z" />
                    <path fill-rule="evenodd" d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708z" />
                </svg>
                Logout
            </a>
        </div>
        <div class="form-section">
            <h2>Tambah Kategori Baru</h2>
            <form action="add_category.php" method="POST">
                <label for="category_name">Nama Kategori:</label>
                <input type="text" id="category_name" name="category_name" required>
                <button type="submit">Tambah Kategori</button>
            </form>
        </div>

        <div class="form-section">
            <h2>Tambah Tugas Baru</h2>
            <form action="add_task.php" method="POST">
                <label for="category_id">Pilih Kategori:</label>
                <select id="category_id" name="category_id" required>
                    <option value="">-- Pilih Kategori --</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo htmlspecialchars($category['id']); ?>">
                            <?php echo htmlspecialchars($category['category_name']); ?>
                        </option>
                    <?php endforeach; ?>
                    <?php if (empty($categories)): ?>
                        <option value="" disabled>Belum ada kategori, silakan tambahkan dulu.</option>
                    <?php endif; ?>
                </select>

                <label for="task_description">Deskripsi Tugas:</label>
                <textarea id="task_description" name="task_description" rows="3" required></textarea>

                <label for="add_due_date">Tanggal Jatuh Tempo (Opsional):</label>
                <input type="date" id="add_due_date" name="due_date"> 
                <button type="submit" <?php echo empty($categories) ? 'disabled' : ''; ?>>Tambah Tugas</button>
            </form>
        </div>

        <h2>Daftar Tugas</h2>
        <?php if (!empty($tasks)): ?>
            <?php foreach ($tasks as $categoryName => $taskList):
                // Hitung jumlah tugas dalam kategori ini
                $taskCount = count($taskList);
                // Tentukan label (misal: "Tugas" atau "Tugas") - bisa disederhanakan
                $taskLabel = ($taskCount === 1) ? 'Tugas' : 'Tugas'; // Selalu pakai "Tugas" saja lebih mudah
            ?>
                <div class="category-block">
                    <h3>Kategori: <?php echo htmlspecialchars($categoryName); ?>
                        <span style="font-weight: normal; font-size: 0.9em; color: var(--text-muted); margin-left: 8px;">
                            (<?php echo $taskCount . ' ' . $taskLabel; ?>)
                        </span>
                    </h3>
                    <?php foreach ($taskList as $task): ?>
                        <div class="task-item <?php echo $task['is_done'] ? 'done' : ''; ?>">
                            <span class="task-description"><?php echo htmlspecialchars($task['task_description']); ?>
                                <?php
                                if (isset($task['due_date']) && !empty($task['due_date'])):
                                    // Format tanggal ke format 'DD Mon YYYY' (misal: 21 Apr 2025)
                                    try {
                                        $dateObject = new DateTime($task['due_date']);
                                        $formatter = new IntlDateFormatter('id_ID', IntlDateFormatter::LONG, IntlDateFormatter::NONE);
                                        $formatter->setPattern('d MMM yyyy'); // Format: 21 Apr 2025
                                        $dueDateFormatted = $formatter->format($dateObject);

                                        $isOverdue = !$task['is_done'] && (strtotime($task['due_date']) < strtotime('today'));
                                    } catch (Exception $e) {
                                        $dueDateFormatted = htmlspecialchars($task['due_date']);
                                        $isOverdue = false;
                                    }

                                ?>
                                    <small style="display: block; color: <?php echo $isOverdue ? '#dc3545' : '#6c757d'; /* Merah jika overdue, abu jika tidak */ ?>; margin-top: 5px; font-size: 0.85em;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-calendar-event" viewBox="0 0 16 16" style="margin-right: 3px; vertical-align: -1px;">
                                            <path d="M11 6.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5z" />
                                            <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z" />
                                        </svg>
                                        Jatuh Tempo: <?php echo $dueDateFormatted; ?>
                                        <?php if ($isOverdue) echo '<strong style="margin-left: 5px;">(Terlewat!)</strong>'; ?>
                                    </small>
                                <?php endif; ?>
                                <?php
                                ?>
                            </span>
                            <div class="task-actions"> <a href="edit_task.php?id=<?php echo $task['id']; ?>" class="edit-link">[Edit]</a>

                                <a href="mark_done.php?id=<?php echo $task['id']; ?>&status=<?php echo $task['is_done'] ? 0 : 1; ?>">
                                    [<?php echo $task['is_done'] ? 'Batal' : 'Selesai'; ?>]
                                </a>
                                <a href="delete_task.php?id=<?php echo $task['id']; ?>" onclick="return confirm('Yakin ingin menghapus tugas ini?');">[Hapus]</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Belum ada tugas.</p>
        <?php endif; ?>
    </div>
</body>

<?php
// --- Tampilkan Pesan Status/Error ---
$alertMessage = '';
$alertType = '';

if (isset($_GET['status'])) {
    $status = $_GET['status'];
    $alertType = 'success';
    if ($status === 'category_success') $alertMessage = 'Kategori berhasil ditambahkan!';
    elseif ($status === 'task_success') $alertMessage = 'Tugas berhasil ditambahkan!';
    elseif ($status === 'update_success') $alertMessage = 'Status tugas berhasil diperbarui!';
    elseif ($status === 'delete_success') $alertMessage = 'Tugas berhasil dihapus!';
} elseif (isset($_GET['error'])) {
    $error = $_GET['error'];
    $alertType = 'error';
    if ($error === 'category_empty') $alertMessage = 'Error: Nama kategori tidak boleh kosong.';
    elseif ($error === 'task_invalid') $alertMessage = 'Error: Kategori harus dipilih dan deskripsi tugas tidak boleh kosong.';
    elseif ($error === 'invalid_id') $alertMessage = 'Error: ID tugas atau status tidak valid.';
    elseif ($error === 'db_error') $alertMessage = 'Error: Terjadi masalah pada database.'; // Contoh error umum
    // Tambahkan pesan error lainnya jika perlu
}

if ($alertMessage) {
    $alertClass = ($alertType === 'success') ? 'alert-success' : 'alert-error';
    echo '<div class="alert ' . $alertClass . '">' . htmlspecialchars($alertMessage) . '</div>';
}
?>

</html>