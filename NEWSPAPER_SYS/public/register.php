<?php
session_start();
require_once '../src/User.php';

$message = '';
$is_success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = new User();
    $name = trim($_POST['name']);
    $id = trim($_POST['id']);
    $password = $_POST['password'];
    $role = $_POST['role']; // Get the selected role from the form

    if (empty($name) || empty($id) || empty($password) || empty($role)) {
        $message = 'All fields are required.';
    } else {
        if ($user->register($id, $name, $password, $role)) {
            $message = 'Registration successful! You can now log in.';
            $is_success = true;
        } else {
            $message = 'User ID already exists. Please choose another one.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="form-container">
        <h2 class="form-title">Register</h2>
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $is_success ? 'success' : 'error'; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <form method="post" action="register.php">
            <div class="form-group">
                <label for="name">Full Name:</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="id">Student/Employee ID:</label>
                <input type="text" id="id" name="id" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="role">Role:</label>
                <select id="role" name="role" required>
                    <option value="student">Student</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <button type="submit" class="form-button">Register</button>
        </form>
        <a href="login.php" class="switch-link">Already have an account? Login here.</a>
    </div>

</body>
</html>
