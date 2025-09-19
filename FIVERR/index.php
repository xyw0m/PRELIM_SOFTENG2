<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FIVERR - Login & Register</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="main-container">
        <div class="form-card">
            <h1>FIVERR</h1>
            <div class="form-toggle">
                <button id="login-btn" class="active">Login</button>
                <button id="register-btn">Register</button>
            </div>

            <?php
            if (isset($_GET['success']) && $_GET['success'] === 'registered') {
                echo '<div class="message-box success-message">Registration successful! You can now log in.</div>';
            }
            if (isset($_GET['error']) && $_GET['error'] === 'registration_failed') {
                echo '<div class="message-box error-message">Registration failed. Username or email may already be in use.</div>';
            }
            if (isset($_GET['login_error']) && $_GET['login_error'] === 'invalid_credentials') {
                echo '<div class="message-box error-message">Invalid username/email or password.</div>';
            }
            ?>

            <div id="login-form-container" class="form-container">
                <h2>Login</h2>
                <form action="process.php" method="POST">
                    <input type="text" name="identifier" placeholder="Username or Email" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <button type="submit" name="login">Login</button>
                </form>
            </div>

            <div id="register-form-container" class="form-container hidden">
                <h2>Register</h2>
                <form action="process.php" method="POST">
                    <input type="text" name="username" placeholder="Username" required>
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <select name="role" required>
                        <option value="freelancer">Freelancer</option>
                        <option value="client">Client</option>
                        <option value="fiverr_administrator">Administrator</option>
                    </select>
                    <button type="submit" name="register">Register</button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const loginBtn = document.getElementById('login-btn');
        const registerBtn = document.getElementById('register-btn');
        const loginFormContainer = document.getElementById('login-form-container');
        const registerFormContainer = document.getElementById('register-form-container');

        loginBtn.addEventListener('click', () => {
            loginBtn.classList.add('active');
            registerBtn.classList.remove('active');
            loginFormContainer.classList.remove('hidden');
            registerFormContainer.classList.add('hidden');
        });

        registerBtn.addEventListener('click', () => {
            registerBtn.classList.add('active');
            loginBtn.classList.remove('active');
            registerFormContainer.classList.remove('hidden');
            loginFormContainer.classList.add('hidden');
        });
    });
    </script>
</body>
</html>
