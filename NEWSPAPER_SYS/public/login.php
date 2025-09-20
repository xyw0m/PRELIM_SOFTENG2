<?php
require_once '../src/Auth.php';
$auth = new Auth();

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = trim($_POST['id']);
    $password = $_POST['password'];

    if ($auth->login($id, $password)) {
        $role = $auth->getUserRole();
        if ($role === 'admin') {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: student_dashboard.php");
        }
        exit();
    } else {
        $message = 'Incorrect ID or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="form-container">
        <h2 class="form-title">Login</h2>
        <?php if (!empty($message)): ?>
            <div class="message error"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <form method="post" action="login.php">
            <div class="form-group">
                <label for="id">Student/Employee ID:</label>
                <input type="text" id="id" name="id" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="form-button">Login</button>
        </form>
        <a href="register.php" class="switch-link">Don't have an account? Register here.</a>
    </div>

</body>
</html>
