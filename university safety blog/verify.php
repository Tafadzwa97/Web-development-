<?php
session_start();
include 'db_connect.php';

if (isset($_GET['token'])) {
    $token = $conn->real_escape_string($_GET['token']);
    
    $sql = "SELECT id FROM users WHERE verification_token = '$token' AND is_verified = 0";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $update_sql = "UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = " . $user['id'];
        
        if ($conn->query($update_sql)) {
            $success = "Your email has been verified! You can now login.";
        } else {
            $error = "Verification failed. Please try again.";
        }
    } else {
        $error = "Invalid verification token or account already verified.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - University Safety Blog</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Email Verification</h1>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="login.php">Login</a></li>
                </ul>
            </nav>
        </header>
        <main>
            <div class="verification-status">
                <?php
                if (isset($success)) {
                    echo "<div class='success'>$success</div>";
                    echo "<p>Click <a href='login.php'>here</a> to login.</p>";
                } elseif (isset($error)) {
                    echo "<div class='error'>$error</div>";
                } else {
                    echo "<p>Please check your email for the verification link.</p>";
                }
                ?>
            </div>
        </main>
        <footer>
            <p>&copy; 2025 University Safety Blog</p>
        </footer>
    </div>
</body>
</html> 