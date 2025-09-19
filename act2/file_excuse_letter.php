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
$excuseLetterHistory = $student->getExcuseLetterHistory($_SESSION['user_id']);

// Define upload directory
$upload_dir = 'uploads/excuse_letters/';

// Check if the upload directory exists, if not, create it
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_excuse'])) {
    $course_id = $_POST['course_id'];
    $absence_date = $_POST['absence_date'];
    $reason = $_POST['reason'];
    $file_path = null;

    // Handle file upload
    if (isset($_FILES['excuse_file']) && $_FILES['excuse_file']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_path = $_FILES['excuse_file']['tmp_name'];
        $file_name = $_FILES['excuse_file']['name'];
        $file_size = $_FILES['excuse_file']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Generate a unique file name to avoid conflicts
        $unique_file_name = uniqid('excuse_') . '.' . $file_ext;
        $destination = $upload_dir . $unique_file_name;

        // Basic file validation
        $allowed_ext = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
        if (in_array($file_ext, $allowed_ext)) {
            // Move the file to the destination directory
            if (move_uploaded_file($file_tmp_path, $destination)) {
                $file_path = $destination;
            } else {
                $message = "Failed to upload file.";
            }
        } else {
            $message = "Invalid file type. Only PDF, DOC, DOCX, JPG, JPEG, and PNG files are allowed.";
        }
    }

    // Only proceed with database insertion if file upload was successful or no file was uploaded
    if (empty($message)) {
        if ($student->fileExcuseLetter($_SESSION['user_id'], $course_id, $absence_date, $reason, $file_path)) {
            $message = "Excuse letter submitted successfully. It will be reviewed by an administrator.";
            // Reload the excuse letter history to show the new submission
            $excuseLetterHistory = $student->getExcuseLetterHistory($_SESSION['user_id']);
        } else {
            $message = "Failed to submit excuse letter. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Excuse Letter</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">
    <nav class="bg-gray-800 p-4 text-white">
        <div class="container mx-auto flex justify-between items-center">
            <span class="text-xl font-bold">Student Dashboard</span>
            <a href="student_db.php" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Back to Dashboard</a>
        </div>
    </nav>

    <div class="container mx-auto px-4 mt-8">
        <?php if ($message): ?>
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $message; ?></span>
            </div>
        <?php endif; ?>

        <!-- Submit Excuse Letter Section -->
        <div class="bg-white rounded-lg shadow-xl p-6 mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Submit a New Excuse Letter</h2>
            <form action="file_excuse_letter.php" method="POST" enctype="multipart/form-data" class="space-y-4">
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
                    <label for="absence_date" class="block text-gray-700 font-bold mb-2">Date of Absence</label>
                    <input type="date" id="absence_date" name="absence_date" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div>
                    <label for="reason" class="block text-gray-700 font-bold mb-2">Reason for Absence</label>
                    <textarea id="reason" name="reason" rows="5" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required></textarea>
                </div>
                <div>
                    <label for="excuse_file" class="block text-gray-700 font-bold mb-2">Upload Excuse Letter (Optional)</label>
                    <input type="file" id="excuse_file" name="excuse_file" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <button type="submit" name="submit_excuse" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Submit Letter
                </button>
            </form>
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
                            <th class="py-2 px-4 border-b text-left">Date Submitted</th>
                            <th class="py-2 px-4 border-b text-left">Course</th>
                            <th class="py-2 px-4 border-b text-left">Absence Date</th>
                            <th class="py-2 px-4 border-b text-left">Reason</th>
                            <th class="py-2 px-4 border-b text-left">Status</th>
                            <th class="py-2 px-4 border-b text-left">File</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($excuseLetterHistory as $letter): ?>
                        <tr class="hover:bg-gray-100">
                            <td class="py-2 px-4 border-b"><?php echo htmlspecialchars((new DateTime($letter['created_at']))->format('Y-m-d')); ?></td>
                            <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($letter['course_name']); ?></td>
                            <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($letter['absence_date']); ?></td>
                            <td class="py-2 px-4 border-b"><?php echo nl2br(htmlspecialchars($letter['reason'])); ?></td>
                            <td class="py-2 px-4 border-b">
                                <?php
                                $status_class = '';
                                switch ($letter['status']) {
                                    case 'approved':
                                        $status_class = 'bg-green-100 text-green-800';
                                        break;
                                    case 'rejected':
                                        $status_class = 'bg-red-100 text-red-800';
                                        break;
                                    case 'pending':
                                    default:
                                        $status_class = 'bg-yellow-100 text-yellow-800';
                                        break;
                                }
                                ?>
                                <span class="px-2 py-1 rounded-full text-xs font-semibold <?php echo $status_class; ?>">
                                    <?php echo htmlspecialchars(ucfirst($letter['status'])); ?>
                                </span>
                            </td>
                            <td class="py-2 px-4 border-b">
                                <?php if ($letter['file_path']): ?>
                                    <a href="<?php echo htmlspecialchars($letter['file_path']); ?>" class="text-blue-500 hover:underline" target="_blank">View File</a>
                                <?php else: ?>
                                    No file
                                <?php endif; ?>
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
