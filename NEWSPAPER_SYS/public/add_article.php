<?php
session_start();
require_once '../src/Auth.php';
require_once '../src/Article.php';
require_once '../src/Category.php';

$auth = new Auth();
$category = new Category();

if (!$auth->isLoggedIn() || !$auth->isStudent()) {
    header("Location: login.php");
    exit();
}

$categories = $category->getAllCategories();

$message = '';
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $categoryId = $_POST['category_id'] ?? null;
    $imageUrl = null;
    $authorId = $auth->getUserId();

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $file_tmp_path = $_FILES['image']['tmp_name'];
        $file_name = $_FILES['image']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($file_ext, $allowed_ext)) {
            $new_file_name = uniqid() . '.' . $file_ext;
            $upload_path = 'uploads/' . $new_file_name;

            if (move_uploaded_file($file_tmp_path, $upload_path)) {
                $imageUrl = $upload_path;
            } else {
                $message = "Failed to upload image. Please try again.";
            }
        } else {
            $message = "Invalid file type. Only JPG, PNG, and GIF are allowed.";
        }
    }

    if (empty($message)) {
        $article = new Article();
        if ($article->addArticle($title, $content, $imageUrl, $authorId, $categoryId)) {
            $message = "Article submitted successfully! It is now published.";
        } else {
            $message = "Failed to submit article. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Article</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header class="header">
        <div class="header-left">
            <h1 class="school-name">Submit a New Article</h1>
        </div>
        <div class="header-right">
            <a href="logout.php" class="login-button">Logout</a>
        </div>
    </header>

    <main class="container">
        <h2 class="section-title">New Article Submission</h2>

        <?php if (!empty($message)): ?>
            <p class="success-message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <form action="add_article.php" method="post" enctype="multipart/form-data" class="article-form">
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" required>

            <label for="category_id">Category:</label>
            <select id="category_id" name="category_id" required>
                <option value="">Select a category</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat['category_id']); ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
                <?php endforeach; ?>
            </select>

            <label for="image">Optional Image:</label>
            <input type="file" id="image" name="image" accept="image/*">

            <label for="content">Context:</label>
            <textarea id="content" name="content" rows="10" required></textarea>

            <button type="submit" class="form-button">Submit Article</button>
        </form>
    </main>

    <footer class="footer">
        <p>&copy; 2025 Emilio Aguinaldo College - Cavite Campus. All Rights Reserved.</p>
    </footer>

</body>
</html>
