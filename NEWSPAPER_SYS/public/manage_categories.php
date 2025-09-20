<?php
session_start();
require_once '../src/Auth.php';
require_once '../src/Category.php';

$auth = new Auth();

// Check if user is logged in and is an admin
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['category_name'])) {
    $categoryName = trim($_POST['category_name']);
    
    if (!empty($categoryName)) {
        $category = new Category();
        if ($category->addCategory($categoryName)) {
            header("Location: admin_dashboard.php?message=Category+added+successfully.");
            exit();
        } else {
            header("Location: admin_dashboard.php?error=Failed+to+add+category+or+category+already+exists.");
            exit();
        }
    } else {
        header("Location: admin_dashboard.php?error=Category+name+cannot+be+empty.");
        exit();
    }
}

header("Location: admin_dashboard.php");
exit();
