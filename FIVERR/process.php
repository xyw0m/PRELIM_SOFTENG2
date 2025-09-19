<?php
session_start();

include_once __DIR__ . '/includes/db.config.php';
include_once __DIR__ . '/includes/class.User.php';
include_once __DIR__ . '/includes/class.Proposal.php';
include_once __DIR__ . '/includes/class.Offer.php';
include_once __DIR__ . '/includes/class.Admin.php';

$user = new User($pdo);
$proposalHandler = new Proposal($pdo);
$offerHandler = new Offer($pdo);
$adminHandler = new Admin($pdo);

// --- Handle Registration ---
if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if (empty($username) || empty($email) || empty($password) || empty($role)) {
        header("Location: index.php?error=registration_failed");
        exit();
    }

    if ($user->register($username, $email, $password, $role)) {
        header("Location: index.php?success=registered");
    } else {
        header("Location: index.php?error=registration_failed");
    }
    exit();
}

// --- Handle Login ---
if (isset($_POST['login'])) {
    $identifier = trim($_POST['identifier']);
    $password = $_POST['password'];

    if (empty($identifier) || empty($password)) {
        header("Location: index.php?login_error=invalid_credentials");
        exit();
    }

    $loggedInUser = $user->login($identifier, $password);

    if ($loggedInUser) {
        $_SESSION['user_id'] = $loggedInUser['id'];
        $_SESSION['username'] = $loggedInUser['username'];
        $_SESSION['role'] = $loggedInUser['role'];
        $_SESSION['base_role'] = $loggedInUser['role']; // Store the original role
        header("Location: dashboard.php");
    } else {
        header("Location: index.php?login_error=invalid_credentials");
    }
    exit();
}

// --- Handle Proposal Posting ---
if (isset($_POST['post_proposal'])) {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'freelancer') {
        header("Location: index.php?error=unauthorized_access");
        exit();
    }

    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $categoryId = $_POST['category'];
    $subcategoryId = $_POST['subcategory'];
    $userId = $_SESSION['user_id'];

    if (empty($title) || empty($description) || empty($categoryId) || empty($subcategoryId)) {
        header("Location: dashboard.php?error=empty_fields");
        exit();
    }

    if ($proposalHandler->createProposal($userId, $title, $description, $categoryId, $subcategoryId)) {
        header("Location: dashboard.php?success=proposal_posted");
    } else {
        header("Location: dashboard.php?error=proposal_failed");
    }
    exit();
}

// --- Handle Offer Submission ---
if (isset($_POST['submit_offer'])) {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
        header("Location: index.php?error=unauthorized_access");
        exit();
    }

    $proposalId = $_POST['proposal_id'];
    $message = trim($_POST['message']);
    $clientId = $_SESSION['user_id'];

    if (empty($proposalId) || empty($message)) {
        header("Location: dashboard.php?error=empty_fields");
        exit();
    }
    
    if ($offerHandler->hasClientAlreadyOffered($proposalId, $clientId)) {
        header("Location: dashboard.php?error=offer_already_submitted");
        exit();
    }

    if ($offerHandler->createOffer($proposalId, $clientId, $message)) {
        header("Location: dashboard.php?success=offer_submitted");
    } else {
        header("Location: dashboard.php?error=offer_failed");
    }
    exit();
}

// --- Handle Admin Actions (Add Category & Subcategory) ---
if (isset($_POST['add_category'])) {
    if (!isset($_SESSION['user_id']) || $_SESSION['base_role'] !== 'fiverr_administrator') {
        header("Location: dashboard.php?error=unauthorized_access");
        exit();
    }
    $categoryName = trim($_POST['category_name']);
    if (empty($categoryName)) {
        header("Location: dashboard.php?error=empty_category_name");
        exit();
    }
    if ($adminHandler->addCategory($categoryName)) {
        header("Location: dashboard.php?success=category_added");
    } else {
        header("Location: dashboard.php?error=category_failed");
    }
    exit();
}

if (isset($_POST['add_subcategory'])) {
    if (!isset($_SESSION['user_id']) || $_SESSION['base_role'] !== 'fiverr_administrator') {
        header("Location: dashboard.php?error=unauthorized_access");
        exit();
    }
    $categoryId = $_POST['category_id'];
    $subcategoryName = trim($_POST['subcategory_name']);
    if (empty($categoryId) || empty($subcategoryName)) {
        header("Location: dashboard.php?error=empty_subcategory_fields");
        exit();
    }
    if ($adminHandler->addSubcategory($categoryId, $subcategoryName)) {
        header("Location: dashboard.php?success=subcategory_added");
    } else {
        header("Location: dashboard.php?error=subcategory_failed");
    }
    exit();
}

// --- Handle Role Switching ---
if (isset($_GET['switch_role'])) {
    if (isset($_SESSION['base_role']) && $_SESSION['base_role'] === 'fiverr_administrator') {
        $targetRole = $_GET['switch_role'];
        if ($targetRole === 'client') {
            $_SESSION['role'] = 'client';
            header("Location: dashboard.php");
        } else {
            $_SESSION['role'] = 'fiverr_administrator';
            header("Location: dashboard.php");
        }
    }
    exit();
}

// --- Handle Logout ---
if (isset($_GET['logout'])) {
    session_destroy();
    session_unset();
    header("Location: index.php");
    exit();
}

header("Location: index.php");
exit();
