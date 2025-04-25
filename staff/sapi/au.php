<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('../../connect/config.php');

$staff_uid = $_SESSION['user_id'] ?? null;

if (isset($_POST['student_id'], $_POST['date'], $_POST['status'], $_POST['subject_id'])) {
    $student_id = $_POST['student_id'];
    $date = $_POST['date'];
    $status = $_POST['status'];
    $subject_id = $_POST['subject_id'];

    if (!$staff_uid) {
        echo "Error: staff UID missing from session.";
        exit;
    }

    // Check if the record already exists
    $stmt = $conn->prepare("SELECT 1 FROM attendance WHERE student_id = ? AND date = ? AND subject_id = ?");
    $stmt->bind_param("isi", $student_id, $date, $subject_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Update existing record
        $update = $conn->prepare("UPDATE attendance SET status = ? WHERE student_id = ? AND date = ?");
        $update->bind_param("sis", $status, $student_id, $date);
        if ($update->execute()) {
            echo "Updated attendance for student $student_id on $date";
        } else {
            echo "Update failed: " . $update->error;
        }
    } else {
        // Insert new record
        $insert = $conn->prepare("INSERT INTO attendance (student_id, subject_id, recorded_by, date, status) VALUES (?, ?, ?, ?, ?)");
        $insert->bind_param("iiiss", $student_id, $subject_id, $staff_uid, $date, $status);
        if ($insert->execute()) {
            echo "Inserted attendance for student $student_id on $date";
        } else {
            echo "Insert failed: " . $insert->error;
        }
    }
} else {
    echo "Error: Missing POST fields.";
}
?>
