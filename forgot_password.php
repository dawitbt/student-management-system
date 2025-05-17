<?php
include 'db.php';
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = "Invalid form submission";
    } else {
        $email = trim($_POST['email']);
        
        if (empty($email)) {
            $error = "Email is required";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format";
        } else {
            // Debug: Show the email being searched
            error_log("Searching for email: " . $email);
            
            $stmt = $conn->prepare("SELECT id, username FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            // Debug: Show number of matches
            error_log("Found " . $result->num_rows . " matches for email: " . $email);
            
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                $token = bin2hex(random_bytes(32));
                $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));
                
                $updateStmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
                $updateStmt->bind_param("ssi", $token, $expires, $user['id']);
                
                if ($updateStmt->execute()) {
                    $success = "Password reset link has been sent to your email";
                    $_SESSION['reset_token'] = $token;
                    
                    // Debug: Show the generated token
                    error_log("Generated token: " . $token . " for user ID: " . $user['id']);
                } else {
                    $error = "Error processing your request: " . $conn->error;
                }
            } else {
                // More detailed error message
                $error = "No account found with " . htmlspecialchars($email) . ". Please check your email or register.";
                
                // Debug: List all emails in database for comparison
                $allEmails = $conn->query("SELECT email FROM users WHERE email IS NOT NULL");
                error_log("Existing emails in DB: " . print_r($allEmails->fetch_all(), true));
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
    <title>Forgot Password | Student Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-container">
        <div class="card">
            <h1 class="text-center"><i class="fas fa-key"></i> Password Recovery</h1>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
                    <!-- Demo only - in production, remove this line -->
                    <div class="demo-reset-link" style="margin-top: 1rem; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                        <small>DEMO LINK: <a href="reset_password.php?token=<?= $_SESSION['reset_token'] ?? '' ?>">Reset Password</a></small>
                    </div>
                </div>
            <?php else: ?>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="dawitbatala@gmail.com" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-paper-plane"></i> Send Reset Link
                    </button>
                    
                    <div class="mt-3 text-center">
                        <a href="login.php" class="text-link"><i class="fas fa-arrow-left"></i> Back to Login</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>
<?php
$conn->close();
?>
