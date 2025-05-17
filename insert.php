<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
include 'db.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = "Invalid form submission";
    } else {
        $name = trim($_POST['name']);
        $age = (int)$_POST['age'];
        
        if (empty($name)) {
            $error = "Name is required";
        } elseif (strlen($name) > 100) {
            $error = "Name must be less than 100 characters";
        } elseif ($age <= 0 || $age > 120) {
            $error = "Age must be between 1 and 120";
        } else {
            $stmt = $conn->prepare("INSERT INTO students (name, age) VALUES (?, ?)");
            $stmt->bind_param("si", $name, $age);
            
            if ($stmt->execute()) {
                $_SESSION['message'] = "Student added successfully";
                header("Location: index.php");
                exit;
            } else {
                $error = "Error adding student: " . $conn->error;
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student | Student Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="form-container">
        <div class="card">
            <h1><i class="fas fa-user-plus"></i> Add New Student</h1>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="Enter student name" required>
                </div>
                
                <div class="form-group">
                    <label for="age">Age</label>
                    <input type="number" id="age" name="age" class="form-control" placeholder="Enter student age" min="1" max="120" required>
                </div>
                
                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                        <i class="fas fa-save"></i> Save Student
                    </button>
                    <a href="index.php" class="btn" style="flex: 1; background: #f0f0f0; color: var(--dark); text-align: center;">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>