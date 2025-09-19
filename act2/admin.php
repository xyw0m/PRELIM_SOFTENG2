<?php
require_once 'Database.php';

class Admin {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAllCourses() {
        $stmt = $this->db->prepare("SELECT id, name, year_level FROM courses");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function addCourse($course_name, $year_level) {
        try {
            $stmt = $this->db->prepare("INSERT INTO courses (name, year_level) VALUES (?, ?)");
            $stmt->execute([$course_name, $year_level]);
            return true;
        } catch (PDOException $e) {
            error_log("Error adding course: " . $e->getMessage());
            return false;
        }
    }

    public function getAttendanceByCourseAndYear($course_id, $year_level) {
        $query = "
            SELECT u.name, u.student_id, a.date, a.time, a.is_late
            FROM attendance a
            JOIN users u ON a.student_id = u.id
            JOIN courses c ON a.course_id = c.id
            WHERE c.id = ? AND c.year_level = ?
            ORDER BY a.date DESC, a.time DESC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$course_id, $year_level]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Fetches all pending excuse letters with student and course details.
     * @return array An array of pending excuse letter records.
     */
    public function getPendingExcuseLetters() {
        $query = "
            SELECT el.*, u.name as student_name, c.name as course_name
            FROM excuse_letters el
            JOIN users u ON el.student_id = u.id
            JOIN courses c ON el.course_id = c.id
            WHERE el.status = 'pending'
            ORDER BY el.created_at DESC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Updates the status of an excuse letter.
     * @param int $letter_id The ID of the excuse letter to update.
     * @param string $status The new status ('approved' or 'rejected').
     * @return bool True on success, false on failure.
     */
    public function updateExcuseLetterStatus($letter_id, $status) {
        if (!in_array($status, ['approved', 'rejected'])) {
            return false;
        }
        
        $query = "UPDATE excuse_letters SET status = ? WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$status, $letter_id]);
    }
}
