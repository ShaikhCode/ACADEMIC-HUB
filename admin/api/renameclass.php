<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('../../connect/config.php');

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['class_id'], $_POST['new_branch'])) {
    $class_id = intval($_POST['class_id']);
    $new_branch = trim($_POST['new_branch']);
    $college_id = $_SESSION['college_id'];

    // Prevent duplicate branch names
    $check = $conn->prepare("SELECT * FROM classes WHERE college_id = ? AND branch = ?");
    $check->bind_param("is", $college_id, $new_branch);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['error_message'] = "Branch name already exists!";
    } else {
        $stmt = $conn->prepare("UPDATE classes SET branch = ? WHERE class_id = ? AND college_id = ?");
        $stmt->bind_param("sii", $new_branch, $class_id, $college_id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Class name updated successfully!";
        } else {
            $_SESSION['error_message'] = "Database update failed.";
        }
    }
} else {
    $_SESSION['error_message'] = "Invalid request.";
}

// Redirect back to addclass.php
header("Location: ../addclass.php");
exit();
?>