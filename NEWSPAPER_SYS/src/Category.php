<?php

require_once 'Database.php';

class Category {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->link;
    }

    public function addCategory($categoryName) {
        $sql = "INSERT INTO categories (category_name) VALUES (?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $categoryName);
        return $stmt->execute();
    }

    public function getAllCategories() {
        $sql = "SELECT * FROM categories ORDER BY category_name ASC";
        $result = $this->db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
