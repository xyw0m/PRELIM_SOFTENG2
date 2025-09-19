<?php
session_start();

define('ROOT_PATH', __DIR__ . '/');

if (!isset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['role'], $_SESSION['base_role'])) {
    session_destroy();
    header("Location: " . ROOT_PATH . "index.php");
    exit();
}

include_once ROOT_PATH . 'includes/db.config.php';
include_once ROOT_PATH . 'includes/class.User.php';
include_once ROOT_PATH . 'includes/class.Proposal.php';
include_once ROOT_PATH . 'includes/class.Offer.php';
include_once ROOT_PATH . 'includes/class.Admin.php';
include_once ROOT_PATH . 'includes/class.Category.php';

$user = new User($pdo);
$proposalHandler = new Proposal($pdo);
$offerHandler = new Offer($pdo);
$adminHandler = new Admin($pdo);
$categoryHandler = new Category($pdo);

$username = $_SESSION['username'];
$role = $_SESSION['role'];
$base_role = $_SESSION['base_role'];

$categories = $categoryHandler->getAllCategories();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FIVERR Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" xintegrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAWiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <style>
        body {
            padding-top: 70px;
        }
        .dropdown-submenu {
            position: absolute;
            left: 100%;
            top: -7px;
        }
        .dropdown-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
</head>
<body>
    <?php include_once ROOT_PATH . 'navbar.php'; ?>

    <div class="container">
        <div class="dashboard-card">
            <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
            <p>You have successfully logged in as a **<?php echo htmlspecialchars($role); ?>**.</p>
            <p>This is your dashboard. The content you see below is determined by your user role.</p>

            <?php
            if (isset($_GET['success'])) {
                $message = '';
                switch ($_GET['success']) {
                    case 'offer_submitted':
                        $message = 'Offer submitted successfully!';
                        break;
                    case 'proposal_posted':
                        $message = 'Proposal posted successfully!';
                        break;
                    case 'category_added':
                        $message = 'Category added successfully!';
                        break;
                    case 'subcategory_added':
                        $message = 'Subcategory added successfully!';
                        break;
                }
                if (!empty($message)) {
                    echo '<div class="message-box success-message">' . htmlspecialchars($message) . '</div>';
                }
            }
            if (isset($_GET['error'])) {
                $message = '';
                switch ($_GET['error']) {
                    case 'offer_already_submitted':
                        $message = 'You have already submitted an offer for this proposal.';
                        break;
                    case 'unauthorized_access':
                        $message = 'You do not have permission to perform this action.';
                        break;
                    case 'empty_fields':
                        $message = 'Please fill out all required fields.';
                        break;
                    case 'category_failed':
                        $message = 'Failed to add category. It may already exist.';
                        break;
                    case 'subcategory_failed':
                        $message = 'Failed to add subcategory.';
                        break;
                }
                if (!empty($message)) {
                    echo '<div class="message-box error-message">' . htmlspecialchars($message) . '</div>';
                }
            }
            ?>

            <div class="user-info">
                <?php if ($role === 'fiverr_administrator'): ?>
                    <h2>Administrator Dashboard</h2>
                    <p>As an administrator, you can manage the platform's content and can assume the client role to browse proposals.</p>

                    <div class="role-switcher">
                        <p>Currently logged in as: **<?php echo htmlspecialchars($role); ?>**</p>
                        <a href="process.php?switch_role=client" class="btn btn-primary btn-sm">Switch to Client Role</a>
                    </div>

                    <div class="admin-section">
                        <h3>Add New Category</h3>
                        <form action="process.php" method="POST">
                            <input type="text" name="category_name" class="form-control" placeholder="Category Name" required>
                            <button type="submit" name="add_category" class="btn btn-success mt-2">Add Category</button>
                        </form>
                    </div>

                    <div class="admin-section">
                        <h3>Add New Subcategory</h3>
                        <form action="process.php" method="POST">
                            <select name="category_id" class="form-control" required>
                                <option value="">Select a Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat['id']); ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="text" name="subcategory_name" class="form-control mt-2" placeholder="Subcategory Name" required>
                            <button type="submit" name="add_subcategory" class="btn btn-success mt-2">Add Subcategory</button>
                        </form>
                    </div>

                <?php elseif ($role === 'freelancer'): ?>
                    <h2>Freelancer Dashboard</h2>
                    <p>Here you can manage your gigs, view client requests, and track your earnings. Get ready to show off your skills and get hired!</p>

                    <div class="proposal-section">
                        <h3>Post a New Proposal</h3>
                        <form action="process.php" method="POST">
                            <input type="text" name="title" class="form-control" placeholder="Proposal Title" required>
                            <textarea name="description" class="form-control mt-2" placeholder="Describe your service..." rows="4" required></textarea>
                            <select id="category-select" name="category" class="form-control mt-2" required>
                                <option value="">Select a Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat['id']); ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select id="subcategory-select" name="subcategory" class="form-control mt-2" required disabled>
                                <option value="">Select a Subcategory</option>
                            </select>
                            <button type="submit" name="post_proposal" class="btn btn-success mt-2">Post Proposal</button>
                        </form>

                        <div class="my-proposals">
                            <h3>My Proposals</h3>
                            <?php
                            $myProposals = $proposalHandler->getProposalsByUserId($_SESSION['user_id']);
                            if (count($myProposals) > 0) {
                                foreach ($myProposals as $proposal) {
                                    $hasOffer = $offerHandler->hasOffer($proposal['id']);
                                    $category = (isset($proposal['category_id'])) ? $categoryHandler->getCategoryById($proposal['category_id']) : null;
                                    $subcategory = (isset($proposal['subcategory_id'])) ? $categoryHandler->getSubcategoryById($proposal['subcategory_id']) : null;

                                    echo '<div class="proposal-card">';
                                    echo '<div class="card-header">';
                                    echo '<h4>' . htmlspecialchars($proposal['title']) . '</h4>';
                                    echo '<span class="date">' . date("M d, Y", strtotime($proposal['created_at'])) . '</span>';
                                    echo '</div>';

                                    $categoryName = ($category && isset($category['name'])) ? htmlspecialchars($category['name']) : 'Uncategorized';
                                    $subcategoryName = ($subcategory && isset($subcategory['name'])) ? htmlspecialchars($subcategory['name']) : 'N/A';
                                    echo '<p><b>Category:</b> ' . $categoryName . ' > ' . $subcategoryName . '</p>';

                                    echo '<p>' . nl2br(htmlspecialchars($proposal['description'])) . '</p>';
                                    if ($hasOffer) {
                                        echo '<div class="offer-status-badge">Offer Submitted!</div>';
                                    }
                                    echo '</div>';
                                }
                            } else {
                                echo '<p>You have not posted any proposals yet.</p>';
                            }
                            ?>
                        </div>
                    </div>

                <?php else: ?>
                    <h2>Client Dashboard</h2>
                    <?php if ($base_role === 'fiverr_administrator'): ?>
                        <div class="role-switcher">
                            <a href="process.php?switch_role=admin" class="btn btn-primary btn-sm">Switch back to Admin Role</a>
                        </div>
                    <?php endif; ?>
                    <p>Welcome, Client! From here, you can post new projects, browse freelancer profiles, and review proposals. Find the perfect talent for your needs.</p>
                    
                    <div class="proposal-section">
                        <h3>
                            <?php 
                            $categoryId = $_GET['category_id'] ?? null;
                            $subcategoryId = $_GET['subcategory_id'] ?? null;
                            $proposals = $proposalHandler->getFilteredProposals($categoryId, $subcategoryId);

                            $heading_text = "All Proposals";
                            if ($categoryId) {
                                $cat_info = $categoryHandler->getCategoryById($categoryId);
                                if ($cat_info) {
                                    $heading_text = "Proposals in: " . htmlspecialchars($cat_info['name']);
                                }
                            } elseif ($subcategoryId) {
                                $subcat_info = $categoryHandler->getSubcategoryById($subcategoryId);
                                if ($subcat_info) {
                                    $heading_text = "Proposals in: " . htmlspecialchars($subcat_info['name']);
                                }
                            }
                            echo $heading_text;
                            ?>
                        </h3>
                        <?php
                        if (count($proposals) > 0) {
                            foreach ($proposals as $proposal) {
                                $clientHasOffered = $offerHandler->hasClientAlreadyOffered($proposal['id'], $_SESSION['user_id']);
                                $category = (isset($proposal['category_id'])) ? $categoryHandler->getCategoryById($proposal['category_id']) : null;
                                $subcategory = (isset($proposal['subcategory_id'])) ? $categoryHandler->getSubcategoryById($proposal['subcategory_id']) : null;

                                echo '<div class="proposal-card" data-has-offered="' . ($clientHasOffered ? 'true' : 'false') . '">';
                                echo '<div class="card-header">';
                                echo '<h4>' . htmlspecialchars($proposal['title']) . '</h4>';
                                echo '<p class="freelancer-name">Posted by: ' . htmlspecialchars($proposal['username'] ?? 'Unknown User') . '</p>';
                                echo '</div>';
                                
                                $categoryName = ($category && isset($category['name'])) ? htmlspecialchars($category['name']) : 'Uncategorized';
                                $subcategoryName = ($subcategory && isset($subcategory['name'])) ? htmlspecialchars($subcategory['name']) : 'N/A';
                                echo '<p><b>Category:</b> ' . $categoryName . ' > ' . $subcategoryName . '</p>';

                                echo '<p>' . nl2br(htmlspecialchars($proposal['description'])) . '</p>';
                                echo '<span class="date">' . date("M d, Y", strtotime($proposal['created_at'])) . '</span>';
                                
                                if (!$clientHasOffered) {
                                    echo '<form action="process.php" method="POST" class="offer-form">';
                                    echo '<input type="hidden" name="proposal_id" value="' . htmlspecialchars($proposal['id']) . '">';
                                    echo '<textarea name="message" placeholder="Your offer message..." rows="3" required></textarea>';
                                    echo '<button type="submit" name="submit_offer">Submit Offer</button>';
                                    echo '</form>';
                                } else {
                                    echo '<div class="offer-status-badge already-sent">Offer Already Sent</div>';
                                }
                                echo '</div>';
                            }
                        } else {
                            echo '<p>No proposals have been posted yet. Check back soon!</p>';
                        }
                        ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div id="offer-modal" class="modal-overlay">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <p>You have already sent an offer for this proposal.</p>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" xintegrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" xintegrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" xintegrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const proposalCards = document.querySelectorAll('.proposal-card');
        const modal = document.getElementById('offer-modal');
        const closeBtn = document.querySelector('.close-btn');

        proposalCards.forEach(card => {
            card.addEventListener('click', (event) => {
                const hasOffered = card.getAttribute('data-has-offered');
                
                const isFormElement = event.target.tagName === 'TEXTAREA' || event.target.tagName === 'BUTTON';
                if (isFormElement) {
                    return;
                }
                
                if (hasOffered === 'true') {
                    modal.style.display = 'flex';
                }
            });
        });

        closeBtn.addEventListener('click', () => {
            modal.style.display = 'none';
        });

        window.addEventListener('click', (event) => {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });

        const categorySelect = document.getElementById('category-select');
        const subcategorySelect = document.getElementById('subcategory-select');
        const categories = <?php echo json_encode($categories); ?>;
        
        categorySelect.addEventListener('change', async (e) => {
            const categoryId = e.target.value;
            subcategorySelect.innerHTML = '<option value="">Loading...</option>';
            subcategorySelect.disabled = true;

            if (categoryId) {
                const url = `get_subcategories.php?category_id=${categoryId}`;
                try {
                    const response = await fetch(url);
                    const subcategories = await response.json();
                    
                    let optionsHtml = '<option value="">Select a Subcategory</option>';
                    if (subcategories.length > 0) {
                        subcategories.forEach(sub => {
                            optionsHtml += `<option value="${sub.id}">${sub.name}</option>`;
                        });
                        subcategorySelect.disabled = false;
                    } else {
                        optionsHtml = '<option value="">No subcategories found</option>';
                    }
                    subcategorySelect.innerHTML = optionsHtml;
                } catch (error) {
                    console.error('Error fetching subcategories:', error);
                    subcategorySelect.innerHTML = '<option value="">Error loading subcategories</option>';
                }
            } else {
                subcategorySelect.innerHTML = '<option value="">Select a Subcategory</option>';
                subcategorySelect.disabled = true;
            }
        });
    });
    </script>
</body>
</html>
