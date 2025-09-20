<?php

require_once 'Database.php';
require_once 'Article.php';
require_once 'Notification.php';

class EditRequest {
    private $db;
    private $article;
    private $notification;

    public function __construct() {
        $database = new Database();
        $this->db = $database->link;
        $this->article = new Article();
        $this->notification = new Notification();
    }

    public function createRequest($articleId, $userId, $newTitle, $newContent, $newImageUrl, $newCategoryId) {
        $sql = "INSERT INTO edit_requests (article_id, user_id, new_title, new_content, new_image_url, new_category_id) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("issssi", $articleId, $userId, $newTitle, $newContent, $newImageUrl, $newCategoryId);
        return $stmt->execute();
    }

    public function getPendingRequests() {
        $sql = "SELECT er.request_id, er.new_title, er.new_content, er.new_image_url, er.created_at, a.title AS original_title, a.content AS original_content, u.name AS user_name, er.article_id, er.new_category_id, c.category_name AS new_category_name
                FROM edit_requests er
                JOIN articles a ON er.article_id = a.article_id
                JOIN users u ON er.user_id = u.id
                LEFT JOIN categories c ON er.new_category_id = c.category_id
                WHERE er.status = 'pending'
                ORDER BY er.created_at ASC";
        $result = $this->db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getStudentRequests($userId) {
        $sql = "SELECT er.request_id, er.new_title, er.new_content, er.new_image_url, er.status, a.title AS original_title, a.article_id
                FROM edit_requests er
                JOIN articles a ON er.article_id = a.article_id
                WHERE er.user_id = ?
                ORDER BY er.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function approveRequest($requestId, $adminId) {
        $sql = "SELECT * FROM edit_requests WHERE request_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $requestId);
        $stmt->execute();
        $result = $stmt->get_result();
        $requestData = $result->fetch_assoc();

        if ($requestData && $requestData['status'] === 'pending') {
            $articleId = $requestData['article_id'];
            $newTitle = $requestData['new_title'];
            $newContent = $requestData['new_content'];
            $newImageUrl = $requestData['new_image_url'];
            $newCategoryId = $requestData['new_category_id'];
            $userId = $requestData['user_id'];

            // Update the original article
            $updateSuccess = $this->article->updateArticle($articleId, $newTitle, $newContent, $newImageUrl, $newCategoryId);
            
            if ($updateSuccess) {
                // Update the request status
                $this->db->query("UPDATE edit_requests SET status = 'approved' WHERE request_id = $requestId");
                
                // Create a notification for the student
                $message = "Your edit request for article '$newTitle' has been approved!";
                $this->notification->createNotification($userId, $articleId, $adminId, $message);
                
                return true;
            }
        }
        return false;
    }

    public function rejectRequest($requestId, $adminId) {
        $sql = "UPDATE edit_requests SET status = 'rejected' WHERE request_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $requestId);
        $success = $stmt->execute();

        if ($success) {
            $sql = "SELECT article_id, user_id, new_title FROM edit_requests WHERE request_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("i", $requestId);
            $stmt->execute();
            $result = $stmt->get_result();
            $requestData = $result->fetch_assoc();

            $articleId = $requestData['article_id'];
            $userId = $requestData['user_id'];
            $newTitle = $requestData['new_title'];
            
            $message = "Your edit request for article '$newTitle' has been rejected by an admin.";
            $this->notification->createNotification($userId, $articleId, $adminId, $message);
        }
        return $success;
    }
}
