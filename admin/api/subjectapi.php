<?php
header("Content-Type: application/json"); // Ensure JSON response
require '../../connect/config.php'; // Include database connection

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Invalid request method."], JSON_PRETTY_PRINT);
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === "edit") {
    editSubject($conn);
} elseif ($action === "get") {
    getSubject($conn);
} elseif ($action === "delete") {
    deleteSubject($conn);
} else {
    echo json_encode(["success" => false, "message" => "Invalid action."], JSON_PRETTY_PRINT);
}

// ✅ Function to Edit Subject
function editSubject($conn) {
    $subject_id = $_POST['subject_id'] ?? '';
    $subject_name = $_POST['subject_name'] ?? '';
    $subject_code = $_POST['subject_code'] ?? '';

    if (!$subject_id || !$subject_name || !$subject_code) {
        echo json_encode(["success" => false, "message" => "Missing required fields."], JSON_PRETTY_PRINT);
        return;
    }

    $stmt = $conn->prepare("UPDATE subjects SET subject_name=?, subject_code=? WHERE subject_id=?");
    $stmt->bind_param("ssi", $subject_name, $subject_code, $subject_id);
    
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Subject updated successfully."], JSON_PRETTY_PRINT);
    } else {
        echo json_encode(["success" => false, "message" => "Update failed."], JSON_PRETTY_PRINT);
    }
}

// ✅ Function to Get Subject
function getSubject($conn) {
    $subject_id = $_POST['subject_id'] ?? '';

    if (!$subject_id) {
        echo json_encode(["success" => false, "message" => "Invalid subject ID."], JSON_PRETTY_PRINT);
        return;
    }

    $stmt = $conn->prepare("SELECT * FROM subjects WHERE subject_id=?");
    $stmt->bind_param("i", $subject_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo json_encode(["success" => true, "data" => $row], JSON_PRETTY_PRINT);
    } else {
        echo json_encode(["success" => false, "message" => "Subject not found."], JSON_PRETTY_PRINT);
    }
}

// ✅ Function to Delete Subject
function deleteSubject($conn) {
    $subject_id = $_POST['subject_id'] ?? '';

    if (!$subject_id) {
        echo json_encode(["success" => false, "message" => "Invalid subject ID."], JSON_PRETTY_PRINT);
        return;
    }

    $stmt = $conn->prepare("DELETE FROM subjects WHERE subject_id=?");
    $stmt->bind_param("i", $subject_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Subject deleted successfully."], JSON_PRETTY_PRINT);
    } else {
        echo json_encode(["success" => false, "message" => "Delete failed."], JSON_PRETTY_PRINT);
    }
}
?>
