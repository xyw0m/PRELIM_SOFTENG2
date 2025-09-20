<?php

require_once 'Database.php';

class Article {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->link;
    }

    public function addArticle($title, $content, $image_url, $author_id, $category_id) {
        $sql = "INSERT INTO articles (title, content, image_url, author_id, category_id) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssssi", $title, $content, $image_url, $author_id, $category_id);
        return $stmt->execute();
    }

    public function getAllPublishedArticles() {
        $sql = "SELECT a.article_id, a.title, a.content, a.image_url, a.created_at, u.name as author_name, a.status, c.category_name
                FROM articles a
                JOIN users u ON a.author_id = u.id
                LEFT JOIN categories c ON a.category_id = c.category_id
                WHERE a.status = 'published'
                ORDER BY a.created_at DESC";
        $result = $this->db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getAllAdminArticles() {
        $sql = "SELECT a.article_id, a.title, a.content, a.image_url, a.created_at, u.name as author_name, a.status, c.category_name
                FROM articles a
                JOIN users u ON a.author_id = u.id
                LEFT JOIN categories c ON a.category_id = c.category_id
                ORDER BY a.created_at DESC";
        $result = $this->db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getAllArticlesForStudentDashboard($userId) {
        $sql = "SELECT a.article_id, a.title, a.content, a.image_url, a.created_at, u.name as author_name, a.status, c.category_name
                FROM articles a
                JOIN users u ON a.author_id = u.id
                LEFT JOIN categories c ON a.category_id = c.category_id
                WHERE a.author_id = ?
                ORDER BY a.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getArticleById($articleId) {
        $sql = "SELECT * FROM articles WHERE article_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $articleId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    public function updateArticle($article_id, $title, $content, $image_url, $category_id) {
        $sql = "UPDATE articles SET title = ?, content = ?, image_url = ?, category_id = ? WHERE article_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sssi", $title, $content, $image_url, $category_id, $article_id);
        return $stmt->execute();
    }

    public function hideArticle($article_id) {
        $sql = "UPDATE articles SET status = 'hidden' WHERE article_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $article_id);
        return $stmt->execute();
    }

    public function getArticleAuthorId($article_id) {
        $sql = "SELECT author_id FROM articles WHERE article_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $article_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['author_id'] ?? null;
    }
}
