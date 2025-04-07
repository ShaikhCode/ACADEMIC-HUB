<?php
header('Content-Type: application/json');
include '../../connect/config.php'; // Ensure database connection

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'overall':
        getOverallLeaderboard($conn);
        break;
    case 'attendance':
        getAttendanceLeaderboard($conn);
        break;
    case 'marks':
        getMarksLeaderboard($conn);
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
}

function getOverallLeaderboard($conn) {
    $query = "SELECT s.student_id , u.avt, s.roll_number, u.username AS name,
       COALESCE((SUM(m.marks_obtained) / NULLIF(SUM(m.total_marks), 0)) * 100, 0) AS marks_percentage,
       COALESCE((SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END) / NULLIF(COUNT(a.attendance_id), 0)) * 100, 0) AS attendance_percentage,
       (
           (COALESCE((SUM(m.marks_obtained) / NULLIF(SUM(m.total_marks), 0)) * 100, 0) * 0.6) +
           (COALESCE((SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END) / NULLIF(COUNT(a.attendance_id), 0)) * 100, 0) * 0.4)
       ) AS overall_score
FROM students s
INNER JOIN users u ON u.user_id = s.user_id
LEFT JOIN marks m ON u.user_id = m.student_id
LEFT JOIN attendance a ON u.user_id = a.student_id
GROUP BY s.student_id, s.roll_number, u.username
ORDER BY overall_score DESC;
";

    sendJsonResponse($conn, $query);
}

function getAttendanceLeaderboard($conn) {
    $query = "SELECT s.student_id,u.avt, s.roll_number, u.username AS name, 
                     (CASE WHEN COUNT(a.attendance_id) > 0 THEN (SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END) / COUNT(a.attendance_id)) * 100 ELSE 0 END) AS attendance_percentage
              FROM students s
              INNER JOIN users u ON u.user_id = s.user_id
              LEFT JOIN attendance a ON u.user_id = a.student_id
              GROUP BY s.student_id, s.roll_number, u.username
              ORDER BY attendance_percentage DESC";

    sendJsonResponse($conn, $query);
}

function getMarksLeaderboard($conn) {
    $query = "SELECT s.student_id,u.avt, s.roll_number, u.username AS name, 
                     (CASE WHEN SUM(m.total_marks) > 0 THEN (SUM(m.marks_obtained) / SUM(m.total_marks)) * 100 ELSE 0 END) AS marks_percentage
              FROM students s
              INNER JOIN users u ON u.user_id = s.user_id
              LEFT JOIN marks m ON u.user_id = m.student_id
              GROUP BY s.student_id, s.roll_number, u.username
              ORDER BY marks_percentage DESC";

    sendJsonResponse($conn, $query);
}

function sendJsonResponse($conn, $query) {
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        echo json_encode(["error" => "SQL Error: " . $conn->error]);
        return;
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'id' => $row['student_id'],
            'roll_number' => $row['roll_number'],
            'name' => $row['name'], 
            'avt' => $row['avt'] ?? 'avatar1', // Ensure avatar is included
            'marks_percentage' => $row['marks_percentage'] ?? 0,
            'attendance_percentage' => $row['attendance_percentage'] ?? 0,
            'overall_score' => $row['overall_score'] ?? 0
        ];
    }
    
    echo json_encode($data);
}
?>