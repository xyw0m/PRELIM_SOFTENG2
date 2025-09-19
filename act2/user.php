<?php

require_once 'Database.php';

class User {
    private $db;
    private $table_name = "users";

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function register($name, $student_id, $role) {
        try {
            $stmt = $this->db->prepare("INSERT INTO " . $this->table_name . " (name, student_id, role) VALUES (?, ?, ?)");
            $stmt->execute([htmlspecialchars(strip_tags($name)), htmlspecialchars(strip_tags($student_id)), $role]);
            return true;
        } catch(PDOException $e) {
            if ($e->getCode() === '23000') {
                return false;
            }
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }

    public function login($name, $student_id, $role) {
        $stmt = $this->db->prepare("SELECT * FROM " . $this->table_name . " WHERE name = ? AND student_id = ? AND role = ? LIMIT 1");
        $name = htmlspecialchars(strip_tags($name));
        $student_id = htmlspecialchars(strip_tags($student_id));
        $stmt->execute([$name, $student_id, $role]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            return $user;
        }
        return false;
    }
}
