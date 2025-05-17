<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
include 'db.php';

// Verify CSRF token if coming from a form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['error'] = "Invalid form submission";
        header("Location: index.php");
        exit;
    }
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // First check if student exists
    $checkStmt = $conn->prepare("SELECT id FROM students WHERE id = ?");
    $checkStmt->bind_param("i", $id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows === 1) {
        $deleteStmt = $conn->prepare("DELETE FROM students WHERE id = ?");
        $deleteStmt->bind_param("i", $id);
        
        if ($deleteStmt->execute()) {
            $_SESSION['message'] = "Student deleted successfully";
        } else {
            $_SESSION['error'] = "Error deleting student";
        }
        
        $deleteStmt->close();
    } else {
        $_SESSION['error'] = "Student not found";
    }
    
    $checkStmt->close();
} else {
    $_SESSION['error'] = "Invalid student ID";
}

header("Location: index.php");
exit;
?>
