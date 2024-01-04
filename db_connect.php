<?php
$servername = "localhost";
$username = "root";
$password = "00000000";
$dbname = "course_work";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
