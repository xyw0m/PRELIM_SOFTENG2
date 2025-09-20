<?php
session_start();
require_once '../src/Auth.php';
require_once '../src/Article.php';

$auth = new Auth();
$article = new Article();

$articles = $article->getAllPublishedArticles(); // Corrected method call

$isLoggedIn = $auth->isLoggedIn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EAC-Cavite Campus Newspaper</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header class="header">
        <div class="header-left">
            <h1 class="school-name">Emilio Aguinaldo College - Cavite Campus</h1>
        </div>
        <div class="header-right">
            <?php if ($isLoggedIn): ?>
                <a href="logout.php" class="login-button">Logout</a>
            <?php else: ?>
                <a href="login.php" class="login-button">Login</a>
            <?php endif; ?>
        </div>
    </header>

    <main class="container">
        <h2 class="section-title">Latest Articles</h2>

        <?php if (!empty($articles)): ?>
            <?php foreach ($articles as $article_data): ?>
                <article class="article">
                    <h3 class="article-title"><?php echo htmlspecialchars($article_data['title']); ?></h3>
                    <p class="article-meta">
                        <strong>Category: <?php echo htmlspecialchars($article_data['category_name'] ?? 'Uncategorized'); ?></strong> |
                        By <?php echo htmlspecialchars($article_data['author_name']); ?> |
                        <?php echo htmlspecialchars(date("F j, Y", strtotime($article_data['created_at']))); ?>
                    </p>
                    <?php if ($article_data['image_url']): ?>
                        <div class="article-image-container">
                            <img src="<?php echo htmlspecialchars($article_data['image_url']); ?>" alt="<?php echo htmlspecialchars($article_data['title']); ?>" class="article-image">
                        </div>
                    <?php endif; ?>
                    <p class="article-content"><?php echo nl2br(htmlspecialchars($article_data['content'])); ?></p>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center;">No articles have been posted yet. Be the first to add one!</p>
        <?php endif; ?>

    </main>

    <footer class="footer">
        <p>&copy; 2025 Emilio Aguinaldo College - Cavite Campus. All Rights Reserved.</p>
    </footer>

</body>
</html>
