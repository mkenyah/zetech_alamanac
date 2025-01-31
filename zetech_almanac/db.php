<?php
$host = "localhost"; // Change if needed
$user = "root"; // Change if needed
$password = ""; // Change if needed
$database = "zetech_almanac"; // Replace with actual database name

$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
