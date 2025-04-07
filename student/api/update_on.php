<?php
session_start();
include '../../connect/config.php';

if (!isset($_SESSION['student_id'])) {
    die("Error: Student not logged in.");
}

if (!isset($_POST['page_no'])) {
    die("Error: No page number received.");
}

$student_id = intval($_SESSION['student_id']); // Ensure it's an integer
$page_no = intval($_POST['page_no']); // Ensure it's an integer

// Fetch existing completed pages
$sql = "SELECT check_b FROM students WHERE student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$completed_pages = ($row && !empty($row['check_b'])) ? explode(",", $row['check_b']) : [];

if (!in_array($page_no, $completed_pages)) { // If page is not already marked as done
    $completed_pages[] = $page_no;
    $updated_pages = implode(",", $completed_pages); // Convert array back to a comma-separated string

    // ✅ Update the student's onboarding progress
    $sql = "UPDATE students SET check_b = ? WHERE student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $updated_pages, $student_id);
    $stmt->execute();
}

$conn->close();
echo "Success: Page $page_no marked as completed.";
?>
