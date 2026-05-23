<?php
session_start();
require_once '../config/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../index.php');
    exit;
}

$error = '';
$success = '';

// Handle create tournament
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_tournament'])) {
    $tournament_name = mysqli_real_escape_string($conn, $_POST['tournament_name']);
    $start_date = mysqli_real_escape_string($conn, $_POST['start_date']);
    $end_date = mysqli_real_escape_string($conn, $_POST['end_date']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $insert_query = "INSERT INTO tournaments (tournament_name, start_date, end_date, location, description, status, created_by) 
                    VALUES ('$tournament_name', '$start_date', '$end_date', '$location', '$description', '$status', " . $_SESSION['user_id'] . ")";

    if (mysqli_query($conn, $insert_query)) {
        $success = "Tournament created successfully!";
    } else {
        $error = "Error creating tournament: " . mysqli_error($conn);
    }
}

// Handle delete tournament
if (isset($_GET['delete'])) {
    $tournament_id = mysqli_real_escape_string($conn, $_GET['delete']);
    $delete_query = "DELETE FROM tournaments WHERE id = '$tournament_id'";
    
    if (mysqli_query($conn, $delete_query)) {
        $success = "Tournament deleted successfully!";
    } else {
        $error = "Error deleting tournament: " . mysqli_error($conn);
    }
}

// Get all tournaments
$query = "SELECT * FROM tournaments ORDER BY start_date DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Tournaments - Admin Panel</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="dashboard">
        <div class="navbar">
            <h1>Admin Dashboard - Manage Tournaments</h1>
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
                    <li><a href="manage_players.php">View Players</a></li>
                    <li><a href="manage_tournaments.php" class="active">Manage Tournaments</a></li>
                    <li><a href="manage_fixtures.php">Manage Fixtures</a></li>
                    <li><a href="tournament_requests.php">Tournament Requests</a></li>
                    <li><a href="manage_notices.php">Manage Notices</a></li>
                </ul>
            </div>

            <div class="main-content">
                <a href="dashboard.php" class="back-button">← Back to Dashboard</a>
                <div class="page-header">
                    <h2>Manage Tournaments</h2>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <!-- Create Tournament Form -->
                <div class="card">
                    <div class="card-header">
                        <h3>Create New Tournament</h3>
                    </div>
                    <form method="POST" action="">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div class="form-group">
                                <label for="tournament_name">Tournament Name:</label>
                                <input type="text" id="tournament_name" name="tournament_name" required>
                            </div>

                            <div class="form-group">
                                <label for="location">Location:</label>
                                <input type="text" id="location" name="location" required>
                            </div>

                            <div class="form-group">
                                <label for="start_date">Start Date:</label>
                                <input type="date" id="start_date" name="start_date" required>
                            </div>

                            <div class="form-group">
                                <label for="end_date">End Date:</label>
                                <input type="date" id="end_date" name="end_date" required>
                            </div>

                            <div class="form-group">
                                <label for="status">Status:</label>
                                <select id="status" name="status" required>
                                    <option value="upcoming">Upcoming</option>
                                    <option value="ongoing">Ongoing</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Description:</label>
                            <textarea id="description" name="description" required></textarea>
                        </div>

                        <button type="submit" name="create_tournament" class="btn btn-success">Create Tournament</button>
                    </form>
                </div>

                <!-- Tournaments List -->
                <div class="card" style="margin-top: 30px;">
                    <div class="card-header">
                        <h3>All Tournaments</h3>
                    </div>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tournament Name</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                if (mysqli_num_rows($result) > 0) {
                                    while ($tournament = mysqli_fetch_assoc($result)): 
                                ?>
                                    <tr>
                                        <td><?php echo $tournament['id']; ?></td>
                                        <td><?php echo htmlspecialchars($tournament['tournament_name']); ?></td>
                                        <td><?php echo $tournament['start_date']; ?></td>
                                        <td><?php echo $tournament['end_date']; ?></td>
                                        <td><?php echo htmlspecialchars($tournament['location']); ?></td>
                                        <td><span class="badge badge-info"><?php echo ucfirst($tournament['status']); ?></span></td>
                                        <td>
                                            <a href="manage_fixtures.php?tournament_id=<?php echo $tournament['id']; ?>" class="btn btn-primary btn-sm">Fixtures</a>
                                            <a href="?delete=<?php echo $tournament['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                                        </td>
                                    </tr>
                                <?php 
                                    endwhile;
                                } else {
                                    echo '<tr><td colspan="7" style="text-align: center;">No tournaments found</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>