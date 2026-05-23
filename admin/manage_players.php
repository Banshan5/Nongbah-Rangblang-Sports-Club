<?php
session_start();
require_once '../config/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../index.php');
    exit;
}

$error = '';
$success = '';

// Get all players with team info
$query = "SELECT p.*, u.team_name FROM players p JOIN users u ON p.team_id = u.id ORDER BY p.created_at DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Players - Admin Panel</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="dashboard">
        <div class="navbar">
            <h1>Admin Dashboard - View Players</h1>
            <div class="navbar-links">
                <span>Welcome, <?php echo $_SESSION['username']; ?></span>
                <a href="../auth/logout.php">Logout</a>
            </div>
        </div>

        <div class="sidebar">
            <div class="side-menu">
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="manage_teams.php">Manage Teams</a></li>
                    <li><a href="manage_players.php" class="active">View Players</a></li>
                    <li><a href="manage_tournaments.php">Manage Tournaments</a></li>
                    <li><a href="manage_fixtures.php">Manage Fixtures</a></li>
                    <li><a href="tournament_requests.php">Tournament Requests</a></li>
                    <li><a href="manage_notices.php">Manage Notices</a></li>
                </ul>
            </div>

            <div class="main-content">
                <a href="dashboard.php" class="back-button">← Back to Dashboard</a>
                <div class="page-header">
                    <h2>View Players by Teams</h2>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Player ID</th>
                                <th>Player Name</th>
                                <th>Team</th>
                                <th>Jersey Number</th>
                                <th>Position</th>
                                <th>Date of Birth</th>
                                <th>Contact</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if (mysqli_num_rows($result) > 0) {
                                while ($player = mysqli_fetch_assoc($result)): 
                            ?>
                                <tr>
                                    <td><?php echo $player['id']; ?></td>
                                    <td><?php echo htmlspecialchars($player['player_name']); ?></td>
                                    <td><?php echo htmlspecialchars($player['team_name']); ?></td>
                                    <td><?php echo $player['jersey_number'] ?? 'N/A'; ?></td>
                                    <td><?php echo htmlspecialchars($player['position'] ?? 'N/A'); ?></td>
                                    <td><?php echo $player['date_of_birth'] ?? 'N/A'; ?></td>
                                    <td><?php echo htmlspecialchars($player['contact_number'] ?? 'N/A'); ?></td>
                                    <td><span class="badge badge-<?php echo $player['status'] == 'active' ? 'success' : 'danger'; ?>"><?php echo ucfirst($player['status']); ?></span></td>
                                </tr>
                            <?php 
                                endwhile;
                            } else {
                                echo '<tr><td colspan="8" style="text-align: center;">No players found</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>