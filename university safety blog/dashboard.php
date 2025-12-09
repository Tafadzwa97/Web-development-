<?php
session_start();
include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle post submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $conn->real_escape_string($_POST['title']);
    $content = $conn->real_escape_string($_POST['content']);
    $category = $conn->real_escape_string($_POST['category']);
    $user_id = $_SESSION['user_id'];
    
    // Handle image upload
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        
        if (in_array(strtolower($filetype), $allowed)) {
            $new_filename = uniqid() . '.' . $filetype;
            $upload_path = 'uploads/' . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image_path = $upload_path;
            } else {
                $error = "Error uploading image.";
            }
        } else {
            $error = "Invalid file type. Only JPG, JPEG, PNG & GIF files are allowed.";
        }
    }
    
    if (!isset($error)) {
        $sql = "INSERT INTO posts (user_id, title, content, category, image_path) 
                VALUES ('$user_id', '$title', '$content', '$category', " . 
                ($image_path ? "'$image_path'" : "NULL") . ")";
        
        if ($conn->query($sql) === TRUE) {
            $success = "Post created successfully!";
        } else {
            $error = "Error: " . $conn->error;
        }
    }
}

// Fetch categories
$categories_sql = "SELECT name FROM categories ORDER BY name";
$categories_result = $conn->query($categories_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - University Safety Blog</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Dashboard</h1>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <?php if (isset(
                        $_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                        <li><a href="admin.php">Admin Panel</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </header>
        <main>
            <section class="create-post">
                <h2>Create New Safety Alert</h2>
                <form method="POST" action="" enctype="multipart/form-data">
                    <?php 
                    if(isset($error)) echo "<div class='error'>$error</div>";
                    if(isset($success)) echo "<div class='success'>$success</div>";
                    ?>
                    <div class="form-group">
                        <label for="title">Title:</label>
                        <input type="text" id="title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="category">Category:</label>
                        <select id="category" name="category" required>
                            <?php
                            while($category = $categories_result->fetch_assoc()) {
                                echo "<option value='" . htmlspecialchars($category['name']) . "'>" . 
                                     htmlspecialchars($category['name']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="content">Content:</label>
                        <textarea id="content" name="content" rows="5" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="image">Image (optional):</label>
                        <input type="file" id="image" name="image" accept="image/*">
                    </div>
                    <button type="submit">Post Alert</button>
                </form>
            </section>
            
            <section class="your-posts">
                <h2>Your Previous Posts</h2>
                <?php
                $user_id = $_SESSION['user_id'];
                $sql = "SELECT * FROM posts WHERE user_id = '$user_id' ORDER BY created_at DESC";
                $result = $conn->query($sql);
                
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<div class='post'>";
                        echo "<h3>" . htmlspecialchars($row['title']) . "</h3>";
                        if ($row['image_path']) {
                            echo "<img src='" . htmlspecialchars($row['image_path']) . "' alt='Post image' class='post-image'>";
                        }
                        echo "<p>" . htmlspecialchars($row['content']) . "</p>";
                        echo "<div class='post-meta'>";
                        echo "Category: " . htmlspecialchars($row['category']) . " | ";
                        echo "Status: " . htmlspecialchars($row['status']) . " | ";
                        echo "Posted on " . date('F j, Y', strtotime($row['created_at']));
                        echo "</div>";
                        echo "</div>";
                    }
                } else {
                    echo "<p>You haven't created any posts yet.</p>";
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