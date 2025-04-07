<?php
$host = "localhost";
$user = "root";  // Change if needed
$password = "";
$dbname = "hub";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
