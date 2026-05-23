<?php
session_start();
require_once '../config/db_connect.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $team_name = mysqli_real_escape_string($conn, $_POST['team_name']);
    $contact_number = mysqli_real_escape_string($conn, $_POST['contact_number']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else if (strlen($password) < 6) {
        $error = "Password must be at least 6 characters!";
    } else {
        $check_query = "SELECT id FROM users WHERE username = '$username'";
        $check_result = mysqli_query($conn, $check_query);

        if (mysqli_num_rows($check_result) > 0) {
            $error = "Username already exists!";
        } else {
            $hashedPassword = hash('sha256', $password);

            $insert_query = "INSERT INTO users (username, email, team_name, contact_number, address, password, role, status) 
                           VALUES ('$username', '$email', '$team_name', '$contact_number', '$address', '$hashedPassword', 'user', 'active')";

            if (mysqli_query($conn, $insert_query)) {
                $success = "Registration successful! Redirecting to login...";
                header('refresh:2; url=login.php?role=user');
            } else {
                $error = "Registration failed: " . mysqli_error($conn);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Sports Club Management</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container" style="padding-top: 40px; padding-bottom: 40px;">
        <div class="form-container">
            <a href="../index.php" class="back-button">← Back</a>
            <h2>Register Your Team</h2>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="team_name">Team Name:</label>
                    <input type="text" id="team_name" name="team_name" required>
                </div>

                <div class="form-group">
                    <label for="contact_number">Contact Number:</label>
                    <input type="text" id="contact_number" name="contact_number" required>
                </div>

                <div class="form-group">
                    <label for="address">Address:</label>
                    <textarea id="address" name="address" required></textarea>
                </div>

                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Register</button>
                    <button type="reset" class="btn btn-warning">Clear</button>
                </div>
            </form>

            <p style="text-align: center; margin-top: 20px;">
                Already have an account? <a href="login.php?role=user" style="color: #3498db;">Login here</a>
            </p>
        </div>
    </div>
</body>
</html>