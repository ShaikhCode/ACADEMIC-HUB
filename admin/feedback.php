<?php
session_start();
include("../connect/config.php");


// Ensure Only Admin Can Access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$onboardingCompleted = 0;
$pageno = 6;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['feedback_id'])) {
    $feedback_id = $_POST['feedback_id'];

    // Update the feedback status in the database
    $query = "UPDATE feedback SET sta = 'done' WHERE feedback_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $feedback_id);

    if ($stmt->execute()) {
        $message = "Feedback marked as done!";
    } else {
        $error_message = "Failed to update feedback";
    }

    $stmt->close();
    exit(); // Stop further execution
}

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
    <title>Feedback-Review</title>
    <link rel="stylesheet" href="css/report.css">
    <link rel="stylesheet" href="admin.css">
    <link rel="shortcut icon" href="../img/favicon.png" type="image/x-icon" />

    <!-- Intro.js CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intro.js/minified/introjs.min.css">

    <!-- Intro.js JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/intro.js/minified/intro.min.js"></script>

    <style>
        /* Modal Background */
        .modal1 {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        /* Modal Content */
        .modal-content1 {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            width: 300px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.3);
        }

        /* Buttons */
        .modal-buttons1 {
            margin-top: 15px;
        }

        .tick-btn,
        .cross-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            margin: 5px;
            cursor: pointer;
        }

        .tick-btn {
            background: #4CAF50;
            /* Green */
            color: white;
        }

        .cross-btn {
            background: #f44336;
            /* Red */
            color: white;
        }
    </style>
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
        <nav class="navbar" data-intro="Here's your navigation menu" data-step="1">
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
        <aside class="sidebar" data-intro="Here is your sidebare" data-step="2">
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

        <!-- Marks Section -->
        <section id="reports" data-intro="Here All Complain Comes if Somebody reports" data-step="3">
            <h2>ALL Complain</h2>
            <div id="reports-container" class="reports-container">

                <?php
                // Define an array of soft colors
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
                ];

                $query = "  SELECT users.role,f.feedback_id as fd, f.message, f.type
                            FROM users
                            INNER JOIN feedback f ON users.user_id = f.user_id
                            WHERE users.college_id = ? AND f.sta != 'done'";

                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $_SESSION["college_id"]);
                $stmt->execute();
                $result = $stmt->get_result();

                $index = 0; // To track colors

                while ($class = $result->fetch_assoc()) {
                    $color = $softColors[$index % count($softColors)]; // Cycle through colors
                    $index++; // Increment index for next card

                    echo "<div class='reports-card' onclick='check({$class['fd']})' style='background-color: $color;color: #1f2937; padding: 15px; border-radius: 8px; margin-bottom: 10px; cursor:pointer;'>
                    <h3 style='text-align:center;'>{$class['role']}</h3>
                    <div class='type'>
                        <p class='it'><strong>Type: </strong>{$class['type']} </p>
                        <p><strong>Message: </strong>{$class['message']}</p>
                    </div>
                </div>";
                }

                $stmt->close();
                ?>



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
            <p>Let's take a guided tour of your Feedback.</p>
            <button onclick="startOnboarding()" style="margin: 10px;">Start Tour</button>
        </div>
    </div>


    <!-- Feedback Modal -->
    <div id="feedbackModal" class="modal1">
        <div class="modal-content1">
            <h3>Mark Feedback as Done?</h3>
            <input type="hidden" id="feedbackId"> <!-- Hidden input to store ID -->
            <div class="modal-buttons1">
                <button class="tick-btn" onclick="confirmFeedback()">✔ Done</button>
                <button class="cross-btn" onclick="closeModal()">✖ Cancel</button>
            </div>
        </div>
    </div>


    <script src="admin.js"></script>
    <script>
        function check(id) {
            // Show the modal
            document.getElementById("feedbackModal").style.display = "flex";

            // Store the feedback ID in a hidden input
            document.getElementById("feedbackId").value = id;
        }

        // Close the modal function
        function closeModal() {
            document.getElementById("feedbackModal").style.display = "none";
            location.reload();
        }

        // Handle feedback confirmation
        function confirmFeedback() {
            let feedbackId = document.getElementById("feedbackId").value;
            document.getElementById("feedbackId").style.display = "none";

            let xhr = new XMLHttpRequest();
            xhr.open("POST", "", true); // Post to the same page
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            xhr.onload = function() {
                try {
                    let response = JSON.parse(this.responseText);
                    if (response.success) {

                        closeModal();
                        location.reload(); // Refresh to update the feedback card
                    } else {
                        location.reload();
                    }
                } catch (error) {
                    console.error("Invalid JSON response:", this.responseText);

                }
            };
            xhr.send("feedback_id=" + encodeURIComponent(feedbackId));
        }
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