<?php

require_once 'Database.php';

class User {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->link;
    }

    public function findById($id) {
        $sql = "SELECT id, name, password, role FROM users WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }

    // Now accepts the user's role
    public function register($id, $name, $password, $role) {
        // Check if user ID already exists
        $sql_check = "SELECT id FROM users WHERE id = ?";
        $stmt_check = $this->db->prepare($sql_check);
        $stmt_check->bind_param("s", $id);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            return false; // User ID already exists
        }

        // Hash the password for security
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user with the specified role
        $sql_insert = "INSERT INTO users (id, name, password, role) VALUES (?, ?, ?, ?)";
        $stmt_insert = $this->db->prepare($sql_insert);
        $stmt_insert->bind_param("ssss", $id, $name, $hashed_password, $role);

        return $stmt_insert->execute();
    }
}
