<?php
session_start();
require_once '../config/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Sports Club Management</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="dashboard">
        <div class="navbar">
            <h1>Admin Dashboard - Nongbah Rangblang Sports Club</h1>
            <div class="navbar-links">
                <span>Welcome, <?php echo $_SESSION['username']; ?></span>
                <a href="../auth/logout.php">Logout</a>
            </div>
        </div>

        <div class="sidebar">
            <div class="side-menu">
                <ul>
                    <li><a href="dashboard.php" class="active">Dashboard</a></li>
                    <li><a href="manage_teams.php">Manage Teams</a></li>
                    <li><a href="manage_players.php">View Players</a></li>
                    <li><a href="manage_tournaments.php">Manage Tournaments</a></li>
                    <li><a href="manage_fixtures.php">Manage Fixtures</a></li>
                    <li><a href="tournament_requests.php">Tournament Requests</a></li>
                    <li><a href="manage_notices.php">Manage Notices</a></li>
                </ul>
            </div>

            <div class="main-content">
                <a href="../index.php" class="back-button">← Back to Home</a>
                <div class="page-header">
                    <h2>Dashboard</h2>
                </div>

                <?php
                $teams_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role='user'"))['count'];
                $players_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM players"))['count'];
                $tournaments_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM tournaments"))['count'];
                $pending_requests = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM tournament_registrations WHERE status='pending'"))['count'];
                ?>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                    <div class="card">
                        <div class="card-header">
                            <h3>Total Teams</h3>
                        </div>
                        <p style="font-size: 36px; color: #3498db; font-weight: bold; margin: 0;"><?php echo $teams_count; ?></p>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3>Total Players</h3>
                        </div>
                        <p style="font-size: 36px; color: #27ae60; font-weight: bold; margin: 0;"><?php echo $players_count; ?></p>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3>Total Tournaments</h3>
                        </div>
                        <p style="font-size: 36px; color: #e74c3c; font-weight: bold; margin: 0;"><?php echo $tournaments_count; ?></p>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3>Pending Requests</h3>
                        </div>
                        <p style="font-size: 36px; color: #f39c12; font-weight: bold; margin: 0;"><?php echo $pending_requests; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>