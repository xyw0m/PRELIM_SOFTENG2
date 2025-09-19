<?php

class Offer {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function createOffer($proposalId, $clientId, $message) {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO offers (proposal_id, client_id, message) VALUES (?, ?, ?)");
            $stmt->execute([$proposalId, $clientId, $message]);
            return true;
        } catch (PDOException $e) {
            error_log("Offer creation failed: " . $e->getMessage());
            return false;
        }
    }

    public function hasOffer($proposalId) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM offers WHERE proposal_id = ?");
        $stmt->execute([$proposalId]);
        return $stmt->fetchColumn() > 0;
    }

    public function hasClientAlreadyOffered($proposalId, $clientId) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM offers WHERE proposal_id = ? AND client_id = ?");
        $stmt->execute([$proposalId, $clientId]);
        return $stmt->fetchColumn() > 0;
    }

    public function getOffersByProposalId($proposalId) {
        $stmt = $this->pdo->prepare("SELECT o.*, u.username FROM offers o JOIN users u ON o.client_id = u.id WHERE o.proposal_id = ? ORDER BY o.created_at DESC");
        $stmt->execute([$proposalId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
