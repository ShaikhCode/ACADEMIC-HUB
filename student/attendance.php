<?php
session_start();
include '../connect/config.php';

// Ensure Only Students Can Access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$page_no = 2; // page no

$student_id = $_SESSION["user_id"];
$c_id = $_SESSION['college_id'];
$class_id = $_SESSION['class_id'];

// Fetch Onboarding
$sql = "SELECT check_b FROM students WHERE user_id='$student_id' AND college_id='$c_id'";
$result = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($result);


$onboardingCompleted = 0; // Default: Not completed

$check_b = isset($data['check_b']) ? trim($data['check_b']) : '';
$completed_pages = array_filter(array_map('trim', explode(',', $check_b))); // Clean and split values

$page_no = strval($page_no); // Ensure it's a string

// Debugging logs
echo "<script>console.log('Fetched check_b value: " . addslashes(json_encode($check_b)) . "');</script>";
echo "<script>console.log('Completed pages array: " . addslashes(json_encode($completed_pages)) . "');</script>";
echo "<script>console.log('Checking page_no: " . addslashes(json_encode($page_no)) . " (Type: " . gettype($page_no) . ")');</script>";



// Check if page_no exists in completed pages
if (in_array($page_no, $completed_pages, true)) {
    $onboardingCompleted = 1;
}
echo "<script>console.log('Onboarding Completed: " . addslashes(json_encode($onboardingCompleted)) . "');</script>";
error_log("Onboarding Completed: " . var_export($onboardingCompleted, true));


$sql9 = "SELECT COUNT(c.subject_id) as subject_count ,b.branch FROM class_subjects c LEFT JOIN classes b ON c.class_id=b.class_id WHERE c.class_id = ? AND c.college_id = ?";
$stm9 = $conn->prepare($sql9);
$stm9->bind_param("ii", $class_id, $c_id); // Assuming both are integers
$stm9->execute();
$result9 = $stm9->get_result();
$row9 = $result9->fetch_assoc();

$subject_count = $row9['subject_count']; // Fetching the count value
$branch_name = $row9['branch'];

// ✅ Fetch Overall Attendance Percentage
$sqlAttendanceOverall = "SELECT 
    COUNT(DISTINCT CONCAT(date, subject_id)) AS total_lectures, 
    SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) AS attended_lectures
    FROM attendance WHERE student_id = ?";

$stmt = mysqli_prepare($conn, $sqlAttendanceOverall);
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$resultOverall = mysqli_stmt_get_result($stmt);
$dataOverall = mysqli_fetch_assoc($resultOverall);

$total_lectures = $dataOverall['total_lectures'] ?? 0;  // Avoid undefined key error
$attended_lectures = $dataOverall['attended_lectures'] ?? 0;

$attendancePercentage = ($total_lectures > 0) ? round(($attended_lectures / $total_lectures) * 100) : 0;


// Prevent division by zero
$attendancePercentage = ($dataOverall['attended_lectures'] > 0) ?
    round(($dataOverall['attended_lectures'] / $dataOverall['total_lectures']) * 100) : 0;
$subjectWiseAttendance = [];
$sqlAttendanceSubject = "SELECT s.subject_name, 
        COUNT(DISTINCT a.date) AS total_days, 
        COUNT(DISTINCT CASE WHEN a.status = 'Present' THEN a.date END) AS present_days 
        FROM attendance a 
        JOIN subjects s ON a.subject_id = s.subject_id 
        WHERE a.student_id = ? 
        GROUP BY a.subject_id, s.subject_name";

$stmt2 = mysqli_prepare($conn, $sqlAttendanceSubject);
mysqli_stmt_bind_param($stmt2, "i", $student_id);
mysqli_stmt_execute($stmt2);
$resultSubject = mysqli_stmt_get_result($stmt2);

while ($row = mysqli_fetch_assoc($resultSubject)) {
    $subject_name = $row['subject_name'];
    $total_days = $row['total_days'];
    $present_days = $row['present_days'];

    // Avoid division by zero
    $percentage = ($total_days > 0) ? round(($present_days / $total_days) * 100) : 0;

    // Store both percentage and total_days
    $subjectWiseAttendance[$subject_name] = [
        'percentage' => $percentage,
        'total_days' => $total_days
    ];
}
$pageno = 2; // Assign a unique number to each page (p1 = 1, p2 = 2, etc.)
$user_id = $_SESSION['user_id'];

// page checker ONBOARDING START IF DONE
$sql = "SELECT * FROM students WHERE user_id='$user_id'";
$result = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($result);
$check_b = isset($data['check_b']) ? trim($data['check_b']) : '';
$completed_pages = array_filter(array_map('trim', explode(',', $check_b))); // Clean and split values


// Debugging logs
echo "<script>console.log('Fetched check_b value: " . addslashes(json_encode($check_b)) . "');</script>";
echo "<script>console.log('Completed pages array: " . addslashes(json_encode($completed_pages)) . "');</script>";
echo "<script>console.log('Checking page_no: " . addslashes(json_encode($page_no)) . " (Type: " . gettype($page_no) . ")');</script>";



// Check if page_no exists in completed pages
if (in_array($page_no, $completed_pages, true)) {
    $onboardingCompleted = 1;
}
echo "<script>console.log('Onboarding Completed: " . addslashes(json_encode($onboardingCompleted)) . "');</script>";
error_log("Onboarding Completed: " . var_export($onboardingCompleted, true));



// ✅ Fetch Attendance Records (Detailed Table)
$sqlAttendanceRecords = "SELECT a.date, s.subject_name, a.status, 
    DATE_FORMAT(a.time, '%h:%i %p') AS update_time 
    FROM attendance a 
    JOIN subjects s ON a.subject_id = s.subject_id 
    WHERE a.student_id = ? 
    ORDER BY a.date DESC";

$stmt3 = mysqli_prepare($conn, $sqlAttendanceRecords);
mysqli_stmt_bind_param($stmt3, "i", $student_id);
mysqli_stmt_execute($stmt3);
$resultRecords = mysqli_stmt_get_result($stmt3);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance</title>
    <link rel="stylesheet" href="css/attendance.css">
    <link rel="stylesheet" href="style.css">

    <!-- Intro.js CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intro.js/minified/introjs.min.css">

    <!-- Intro.js JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/intro.js/minified/intro.min.js"></script>


    <link rel="shortcut icon" href="../img/favicon.png" type="image/x-icon" />


</head>

<body>
    

    <!-- Header -->
    <header class="header">
        <div class="logo">Academic Hub</div>
        <nav class="navbar" data-intro=" Here's your navigation menu" data-step="1">
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

        <!-- Attendance Section -->
        <section id="attendance">
            <h6>Student Attendance:</h6><br>
            <div class="attendance-container">
                <div class="attendance-header" data-intro=" Here's your Subjects Overall attendance% will display" data-step="2">
                    <p><strong class="space">Department:</strong> <?php echo $branch_name; ?> </p>

                    <p><strong class="space">Overall Attendance:</strong> <?php echo $attendancePercentage; ?>%</p>
                </div>

                <!-- Subject-Wise Attendance -->
                <h4>Subject-wise Attendance:</h4>

                <table data-intro=" Here's your subject wise attendance + Lecturestaken will displayed" data-step="3">
                    <thead>
                        <tr>
                            <th>Subject_name</th>
                            <th>Progress</th>
                            <th>Total Lectures</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subjectWiseAttendance as $subject => $data): ?>
                            <tr>
                                <td><?php echo $subject; ?></td>
                                <td><?php echo $data['percentage']; ?>%</td>
                                <td><?php echo $data['total_days']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>


                <br><br>

                <!-- Attendance Table -->
                <h4>Attendance Records:</h4>
                <div style="overflow-x: auto;" data-intro=" Here's your date wise and realtime attendance will display" data-step="4">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Time of Update</th>
                            </tr>
                        </thead>
                        <tbody id="attendance-table">
                            <?php while ($row = mysqli_fetch_assoc($resultRecords)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['date']); ?></td>
                                    <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
                                    <td class="status <?php echo strtolower(htmlspecialchars($row['status'])); ?>">
                                        <?php echo htmlspecialchars($row['status']); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['update_time']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2025 Academic Hub. All rights reserved.</p>
    </footer>

    <!-- Onboarding Modal -->
    <div id="onboarding-modal" class="modal">
        <div class="modal-content">
            <h2>Welcome to Academic Hub!</h2>
            <p>Let's take a guided tour of your dashboard.</p>
            <button id="start-tour-btn" onclick="startOnboarding()">Start Tour</button>
            <button id="skip-btn" onclick="completeOnboarding(2)">Skip</button>
        </div>
    </div>

    <script src="script.js"></script>
    

    <!-- Place this where your JS is -->
    <script>
        const onboardingCompleted = <?php echo json_encode($onboardingCompleted == 0); ?>;

        if (onboardingCompleted) {
            document.getElementById("onboarding-modal").style.display = 'block';
        }
        const currentPage = <?php echo $pageno; ?>;

        function startOnboarding() {
            const intro = introJs();
            document.getElementById("onboarding-modal").style.display = 'none';

            intro.oncomplete(function() {
                sendCompletionStatus(currentPage);
            });

            intro.onexit(function() {
                sendCompletionStatus(currentPage);
            });

            intro.start();
        }

        function sendCompletionStatus(pageNumber) {
            fetch('api/update_on.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded' // Because you're using $_POST, not json
                    },
                    body: new URLSearchParams({
                        page_no: pageNumber
                    })
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Update successful:', data);
                })
                .catch(error => {
                    console.error('Error updating onboarding status:', error);
                });

        }
    </script>
</body>

</html>