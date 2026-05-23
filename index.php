<?php
session_start();

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: user/dashboard.php');
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nongbah Rangblang Sports Club - Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container login-container">
        <div class="login-box">
            <h1>Nongbah Rangblang Sports & Cultural Club</h1>
            <h2>Management System</h2>
            
            <div class="role-selection">
                <button class="role-btn" onclick="switchRole('admin')">Login as Admin</button>
                <button class="role-btn" onclick="switchRole('user')">Login as Team</button>
            </div>

            <form id="loginForm" method="POST" action="auth/login.php">
                <input type="hidden" id="role" name="role" value="admin">
                
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>

                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn-login">Login</button>
            </form>

            <div id="userRegisterLink" style="display:none;">
                <p>Don't have an account? <a href="auth/register.php">Register here</a></p>
            </div>
        </div>
    </div>

    <script>
        function switchRole(role) {
            document.getElementById('role').value = role;
            const registerLink = document.getElementById('userRegisterLink');
            if (role === 'user') {
                registerLink.style.display = 'block';
            } else {
                registerLink.style.display = 'none';
            }
        }
    </script>
</body>
</html>