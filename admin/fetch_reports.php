<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json'); // Ensure JSON response

session_start();
include("../connect/config.php");

// Check user authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(["error" => "Unauthorized access"]);
    exit();
}

// Check session expiration
if (!isset($_SESSION['college_id'])) {
    echo json_encode(["error" => "Session expired. Please log in again."]);
    exit();
}

$college_id = $_SESSION['college_id'];

// Fetch POST parameters
$data = json_decode(file_get_contents("php://input"), true);
$class_id = isset($data['class_id']) ? intval($data['class_id']) : null;
$min_marks = isset($data['min_marks']) ? floatval($data['min_marks']) : 0;
$max_marks = isset($data['max_marks']) ? floatval($data['max_marks']) : 100;
$min_attendance = isset($data['min_attendance']) ? floatval($data['min_attendance']) : 0;
$max_attendance = isset($data['max_attendance']) ? floatval($data['max_attendance']) : 100;

// Query to get attendance percentage
$attendance_query = "
    SELECT student_id, 
           (SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) / COUNT(*)) * 100 AS attendance_percentage
    FROM attendance
    WHERE college_id = ?
    GROUP BY student_id
";

// Query to get marks percentage
$marks_query = "
    SELECT student_id, 
           (SUM(marks_obtained) / SUM(total_marks)) * 100 AS marks_percentage
    FROM marks
    WHERE college_id = ?
    GROUP BY student_id
";

// Final Query to fetch student data
$final_query = "
    SELECT students.roll_number, users.username, students.phone,
           IFNULL(marks.marks_percentage, 0) AS marks_percentage,
           IFNULL(attendance.attendance_percentage, 0) AS attendance_percentage,
           (IFNULL(marks.marks_percentage, 0) * 0.7 + IFNULL(attendance.attendance_percentage, 0) * 0.3) AS avg
    FROM students
    INNER JOIN users ON users.user_id = students.user_id
    LEFT JOIN ($marks_query) AS marks ON students.user_id = marks.student_id
    LEFT JOIN ($attendance_query) AS attendance ON students.user_id = attendance.student_id
    WHERE students.college_id = ?
";

// Bind parameters dynamically
$params = [$college_id, $college_id, $college_id];
$types = "iii";

// Add Class Filter
if (!empty($class_id)) {
    $final_query .= " AND students.class_id = ?";
    $params[] = $class_id;
    $types .= "i";
}

// Add Attendance Percentage Filter
$final_query .= " AND IFNULL(attendance.attendance_percentage, 0) BETWEEN ? AND ?";
$params[] = $min_attendance;
$params[] = $max_attendance;
$types .= "dd";

// Add Marks Percentage Filter
$final_query .= " AND IFNULL(marks.marks_percentage, 0) BETWEEN ? AND ?";
$params[] = $min_marks;
$params[] = $max_marks;
$types .= "dd";

// Order by Numeric Roll Number (Even if roll_number is VARCHAR)
$final_query .= " ORDER BY LENGTH(students.roll_number), students.roll_number ASC";

// Prepare SQL statement
$stmt = $conn->prepare($final_query);
if (!$stmt) {
    die(json_encode(["error" => "SQL Error: " . $conn->error]));
}

// Bind parameters
$stmt->bind_param($types, ...$params);

// Execute query
if (!$stmt->execute()) {
    die(json_encode(["error" => "Query Execution Failed: " . $stmt->error]));
}

// Get results
$result = $stmt->get_result();
$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}

// Close connection
$stmt->close();
$conn->close();

// Return JSON response
echo json_encode($students);
?>
