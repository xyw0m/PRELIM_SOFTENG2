<?php

require_once 'User.php';

class Auth {
    private $user;

    public function __construct() {
        $this->user = new User();
    }

    public function login($id, $password) {
        $user_data = $this->user->findById($id);

        if ($user_data && password_verify($password, $user_data['password'])) {
            session_start();
            $_SESSION['user_id'] = $user_data['id'];
            $_SESSION['user_role'] = $user_data['role'];
            return true;
        }

        return false;
    }

    public function logout() {
        session_start();
        $_SESSION = array();
        session_destroy();
    }

    public function isLoggedIn() {
        // Check if the session is active and user ID is set
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['user_id']);
    }

    public function getUserRole() {
        return $_SESSION['user_role'] ?? null;
    }

    public function isAdmin() {
        return $this->getUserRole() === 'admin';
    }

    public function isStudent() {
        return $this->getUserRole() === 'student';
    }

    public function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }
}
