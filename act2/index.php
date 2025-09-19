<?php
session_start();

require_once 'User.php';

$message = '';
$user = new User();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $name = $_POST['name'];
    $student_id = $_POST['student_id'];
    $role = $_POST['role'];

    if ($user->register($name, $student_id, $role)) {
        $message = "Registration successful! You can now log in.";
    } else {
        $message = "Registration failed. The ID number '{$student_id}' is already registered. Please use a different ID or log in to an existing account.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">
    <div class="container mx-auto max-w-lg mt-10">
        <div class="bg-white rounded-lg shadow-xl p-8">
            <h1 class="text-3xl font-bold mb-6 text-center text-gray-800">Register Account</h1>
            
            <?php if ($message): ?>
                <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo $message; ?></span>
                </div>
            <?php endif; ?>

            <form action="index.php" method="POST" class="space-y-6">
                <div>
                    <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Full Name</label>
                    <input type="text" id="name" name="name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div>
                    <label for="student_id" class="block text-gray-700 text-sm font-bold mb-2">ID Number</label>
                    <input type="text" id="student_id" name="student_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div>
                    <label for="role" class="block text-gray-700 text-sm font-bold mb-2">Account Type</label>
                    <select id="role" name="role" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="student">Student</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="flex items-center justify-between">
                    <button type="submit" name="register" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Register
                    </button>
                    <a href="login.php" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                        Already have an account? Login here.
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
