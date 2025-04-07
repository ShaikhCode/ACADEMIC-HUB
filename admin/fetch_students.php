<?php
include('../connect/config.php'); // Ensure correct DB connection file

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set JSON header and clean any previous output
header('Content-Type: application/json');
ob_clean();
flush();

// Check if class_id is set
if (!isset($_GET['class_id'])) {
    echo json_encode(["error" => "Class ID not provided"]);
    exit;
}

$class_id = $conn->real_escape_string($_GET['class_id']);

$query = "SELECT s.roll_number, u.username, s.avg, s.phone 
              FROM students s 
              INNER JOIN users u ON s.user_id = u.user_id 
              WHERE s.class_id = ?
              ORDER BY CAST(s.roll_number AS UNSIGNED)";
              
$stmt = $conn->prepare($query);
if (!$stmt) {
    echo json_encode(["error" => "SQL prepare failed", "message" => $conn->error]);
    exit;
}

$stmt->bind_param("i", $class_id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    echo json_encode(["error" => "SQL execution failed", "message" => $stmt->error]);
    exit;
}

$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}

echo json_encode($students);
exit;
?>
