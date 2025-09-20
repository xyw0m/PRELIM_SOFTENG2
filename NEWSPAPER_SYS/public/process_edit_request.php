<?php
session_start();
require_once '../src/Auth.php';
require_once '../src/EditRequest.php';

$auth = new Auth();
$editRequest = new EditRequest();

// Check if user is logged in and is an admin
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['request_id'])) {
    $action = $_POST['action'];
    $requestId = $_POST['request_id'];
    $adminId = $auth->getUserId();

    if ($action === 'approve') {
        if ($editRequest->approveRequest($requestId, $adminId)) {
            header("Location: admin_dashboard.php?message=Edit+request+approved+successfully.");
            exit();
        } else {
            header("Location: admin_dashboard.php?error=Failed+to+approve+edit+request.");
            exit();
        }
    } elseif ($action === 'reject') {
        if ($editRequest->rejectRequest($requestId, $adminId)) {
            header("Location: admin_dashboard.php?message=Edit+request+rejected.");
            exit();
        } else {
            header("Location: admin_dashboard.php?error=Failed+to+reject+edit+request.");
            exit();
        }
    }
}

header("Location: admin_dashboard.php");
exit();
