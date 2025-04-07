<?php
session_start();
include '../connect/config.php';
include '../connect/functions.php';

// Ensure Only Admin Can Access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$onboardingCompleted = 0;
$pageno = 1;
$username = $_SESSION["username"];
$c_id = $_SESSION["college_id"];

// Fetch Counts
$staff_count = getTotalCount("staff", $c_id);
$student_count = getTotalCount("students", $c_id);
$class_count = getTotalCount("classes", $c_id);

// Fetch Data
$staff_data = getStaffList($c_id);
$student_data = getStudentList($c_id);
$class_data = getClassList($c_id);

// page checker ONBOARDING START IF DONE
$sql = "SELECT * FROM admins WHERE user_id='$user_id'";
$result = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($result);
$check_b = isset($data['check_b']) ? trim($data['check_b']) : '';
$completed_pages = array_filter(array_map('trim', explode(',', $check_b))); // Clean and split values


$page_no = strval($pageno); // Ensure it's a string

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
    <title>Admin Dashboard - Academic Hub</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="shortcut icon" href="../img/favicon.png" type="image/x-icon" />

    <!-- Intro.js CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intro.js/minified/introjs.min.css">

    <!-- Intro.js JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/intro.js/minified/intro.min.js"></script>


    <!-- Chart js-->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        section {
            margin-top: 40px;
            overflow-x: auto;
        }

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
    </style>
</head>

<body>
    <div class="overlay"></div>
    <!-- Header -->
    <header class="header">
        <div class="logo">Academic Hub</div>
        <nav class="navbar" data-intro=" Here's your navigation menu" data-step="1">
            <a href="admin.php">Home</a>
            <a href="addstaff.php">Staff-Manage</a>
            <a href="addstud.php">Student-Manage</a>
            <a href="addclass.php">Class-Manage</a>
            <a href="addsub.php">Subjects/Exams</a>
            <a href="reports.php">Report</a>
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

    <div class="container">
        <aside class="sidebar">
            <ul>
                <li><a href="admin.php">Dashboard</a></li>
                <li><a href="addstaff.php">Staff-Manage</a></li>
                <li><a href="addstud.php">Student-Manage</a></li>
                <li><a href="addclass.php">Class-Organization</a></li>
                <li><a href="addsub.php">Subjects/Exams ADD</a></li>
                <li><a href="reports.php">Reports</a></li>
                <li><a href="feedback.php">Feedback-Review</a></li>
            </ul>
        </aside>


        <main class="main-content">
            <h6>Welcome, <span><?php echo $username; ?></span>!</h6>
            <h2>Dashboard</h2>
            <button id="logoutbtn"><a href="../connect/logout.php">Logout</a></button>

            <div class="metrics" data-intro="Here Information about the Institude" data-step="2">
                <div class="card" style="background-color:rgb(222, 247, 218);">
                    <h3>Total Staff</h3>
                    <p><?php echo $staff_count; ?></p>
                </div>
                <div class="card" style="background-color: #e6d8ec;">
                    <h3>Total Students</h3>
                    <p><?php echo $student_count; ?></p>
                </div>
                <div class="card" style="background-color:rgb(255, 228, 204);">
                    <h3>Classes</h3>
                    <p><?php echo $class_count; ?></p>
                </div>
            </div>

            <section data-intro="Here You Can Manage register staff" data-step="3">
                <h2>Staff Management</h2>
                <button onclick="window.location.href='addstaff.php';">Add New Staff</button>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Department</th>
                            <th>Phone</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($staff = $staff_data->fetch_assoc()): ?>
                            <tr>
                                <td><?= $staff['username'] ?></td>
                                <td><?= $staff['department'] ?></td>
                                <td><?= $staff['phone'] ?></td>
                                <td>
                                    <a href='addstaff.php'><button>Changes</button></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </section>

            <section data-intro="Here You Can Manage register Student" data-step="4">
                <h2>Student Management</h2>
                <button onclick="window.location.href='addstud.php';">Add New Student</button>
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
                                    <a href='addstud.php'><button>Changes</button></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>

                    </tbody>
                </table>
            </section>

            <section data-intro="Here You Can Manage Your Added Classes" data-step="5">
                <h2>Class Organization</h2>
                <button onclick="window.location.href='addclass.php';">Add New Class</button>
                <table>
                    <thead>
                        <tr>
                            <th>Class</th>
                            <th>Students</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($class = $class_data->fetch_assoc()): ?>
                            <tr>
                                <td><?= $class['branch'] ?></td>
                                <td><?= $class['total'] ?></td>
                                <td>
                                    <a href='addclass.php'><button>Changes</button></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </section>

            <!-- Reports Section -->
            <section id="reports" data-intro="Here You Can Generate Report of students" data-step="6">
                <h2>Reports</h2>
                <button onclick="window.location.href='reports.php';">Generate Report</button>
                <p>Create easy-to-read reports for classes and categories. You can save them as PDFs to print, or as Excel files to use offline.</p>
            </section>

            <!-- Class Organization Section -->
            <section id="chart-section">
                <h2>Class & Student Distribution</h2>
                <div style="width: 300px; height: 300px; margin: auto;">
                    <canvas id="doughnutChart"></canvas>
                </div>
            </section>

        </main>
    </div>

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




    <script src="admin.js"></script>

    <script>
        // PHP Variables for Chart Data
        var classCount = <?php echo $class_count; ?>;
        var studentCount = <?php echo $student_count; ?>;

        document.addEventListener("DOMContentLoaded", function() {
            var ctx = document.getElementById("doughnutChart").getContext("2d");

            var myChart = new Chart(ctx, {
                type: "doughnut",
                data: {
                    labels: ["Total Classes", "Total Students"],
                    datasets: [{
                        data: [classCount, studentCount],
                        backgroundColor: ["#4CAF50", "#FF9800"],
                        hoverBackgroundColor: ["#388E3C", "#F57C00"]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: "60%", // Makes the chart more compact
                    plugins: {
                        legend: {
                            position: "bottom",
                            labels: {
                                font: {
                                    size: 12
                                }
                            }
                        }
                    }
                }
            });
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
            fetch('api/update.php', {
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