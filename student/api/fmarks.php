<?php
session_start();
header('Content-Type: application/json');
include("../../connect/config.php");

if (!isset($_SESSION['student_id'])) {
    die("Error: Student not logged in.");
}

if (isset($_POST['subject_id']) && isset($_POST['subject_name'])) {
    $subject_id = $_POST['subject_id'];
    $student_id = $_SESSION['user_id']; // Ensure session has student ID

    $query = "SELECT e.exam_name, m.marks_obtained, m.total_marks 
              FROM marks m
              JOIN exam_types e ON m.exam_id = e.exam_id
              WHERE m.student_id = ? AND m.subject_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $student_id, $subject_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<table style='width: 100%; border-collapse: collapse; margin-top: 10px;' id='examTable_$subject_id'>";
        echo "<thead><tr style='background-color: #ddd;'><th>Exam Name</th><th>Obtained Marks</th><th>Total Marks</th></tr></thead><tbody>";

        $rowIndex = 0;
        while ($row = $result->fetch_assoc()) {
            $hiddenClass = ($rowIndex >= 5) ? "hidden-row" : "";
            echo "<tr class='$hiddenClass' data-index='$rowIndex'>
                    <td>" . htmlspecialchars($row['exam_name']) . "</td>
                    <td>" . round($row['marks_obtained'], 2) . "</td>
                    <td>" . round($row['total_marks'], 2) . "</td>
                  </tr>";
            $rowIndex++;
        }
        echo "</tbody></table>";

        // Add Show More button if rows > 5
        if ($result->num_rows > 5) {
            echo "<button class='show-more-btn' onclick='toggleRows($subject_id)'>Show More</button>";
        }
    } else {
        echo "<p style='color: red;'>No exams found for this subject.</p>";
    }

    $stmt->close();
}
?>
