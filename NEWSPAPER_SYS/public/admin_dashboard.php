<?php
session_start();
require_once '../src/Auth.php';
require_once '../src/Article.php';
require_once '../src/EditRequest.php';
require_once '../src/Category.php';

$auth = new Auth();
$article = new Article();
$editRequest = new EditRequest();
$category = new Category();

// Check if user is logged in and is an admin
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    header("Location: login.php");
    exit();
}

$articles = $article->getAllAdminArticles();
$pendingRequests = $editRequest->getPendingRequests();
$categories = $category->getAllCategories();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .article-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .article-table th, .article-table td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
            vertical-align: top;
        }
        .article-table th {
            background-color: #f2f2f2;
            font-size: 1.1em;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 12px;
            font-size: 0.8em;
            color: #fff;
            font-weight: bold;
        }
        .status-published { background-color: #28a745; }
        .status-hidden { background-color: #dc3545; }
        .status-pending { background-color: #ffc107; }
        .status-approved { background-color: #28a745; }
        .status-rejected { background-color: #dc3545; }
        .article-actions .remove-button {
            background-color: #ff4d4d;
            color: white;
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .article-actions .remove-button:hover {
            background-color: #cc0000;
        }
        .edit-request-buttons button {
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            color: white;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .edit-request-buttons .approve-button {
            background-color: #28a745;
        }
        .edit-request-buttons .approve-button:hover {
            background-color: #1e7e34;
        }
        .edit-request-buttons .reject-button {
            background-color: #dc3545;
        }
        .edit-request-buttons .reject-button:hover {
            background-color: #c82333;
        }
        .request-details {
            display: flex;
            gap: 20px;
        }
        .request-details p {
            margin: 0;
            flex: 1;
        }
        .request-details h4 {
            margin-top: 0;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>

    <header class="header">
        <div class="header-left">
            <h1 class="school-name">Admin Dashboard</h1>
        </div>
        <div class="header-right">
            <a href="logout.php" class="login-button">Logout</a>
        </div>
    </header>

    <main class="container">
        <h2 class="section-title">Welcome, Admin!</h2>
        <p>This is the administrator dashboard. Here you can manage all articles and edit requests.</p>
        <p>Your user ID is: <strong><?php echo htmlspecialchars($auth->getUserId()); ?></strong></p>
        
        ---
        
        <h2 class="section-title">Add New Category</h2>
        <form action="manage_categories.php" method="post" class="article-form">
            <label for="category_name">Category Name:</label>
            <input type="text" id="category_name" name="category_name" required>
            <button type="submit" class="form-button">Add Category</button>
        </form>

        ---
        
        <h2 class="section-title">Pending Edit Requests</h2>
        <?php if (!empty($pendingRequests)): ?>
            <div class="pending-requests-list">
                <?php foreach ($pendingRequests as $request): ?>
                    <div class="request-item">
                        <h3>Edit Request from <?php echo htmlspecialchars($request['user_name']); ?></h3>
                        <p><strong>Original Article:</strong> <?php echo htmlspecialchars($request['original_title']); ?></p>
                        
                        <div class="request-details">
                            <p>
                                <h4>Original Content:</h4>
                                <?php echo nl2br(htmlspecialchars($request['original_content'])); ?>
                            </p>
                            <p>
                                <h4>Proposed New Content:</h4>
                                <?php echo nl2br(htmlspecialchars($request['new_content'])); ?>
                            </p>
                        </div>
                        <div class="request-details">
                            <p>
                                <h4>Proposed New Category:</h4>
                                <?php echo htmlspecialchars($request['new_category_name'] ?? 'N/A'); ?>
                            </p>
                        </div>

                        <div class="edit-request-buttons">
                            <form action="process_edit_request.php" method="post" style="display:inline;">
                                <input type="hidden" name="request_id" value="<?php echo $request['request_id']; ?>">
                                <button type="submit" name="action" value="approve" class="approve-button">Approve</button>
                            </form>
                            <form action="process_edit_request.php" method="post" style="display:inline;">
                                <input type="hidden" name="request_id" value="<?php echo $request['request_id']; ?>">
                                <button type="submit" name="action" value="reject" class="reject-button">Reject</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="text-align: center;">There are no pending edit requests at this time.</p>
        <?php endif; ?>

        ---
        
        <h2 class="section-title">Manage Articles</h2>

        <table class="article-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Author</th>
                    <th>Date Posted</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($articles)): ?>
                    <?php foreach ($articles as $article_data): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($article_data['title']); ?></td>
                            <td><?php echo htmlspecialchars($article_data['category_name'] ?? 'Uncategorized'); ?></td>
                            <td><?php echo htmlspecialchars($article_data['author_name']); ?></td>
                            <td><?php echo htmlspecialchars(date("F j, Y", strtotime($article_data['created_at']))); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo htmlspecialchars($article_data['status']); ?>">
                                    <?php echo htmlspecialchars($article_data['status']); ?>
                                </span>
                            </td>
                            <td class="article-actions">
                                <?php if ($article_data['status'] !== 'hidden'): ?>
                                    <form action="manage_articles.php" method="post" onsubmit="return confirm('Are you sure you want to hide this article?');">
                                        <input type="hidden" name="article_id" value="<?php echo $article_data['article_id']; ?>">
                                        <button type="submit" name="action" value="hide" class="remove-button">Remove</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">No articles have been posted yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>

    <footer class="footer">
        <p>&copy; 2025 Emilio Aguinaldo College - Cavite Campus. All Rights Reserved.</p>
    </footer>

</body>
</html>
