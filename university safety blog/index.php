<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Safety Blog</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>University Safety Blog</h1>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li><a href="dashboard.php">Dashboard</a></li>
                        <li><a href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Register</a></li>
                    <?php endif; ?>
                    <?php if(isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                        <li><a href="admin.php">Admin Panel</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </header>
        <main>
            <div class="welcome-section">
                <h2>Welcome to the University Safety Blog</h2>
                <p>Help keep our campus safe by sharing important safety information and warnings.</p>
            </div>
            <?php
            include 'db_connect.php';
            
            // Display recent posts
            $sql = "SELECT posts.*, users.username FROM posts 
                   JOIN users ON posts.user_id = users.id 
                   ORDER BY created_at DESC LIMIT 5";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<div class='post'>";
                    echo "<h3><a href='post.php?id=" . $row['id'] . "'>" . htmlspecialchars($row['title']) . "</a></h3>";
                    // Show image if it exists
                    if (!empty($row['image_path'])) {
                        echo "<img src='" . htmlspecialchars($row['image_path']) . "' alt='Post image' class='post-image'>";
                    }
                    echo "<p>" . htmlspecialchars($row['content']) . "</p>";
                    echo "<div class='post-meta'>Posted by " . htmlspecialchars($row['username']) . 
                         " on " . date('F j, Y', strtotime($row['created_at'])) . "</div>";
                    echo "<a href='post.php?id=" . $row['id'] . "' class='read-more'>Read More</a>";
                    echo "</div>";
                }
            } else {
                echo "<p>No safety posts yet.</p>";
            }
            ?>
        </main>
        <footer>
            <p>&copy; 2025 University Safety Blog</p>
        </footer>
    </div>
</body>
</html> 