<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
include 'db.php';

// Verify CSRF token
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $_SESSION['error'] = "Invalid form submission";
    header("Location: index.php");
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$name = trim($_POST['name']);
$age = (int)$_POST['age'];

// Validate inputs
if ($id <= 0) {
    $_SESSION['error'] = "Invalid student ID";
    header("Location: index.php");
    exit;
}

if (empty($name)) {
    $_SESSION['error'] = "Name is required";
    header("Location: edit.php?id=$id");
    exit;
} elseif (strlen($name) > 100) {
    $_SESSION['error'] = "Name must be less than 100 characters";
    header("Location: edit.php?id=$id");
    exit;
} elseif ($age <= 0 || $age > 120) {
    $_SESSION['error'] = "Age must be between 1 and 120";
    header("Location: edit.php?id=$id");
    exit;
}

// Update student
$stmt = $conn->prepare("UPDATE students SET name = ?, age = ? WHERE id = ?");
$stmt->bind_param("sii", $name, $age, $id);

if ($stmt->execute()) {
    $_SESSION['message'] = "Student updated successfully";
} else {
    $_SESSION['error'] = "Error updating student: " . $conn->error;
}

$stmt->close();
$conn->close();

header("Location: index.php");
exit;
?>
