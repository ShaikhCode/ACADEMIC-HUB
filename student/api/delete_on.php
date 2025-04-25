<?php
session_start();
include '../../connect/config.php';

if (!isset($_SESSION['student_id'])) {
    die("Error: Student not logged in.");
}

$student_id = intval($_SESSION['student_id']); // Ensure it's an integer

// Fix SQL syntax: Remove 'TABLE' and use 'SET' correctly
$sql = "UPDATE students SET check_b='' WHERE student_id= ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Error: " . $conn->error);
}

$stmt->bind_param("i", $student_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    
} else {
    echo "Warning: No changes made.";
}

$stmt->close();
$conn->close();
?>
