<?php
session_start();
include '../../connect/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Student not logged in.']);
    exit;
}

if (!isset($_POST['page_no'])) {
    echo json_encode(['success' => false, 'error' => 'No page number received.']);
    exit;
}

$student_id = intval($_SESSION['user_id']);
$page_no = intval($_POST['page_no']);

$sql = "SELECT check_b FROM admins WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$completed_pages = ($row && !empty($row['check_b'])) ? explode(",", $row['check_b']) : [];

if (!in_array($page_no, $completed_pages)) {
    $completed_pages[] = $page_no;
    $updated_pages = implode(",", $completed_pages);

    $sql = "UPDATE admins SET check_b = ? WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $updated_pages, $student_id);
    $stmt->execute();
}

$conn->close();

echo json_encode(['success' => true, 'message' => "Page $page_no marked as completed."]);
