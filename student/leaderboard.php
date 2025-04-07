<?php
session_start();
include '../connect/config.php'; // Ensure you include database connection

// Ensure Only Students Can Access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}


?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="css/marks.css">

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
">
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
    ">
                    <option value="overall">🏆 Overall Leaderboard</option>
                    <option value="marks">📚 Marks-Based Leaderboard</option>
                    <option value="attendance">📝 Attendance-Based Leaderboard</option>
                </select>
            </div>

            <div id="leaderboard" style="overflow-x: auto;"></div>
        </section>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2025 Academic Hub. All rights reserved.</p>
    </footer>


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
</body>

</html>