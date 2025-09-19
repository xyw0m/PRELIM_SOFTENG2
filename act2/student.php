<?php
require_once 'Database.php';

class Student {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAllStudents() {
        $stmt = $this->db->prepare("SELECT id, name, student_id FROM users WHERE role = 'student'");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fileAttendance($user_id, $course_id, $is_late) {
        $date = date("Y-m-d");
        $time = date("H:i:s");
        
        try {
            $checkStmt = $this->db->prepare("SELECT COUNT(*) FROM attendance WHERE student_id = ? AND course_id = ? AND date = ?");
            $checkStmt->execute([$user_id, $course_id, $date]);
            if ($checkStmt->fetchColumn() > 0) {
                return false; 
            }

            $stmt = $this->db->prepare("INSERT INTO attendance (student_id, course_id, date, time, is_late) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $course_id, $date, $time, $is_late]);
            return true;
        } catch(PDOException $e) {
            error_log("Error filing attendance: " . $e->getMessage());
            return false;
        }
    }

    public function getAttendanceHistory($user_id) {
        $query = "
            SELECT c.name as course_name, a.date, a.time, a.is_late
            FROM attendance a
            JOIN courses c ON a.course_id = c.id
            WHERE a.student_id = ?
            ORDER BY a.date DESC, a.time DESC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Files an excuse letter for a student.
     * @param int $student_id The student's user ID.
     * @param int $course_id The course ID the letter is for.
     * @param string $absence_date The date of absence.
     * @param string $reason The reason for the absence.
     * @param string|null $file_path The file path of the uploaded document, or null if none.
     * @return bool True on success, false on failure.
     */
    public function fileExcuseLetter($student_id, $course_id, $absence_date, $reason, $file_path) {
        try {
            $query = "INSERT INTO excuse_letters (student_id, course_id, absence_date, reason, file_path) VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([$student_id, $course_id, $absence_date, $reason, $file_path]);
        } catch (PDOException $e) {
            error_log("Error filing excuse letter: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Gets the history of all excuse letters for a student.
     * @param int $student_id The student's user ID.
     * @return array An array of excuse letter records.
     */
    public function getExcuseLetterHistory($student_id) {
        $query = "
            SELECT el.*, c.name AS course_name
            FROM excuse_letters el
            JOIN courses c ON el.course_id = c.id
            WHERE el.student_id = ?
            ORDER BY el.created_at DESC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$student_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
