<?php
session_start();
include 'db_connect.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: login.php");
    exit();
}

// Handle post moderation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $post_id = $conn->real_escape_string($_POST['post_id']);
    
    if ($_POST['action'] == 'approve') {
        $sql = "UPDATE posts SET status = 'approved' WHERE id = '$post_id'";
    } elseif ($_POST['action'] == 'reject') {
        $sql = "UPDATE posts SET status = 'rejected' WHERE id = '$post_id'";
    } elseif ($_POST['action'] == 'delete') {
        $sql = "DELETE FROM posts WHERE id = '$post_id'";
    }
    
    if ($conn->query($sql)) {
        $success = "Post updated successfully!";
    } else {
        $error = "Error updating post: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - University Safety Blog</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Admin Panel</h1>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="logout.php">Logout</a></li>
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

            <section class="pending-posts">
                <h2>Pending Posts</h2>
                <?php
                $sql = "SELECT posts.*, users.username 
                        FROM posts 
                        JOIN users ON posts.user_id = users.id 
                        WHERE posts.status = 'pending' 
                        ORDER BY created_at DESC";
                $result = $conn->query($sql);
                
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<div class='post admin-post'>";
                        echo "<h3>" . htmlspecialchars($row['title']) . "</h3>";
                        echo "<p>" . htmlspecialchars($row['content']) . "</p>";
                        if ($row['image_path']) {
                            echo "<img src='" . htmlspecialchars($row['image_path']) . "' alt='Post image' class='post-image'>";
                        }
                        echo "<div class='post-meta'>Posted by " . htmlspecialchars($row['username']) . 
                             " on " . date('F j, Y', strtotime($row['created_at'])) . "</div>";
                        echo "<div class='admin-actions'>";
                        echo "<form method='POST' action='' style='display: inline;'>";
                        echo "<input type='hidden' name='post_id' value='" . $row['id'] . "'>";
                        echo "<button type='submit' name='action' value='approve' class='approve-btn'>Approve</button>";
                        echo "<button type='submit' name='action' value='reject' class='reject-btn'>Reject</button>";
                        echo "<button type='submit' name='action' value='delete' class='delete-btn'>Delete</button>";
                        echo "</form>";
                        echo "</div>";
                        echo "</div>";
                    }
                } else {
                    echo "<p>No pending posts.</p>";
                }
                ?>
            </section>

            <section class="user-management">
                <h2>User Management</h2>
                <?php
                $sql = "SELECT id, username, email, is_verified, is_admin, created_at 
                        FROM users ORDER BY created_at DESC";
                $result = $conn->query($sql);
                
                if ($result->num_rows > 0) {
                    echo "<table class='user-table'>";
                    echo "<tr><th>Username</th><th>Email</th><th>Status</th><th>Role</th><th>Joined</th></tr>";
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                        echo "<td>" . ($row['is_verified'] ? 'Verified' : 'Unverified') . "</td>";
                        echo "<td>" . ($row['is_admin'] ? 'Admin' : 'User') . "</td>";
                        echo "<td>" . date('F j, Y', strtotime($row['created_at'])) . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p>No users found.</p>";
                }
                ?>
            </section>
        </main>
        <footer>
            <p>&copy; 2025 University Safety Blog</p>
        </footer>
    </div>
</body>
</html> 