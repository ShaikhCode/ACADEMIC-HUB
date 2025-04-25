<?php
session_start();
include '../connect/config.php';
include '../connect/functions.php';

// Ensure Only Staff Can Access
if (!isset($_SESSION['user_id']) && $_SESSION['role'] == 'staff') {
    header("Location: ../login.php");
    exit();
}
$username = $_SESSION["username"];
$c_id = $_SESSION["college_id"];
$staff_id = $_SESSION['staff_id'];

if (isset($_SESSION['avt'])) {
    $avt = $_SESSION['avt'];
} else {
    $avt = 'avatar1';
}



//fetch classes
$query2 = "SELECT COUNT(class_id) as a ,class_id as c FROM staff_subjects_classes WHERE staff_id='$staff_id'";
$result2 = mysqli_query($conn, $query2);
$c = mysqli_fetch_assoc($result2);
$class_count = $c['a'];
$cla = $c['c'];


//fetch student
$query = "SELECT COUNT(student_id) as a FROM students WHERE class_id='$cla' ";
$result = mysqli_query($conn, $query);
$s = mysqli_fetch_assoc($result);
$student_count = $s["a"];



// Fetch Data
$student_data = getStudentList($c_id);


// Fetch all classes assigned to staff
$query = "SELECT DISTINCT ssc.class_id, ssc.subject_id, c.branch, s.subject_name as sub
        FROM staff_subjects_classes ssc
        JOIN classes c ON ssc.class_id = c.class_id
        JOIN subjects s ON ssc.subject_id = s.subject_id  -- Corrected JOIN condition
        WHERE ssc.staff_id = '$staff_id'";
$class_result = mysqli_query($conn, $query);

$class_data = [];

while ($class = mysqli_fetch_assoc($class_result)) {
    $class_id = $class['class_id'];
    $sub_id = $class['subject_id'];
    $class_name = $class['branch']; // Fetch class name
    $su = $class['sub'];

    // Fetch attendance details for this class
    $attendance_query = "SELECT 
        COUNT(CASE WHEN status = 'present' THEN 1 END) AS present_count,
        COUNT(CASE WHEN status = 'absent' THEN 1 END) AS absent_count,
        COUNT(*) AS total_lectures
        FROM attendance
        WHERE class_id='$class_id' AND subject_id='$sub_id'";

    $attendance_result = mysqli_query($conn, $attendance_query);
    $attendance = mysqli_fetch_assoc($attendance_result);

    $class_data[] = [
        "class_id" => $class_id,
        "class_name" => $class_name,  // Store class name
        "subject_name" => $su,
        "present" => $attendance['present_count'] ?? 0,
        "absent" => $attendance['absent_count'] ?? 0,
        "total_lectures" => $attendance['total_lectures'] ?? 0
    ];
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - Academic Hub</title>

    <link rel="shortcut icon" href="../img/favicon.png" type="image/x-icon" />

    <link rel="stylesheet" href="staff.css">
    <!-- Chart js-->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        .navbar a img {
            vertical-align: middle;
            height: 30px;
            width: 30px;
            object-fit: cover;
            border-radius: 50%;
        }
    </style>
</head>

<body>

    <div class="overlay"></div>

    <!-- Header -->
    <header class="header">
        <div class="logo">Academic Hub</div>
        <nav class="navbar">
            <a href="staff.php">Home</a>
            <a href="atten.php">Attendance</a>
            <a href="mark.php">Marks</a>
            <a href="stud_manage.php">Student-Managent</a>
            <a href="feedback.php">Feedback</a>
            <a href="report.php">Reports</a>
            <a href="profile.php"><img src="../img/avt/<?php echo $_SESSION['avt']; ?>.png" alt="Profile" style="vertical-align: middle;  height: 30px;  width: 30px;  object-fit: cover;  border-radius: 50%;">
                <span class="profile-text">Profile</span>
            </a>
        </nav>
        <div class="hamburger" id="hamburger">
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
                <li><a href="staff.php">Dashboard</a></li>
                <li><a href="atten.php">Attendance</a></li>
                <li><a href="mark.php">Marks</a></li>
                <li><a href="stud_manage.php">Student-Managent</a></li>
                <li><a href="feedback.php">Feedback</a></li>
                <li><a href="report.php">Reports</a></li>
                <li><a href="profile.php">Profile</a></li>
            </ul>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            <!-- Dashboard Overview -->
            <section id="overview">
                <h2>Dashboard:</h2>
                <h6>Welcome, <span id="pname"><?php echo $username; ?></span>!</h6>

                <button id="logoutbtn"><a href="../connect/logout.php">Logout</a></button>
                <div class="metrics">
                    <div class="card">
                        <h3>Total Class</h3>
                        <p><?php echo $class_count; ?></p>
                    </div>
                    <div class="card">
                        <h3>Total Students</h3>
                        <p><?php echo $student_count; ?></p>
                    </div>

                </div>
            </section>

            <!-- Student Management Section -->
            <section>
                <h2>Student Management</h2>
                <button onclick="window.location.href='stud_manage.php';">Add New Student</button>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Roll No</th>
                            <th>Phone no</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($student = $student_data->fetch_assoc()): ?>
                            <tr>
                                <td><?= $student['username'] ?></td>
                                <td><?= $student['roll_number'] ?></td>
                                <td><?= $student['phone'] ?></td>
                                <td>
                                    <a href='stud_manage.php'><button>Changes</button></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>

                    </tbody>
                </table>
            </section>

            <!-- Class Organization Section -->
            <section id="chart-section" style="
    max-height: 600px; 
    overflow-y: auto; 
">
                <h2>Class & Student Overview</h2>
                <div style="width: 300px;  margin: auto;">
                    <div id="charts-container"></div>
                </div>
            </section>


            <!-- Reports Section -->
            <section id="reports">
                <h2>Reports</h2>
                <button onclick="window.location.href='report.php';">Generate Report</button>
                <p>Create easy-to-read reports for classes and categories. You can save them as PDFs to print, or as Excel files to use offline.</p>
            </section>


        </main>

    </div>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2025 Academic Hub. All rights reserved.</p>
    </footer>

    <script src="staff.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var classData = <?php echo json_encode($class_data); ?>;
            var container = document.getElementById("charts-container");

            if (!container) {
                console.error("charts-container element not found!");
                return;
            }

            var aggregatedData = {};

            classData.forEach(classInfo => {
                var key = classInfo.class_id + "-" + classInfo.subject_name; // Unique key for class & subject

                if (!aggregatedData[key]) {
                    aggregatedData[key] = {
                        class_name: classInfo.class_name,
                        subject_name: classInfo.subject_name,
                        present: 0,
                        absent: 0,
                        total_lectures: 0
                    };
                }

                aggregatedData[key].present += parseInt(classInfo.present) || 0;
                aggregatedData[key].absent += parseInt(classInfo.absent) || 0;
                aggregatedData[key].total_lectures += parseInt(classInfo.total_lectures) || 0;
            });

            Object.keys(aggregatedData).forEach((key, index) => {
                var data = aggregatedData[key];

                if (data.total_lectures === 0) {
                    console.warn(`Skipping chart for ${data.class_name} - ${data.subject_name}: No lectures recorded.`);
                    return;
                }

                var classSubjectLabel = `${data.class_name} - ${data.subject_name}`;
                var present = data.present;
                var absent = data.absent;
                var total_lectures = data.total_lectures;

                var canvas = document.createElement("canvas");
                canvas.id = "chart" + index;
                canvas.style.width = "250px";
                canvas.style.height = "250px";
                canvas.style.cursor = "pointer";

                var wrapper = document.createElement("div");
                wrapper.style.display = "inline-block";
                wrapper.style.margin = "10px";
                wrapper.style.textAlign = "center";
                wrapper.appendChild(canvas);

                var title = document.createElement("h4");
                title.innerText = classSubjectLabel;
                wrapper.prepend(title);

                container.appendChild(wrapper);

                if (typeof Chart === "undefined") {
                    console.error("Chart.js is not loaded! Make sure to include the library.");
                    return;
                }

                new Chart(canvas.getContext("2d"), {
                    type: "doughnut",
                    data: {
                        labels: ["Present", "Absent", "total_lectures"],
                        datasets: [{
                            data: [present, absent, total_lectures],
                            backgroundColor: ["#4CAF50", "#FF9800", "#513595"],
                            hoverBackgroundColor: ["#388E3C", "#F57C00", "#6744bc"]
                        }]
                    },
                    options: {
                        responsive: false,
                        maintainAspectRatio: false,
                        cutout: "60%",
                        plugins: {
                            legend: {
                                position: "bottom"
                            }
                        }
                    }
                });
            });
        });
    </script>




    <script>
        // Toggle hamburger menu
        const hamburger = document.querySelector('.hamburger');
        const navbar = document.querySelector('.navbar');

        hamburger.addEventListener('click', () => {
            hamburger.classList.toggle('active');
            navbar.classList.toggle('active');

        });
    </script>
</body>

</html>