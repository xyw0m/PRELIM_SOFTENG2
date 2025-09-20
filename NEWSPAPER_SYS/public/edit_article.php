<?php
session_start();
require_once '../src/Auth.php';
require_once '../src/Article.php';
require_once '../src/EditRequest.php';
require_once '../src/Category.php';

$auth = new Auth();
$article = new Article();
$category = new Category();

if (!$auth->isLoggedIn() || !$auth->isStudent()) {
    header("Location: login.php");
    exit();
}

$articleId = $_GET['id'] ?? null;
$articleData = null;

if ($articleId) {
    $articleData = $article->getArticleById($articleId);
    
    // Check if the logged-in student is the author of the article
    if ($articleData && $articleData['author_id'] !== $auth->getUserId()) {
        echo "<p class='error-message'>You do not have permission to edit this article.</p>";
        exit();
    }
} else {
    echo "<p class='error-message'>No article selected for editing.</p>";
    exit();
}

$categories = $category->getAllCategories();

// Handle form submission for editing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newTitle = $_POST['title'] ?? '';
    $newContent = $_POST['content'] ?? '';
    $newCategoryId = $_POST['category_id'] ?? null;
    $newImageUrl = $articleData['image_url']; // Default to old image URL

    // Handle image upload if a new one is provided
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $file_tmp_path = $_FILES['image']['tmp_name'];
        $file_name = $_FILES['image']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($file_ext, $allowed_ext)) {
            $new_file_name = uniqid() . '.' . $file_ext;
            $upload_path = 'uploads/' . $new_file_name;

            if (move_uploaded_file($file_tmp_path, $upload_path)) {
                $newImageUrl = $upload_path;
            } else {
                $message = "Failed to upload image. Please try again.";
            }
        } else {
            $message = "Invalid file type. Only JPG, PNG, and GIF are allowed.";
        }
    }

    $editRequest = new EditRequest();
    if ($editRequest->createRequest($articleId, $auth->getUserId(), $newTitle, $newContent, $newImageUrl, $newCategoryId)) {
        $message = "Your edit request has been submitted and is pending admin approval.";
    } else {
        $message = "Failed to submit edit request.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Article</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header class="header">
        <div class="header-left">
            <h1 class="school-name">Edit Article</h1>
        </div>
        <div class="header-right">
            <a href="logout.php" class="login-button">Logout</a>
        </div>
    </header>

    <main class="container">
        <h2 class="section-title">Edit Your Article</h2>

        <?php if (isset($message)): ?>
            <p class="success-message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <?php if ($articleData): ?>
            <form action="edit_article.php?id=<?php echo $articleId; ?>" method="post" enctype="multipart/form-data" class="article-form">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($articleData['title']); ?>" required>

                <label for="category_id">Category:</label>
                <select id="category_id" name="category_id" required>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat['category_id']); ?>" <?php if ($articleData['category_id'] == $cat['category_id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($cat['category_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="image">Optional Image (will replace old image):</label>
                <input type="file" id="image" name="image" accept="image/*">
                
                <?php if ($articleData['image_url']): ?>
                    <p style="text-align: center;">Current Image:</p>
                    <img src="<?php echo htmlspecialchars($articleData['image_url']); ?>" alt="Current Article Image" style="max-width: 300px; display: block; margin: 0 auto 20px;">
                <?php endif; ?>

                <label for="content">Context:</label>
                <textarea id="content" name="content" rows="10" required><?php echo htmlspecialchars($articleData['content']); ?></textarea>

                <button type="submit" class="form-button">Submit Edit Request</button>
            </form>
        <?php else: ?>
            <p style="text-align: center;">Article not found.</p>
        <?php endif; ?>
    </main>

    <footer class="footer">
        <p>&copy; 2025 Emilio Aguinaldo College - Cavite Campus. All Rights Reserved.</p>
    </footer>

</body>
</html>
