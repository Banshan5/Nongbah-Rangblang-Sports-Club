<?php
// Database connection configuration
$servername = "localhost";
$username = "root";
$password = "";
$database = "sports_club_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to UTF-8
$conn->set_charset("utf8");

?>