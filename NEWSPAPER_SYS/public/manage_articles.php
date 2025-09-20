<?php
session_start();
require_once '../src/Auth.php';
require_once '../src/Article.php';
require_once '../src/Notification.php';

$auth = new Auth();
$article = new Article();
$notification = new Notification();

// Check if user is logged in and is an admin
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $article_id = $_POST['article_id'];

    if ($action === 'hide' && !empty($article_id)) {
        if ($article->hideArticle($article_id)) {
            // Get the author ID of the article
            $student_id = $article->getArticleAuthorId($article_id);
            $admin_id = $auth->getUserId();
            $message = "Your article has been removed by an admin.";
            
            // Create a notification for the student
            $notification->createNotification($student_id, $article_id, $admin_id, $message);

            // Redirect back to the admin dashboard with a success message
            header("Location: admin_dashboard.php?message=Article+hidden+successfully.");
            exit();
        } else {
            // Redirect with an error message
            header("Location: admin_dashboard.php?error=Failed+to+hide+article.");
            exit();
        }
    }
}

// If no valid action or data, redirect back
header("Location: admin_dashboard.php");
exit();
