<?php
session_start();
require_once '../config/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../index.php');
    exit;
}

$error = '';
$success = '';

// Handle delete
if (isset($_GET['delete'])) {
    $team_id = mysqli_real_escape_string($conn, $_GET['delete']);
    $delete_query = "DELETE FROM users WHERE id = '$team_id' AND role = 'user'";
    
    if (mysqli_query($conn, $delete_query)) {
        $success = "Team deleted successfully!";
    } else {
        $error = "Error deleting team: " . mysqli_error($conn);
    }
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_team'])) {
    $team_id = mysqli_real_escape_string($conn, $_POST['team_id']);
    $team_name = mysqli_real_escape_string($conn, $_POST['team_name']);
    $contact_number = mysqli_real_escape_string($conn, $_POST['contact_number']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $update_query = "UPDATE users SET team_name = '$team_name', contact_number = '$contact_number', address = '$address', status = '$status' WHERE id = '$team_id'";
    
    if (mysqli_query($conn, $update_query)) {
        $success = "Team updated successfully!";
    } else {
        $error = "Error updating team: " . mysqli_error($conn);
    }
}

// Get all teams
$query = "SELECT * FROM users WHERE role = 'user' ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Teams - Admin Panel</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="dashboard">
        <div class="navbar">
            <h1>Admin Dashboard - Manage Teams</h1>
            <div class="navbar-links">
                <span>Welcome, <?php echo $_SESSION['username']; ?></span>
                <a href="../auth/logout.php">Logout</a>
            </div>
        </div>

        <div class="sidebar">
            <div class="side-menu">
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="manage_teams.php" class="active">Manage Teams</a></li>
                    <li><a href="manage_players.php">View Players</a></li>
                    <li><a href="manage_tournaments.php">Manage Tournaments</a></li>
                    <li><a href="manage_fixtures.php">Manage Fixtures</a></li>
                    <li><a href="tournament_requests.php">Tournament Requests</a></li>
                    <li><a href="manage_notices.php">Manage Notices</a></li>
                </ul>
            </div>

            <div class="main-content">
                <a href="dashboard.php" class="back-button">← Back to Dashboard</a>
                <div class="page-header">
                    <h2>Manage Teams</h2>
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
                                <th>ID</th>
                                <th>Username</th>
                                <th>Team Name</th>
                                <th>Email</th>
                                <th>Contact</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($team = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?php echo $team['id']; ?></td>
                                    <td><?php echo htmlspecialchars($team['username']); ?></td>
                                    <td><?php echo htmlspecialchars($team['team_name']); ?></td>
                                    <td><?php echo htmlspecialchars($team['email']); ?></td>
                                    <td><?php echo htmlspecialchars($team['contact_number']); ?></td>
                                    <td><span class="badge badge-<?php echo $team['status'] == 'active' ? 'success' : 'danger'; ?>"><?php echo ucfirst($team['status']); ?></span></td>
                                    <td>
                                        <button class="btn btn-primary btn-sm" onclick="editTeam(<?php echo $team['id']; ?>)">Edit</button>
                                        <a href="?delete=<?php echo $team['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal (Simple implementation) -->
    <script>
        function editTeam(teamId) {
            alert('Edit functionality - In production, use a modal dialog');
        }
    </script>
</body>
</html>