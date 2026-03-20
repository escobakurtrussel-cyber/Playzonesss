<?php
$servername = "localhost";
$username = "root";
$password = ""; // Karaniwan ay blank ito sa XAMPP
$dbname = "learnquest_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>