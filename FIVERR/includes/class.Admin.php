<?php

class Admin {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function addCategory($name) {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->execute([$name]);
            return true;
        } catch (PDOException $e) {
            error_log("Failed to add category: " . $e->getMessage());
            return false;
        }
    }

    public function addSubcategory($categoryId, $name) {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO subcategories (category_id, name) VALUES (?, ?)");
            $stmt->execute([$categoryId, $name]);
            return true;
        } catch (PDOException $e) {
            error_log("Failed to add subcategory: " . $e->getMessage());
            return false;
        }
    }
}
