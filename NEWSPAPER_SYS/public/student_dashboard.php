<?php
session_start();
require_once '../src/Auth.php';
require_once '../src/Article.php';
require_once '../src/Notification.php';
require_once '../src/EditRequest.php';

$auth = new Auth();
$article = new Article();
$notification = new Notification();
$editRequest = new EditRequest();

// Check if user is logged in
if (!$auth->isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Redirect admins to their dashboard
if ($auth->isAdmin()) {
    header("Location: admin_dashboard.php");
    exit();
}

// Get the user's articles, notifications, and edit requests
$userId = $auth->getUserId();
$myArticles = $article->getAllArticlesForStudentDashboard($userId);
$myNotifications = $notification->getNotificationsByUserId($userId);
$myEditRequests = $editRequest->getStudentRequests($userId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header class="header">
        <div class="header-left">
            <h1 class="school-name">Student Dashboard</h1>
        </div>
        <div class="header-right">
            <a href="logout.php" class="login-button">Logout</a>
        </div>
    </header>

    <main class="container">
        <h2 class="section-title">Welcome, Student!</h2>
        <p>This is the student dashboard. Here you can view your personal information and manage your submitted articles.</p>
        <p>Your user ID is: <strong><?php echo htmlspecialchars($userId); ?></strong></p>
        
        <div class="dashboard-actions">
            <a href="add_article.php" class="form-button">Submit a New Article</a>
        </div>
        
        ---
        
        <h2 class="section-title">My Notifications</h2>
        <?php if (!empty($myNotifications)): ?>
            <div class="notifications-list">
                <?php foreach ($myNotifications as $notif): ?>
                    <div class="notification-item">
                        <p><strong><?php echo htmlspecialchars($notif['message']); ?></strong></p>
                        <p>Article Title: <em>"<?php echo htmlspecialchars($notif['article_title']); ?>"</em></p>
                        <p>Action taken by: <?php echo htmlspecialchars($notif['admin_name']); ?></p>
                        <p class="notification-date"><?php echo htmlspecialchars(date("F j, Y, g:i a", strtotime($notif['created_at']))); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="text-align: center;">You have no new notifications.</p>
        <?php endif; ?>

        ---
        
        <h2 class="section-title">My Edit Requests</h2>
        <?php if (!empty($myEditRequests)): ?>
            <div class="edit-requests-list">
                <?php foreach ($myEditRequests as $request): ?>
                    <div class="request-item status-<?php echo strtolower($request['status']); ?>">
                        <p><strong>Request ID: <?php echo htmlspecialchars($request['request_id']); ?></strong></p>
                        <p>Original Title: <em>"<?php echo htmlspecialchars($request['original_title']); ?>"</em></p>
                        <p>New Title: <em>"<?php echo htmlspecialchars($request['new_title']); ?>"</em></p>
                        <p>Status: <span class="status-badge status-<?php echo htmlspecialchars($request['status']); ?>"><?php echo htmlspecialchars($request['status']); ?></span></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="text-align: center;">You have no pending edit requests.</p>
        <?php endif; ?>

        ---
        
        <h2 class="section-title">My Posted Articles</h2>
        
        <?php if (!empty($myArticles)): ?>
            <?php foreach ($myArticles as $article_data): ?>
                <article class="article">
                    <h3 class="article-title">
                        <?php echo htmlspecialchars($article_data['title']); ?>
                        <?php if ($article_data['status'] === 'hidden'): ?>
                            <span style="color: red; font-size: 0.8em;">(Removed by Admin)</span>
                        <?php endif; ?>
                    </h3>
                    <p class="article-meta">
                        <strong>Category: <?php echo htmlspecialchars($article_data['category_name'] ?? 'Uncategorized'); ?></strong> |
                        <small>Posted on <?php echo htmlspecialchars(date("F j, Y", strtotime($article_data['created_at']))); ?></small>
                    </p>
                    <?php if ($article_data['image_url']): ?>
                        <div class="article-image-container">
                            <img src="<?php echo htmlspecialchars($article_data['image_url']); ?>" alt="<?php echo htmlspecialchars($article_data['title']); ?>" class="article-image">
                        </div>
                    <?php endif; ?>
                    <p class="article-content"><?php echo nl2br(htmlspecialchars($article_data['content'])); ?></p>
                    <div class="article-actions">
                         <a href="edit_article.php?id=<?php echo $article_data['article_id']; ?>" class="form-button edit-button">Edit Article</a>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center;">You have not posted any articles yet.</p>
        <?php endif; ?>
    </main>

    <footer class="footer">
        <p>&copy; 2025 Emilio Aguinaldo College - Cavite Campus. All Rights Reserved.</p>
    </footer>

</body>
</html>
