<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
include 'db.php';

// Generate CSRF token for search form
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle search and pagination
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 6; // Changed to use GET parameter
$offset = ($page - 1) * $limit;

// Get students data
$query = "SELECT * FROM students WHERE name LIKE ? ORDER BY name LIMIT ?, ?";
$stmt = $conn->prepare($query);
$like = "%$search%";
$stmt->bind_param("sii", $like, $offset, $limit);
$stmt->execute();
$result = $stmt->get_result();

// Get total count for pagination (important for correct page calculation)
$countStmt = $conn->prepare("SELECT COUNT(*) as count FROM students WHERE name LIKE ?");
$countStmt->bind_param("s", $like);
$countStmt->execute();
$countResult = $countStmt->get_result();
$total = $countResult->fetch_assoc()['count'];
$pages = ceil($total / $limit); // Now uses dynamic limit
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard | Modern SMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-card">
            <div class="dashboard-header">
                <h1 class="welcome-message">
                    <i class="fas fa-user-graduate"></i> Welcome, <?= htmlspecialchars($_SESSION['username']) ?>
                </h1>
                <a href="logout.php" class="btn" style="background: var(--danger); color: white; padding: 0.75rem 1.5rem;">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>

            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert" style="background: rgba(76, 201, 240, 0.1); color: #166534; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem; border-left: 4px solid var(--success);">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($_SESSION['message']) ?>
                    <?php unset($_SESSION['message']); ?>
                </div>
            <?php endif; ?>

            <div class="search-container">
                <form method="GET" style="display: flex; width: 100%; gap: 1rem;">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="text" class="search-input" name="search" placeholder="Search students..." 
                           value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i> Search
                    </button>
                </form>
            </div>
<div class="table-controls">
  <div class="entries-selector">
    <span>Show</span>
    <select id="entries-per-page">
      <option value="5" <?= $limit == 5 ? 'selected' : '' ?>>5</option>
      <option value="6" <?= $limit == 6 ? 'selected' : '' ?>>6</option>
      <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>10</option>
      <option value="25" <?= $limit == 25 ? 'selected' : '' ?>>25</option>
      <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50</option>
    </select>
    <span>entries</span>
  </div>
</div>

<script>
document.getElementById('entries-per-page').addEventListener('change', function() {
  const limit = this.value;
  window.location.href = `index.php?limit=${limit}`;
});
</script>
            <table class="student-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Age</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>#<?= htmlspecialchars($row['id']) ?></td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 1rem;">
                                        <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%); display: flex; align-items: center; justify-content: center; color: white;">
                                            <?= strtoupper(substr($row['name'], 0, 1)) ?>
                                        </div>
                                        <?= htmlspecialchars($row['name']) ?>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($row['age']) ?></td>
                                <td>
                                    <div class="action-btns">
                                        <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-delete">
                                            <i class="fas fa-trash-alt"></i> Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">
                                <div class="empty-state">
                                    <div class="empty-icon">
                                        <i class="fas fa-user-graduate"></i>
                                    </div>
                                    <h3 class="empty-title">No students found</h3>
                                    <p class="empty-description">Add your first student to get started</p>
                                    <a href="insert.php" class="btn" style="background: var(--primary); color: white; padding: 0.75rem 1.5rem;">
                                        <i class="fas fa-plus"></i> Add Student
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <a href="insert.php" class="add-student-btn">
        <i class="fas fa-plus"></i>
    </a>

    <script src="script.js"></script>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>