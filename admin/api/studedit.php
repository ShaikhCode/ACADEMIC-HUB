<?php

ob_start(); // Start output buffering

header("Content-Type: application/json");
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Enable exception handling

// Error logging (recommended)
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/your/error.log'); // Replace with your log file path
ini_set('display_errors', 0); // Disable displaying errors in production

require '../../connect/config.php'; // Include database connection

try {
    if (!$conn) {
        ob_clean();
        die(json_encode(["success" => false, "message" => "Database connection failed."]));
    }

    mysqli_set_charset($conn, "utf8"); // Enforce UTF-8

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        ob_clean();
        echo json_encode(["success" => false, "message" => "Invalid request method."]);
        exit;
    }

    $action = $_POST['action'] ?? '';

    if ($action === "edit") {
        editStudent($conn);
    } elseif ($action === "get") {
        getStudent($conn);
    } elseif ($action === "delete") {
        deleteStudent($conn);
    } else {
        ob_clean();
        echo json_encode(["success" => false, "message" => "Invalid action."]);
    }
} catch (Exception $e) {
    mysqli_rollback($conn); // Ensure rollback on any exception
    error_log("Exception: " . $e->getMessage()); // Log the exception
    ob_clean();
    echo json_encode(["success" => false, "message" => "Server error: " . $e->getMessage()]);
} finally {
    if (isset($stmt)) {
        try {
            $stmt->close();
        } catch (Exception $e) {
            error_log("Error closing statement: " . $e->getMessage());
        }
    }
    if (isset($conn)) {
        try {
            $conn->close();
        } catch (Exception $e) {
            error_log("Error closing connection: " . $e->getMessage());
        }
    }
}

// ✅ Function to Edit Students
function editStudent($conn)
{
    $student_id = $_POST['student_id'] ?? '';
    $student_name = $_POST['student_name'] ?? '';
    $roll_number = $_POST['student_code'] ?? ''; // Corrected variable name
    $number_p = $_POST['student_no'] ?? '';

    if (!$student_id || !$student_name || !$roll_number) {
        ob_clean();
        echo json_encode(["success" => false, "message" => "Missing required fields."]);
        return;
    }

    $stmt = $conn->prepare("
        UPDATE users 
        INNER JOIN students ON users.user_id = students.user_id
        SET users.username = ?, students.roll_number = ?,students.phone = ?
        WHERE students.user_id = ?
    ");

    $stmt->bind_param("ssii", $student_name, $roll_number,$number_p, $student_id);

    if ($stmt->execute()) {
        ob_clean();
        echo json_encode(["success" => true, "message" => "Student updated successfully."]);
    } else {
        ob_clean();
        echo json_encode(["success" => false, "message" => "Update failed."]);
    }
}

// ✅ Function to Get Students
function getStudent($conn)
{
    $student_id = $_POST['student_id'] ?? '';

    if (!$student_id) {
        ob_clean();
        echo json_encode(["success" => false, "message" => "Invalid student ID."]);
        return;
    }

    $stmt = $conn->prepare("
        SELECT u.user_id, u.username, s.roll_number,s.phone
        FROM users u 
        INNER JOIN students s ON u.user_id = s.user_id 
        WHERE u.user_id = ?
    ");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        ob_clean();
        echo json_encode(["success" => true, "data" => $row]);
    } else {
        ob_clean();
        echo json_encode(["success" => false, "message" => "Student not found."]);
    }
}

// ✅ Function to Delete Students
function deleteStudent($conn)
{
    $student_id = $_POST['student_id'] ?? '';

    if (!$student_id) {
        ob_clean();
        echo json_encode(["success" => false, "message" => "Invalid student ID."]);
        return;
    }

    $stmt = $conn->prepare("DELETE FROM users WHERE user_id=?");
    $stmt->bind_param("i", $student_id);

    if ($stmt->execute()) {
        ob_clean();
        echo json_encode(["success" => true, "message" => "Student deleted successfully."]);
    } else {
        ob_clean();
        echo json_encode(["success" => false, "message" => "Delete failed."]);
    }
}
?>