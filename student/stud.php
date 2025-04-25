<?php
session_start();
include '../connect/config.php';

// Ensure Only Students Can Access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$pageno = 1; // Assign a unique number to each page (p1 = 1, p2 = 2, etc.)
$stud_id = $_SESSION["user_id"];
$c_id = $_SESSION["college_id"];
$username = $_SESSION["username"];
$user_id = $stud_id;


// Fetch Attendance and Marks Data
$sql = "SELECT * FROM students WHERE user_id='$stud_id' AND college_id='$c_id'";
$result = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($result);


$avg = $data['avg'];

$onboardingCompleted = 0; // Default: Not completed

$check_b = isset($data['check_b']) ? trim($data['check_b']) : '';
$completed_pages = array_filter(array_map('trim', explode(',', $check_b))); // Clean and split values

$page_no = strval($pageno); // Ensure it's a string

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



// ✅ Fetch Overall Attendance Percentage
$sqlAttendanceOverall = "SELECT 
    COUNT(DISTINCT CONCAT(date, subject_id)) AS total_lectures, 
    SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) AS attended_lectures
    FROM attendance WHERE student_id = ?";

$stmt = mysqli_prepare($conn, $sqlAttendanceOverall);
mysqli_stmt_bind_param($stmt, "i", $stud_id);
mysqli_stmt_execute($stmt);
$resultOverall = mysqli_stmt_get_result($stmt);
$dataOverall = mysqli_fetch_assoc($resultOverall);

$total_lectures = $dataOverall['total_lectures'] ?? 0;  // Avoid undefined key error
$attended_lectures = $dataOverall['attended_lectures'] ?? 0;

$attendancePercentage = ($total_lectures > 0) ? round(($attended_lectures / $total_lectures) * 100) : 0;


// Prevent division by zero
$attendancePercentage = ($dataOverall['attended_lectures'] > 0) ?
    round(($dataOverall['attended_lectures'] / $dataOverall['total_lectures']) * 100) : 0;

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Academic Hub</title>
    <link rel="stylesheet" href="style.css">

    <link rel="shortcut icon" href="../img/favicon.png" type="image/x-icon" />

    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.9.1/gsap.min.js"></script>

    <!-- Intro.js CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intro.js/minified/introjs.min.css">

    <!-- Intro.js JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/intro.js/minified/intro.min.js"></script>



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

        .progress-container {
            width: 160px;
            height: 160px;
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .progress-circle {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            background: conic-gradient(#ddd 0deg, #ddd 360deg);
            /* Default empty */
            transition: background 1s ease-out;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
            /* Subtle Shadow */
        }

        .progress-circle::before {
            content: "";
            position: absolute;
            width: 85%;
            height: 85%;
            background: white;
            border-radius: 50%;
            box-shadow: inset 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        .progress-circle span {
            position: absolute;
            font-size: 1.7em;
            font-weight: bold;
            z-index: 2;
            color: #333;
            /* Dark for readability */
        }

        @keyframes progressGlow {
            0% {
                box-shadow: 0px 0px 10px rgba(0, 123, 255, 0.5);
            }

            50% {
                box-shadow: 0px 0px 20px rgba(0, 123, 255, 1);
            }

            100% {
                box-shadow: 0px 0px 10px rgba(0, 123, 255, 0.5);
            }
        }
    </style>

</head>

<body>


    <!-- Overlay for Blurring Background -->
    <div id="overlay" class="overlay"></div>

    <!-- Tooltips -->
    <div id="tooltip" class="tooltip"></div>


    <div class="overlay"></div>

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

        <!-- Dashboard -->
        <main class="main-content">
            <section id="dashboard">
                <h1>Welcome, <span><?php echo $username; ?></span>!</h1>
                <button id="logoutbtn"><a href="../connect/logout.php">Logout</a></button>

                <div class="metrics" data-intro=" Here's your Progress is calculated" data-step="2">
                    <div class="card" style="background-color:rgb(83, 83, 129);">
                        <h3>Attendance</h3>
                        <div class="progress-container">
                            <div class="progress-circle" id="attendanceCircle">
                                <span id="attendanceCirclePercentage">0%</span>
                            </div>
                        </div>
                    </div>

                    <div class="card" style="background-color:rgb(137, 137, 214);">
                        <h3>Grades</h3>
                        <p id="motivationText" style="color: #ffffff;">Loading...</p>
                    </div>

                    <div class="card" style="background-color: #688dc1;">
                        <h3>Progress</h3>
                        <div class="progress-container">
                            <div class="progress-circle" id="progressCircle">
                                <span id="progressCirclePercentage">0%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <br><br>
            <section id="subject_car">
                <h4 style="margin: 23px 0; font-size: medium;">Total Subjects to Complete in this Semester:</h4>

                <div class="metrics" data-intro=" Here's your Subject and faculty_name will display" data-step="3">

                    <?php
                    // Fetch subject IDs from the `students` table
                    $student_id = $_SESSION['user_id'];
                    $query = "SELECT sub_info FROM students WHERE user_id = $student_id";
                    $result = mysqli_query($conn, $query);
                    $row = mysqli_fetch_assoc($result);

                    if ($row && !empty($row['sub_info'])) {
                        $subject_ids = explode(',', $row['sub_info']); // Convert to array
                        $subject_id_list = implode(',', $subject_ids); // Convert back to a comma-separated string

                        // Fetch subject names, total lectures considering unique (date, time), and faculty names
                        $query = "SELECT 
                                   s.subject_name, 
                                   COUNT(DISTINCT CONCAT(a.date, a.time)) AS total_lectures,  
                                   u.username AS faculty_name  
                                 FROM subjects s
                                 LEFT JOIN attendance a ON a.subject_id = s.subject_id AND a.student_id = $student_id
                                 LEFT JOIN users u ON a.recorded_by = u.user_id 
                                 WHERE s.subject_id IN ($subject_id_list)
                                 GROUP BY s.subject_id, u.username";

                        $result = mysqli_query($conn, $query);

                        $softColors = [
                            "#FFC1C1",
                            "#FFDAB9",
                            "#FAFAD2",
                            "#E0FFFF",
                            "#D1E7E0",
                            "#C5D8A4",
                            "#D7BDE2",
                            "#F9E79F",
                            "#AED6F1",
                            "#F5CBA7"
                        ]; // Array of soft colors

                        $index = 0; // Track colors

                        while ($row = mysqli_fetch_assoc($result)) {
                            $color = $softColors[$index % count($softColors)]; // Cycle through colors
                            $index++; // Increment color index
                    ?>
                            <div class="card" style="background-color: <?php echo $color; ?>; padding: 15px; border-radius: 10px; margin: 10px 0; box-shadow: 2px 2px 10px rgba(0,0,0,0.1);">
                                <h3><?php echo htmlspecialchars($row['subject_name']); ?></h3>
                                <p>Total Lectures: <?php echo $row['total_lectures']; ?></p>
                                <p>Faculty: <?php echo htmlspecialchars($row['faculty_name']); ?></p>
                            </div>
                    <?php }
                    } else {
                        echo "<p>No subjects found.</p>";
                    }
                    ?>
                </div>
            </section>

        </main>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2025 Academic Hub. All rights reserved.</p>
    </footer>

    <!-- Onboarding Modal -->
    <div id="onboarding-modal" class="modal">
        <div class="modal-content">
            <h2>Welcome to Academic Hub!</h2>
            <p>Let's take a guided tour of your Home-dashboard.</p>
            <button onclick="startOnboarding()" style="margin: 10px;">Start Tour</button>
        </div>
    </div>


    <script src="script.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function animateProgress(elementId, targetPercentage) {
                let currentPercentage = 0;
                const percentageElementId = elementId + 'Percentage';
                const percentageElement = document.getElementById(percentageElementId);
                const progressElement = document.getElementById(elementId);

                if (percentageElement) {
                    const interval = setInterval(function() {
                        currentPercentage++;
                        percentageElement.textContent = currentPercentage + '%';

                        // Dynamic color change based on percentage
                        let progressColor = currentPercentage < 50 ? "#ff4d4d" :
                            currentPercentage < 75 ? "#ffcc00" :
                            "#28a745";

                        progressElement.style.background = `conic-gradient(
                    ${progressColor} ${currentPercentage * 3.6}deg, 
                    #ddd ${currentPercentage * 3.6}deg
                )`;

                        progressElement.style.animation = "progressGlow 1s infinite alternate"; // Add glow animation

                        if (currentPercentage >= targetPercentage) {
                            clearInterval(interval);
                        }
                    }, 10);
                } else {
                    console.error("Element not found:", percentageElementId);
                }
            }

            // PHP variables to JavaScript
            const attendancePercentage = <?php echo $attendancePercentage; ?>;
            const progressPercentage = <?php echo $avg; ?>;

            animateProgress('attendanceCircle', attendancePercentage);
            animateProgress('progressCircle', progressPercentage);


            // Motivation Logic
            const motivationTextElement = document.getElementById('motivationText');

            if (progressPercentage < 50) {
                motivationTextElement.textContent = "Keep pushing! Every step counts.";
            } else if (progressPercentage < 75) {
                motivationTextElement.textContent = "Great progress! You're on the right track.";
            } else if (progressPercentage < 90) {
                motivationTextElement.textContent = "Excellent work! You're almost there.";
            } else {
                motivationTextElement.textContent = "You're a superstar! Keep shining!";
            }
        });
    </script>

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