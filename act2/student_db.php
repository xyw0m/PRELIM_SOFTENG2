<?php
session_start();
require_once 'Student.php';
require_once 'Admin.php';
require_once 'Database.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$student = new Student();
$admin = new Admin();

$message = '';
$allCourses = $admin->getAllCourses(); 
$attendanceHistory = $student->getAttendanceHistory($_SESSION['user_id']);
$excuseLetterHistory = $student->getExcuseLetterHistory($_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['file_attendance'])) {
    $course_id = $_POST['course_id'];
    $is_late = (int)$_POST['is_late'];

    if ($student->fileAttendance($_SESSION['user_id'], $course_id, $is_late)) {
        $message = "Attendance filed successfully.";
        $attendanceHistory = $student->getAttendanceHistory($_SESSION['user_id']);
    } else {
        $message = "Failed to file attendance. You may have already filed for this class today.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">
    <nav class="bg-gray-800 p-4 text-white">
        <div class="container mx-auto flex justify-between items-center">
            <span class="text-xl font-bold">Student Dashboard</span>
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

        <!-- File Attendance Section -->
        <div class="bg-white rounded-lg shadow-xl p-6 mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">File Your Attendance</h2>
            <form action="student_db.php" method="POST" class="space-y-4">
                <div>
                    <label for="course_id" class="block text-gray-700 font-bold mb-2">Select Course</label>
                    <select id="course_id" name="course_id" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        <option value="">-- Select Course --</option>
                        <?php foreach ($allCourses as $course): ?>
                            <option value="<?php echo htmlspecialchars($course['id']); ?>"><?php echo htmlspecialchars($course['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex items-center space-x-4">
                    <label class="block text-gray-700 font-bold">Attendance Status:</label>
                    <label for="status_present" class="inline-flex items-center">
                        <input type="radio" id="status_present" name="is_late" value="0" class="form-radio text-green-500" checked>
                        <span class="ml-2">Present</span>
                    </label>
                    <label for="status_late" class="inline-flex items-center">
                        <input type="radio" id="status_late" name="is_late" value="1" class="form-radio text-red-500">
                        <span class="ml-2">Late</span>
                    </label>
                </div>
                <button type="submit" name="file_attendance" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    File Attendance
                </button>
            </form>
        </div>

        <!-- Excuse Letter Section -->
        <div class="bg-white rounded-lg shadow-xl p-6 mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Submit an Excuse Letter</h2>
            <p class="text-gray-600 mb-4">
                If you were absent, you can file an excuse letter to explain your absence.
            </p>
            <a href="file_excuse_letter.php" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Submit Excuse Letter
            </a>
        </div>

        <!-- Attendance History Section -->
        <div class="bg-white rounded-lg shadow-xl p-6 mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Attendance History</h2>
            <div class="overflow-x-auto">
                <?php if (empty($attendanceHistory)): ?>
                    <p class="text-gray-600">No attendance history found. File your first attendance above.</p>
                <?php else: ?>
                <table class="min-w-full bg-white border-collapse">
                    <thead class="bg-gray-200 text-gray-600">
                        <tr>
                            <th class="py-2 px-4 border-b text-left">Course Name</th>
                            <th class="py-2 px-4 border-b text-left">Date</th>
                            <th class="py-2 px-4 border-b text-left">Time</th>
                            <th class="py-2 px-4 border-b text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($attendanceHistory as $record): ?>
                        <tr class="hover:bg-gray-100">
                            <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($record['course_name']); ?></td>
                            <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($record['date']); ?></td>
                            <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($record['time']); ?></td>
                            <td class="py-2 px-4 border-b <?php echo $record['is_late'] ? 'text-red-500 font-bold' : 'text-green-500 font-bold'; ?>">
                                <?php echo $record['is_late'] ? 'Late' : 'Present'; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Excuse Letter History Section -->
        <div class="bg-white rounded-lg shadow-xl p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Excuse Letter History</h2>
            <div class="overflow-x-auto">
                <?php if (empty($excuseLetterHistory)): ?>
                    <p class="text-gray-600">No excuse letters submitted yet.</p>
                <?php else: ?>
                <table class="min-w-full bg-white border-collapse">
                    <thead class="bg-gray-200 text-gray-600">
                        <tr>
                            <th class="py-2 px-4 border-b text-left">Course Name</th>
                            <th class="py-2 px-4 border-b text-left">Absence Date</th>
                            <th class="py-2 px-4 border-b text-left">Reason</th>
                            <th class="py-2 px-4 border-b text-left">File</th>
                            <th class="py-2 px-4 border-b text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($excuseLetterHistory as $letter): ?>
                        <tr class="hover:bg-gray-100">
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
                                <?php
                                    $statusColor = '';
                                    switch ($letter['status']) {
                                        case 'pending':
                                            $statusColor = 'text-yellow-500';
                                            break;
                                        case 'approved':
                                            $statusColor = 'text-green-500';
                                            break;
                                        case 'rejected':
                                            $statusColor = 'text-red-500';
                                            break;
                                    }
                                ?>
                                <span class="font-bold <?php echo $statusColor; ?>"><?php echo htmlspecialchars(ucfirst($letter['status'])); ?></span>
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
