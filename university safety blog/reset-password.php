<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['email'])) {
        // Step 1: Request password reset
        $email = $conn->real_escape_string($_POST['email']);
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $sql = "UPDATE users SET reset_token = '$token', reset_token_expiry = '$expiry' 
                WHERE email = '$email' AND is_verified = 1";
        
        if ($conn->query($sql) && $conn->affected_rows > 0) {
            // In a real application, send email with reset link
            $reset_link = "http://localhost/safety-blog/reset-password.php?token=" . $token;
            $success = "Password reset instructions have been sent to your email.";
        } else {
            $error = "Email not found or account not verified.";
        }
    } elseif (isset($_POST['new_password']) && isset($_POST['token'])) {
        // Step 2: Process password reset
        $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $token = $conn->real_escape_string($_POST['token']);
        
        $sql = "UPDATE users SET password = '$new_password', reset_token = NULL, reset_token_expiry = NULL 
                WHERE reset_token = '$token' AND reset_token_expiry > NOW()";
        
        if ($conn->query($sql) && $conn->affected_rows > 0) {
            $success = "Password has been reset successfully. You can now login.";
        } else {
            $error = "Invalid or expired reset token.";
        }
    }
}

// Check if there's a reset token in the URL
$show_reset_form = false;
if (isset($_GET['token'])) {
    $token = $conn->real_escape_string($_GET['token']);
    $sql = "SELECT id FROM users WHERE reset_token = '$token' AND reset_token_expiry > NOW()";
    $result = $conn->query($sql);
    $show_reset_form = $result->num_rows > 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - University Safety Blog</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Reset Password</h1>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="login.php">Login</a></li>
                </ul>
            </nav>
        </header>
        <main>
            <?php if(isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if(isset($success)): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if ($show_reset_form): ?>
                <!-- Reset Password Form -->
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="new_password">New Password:</label>
                        <input type="password" id="new_password" name="new_password" required>
                    </div>
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">
                    <button type="submit">Reset Password</button>
                </form>
            <?php else: ?>
                <!-- Request Reset Form -->
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <button type="submit">Request Password Reset</button>
                </form>
            <?php endif; ?>
        </main>
        <footer>
            <p>&copy; 2024 University Safety Blog</p>
        </footer>
    </div>
</body>
</html> 