<?php
include 'db.php';
session_start();

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
$success = false;
$validToken = false;
$token = $_GET['token'] ?? '';

// Verify token if it exists in URL
if (!empty($token)) {
    $stmt = $conn->prepare("SELECT id, reset_expires FROM users WHERE reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (strtotime($user['reset_expires']) >= time()) {
            $validToken = true;
            $_SESSION['reset_user_id'] = $user['id'];
        } else {
            $error = "Reset link has expired";
        }
    } else {
        $error = "Invalid reset link";
    }
}

// Handle password reset form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && $validToken) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = "Invalid form submission";
    } else {
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (empty($password) || empty($confirm_password)) {
            $error = "Both password fields are required";
        } elseif (strlen($password) < 8) {
            $error = "Password must be at least 8 characters";
        } elseif ($password !== $confirm_password) {
            $error = "Passwords do not match";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $null = null;
            
            $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = ?, reset_expires = ? WHERE id = ?");
            $stmt->bind_param("sssi", $hashedPassword, $null, $null, $_SESSION['reset_user_id']);
            
            if ($stmt->execute()) {
                $success = true;
                unset($_SESSION['reset_user_id']);
            } else {
                $error = "Error updating password";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | Student Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-container">
        <div class="card">
            <h1 class="text-center"><i class="fas fa-key"></i> Reset Password</h1>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> Your password has been reset successfully!
                    <div class="mt-3 text-center">
                        <a href="login.php" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt"></i> Login Now
                        </a>
                    </div>
                </div>
            <?php elseif ($validToken): ?>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    
                    <div class="form-group">
                        <label for="password">New Password</label>
                        <div style="position: relative;">
                            <input type="password" id="password" name="password" class="form-control" 
                                   placeholder="Enter new password (min 8 chars)" required minlength="8">
                            <span toggle="#password" class="toggle-password" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer;">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                               placeholder="Confirm new password" required minlength="8">
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-save"></i> Reset Password
                    </button>
                </form>
            <?php else: ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i> Password reset link is invalid or expired
                    <div class="mt-3 text-center">
                        <a href="forgot_password.php" class="text-link">
                            <i class="fas fa-redo"></i> Request new reset link
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>
<?php
$conn->close();
?>
