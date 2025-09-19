<?php

class Category {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAllCategories() {
        $stmt = $this->pdo->query("SELECT * FROM categories ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSubcategoriesByCategoryId($categoryId) {
        $stmt = $this->pdo->prepare("SELECT * FROM subcategories WHERE category_id = ? ORDER BY name ASC");
        $stmt->execute([$categoryId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCategoryById($categoryId) {
        $stmt = $this->pdo->prepare("SELECT name FROM categories WHERE id = ?");
        $stmt->execute([$categoryId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getSubcategoryById($subcategoryId) {
        $stmt = $this->pdo->prepare("SELECT name FROM subcategories WHERE id = ?");
        $stmt->execute([$subcategoryId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
