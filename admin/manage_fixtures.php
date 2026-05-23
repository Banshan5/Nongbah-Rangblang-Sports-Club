<?php
session_start();
require_once '../config/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../index.php');
    exit;
}

$error = '';
$success = '';
$tournament_id = isset($_GET['tournament_id']) ? mysqli_real_escape_string($conn, $_GET['tournament_id']) : '';

// Handle generate knockout fixtures
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['generate_fixtures'])) {
    $tournament_id = mysqli_real_escape_string($conn, $_POST['tournament_id']);
    
    // Get accepted teams for this tournament
    $teams_query = "SELECT u.id FROM users u 
                   JOIN tournament_registrations tr ON u.id = tr.team_id 
                   WHERE tr.tournament_id = '$tournament_id' AND tr.status = 'accepted'";
    $teams_result = mysqli_query($conn, $teams_query);
    $teams = [];
    
    while ($team = mysqli_fetch_assoc($teams_result)) {
        $teams[] = $team['id'];
    }
    
    if (count($teams) < 2) {
        $error = "Need at least 2 teams to generate fixtures!";
    } else {
        // Simple knockout bracket generation
        shuffle($teams);
        $round = 1;
        
        for ($i = 0; $i < count($teams) - 1; $i += 2) {
            if (isset($teams[$i + 1])) {
                $team_a = $teams[$i];
                $team_b = $teams[$i + 1];
                
                $fixture_query = "INSERT INTO matches (tournament_id, team_a_id, team_b_id, status, round) 
                                VALUES ('$tournament_id', '$team_a', '$team_b', 'pending', 'Round $round')";
                mysqli_query($conn, $fixture_query);
            }
        }
        
        $success = "Fixtures generated successfully!";
    }
}

// Handle update match result
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_result'])) {
    $match_id = mysqli_real_escape_string($conn, $_POST['match_id']);
    $team_a_score = mysqli_real_escape_string($conn, $_POST['team_a_score']);
    $team_b_score = mysqli_real_escape_string($conn, $_POST['team_b_score']);
    
    $update_query = "UPDATE matches SET team_a_score = '$team_a_score', team_b_score = '$team_b_score', status = 'completed' WHERE id = '$match_id'";
    
    if (mysqli_query($conn, $update_query)) {
        $success = "Match result updated successfully!";
    } else {
        $error = "Error updating match result: " . mysqli_error($conn);
    }
}

// Get tournaments
$tournaments_query = "SELECT * FROM tournaments ORDER BY start_date DESC";
$tournaments_result = mysqli_query($conn, $tournaments_query);

// Get fixtures for selected tournament
$fixtures = [];
if ($tournament_id) {
    $fixtures_query = "SELECT m.*, u1.team_name as team_a_name, u2.team_name as team_b_name 
                      FROM matches m 
                      LEFT JOIN users u1 ON m.team_a_id = u1.id 
                      LEFT JOIN users u2 ON m.team_b_id = u2.id 
                      WHERE m.tournament_id = '$tournament_id' 
                      ORDER BY m.round";
    $fixtures_result = mysqli_query($conn, $fixtures_query);
    $fixtures = mysqli_fetch_all($fixtures_result, MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Fixtures - Admin Panel</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="dashboard">
        <div class="navbar">
            <h1>Admin Dashboard - Manage Fixtures</h1>
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
                    <li><a href="manage_tournaments.php">Manage Tournaments</a></li>
                    <li><a href="manage_fixtures.php" class="active">Manage Fixtures</a></li>
                    <li><a href="tournament_requests.php">Tournament Requests</a></li>
                    <li><a href="manage_notices.php">Manage Notices</a></li>
                </ul>
            </div>

            <div class="main-content">
                <a href="dashboard.php" class="back-button">← Back to Dashboard</a>
                <div class="page-header">
                    <h2>Manage Fixtures & Matches</h2>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <!-- Select Tournament -->
                <div class="card">
                    <div class="card-header">
                        <h3>Select Tournament</h3>
                    </div>
                    <form method="GET" action="">
                        <div style="display: flex; gap: 10px;">
                            <select name="tournament_id" style="flex: 1; padding: 10px; border: 1px solid #bdc3c7; border-radius: 5px;">
                                <option value="">Select a tournament...</option>
                                <?php 
                                if (mysqli_num_rows($tournaments_result) > 0) {
                                    mysqli_data_seek($tournaments_result, 0);
                                    while ($tournament = mysqli_fetch_assoc($tournaments_result)): 
                                ?>
                                    <option value="<?php echo $tournament['id']; ?>" <?php echo $tournament_id == $tournament['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($tournament['tournament_name']); ?>
                                    </option>
                                <?php 
                                    endwhile;
                                }
                                ?>
                            </select>
                            <button type="submit" class="btn btn-primary">Load</button>
                        </div>
                    </form>
                </div>

                <?php if ($tournament_id): ?>
                    <!-- Generate Fixtures -->
                    <div class="card" style="margin-top: 20px;">
                        <div class="card-header">
                            <h3>Generate Knockout Fixtures</h3>
                        </div>
                        <form method="POST" action="">
                            <input type="hidden" name="tournament_id" value="<?php echo $tournament_id; ?>">
                            <button type="submit" name="generate_fixtures" class="btn btn-success">Generate Auto Knockout Fixtures</button>
                        </form>
                    </div>

                    <!-- Fixtures Table -->
                    <div class="card" style="margin-top: 20px;">
                        <div class="card-header">
                            <h3>Fixtures & Results</h3>
                        </div>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Round</th>
                                        <th>Team A</th>
                                        <th>Score A</th>
                                        <th>Score B</th>
                                        <th>Team B</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    if (count($fixtures) > 0) {
                                        foreach ($fixtures as $fixture): 
                                    ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($fixture['round']); ?></td>
                                            <td><?php echo htmlspecialchars($fixture['team_a_name'] ?? 'TBD'); ?></td>
                                            <td><?php echo $fixture['team_a_score']; ?></td>
                                            <td><?php echo $fixture['team_b_score']; ?></td>
                                            <td><?php echo htmlspecialchars($fixture['team_b_name'] ?? 'TBD'); ?></td>
                                            <td><span class="badge badge-<?php echo $fixture['status'] == 'completed' ? 'success' : 'warning'; ?>"><?php echo ucfirst($fixture['status']); ?></span></td>
                                            <td>
                                                <?php if ($fixture['status'] == 'pending'): ?>
                                                    <button class="btn btn-primary btn-sm" onclick="updateResult(<?php echo $fixture['id']; ?>)">Update Result</button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php 
                                        endforeach;
                                    } else {
                                        echo '<tr><td colspan="7" style="text-align: center;">No fixtures found</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Update Result Form (Hidden by default) -->
                    <div id="updateResultModal" style="display: none; margin-top: 20px; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                        <h3>Update Match Result</h3>
                        <form method="POST" action="">
                            <input type="hidden" id="match_id" name="match_id">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                                <div class="form-group">
                                    <label for="team_a_score">Team A Score:</label>
                                    <input type="number" id="team_a_score" name="team_a_score" min="0" required>
                                </div>
                                <div class="form-group">
                                    <label for="team_b_score">Team B Score:</label>
                                    <input type="number" id="team_b_score" name="team_b_score" min="0" required>
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="submit" name="update_result" class="btn btn-success">Submit</button>
                                <button type="button" class="btn btn-danger" onclick="document.getElementById('updateResultModal').style.display='none';">Cancel</button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function updateResult(matchId) {
            document.getElementById('match_id').value = matchId;
            document.getElementById('updateResultModal').style.display = 'block';
        }
    </script>
</body>
</html>