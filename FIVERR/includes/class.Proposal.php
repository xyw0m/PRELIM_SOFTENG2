<?php
class Proposal {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function createProposal($user_id, $title, $description, $category_id, $subcategory_id) {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO proposals (user_id, title, description, category_id, subcategory_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $title, $description, $category_id, $subcategory_id]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getProposalsByUserId($user_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM proposals WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFilteredProposals($categoryId = null, $subcategoryId = null) {
        $sql = "SELECT p.*, u.username FROM proposals p JOIN users u ON p.user_id = u.id";
        $params = [];
        $where = [];

        if ($categoryId) {
            $where[] = "p.category_id = ?";
            $params[] = $categoryId;
        }

        if ($subcategoryId) {
            $where[] = "p.subcategory_id = ?";
            $params[] = $subcategoryId;
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY p.created_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getAllProposals() {
        $stmt = $this->pdo->query("SELECT p.*, u.username FROM proposals p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProposalById($proposalId) {
        $stmt = $this->pdo->prepare("SELECT p.*, u.username FROM proposals p JOIN users u ON p.user_id = u.id WHERE p.id = ?");
        $stmt->execute([$proposalId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
