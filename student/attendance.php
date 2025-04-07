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

    <link rel="shortcut icon" href="../img/favicon.png" type="image/x-icon" />

    <style>
        /* Modal Styling */
        .modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
            text-align: center;
            z-index: 1001;
        }

        .modal-content button {
            margin: 10px;
            padding: 10px;
            cursor: pointer;
        }

        /* Overlay for Background Blur */
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            z-index: 1000;
        }

        /* Tooltip Styling */
        .tooltip {
            display: none;
            position: absolute;
            background: black;
            color: white;
            padding: 8px;
            border-radius: 5px;
            font-size: 14px;
            z-index: 1002;
        }

        /* Blurred Background Effect */
        .blur {
            filter: blur(5px);
        }

        /* ✅ Added Blur Effect */
        .blur-effect {
            filter: blur(5px);
            pointer-events: none;
            /* Prevent clicking blurred elements */
        }
    </style>
</head>

<body>
    <!-- Onboarding Modal -->
    <div id="onboarding-modal" class="modal">
        <div class="modal-content">
            <h2>Welcome to Academic Hub!</h2>
            <p>Let's take a guided tour of your dashboard.</p>
            <button id="start-tour-btn">Start Tour</button>
            <button id="skip-btn" onclick="completeOnboarding(2)">Skip</button>
        </div>
    </div>

    <!-- Overlay for Blurring Background -->
    <div id="overlay" class="overlay"></div>

    <!-- Tooltips -->
    <div id="tooltip" class="tooltip"></div>

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

        <!-- Attendance Section -->
        <section id="attendance">
            <h6>Student Attendance:</h6><br>
            <div class="attendance-container">
                <div class="attendance-header">
                    <p><strong class="space">Department:</strong> <?php echo $branch_name; ?> </p>

                    <p><strong class="space">Overall Attendance:</strong> <?php echo $attendancePercentage; ?>%</p>
                </div>

                <!-- Subject-Wise Attendance -->
                <h4>Subject-wise Attendance:</h4>

                <table>
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
                <div style="overflow-x: auto;">
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

    <script src="script.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var onboardingCompleted = <?php echo $onboardingCompleted; ?>;
            console.log("Onboarding script loaded");

            const overlay = document.getElementById("overlay");
            const onboardingModal = document.getElementById("onboarding-modal");
            const metricsSection = document.querySelector("#attendance");
            const popup = document.createElement("div");
            popup.classList.add("onboarding-popup");

            if (onboardingCompleted == 0) {

                popup.style.position = "absolute";
                popup.style.background = "white";
                popup.style.padding = "15px";
                popup.style.borderRadius = "8px";
                popup.style.boxShadow = "0 0 10px rgba(0, 0, 0, 0.3)";
                popup.style.zIndex = "1002";
                popup.style.display = "none";
                document.body.appendChild(popup);

                if (onboardingCompleted == 0) {
                    onboardingModal.style.display = "block";
                    overlay.style.display = "block";
                }

                let steps = [];

                if (window.innerWidth > 989) {
                    // Large screens (PC)
                    steps = [{
                            selector: ".navbar",
                            message: "This is the navigation bar where you can access different sections."
                        },
                        {
                            selector: ".sidebar",
                            message: "This is the sidebar for quick access to important links."
                        },
                        {
                            selector: "#attendance",
                            message: "Here you can see your Subject-Wise, Total, and Daily Attendance."
                        }
                    ];
                } else {
                    // Small screens (Mobile/Tablets) - Exclude sidebar and navbar
                    steps = [{
                            selector: ".hamburger",
                            message: "This is the navigation Menu where you can access different sections."
                        },
                        {
                            selector: "#attendance",
                            message: "Here you can see your Subject-Wise, Total, and Daily Attendance."
                        }
                    ];
                }



                function showStep() {
                    if (currentStep >= steps.length) {
                        endTour();
                        return;
                    }

                    const {
                        selector,
                        message
                    } = steps[currentStep];
                    const element = document.querySelector(selector);

                    if (element) {
                        const rect = element.getBoundingClientRect();
                        let top = rect.top + window.scrollY + rect.height + 10;
                        let left = rect.left;

                        popup.innerHTML = `<p>${message}</p><button id='nextStep'>Next</button>`;
                        popup.style.display = "block";

                        // Adjust position if popup goes outside the screen
                        const popupRect = popup.getBoundingClientRect();
                        const screenWidth = window.innerWidth;
                        const screenHeight = window.innerHeight;

                        // Adjust Left Position if Overflowing Right
                        if (left + popupRect.width > screenWidth) {
                            left = screenWidth - popupRect.width - 20; // 20px padding from the right
                        }
                        if (left < 10) left = 10; // Prevents going off the left side

                        // Adjust Top Position if Overflowing Bottom
                        if (top + popupRect.height > screenHeight) {
                            top = rect.top + window.scrollY - popupRect.height - 10; // Move above the element
                        }
                        if (top < 10) top = 10; // Prevents going off the top side

                        popup.style.top = `${top}px`;
                        popup.style.left = `${left}px`;

                        document.getElementById("nextStep").addEventListener("click", nextStep);
                    }
                }


                function nextStep() {
                    currentStep++;
                    showStep();
                }

                function startTour() {
                    currentStep = 0;
                    onboardingModal.style.display = "none";
                    overlay.style.display = "none"; // Ensure overlay remains hidden
                    metricsSection.style.display = "flex"; // Show metrics section
                    showStep();
                }

                function endTour() {
                    popup.style.display = "none";
                    overlay.style.display = "none"; // Ensure overlay remains hidden
                    metricsSection.style.display = "flex"; // Ensure metrics are visible after tour
                    completeOnboarding(2);
                }

                document.getElementById("start-tour-btn").addEventListener("click", startTour);
                document.getElementById("skip-btn").addEventListener("click", endTour);

                function completeOnboarding(pageNo) {
                    console.log("Sending page_no:", pageNo);
                    fetch("api/update_on.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: `page_no=${pageNo}`
                        })
                        .then(response => response.text())
                        .then(data => {
                            console.log("Response:", data);
                            window.location.reload();
                        })
                        .catch(error => console.error("Error:", error));
                }


            }


        });
    </script>
</body>

</html>