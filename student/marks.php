<?php

session_start();
include '../connect/config.php'; // Ensure you include database connection

// Ensure Only Students Can Access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

// Get logged-in student ID
$student_id = $_SESSION['user_id'];
$college_id = $_SESSION['college_id'];
// Fetch common exam IDs for the college
$query_exams = "SELECT exam_id FROM exam_types WHERE type = 'common' AND college_id = ?";
$stmt_exams = $conn->prepare($query_exams);
$stmt_exams->bind_param("i", $college_id);
$stmt_exams->execute();
$result_exams = $stmt_exams->get_result();

$exam_ids = [];
while ($row = $result_exams->fetch_assoc()) {
    $exam_ids[] = $row['exam_id'];
}

// Check if no common exams found
if (empty($exam_ids)) {
    die("No common exams found for this college.");
}

$result = '';
// Check if no common exams found
if (empty($exam_ids)) {
    $error_message = ("No common exams found for this college.");
} else {

    // Convert exam IDs array to a comma-separated string for SQL
    $exam_ids_str = implode(",", $exam_ids);

    // Query to fetch marks with subject names and dynamic exam names
    $query = "SELECT 
            s.subject_id, 
            s.subject_name,
            GROUP_CONCAT(e.exam_name ORDER BY m.exam_id) AS exam_names,
            GROUP_CONCAT(m.marks_obtained ORDER BY m.exam_id) AS marks_obtained,
            GROUP_CONCAT(m.total_marks ORDER BY m.exam_id) AS total_marks
          FROM marks m
          JOIN subjects s ON m.subject_id = s.subject_id
          JOIN exam_types e ON m.exam_id = e.exam_id
          WHERE m.student_id = ? AND m.exam_id IN ($exam_ids_str)
          GROUP BY s.subject_id, s.subject_name";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marks</title>
    <link rel="stylesheet" href="css/marks.css">
    <link rel="stylesheet" href="style.css">

    <link rel="shortcut icon" href="../img/favicon.png" type="image/x-icon" />

    <style>
        .hidden-row {
            display: none;
        }

        .show-more-btn {
            display: block;
            margin: 10px auto;
            padding: 8px 15px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .show-more-btn:hover {
            background: #0056b3;
        }

        .subject-card {
            padding: 15px;
            border-radius: 10px;
            margin: 10px 0;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .subject-card:hover {
            transform: translateY(-5px);
            box-shadow: 0px 6px 15px rgba(0, 0, 0, 0.3);
        }

        .subject-card h3 {
            text-align: center;
            font-size: 1.5em;
            font-weight: bold;
            text-shadow: 2px 2px 3px rgba(0, 0, 0, 0.3);
            /* Adding outline effect */
            color: #333;
        }

        .subject-card p {
            font-size: 1.1em;
            font-weight: bold;
            color: #1f2937;
        }
    </style>

</head>

<body>
    <div class="overlay"></div>

    <!-- Header -->
    <header class="header">
        <div class="logo">Academic Hub</div>
        <nav class="navbar">
            <a href="stud.php">Home</a>
            <a href="attendance.php">Attendance</a>
            <a href="marks.php">Marks</a>
            <a href="feedback.php">Feedback</a>
            <a href="Leaderboard.php">Leaderboard</a>
            <a href="profile.php"><img src="../img/avt/<?php echo $_SESSION['avt']; ?>.png" alt="Profile" style="vertical-align: middle;  height: 30px;  width: 30px;  object-fit: cover;  border-radius: 50%;">
                <span class="profile-text">Profile</span>
            </a>
        </nav>
        <div class="hamburger">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </header>

    <!-- Main Content -->
    <div class="container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <ul>
                <li><a href="stud.php">Dashboard</a></li>
                <li><a href="attendance.php">Attendance</a></li>
                <li><a href="marks.php">Marks</a></li>
                <li><a href="feedback.php">Feedback</a></li>
                <li><a href="Leaderboard.php">Leaderboard</a></li>
            </ul>
        </aside>



        <!-- Marks Section -->
        <section id="marks">
            <h2>Student Marks</h2>
            <div class="marks-container" style="cursor: pointer;">
                <?php
                $softColors = ["#FFC1C1", "#FFDAB9", "#FAFAD2", "#E0FFFF", "#D1E7E0", "#C5D8A4", "#D7BDE2", "#F9E79F", "#AED6F1", "#F5CBA7"];
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) :
                        $randomColor = $softColors[array_rand($softColors)];
                        $marksArray = !empty($row['marks_obtained']) ? explode(",", $row['marks_obtained']) : [];
                        $totalMarksArray = !empty($row['total_marks']) ? explode(",", $row['total_marks']) : [];
                        $examNamesArray = !empty($row['exam_names']) ? explode(",", $row['exam_names']) : [];

                        // Ensure all arrays have the same length
                        $examCount = min(count($marksArray), count($totalMarksArray), count($examNamesArray));

                        $totalMarksSum = array_sum($totalMarksArray);
                        $obtainedMarksSum = array_sum($marksArray);
                        $subjectId = $row['subject_id'];
                ?>
                        <div class="subject-card" style="background-color: <?php echo $randomColor; ?>;"
                            onclick="fetchExamDetails('<?php echo $subjectId; ?>', '<?php echo htmlspecialchars($row['subject_name']); ?>')">
                            <h3><?php echo htmlspecialchars($row['subject_name']); ?></h3>
                            <?php
                            for ($i = 0; $i < $examCount; $i++) {
                                echo "<p><strong>" . htmlspecialchars($examNamesArray[$i]) . ":</strong> " .
                                    round(floatval($marksArray[$i]), 2) . "/" . round(floatval($totalMarksArray[$i]), 2) . "</p>";
                            }
                            ?>
                            <p><strong>Total:</strong> <?php echo round(floatval($obtainedMarksSum), 2); ?>/<?php echo round(floatval($totalMarksSum), 2); ?></p>
                            <input type="hidden" value="<?php echo htmlspecialchars($subjectId); ?>" name="sub_id" />
                        </div>
                        <!-- Table Placeholder (Initially Hidden) -->
                        <div id="exam_table_<?php echo $subjectId; ?>" class="exam-table-container" style="display: none;"></div>
                <?php
                    endwhile;
                } else {
                    echo "<p>No subjects found.</p>";
                }
                ?>
            </div>


            <div>

            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2025 Academic Hub. All rights reserved.</p>
    </footer>

    <script src="script.js"></script>

    <script>
        function fetchExamDetails(subjectId, subjectName) {
            let tableContainer = document.getElementById("exam_table_" + subjectId);

            if (!tableContainer) {
                console.error("Error: No element found with ID exam_table_" + subjectId);
                return;
            }

            // Close other open tables
            document.querySelectorAll('.exam-table-container').forEach(el => {
                if (el.id !== "exam_table_" + subjectId) {
                    el.style.display = "none";
                    el.innerHTML = "";
                }
            });

            // If already open, close it
            if (tableContainer.style.display === "block") {
                tableContainer.style.display = "none";
                tableContainer.innerHTML = "";
                return;
            }

            // Send AJAX request
            let xhr = new XMLHttpRequest();
            xhr.open("POST", "api/fmarks.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            xhr.onload = function() {
                if (this.status === 200) {
                    tableContainer.innerHTML = this.responseText;
                    tableContainer.style.display = "block";
                } else {
                    console.error("Error fetching data:", this.status);
                }
            };

            xhr.onerror = function() {
                console.error("Request failed");
            };

            xhr.send("subject_id=" + encodeURIComponent(subjectId) + "&subject_name=" + encodeURIComponent(subjectName));
        }

        // Function to toggle rows visibility
        function toggleRows(subjectId) {
            let table = document.getElementById(`examTable_${subjectId}`);
            let rows = table.querySelectorAll(".hidden-row");
            let button = table.nextElementSibling; // Show More button

            if (button.innerText === "Show More") {
                rows.forEach(row => row.style.display = "table-row");
                button.innerText = "Show Less";
            } else {
                rows.forEach(row => row.style.display = "none");
                button.innerText = "Show More";
            }
        }
    </script>

</body>

</html>