<?php
session_start();
include '../connect/config.php'; // Include your database connection file

// Ensure Only Admin Can Access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}
$pageno = 6; // Assign a unique number to each page (p1 = 1, p2 = 2, etc.)
$user_id = $_SESSION['user_id'];
$onboardingCompleted = 0; // Default: Not completed
$c_id = $_SESSION['college_id'];
$role = $_SESSION['role'];

if ($role == 'student') {
    $table = 'students';
    // Fetch user data from database
    $query = "
    SELECT users.*, $table.*, classes.branch, YEAR(classes.date) AS year 
    FROM users 
    INNER JOIN $table ON users.user_id = $table.user_id 
    INNER JOIN classes ON $table.class_id = classes.class_id 
    WHERE users.user_id = ?
";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
}
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $_SESSION['avt'] = $user['avt'];
} else {
    echo "User not found!";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $avt = $_POST['avatar'];
    $_SESSION['avt'] = $avt;
    $query = "UPDATE users SET avt = ? WHERE user_id = ? AND role=?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sis", $avt, $user_id, $role);

    if (mysqli_stmt_execute($stmt)) {
        $message = 'Added Success!';
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $error_message = "server down try again later ";
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}
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
    <title>Profile - Academic Hub</title>
    <link rel="stylesheet" href="css/profile.css">
    <link rel="stylesheet" href="style.css">

    <!-- Intro.js CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intro.js/minified/introjs.min.css">

    <!-- Intro.js JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/intro.js/minified/intro.min.js"></script>


    <link rel="shortcut icon" href="../img/favicon.png" type="image/x-icon" />
</head>
<style>
    @media (max-width:600px) {
        .main-content {
            display: contents;
        }
    }
</style>

<body>
    <!-- Popup Message -->
    <?php if (!empty($message) || !empty($error_message)): ?>
        <div id="popup-message" class="popup-message <?php echo !empty($message) ? 'success-message' : 'error-message'; ?>">
            <?php echo !empty($message) ? $message : $error_message; ?>
        </div>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                let popup = document.getElementById("popup-message");

                if (popup) {
                    popup.classList.add("show-popup");

                    setTimeout(function() {
                        popup.classList.remove("show-popup");
                        popup.classList.add("hide-popup");
                    }, 3000); // Hide after 3 seconds
                }
            });
        </script>
    <?php endif; ?>

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



        <!-- Main Content -->
        <main class="main-content">

            <section id="student-profile">
                <div class="profile-container">
                    <!-- Profile Image -->
                    <div class="profile-image">
                        <img src="../img/avt/<?php echo $user['avt']; ?>.png" alt="Profile Image" id="profile-img">
                    </div>

                    <!-- Personal Information -->
                    <div class="profile-info" data-intro=" Here's your Presonal Information" data-step="2">
                        <h3>Personal Information</h3>
                        <p><strong>Name:</strong> <?php echo $user['username']; ?></p>
                        <p><strong>Email:</strong> <?php echo $user['email']; ?></p>
                        <p><strong>Phone:</strong> <?php if ($role != 'admin') {
                                                        echo $user['phone'];
                                                    } ?></p>
                    </div>


                    <?php
                    // Assuming $conn is your database connection and $c_id is defined
                    $query1 = "SELECT * FROM colleges WHERE college_id = $c_id";
                    $result1 = mysqli_query($conn, $query1); // Use $conn as the DB connection
                    $user1 = mysqli_fetch_assoc($result1);
                    ?>


                    <!-- Academic Information -->
                    <div class="academic-info" data-intro="Here yours Institute Info" data-step="4">
                        <h3>Academic Information</h3>
                        <p><strong>Roll no:</strong> <?php echo $user['roll_number']; ?></p>
                        <p><strong>Course:</strong> <?php echo $user['branch']; ?></p>
                        <p><strong>Year:</strong> <?php echo $user['year']; ?></p>
                        <p><strong>Institute:</strong> <?php echo $user1['college_name']; ?></p>
                        <p><strong>Started on Academic-Hub:</strong> <?php echo date('Y', strtotime($user1['created_at'])); ?></p>
                    </div>


                    <div id="message-box"></div> <!-- Message Box -->


                    <!-- Settings Section -->
                    <div class="settings" data-intro=" Here's your Basic tools like reset tutorial or logout" data-step="5">
                        <h3>Settings</h3>
                        <button id="edit-profile">Re-start Tutorial</button>
                        <button id="change-password" style="background-color:#ff5151;" onclick="window.location.href=('../connect/logout.php');"><a href="../connect/logout.php" style="background-color:#ff5151; text-decoration: none; font-weight: bold; color: white;">Logout</a></button>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <form method="POST" action="profile.php" class="avatar-form" id="avt">
        <label>Select Your Avatar:</label>
        <div class="avatar-container">
            <input type="radio" name="avatar" value="avatar1" id="avatar1" required>
            <label for="avatar1"><img src="../img/avt/avatar1.png" class="avatar-img"></label>

            <input type="radio" name="avatar" value="avatar2" id="avatar2">
            <label for="avatar2"><img src="../img/avt/avatar2.png" class="avatar-img"></label>

            <input type="radio" name="avatar" value="avatar3" id="avatar3">
            <label for="avatar3"><img src="../img/avt/avatar3.png" class="avatar-img"></label>

            <input type="radio" name="avatar" value="avatar4" id="avatar4">
            <label for="avatar4"><img src="../img/avt/avatar4.png" class="avatar-img"></label>

            <input type="radio" name="avatar" value="avatar5" id="avatar5">
            <label for="avatar5"><img src="../img/avt/avatar5.png" class="avatar-img"></label>

            <input type="radio" name="avatar" value="avatar6" id="avatar6">
            <label for="avatar6"><img src="../img/avt/avatar6.png" class="avatar-img"></label>

            <input type="radio" name="avatar" value="avatar7" id="avatar7">
            <label for="avatar7"><img src="../img/avt/avatar7.png" class="avatar-img"></label>

            <input type="radio" name="avatar" value="avatar8" id="avatar8">
            <label for="avatar8"><img src="../img/avt/avatar8.png" class="avatar-img"></label>
        </div>

        <button type="submit" id="toggleButton">Save Avatar</button>
    </form>


    <footer class="footer">
        <p>&copy; 2025 Academic Hub. All rights reserved.</p>
    </footer>
    <!-- Onboarding Modal -->
    <div id="onboarding-modal" class="modal">
        <div class="modal-content">
            <h2>Welcome to Academic Hub!</h2>
            <p>Let's take a guided tour of your dashboard.</p>
            <button onclick="startOnboarding()" id="start-tour-btn">Start Tour</button>
            <button id="skip-btn" onclick="sendCompletionStatus('6')">Skip</button>
        </div>
    </div>
    <script src="script.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function repage() {
                fetch("api/delete_on.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        }
                    })
                    .then(response => response.text())
                    .then(data => {

                        console.log("Done");
                        window.location.href = "stud.php"; // Redirect to stud.php
                    })
                    .catch(error => console.error("Error:", error));
            }

            // Attach function to the button click event
            document.getElementById("edit-profile").addEventListener("click", repage);
        });
    </script>



    <script>
        document.addEventListener("DOMContentLoaded", function() {

            const toggleButton = document.getElementById("toggleButton");
            const formContainer = document.getElementById("avt");
            const profileImg = document.getElementById("profile-img");


            // Show avatar selection form when clicking the profile image
            profileImg.addEventListener("click", () => {
                if (formContainer.style.display === "none" || formContainer.style.display === "") {
                    formContainer.style.display = "block";
                    toggleButton.style.display = "block"; // Ensure the save button appears
                } else {
                    formContainer.style.display = "none";
                }
            });

            // Hide form when clicking the save button
            toggleButton.addEventListener("click", (event) => {
                // Prevent form submission
                formContainer.style.display = "none";
            });

            function showMessage(text, type) {
                let messageBox = document.getElementById("message-box");
                messageBox.innerHTML = text;
                messageBox.className = type === "success" ? "success" : "error"; // Apply class
                messageBox.style.display = "block";

                // Hide after 2 seconds
                setTimeout(() => {
                    messageBox.style.display = "none";
                }, 1500);
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