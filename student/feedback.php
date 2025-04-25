<?php
session_start();
include('../connect/config.php');

// Ensure Only Student Can Access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}


$c_id = $_SESSION['college_id'];
$pageno = 4; // Assign a unique number to each page (p1 = 1, p2 = 2, etc.)
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


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $type = $_POST['feedback-type'];
    $mess = $_POST['feedback-message'];

    // Check if feedback already exists for today
    $stmt = $conn->prepare("SELECT DATE(created_at) as da FROM feedback WHERE college_id = ? AND user_id = ? AND DATE(created_at) = CURDATE()");
    $stmt->bind_param("ii", $c_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Feedback already submitted
        $error_message = "Feedback already submitted today.";
    } else {
        // Close the previous statement before preparing a new one
        $stmt->close();

        // Insert new feedback
        $stmt = $conn->prepare("INSERT INTO feedback (user_id, college_id, message, type) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $user_id, $c_id, $mess, $type);


        if ($stmt->execute()) {
            $message    = 'Message Submited!';
            header("Location: feedback.php?success=1");
            exit();
        } else {
            $error_message = "Error submitting feedback.";
            header("Location: feedback.php?error=1");
            exit();
        }
    }
    $stmt->close();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Feedback</title>
    <link rel="stylesheet" href="css/feedback.css">
    <link rel="stylesheet" href="style.css">

    <!-- Intro.js CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intro.js/minified/introjs.min.css">

    <!-- Intro.js JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/intro.js/minified/intro.min.js"></script>


    <link rel="shortcut icon" href="../img/favicon.png" type="image/x-icon" />

</head>

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


        <section id="feedback">
            <h1>Submit Your Feedback</h1>
            <div class="feedback-container" data-intro=" Here's you have one post daily u can tell complaint, suggestion, more and also u name will not come in the post you are free to say any thing " data-step="2">
                <form action="" method="post">

                    <div class="form-group">
                        <label for="feedback-type">Feedback Type:</label>
                        <select id="feedback-type" name="feedback-type" required>
                            <option value="general">General</option>
                            <option value="suggestion">Suggestion</option>
                            <option value="complaint">Complaint</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="feedback-message">Your Feedback:</label>
                        <textarea id="feedback-message" name="feedback-message" placeholder="Write your feedback here..." rows="5" required></textarea>
                    </div>

                    <button type="submit" class="submit-btn">Submit Feedback</button>
                </form>
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
            <button id="skip-btn" onclick="completeOnboarding(4)">Skip</button>
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