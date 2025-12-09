<?php
session_start();
include 'db_connect.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$post_id = $conn->real_escape_string($_GET['id']);

// Handle comment submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
    $content = $conn->real_escape_string($_POST['comment']);
    $user_id = $_SESSION['user_id'];
    
    $sql = "INSERT INTO comments (post_id, user_id, content) VALUES ('$post_id', '$user_id', '$content')";
    if ($conn->query($sql) === TRUE) {
        $success = "Comment added successfully!";
    } else {
        $error = "Error adding comment: " . $conn->error;
    }
}

// Fetch post details
$sql = "SELECT posts.*, users.username 
        FROM posts 
        JOIN users ON posts.user_id = users.id 
        WHERE posts.id = '$post_id' AND posts.status = 'approved'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    header("Location: index.php");
    exit();
}

$post = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?> - University Safety Blog</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Safety Alert</h1>
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
                </ul>
            </nav>
        </header>
        <main>
            <article class="post full-post">
                <h2><?php echo htmlspecialchars($post['title']); ?></h2>
                <?php if ($post['image_path']): ?>
                    <img src="<?php echo htmlspecialchars($post['image_path']); ?>" alt="Post image" class="post-image">
                <?php endif; ?>
                <div class="post-meta">
                    Category: <?php echo htmlspecialchars($post['category']); ?> |
                    Posted by <?php echo htmlspecialchars($post['username']); ?> 
                    on <?php echo date('F j, Y', strtotime($post['created_at'])); ?>
                </div>
                <div class="post-content">
                    <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                </div>
            </article>

            <section class="comments">
                <h3>Comments</h3>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <form method="POST" action="" class="comment-form">
                        <?php 
                        if(isset($error)) echo "<div class='error'>$error</div>";
                        if(isset($success)) echo "<div class='success'>$success</div>";
                        ?>
                        <div class="form-group">
                            <label for="comment">Add a comment:</label>
                            <textarea id="comment" name="comment" rows="3" required></textarea>
                        </div>
                        <button type="submit">Post Comment</button>
                    </form>
                <?php else: ?>
                    <p>Please <a href="login.php">login</a> to comment.</p>
                <?php endif; ?>

                <?php
                $comments_sql = "SELECT comments.*, users.username 
                               FROM comments 
                               JOIN users ON comments.user_id = users.id 
                               WHERE post_id = '$post_id' 
                               ORDER BY created_at DESC";
                $comments_result = $conn->query($comments_sql);
                
                if ($comments_result->num_rows > 0) {
                    while($comment = $comments_result->fetch_assoc()) {
                        echo "<div class='comment'>";
                        echo "<p>" . nl2br(htmlspecialchars($comment['content'])) . "</p>";
                        echo "<div class='comment-meta'>Posted by " . htmlspecialchars($comment['username']) . 
                             " on " . date('F j, Y g:i A', strtotime($comment['created_at'])) . "</div>";
                        echo "</div>";
                    }
                } else {
                    echo "<p>No comments yet.</p>";
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