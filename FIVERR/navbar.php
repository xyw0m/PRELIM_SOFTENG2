<?php
include_once __DIR__ . '/includes/db.config.php';
include_once __DIR__ . '/includes/class.Category.php';

$categoryHandler = new Category($pdo);
$categories = $categoryHandler->getAllCategories();
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">FIVERR</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavDropdown">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="dashboard.php">Home</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-toggle="dropdown" aria-expanded="false">
                        Categories
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                        <?php foreach ($categories as $category): ?>
                            <li>
                                <a class="dropdown-item" href="dashboard.php?category_id=<?php echo htmlspecialchars($category['id']); ?>"><?php echo htmlspecialchars($category['name']); ?> &raquo;</a>
                                <ul class="dropdown-menu dropdown-submenu">
                                    <?php
                                    $subcategories = $categoryHandler->getSubcategoriesByCategoryId($category['id']);
                                    if (count($subcategories) > 0):
                                        foreach ($subcategories as $subcategory):
                                    ?>
                                        <li><a class="dropdown-item" href="dashboard.php?subcategory_id=<?php echo htmlspecialchars($subcategory['id']); ?>"><?php echo htmlspecialchars($subcategory['name']); ?></a></li>
                                    <?php
                                        endforeach;
                                    else:
                                    ?>
                                        <li><a class="dropdown-item" href="#">No Subcategories</a></li>
                                    <?php endif; ?>
                                </ul>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a href="process.php?logout" class="btn btn-outline-light">Log Out</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
