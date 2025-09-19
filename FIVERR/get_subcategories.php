<?php
header('Content-Type: application/json');

if (!isset($_GET['category_id'])) {
    echo json_encode([]);
    exit;
}

include_once __DIR__ . '/includes/db.config.php';
include_once __DIR__ . '/includes/class.Category.php';

$categoryHandler = new Category($pdo);
$categoryId = $_GET['category_id'];

$subcategories = $categoryHandler->getSubcategoriesByCategoryId($categoryId);
echo json_encode($subcategories);
