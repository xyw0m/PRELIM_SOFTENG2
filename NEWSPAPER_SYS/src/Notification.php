<?php

require_once 'Database.php';

class Notification {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->link;
    }

    public function createNotification($userId, $articleId, $adminId, $message) {
        $sql = "INSERT INTO notifications (user_id, article_id, admin_id, message) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("siss", $userId, $articleId, $adminId, $message);
        return $stmt->execute();
    }

    public function getNotificationsByUserId($userId) {
        $sql = "SELECT n.notification_id, n.message, n.created_at, u.name AS admin_name, a.title AS article_title
                FROM notifications n
                JOIN users u ON n.admin_id = u.id
                JOIN articles a ON n.article_id = a.article_id
                WHERE n.user_id = ?
                ORDER BY n.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
