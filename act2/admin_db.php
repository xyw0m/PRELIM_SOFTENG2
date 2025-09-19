<?php
session_start();
require_once 'Admin.php';
require_once 'Student.php';
require_once 'Database.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$admin = new Admin();
$student = new Student();

$message = '';
$allCourses = $admin->getAllCourses();
$allStudents = $student->getAllStudents();
$attendanceRecords = [];
$pendingLetters = $admin->getPendingExcuseLetters();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_course'])) {
        $course_name = $_POST['course_name'];
        $year_level = $_POST['year_level'];
        if ($admin->addCourse($course_name, $year_level)) {
            $message = "Course added successfully.";
            $allCourses = $admin->getAllCourses(); 
        } else {
            $message = "Failed to add course. It may already exist.";
        }
    } elseif (isset($_POST['check_attendance'])) {
        $selected_course_id = $_POST['course_id'];
        $selected_year_level = $_POST['year_level'];
        $attendanceRecords = $admin->getAttendanceByCourseAndYear($selected_course_id, $selected_year_level);
        if (empty($attendanceRecords)) {
            $message = "No attendance records found for the selected criteria.";
        }
    } elseif (isset($_POST['action'])) {
        $letter_id = $_POST['letter_id'];
        $status = $_POST['action'];

        if ($admin->updateExcuseLetterStatus($letter_id, $status)) {
            $message = "Excuse letter status updated to " . htmlspecialchars($status) . ".";
        } else {
            $message = "Failed to update excuse letter status.";
        }
        $pendingLetters = $admin->getPendingExcuseLetters();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">
    <nav class="bg-gray-800 p-4 text-white">
        <div class="container mx-auto flex justify-between items-center">
            <span class="text-xl font-bold">Admin Panel</span>
            <div class="flex items-center space-x-4">
                <span class="text-sm">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                <a href="logout.php" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 mt-8">
        <?php if ($message): ?>
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $message; ?></span>
            </div>
        <?php endif; ?>

        <!-- Add Course Section -->
        <div class="bg-white rounded-lg shadow-xl p-6 mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Add New Course</h2>
            <form action="admin_db.php" method="POST" class="space-y-4">
                <div>
                    <label for="course_name" class="block text-gray-700 font-bold mb-2">Course Name</label>
                    <input type="text" id="course_name" name="course_name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div>
                    <label for="year_level" class="block text-gray-700 font-bold mb-2">Year Level</label>
                    <input type="number" id="year_level" name="year_level" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <button type="submit" name="add_course" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Add Course
                </button>
            </form>
        </div>

        <!-- Check Attendance Section -->
        <div class="bg-white rounded-lg shadow-xl p-6 mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Check Student Attendance</h2>
            <form action="admin_db.php" method="POST" class="space-y-4">
                <div>
                    <label for="course_id" class="block text-gray-700 font-bold mb-2">Select Course</label>
                    <select id="course_id" name="course_id" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        <option value="">-- Select Course --</option>
                        <?php foreach ($allCourses as $course): ?>
                            <option value="<?php echo htmlspecialchars($course['id']); ?>"><?php echo htmlspecialchars($course['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="year_level" class="block text-gray-700 font-bold mb-2">Select Year Level</label>
                    <input type="number" id="year_level" name="year_level" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <button type="submit" name="check_attendance" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Check Attendance
                </button>
            </form>
        </div>
        
        <!-- Attendance Table -->
        <?php if (!empty($attendanceRecords)): ?>
        <div class="bg-white rounded-lg shadow-xl p-6 mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Attendance Records</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border-collapse">
                    <thead class="bg-gray-200 text-gray-600">
                        <tr>
                            <th class="py-2 px-4 border-b text-left">Student Name</th>
                            <th class="py-2 px-4 border-b text-left">Student ID</th>
                            <th class="py-2 px-4 border-b text-left">Date</th>
                            <th class="py-2 px-4 border-b text-left">Time</th>
                            <th class="py-2 px-4 border-b text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($attendanceRecords as $record): ?>
                        <tr class="hover:bg-gray-100">
                            <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($record['name']); ?></td>
                            <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($record['student_id']); ?></td>
                            <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($record['date']); ?></td>
                            <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($record['time']); ?></td>
                            <td class="py-2 px-4 border-b <?php echo $record['is_late'] ? 'text-red-500 font-bold' : 'text-green-500 font-bold'; ?>">
                                <?php echo $record['is_late'] ? 'Late' : 'Present'; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Pending Excuse Letters Section -->
        <div class="bg-white rounded-lg shadow-xl p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Pending Excuse Letters</h2>
            <div class="overflow-x-auto">
                <?php if (empty($pendingLetters)): ?>
                    <p class="text-gray-600">No pending excuse letters found.</p>
                <?php else: ?>
                <table class="min-w-full bg-white border-collapse">
                    <thead class="bg-gray-200 text-gray-600">
                        <tr>
                            <th class="py-2 px-4 border-b text-left">Student Name</th>
                            <th class="py-2 px-4 border-b text-left">Course</th>
                            <th class="py-2 px-4 border-b text-left">Absence Date</th>
                            <th class="py-2 px-4 border-b text-left">Reason</th>
                            <th class="py-2 px-4 border-b text-left">File</th>
                            <th class="py-2 px-4 border-b text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingLetters as $letter): ?>
                        <tr class="hover:bg-gray-100">
                            <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($letter['student_name']); ?></td>
                            <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($letter['course_name']); ?></td>
                            <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($letter['absence_date']); ?></td>
                            <td class="py-2 px-4 border-b"><?php echo nl2br(htmlspecialchars($letter['reason'])); ?></td>
                            <td class="py-2 px-4 border-b">
                                <?php if ($letter['file_path']): ?>
                                    <a href="<?php echo htmlspecialchars($letter['file_path']); ?>" class="text-blue-500 hover:underline" target="_blank">View File</a>
                                <?php else: ?>
                                    No file
                                <?php endif; ?>
                            </td>
                            <td class="py-2 px-4 border-b">
                                <form method="POST" class="inline">
                                    <input type="hidden" name="letter_id" value="<?php echo htmlspecialchars($letter['id']); ?>">
                                    <button type="submit" name="action" value="approved" class="bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-3 rounded-full text-xs">Approve</button>
                                </form>
                                <form method="POST" class="inline ml-2">
                                    <input type="hidden" name="letter_id" value="<?php echo htmlspecialchars($letter['id']); ?>">
                                    <button type="submit" name="action" value="rejected" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded-full text-xs">Decline</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
