<?php
session_start();
include '../connect/config.php'; // Ensure you include database connection

// Ensure Only Students Can Access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}
$pageno = 5; // Assign a unique number to each page (p1 = 1, p2 = 2, etc.)
$user_id = $_SESSION['user_id'];
$onboardingCompleted = 0; // Default: Not completed
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



?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="css/marks.css">

    <!-- Intro.js CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intro.js/minified/introjs.min.css">

    <!-- Intro.js JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/intro.js/minified/intro.min.js"></script>


    <link rel="shortcut icon" href="../img/favicon.png" type="image/x-icon" />


    <style>
        .highlight-row {
            background-color: #d6f0ff;
        }

        #leaderboardFilter:hover {
            background: #dfe6e9;
            border-color: #2980b9;
        }

        #leaderboardFilter:focus {
            outline: none;
            border-color: #2ecc71;
            /* Green border when focused */
            background: #fff;
        }

        @media (max-width:769px) {
            section {
                width: 90%;
                margin: 0 auto;
            }

        }

        @media (min-width:769px) {
            section {
                width: 70%;
                margin: 0 auto;
            }

        }
    </style>
</head>

<body>
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
        <aside class="sidebar">
            <ul>
                <li><a href="stud.php">Dashboard</a></li>
                <li><a href="attendance.php">Attendance</a></li>
                <li><a href="marks.php">Marks</a></li>
                <li><a href="feedback.php">Feedback</a></li>
                <li><a href="Leaderboard.php">Leaderboard</a></li>
            </ul>
        </aside>

        <section>
            <div style="
    margin: 20px auto;
    display: flex;
    justify-content: center;
" >
                <select id="leaderboardFilter" style="
        height: 58px;
        width: max-content; /* Set a fixed width */
        text-align: center;
        font-weight: bold;
        font-size: 16px;
        padding: 10px;
        border: 2px solid #3498db; /* Blue border */
        border-radius: 10px;
        background: #ecf0f1; /* Light gray background */
        color: #2c3e50; /* Dark text */
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1); /* Soft shadow */
        transition: all 0.3s ease-in-out;
        cursor: pointer;
    " data-intro=" Here's your filter for leaderboard" data-step="2">
                    <option value="overall">🏆 Overall Leaderboard</option>
                    <option value="marks">📚 Marks-Based Leaderboard</option>
                    <option value="attendance">📝 Attendance-Based Leaderboard</option>
                </select>
            </div>

            <div id="leaderboard" style="overflow-x: auto;" data-intro=" Here's your Leaderboard your row will be highlighted " data-step="3"></div>
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
            <button id="skip-btn" onclick="completeOnboarding(5)">Skip</button>
        </div>
    </div>


    <script src="script.js"></script>


    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const leaderboardFilter = document.getElementById("leaderboardFilter");
            leaderboardFilter.addEventListener("change", updateLeaderboard);
            updateLeaderboard(); // Load the default leaderboard on page load

            function updateLeaderboard() {
                let filter = leaderboardFilter.value;
                fetch(`api/fetch.php?action=${filter}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            document.getElementById("leaderboard").innerHTML = "<p>Error fetching leaderboard data.</p>";
                            return;
                        }
                        console.log("API Response:", data); // Debugging line

                        let leaderboardHTML = `<table>
                    <tr><th>Roll Number</th><th>Name</th>`;

                        // Dynamically adjust columns based on filter
                        if (filter === "marks") {
                            leaderboardHTML += `<th>Marks %</th>`;
                        } else if (filter === "attendance") {
                            leaderboardHTML += `<th>Attendance %</th>`;
                        } else if (filter === "overall") {
                            leaderboardHTML += `<th>Overall Score</th>`;
                        }

                        leaderboardHTML += `</tr>`; // Close header row

                        const loggedInStudentId = "<?php echo $_SESSION['student_id']; ?>";


                        data.forEach((student) => {
                            let score = parseFloat(student.overall_score) || 0;
                            let marks = parseFloat(student.marks_percentage) || 0;
                            let attendance = parseFloat(student.attendance_percentage) || 0;

                            let formattedScore = score.toFixed(2);
                            let formattedMarks = marks.toFixed(2);
                            let formattedAttendance = attendance.toFixed(2);

                            const avatarPath = student.avt ? `../img/avt/${student.avt}.png` : `../../img/avt/default.png`;

                            // Add highlight class if student ID matches session ID
                            let highlightClass = student.id == loggedInStudentId ? 'highlight-row' : '';

                            leaderboardHTML += `<tr class="${highlightClass}">
                            <td><img src="${avatarPath}" alt="logo" width="40px"></td>
                            <td>${student.name}</td>`;

                            if (filter === "marks") {
                                leaderboardHTML += `<td>${formattedMarks}%</td>`;
                            } else if (filter === "attendance") {
                                leaderboardHTML += `<td>${formattedAttendance}%</td>`;
                            } else if (filter === "overall") {
                                leaderboardHTML += `<td>${formattedScore}</td>`;
                            }

                            leaderboardHTML += `</tr>`; // Close row
                        });

                        leaderboardHTML += "</table>";
                        document.getElementById("leaderboard").innerHTML = leaderboardHTML;
                    })
                    .catch(error => console.error("Error fetching leaderboard:", error));
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