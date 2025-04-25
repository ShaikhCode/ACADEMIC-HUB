<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer files
require '../connect/Exception.php';
require '../connect/PHPMailer.php';
require '../connect/SMTP.php';

include('../connect/config.php');

// Ensure Only Staff Can Access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$c_id = $_SESSION['college_id'];
$user_id = $_SESSION['user_id'];
$onboardingCompleted = 0;
$pageno = 4;
// Fetch available classes
$classQuery = "SELECT class_id, branch FROM classes WHERE college_id = ? ";
$classStmt = mysqli_prepare($conn, $classQuery);
mysqli_stmt_bind_param($classStmt, "i", $c_id);
mysqli_stmt_execute($classStmt);
$classResult = mysqli_stmt_get_result($classStmt);
mysqli_stmt_close($classStmt);

$error_message = ""; // Initialize error message variable
$message = ""; // Initialize success message variable

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $class_id = intval($_POST['class_id']); // Ensure it's an integer
    $phone = $_POST['phone'];
    $roll = $_POST['roll'];
    $password_plain = $_POST['password']; // Store plain password for email
    $password = password_hash($password_plain, PASSWORD_DEFAULT);
    $role = "student"; // Default role for students

    // Check if student already exists
    $checkUserQuery = "SELECT * FROM users WHERE college_id = ? AND username = ? AND email=?";
    $checkStmt = mysqli_prepare($conn, $checkUserQuery);
    mysqli_stmt_bind_param($checkStmt, "iss", $c_id, $name, $email);
    mysqli_stmt_execute($checkStmt);
    $checkResult = mysqli_stmt_get_result($checkStmt);

    if (mysqli_num_rows($checkResult) > 0) {
        $error_message = "Student already registered in this class";
    } else {
        // Send Email Before Inserting into Database
        $mail = new PHPMailer(true);
        try {
            // Email Configuration
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP host
            $mail->SMTPAuth = true;
            $mail->Username = 'signinfor78@gmail.com'; // Replace with your email
            $mail->Password = 'ipxa obqo lpng ofkn'; // Replace with your email password (Use App Password for Gmail)
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Email settings
            $mail->setFrom('signinfor78@gmail.com', 'Academic-HUB Name');
            $mail->addAddress($email, $name);
            $mail->isHTML(true);
            $mail->Subject = "Welcome to Our Website!";
            $mail->Body = "
          <h2>Welcome to Our Website, $name!</h2>
          <p>Thank you for registering. Below are your login details:</p>
          <p><strong>Username:</strong> $name</p>
          <p><strong>Password:</strong> $password_plain</p>
          <p><strong>Note:</strong> Please keep your credentials safe.</p>
          <p>Visit our website: <a href='https://actively-glowing-toad.ngrok-free.app'>Click here</a></p>
          <br>
          <p>Best Regards,</p>
          <p>CAPTAIN</p>
      ";

            if ($mail->send()) {
                // Begin transaction
                mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Enable exception handling
                mysqli_begin_transaction($conn);
                try {
                    // Insert into users table
                    $userQuery = "INSERT INTO users (college_id, username, email, password, role) VALUES (?, ?, ?, ?, ?)";
                    $userStmt = mysqli_prepare($conn, $userQuery);
                    mysqli_stmt_bind_param($userStmt, "issss", $c_id, $name, $email, $password, $role);

                    if (mysqli_stmt_execute($userStmt)) {
                        $user_id = mysqli_insert_id($conn);

                        $query = "INSERT INTO students (user_id, college_id, roll_number, class_id, phone) VALUES (?, ?, ?, ?, ?)";
                        $stmt = mysqli_prepare($conn, $query);
                        mysqli_stmt_bind_param($stmt, "iisii", $user_id, $c_id, $roll, $class_id, $phone);

                        if (mysqli_stmt_execute($stmt)) {
                            $student_id = mysqli_insert_id($conn);

                            // Assign subjects to student
                            $query2 = "INSERT INTO student_subjects (student_id, subject_id) 
                                      SELECT ?, subject_id FROM class_subjects 
                                      WHERE class_id = ? AND college_id = ?";
                            $stmt2 = mysqli_prepare($conn, $query2);
                            mysqli_stmt_bind_param($stmt2, "iii", $student_id, $class_id, $c_id);

                            // Update total students count in class
                            $query5 = "UPDATE classes c SET total = (SELECT COUNT(*) FROM students s WHERE s.class_id = c.class_id)";
                            $stm5 = $conn->prepare($query5);
                            $stm5->execute();

                            if (mysqli_stmt_execute($stmt2)) {
                                $message = "Student added successfully and assigned to subjects!";
                                mysqli_commit($conn); // Commit if everything is successful
                            } else {
                                throw new Exception('Error assigning student to subjects: ' . mysqli_error($conn));
                            }
                            mysqli_stmt_close($stmt2);
                        } else {
                            throw new Exception('Error adding student: ' . mysqli_error($conn));
                        }
                    }
                } catch (mysqli_sql_exception $e) {
                    mysqli_rollback($conn); // Rollback changes if any error occurs
                    $error_message = "Error: " . $e->getMessage();
                }
            } else {
                $error_message = "Failed to send email. Please check the email address.";
            }
        } catch (Exception $e) {
            $error_message = "Mailer Error: " . $mail->ErrorInfo;
        }
    }
    mysqli_stmt_close($checkStmt);
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
    <title>Add Student</title>
    <link rel="stylesheet" href="css/addstaff.css" />
    <link rel="stylesheet" href="admin.css" />
    <link rel="stylesheet" href="api.css" />
    <link rel="shortcut icon" href="../img/favicon.png" type="image/x-icon" />

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <!-- Intro.js CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intro.js/minified/introjs.min.css">

    <!-- Intro.js JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/intro.js/minified/intro.min.js"></script>

    <style>
        #preloader {
            position: fixed;
            width: 100%;
            height: 100%;
            background: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .glowing {
            font-size: 36px;
            font-weight: bold;
            font-family: Arial, sans-serif;
            color: #3498db;
            text-shadow: 0 0 5px #3498db, 0 0 10px #2980b9, 0 0 15px #1abc9c;
            animation: glow 1.5s infinite alternate;
        }

        @keyframes glow {
            from {
                text-shadow: 0 0 5px #3498db;
            }

            to {
                text-shadow: 0 0 20px #1abc9c;
            }
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

    <!-- Delete Confirmation Popup -->
    <div class="overlay" id="overlay"></div>
    <div class="popup" id="deletePopup">
        <p>Are you sure you want to delete this subject?</p>
        <button class="btn" onclick="closePopup()">No</button>
        <button class="btn" id="yesBtn" onclick="confirmDelete()">Yes</button>
    </div>

    <!-- Edit Subject Popup -->
    <div id="editPopup" class="popup">
        <h2>Edit Subject</h2>
        <input type="hidden" id="editStudId">
        <label>User Name:</label>
        <input type="text" id="editStudName" placeholder="Enter new username">
        <label>Rollnumber:</label>
        <input type="text" id="editStudCode" placeholder="Enter new Rollnumber">
        <label>Phone:</label>
        <input type="number" id="editStudNo" placeholder="Enter new Phone">
        <button onclick="saveChanges()">Save</button>
        <button onclick="closeEditPopup()">Cancel</button>
    </div>

    <div class="overlay"></div>
    <!-- Header -->
    <header class="header">
        <div class="logo">Academic Hub</div>
        <nav class="navbar" data-intro=" Here's your navigation menu" data-step="1">
            <a href="admin.php">Home</a>
            <a href="addstaff.php">Staff-Manage</a>
            <a href="addstud.php">Student-Manage</a>
            <a href="addclass.php">Class-Manage</a>
            <a href="addsub.php">Subjects</a>
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

    <!-- Main Content -->
    <div class="container">

        <!-- Sidebar -->
        <aside class="sidebar">
            <ul>
                <li><a href="admin.php">Dashboard</a></li>
                <li><a href="addstaff.php">Staff-Manage</a></li>
                <li><a href="addstud.php">Student-Manage</a></li>
                <li><a href="addclass.php">Class-Organization</a></li>
                <li><a href="addsub.php">Subjects ADD</a></li>
                <li><a href="reports.php">Reports</a></li>
                <li><a href="feedback.php">Feedback-Review</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <h6>Add Student to Class:</h6>




            <form class="form-container" method="POST" id="add-student-form" style="display:none;">
                <div class="form-group">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="roll">Rollnumber:</label>
                    <input type="text" id="roll" name="roll" required>
                </div>
                <div class="form-group">
                    <label for="class_id">Class:</label>
                    <select id="class_id" name="class_id" required>
                        <option value="">Select Class</option>
                        <?php while ($row = mysqli_fetch_assoc($classResult)) { ?>
                            <option value="<?php echo $row['class_id']; ?>"><?php echo $row['branch']; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="phone">Phone:</label>
                    <input type="number" id="phone" name="phone" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit">Add Student</button>
            </form>

            <button id="toggleButton" data-intro=" BY Click this button A student can be added But first U have to Create A class Where u will Add Student" data-step="2">Add New Student</button>

            <!-- Staff Management Section -->
            <section id="stud-management" data-intro=" Here U can See Your Added students and U can Make changes also here" data-step="3">

                <table>
                    <thead>
                        <tr>
                            <th>Class</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php

                        // Fetch user data along with student class information and branch from the classes table
                        $query = "SELECT users.username, users.user_id, students.phone, classes.branch 
            FROM users
            INNER JOIN students ON users.user_id = students.user_id
            INNER JOIN classes ON students.class_id = classes.class_id
            WHERE students.college_id = ?
            ORDER BY users.user_id DESC";

                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("i", $c_id);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        while ($class = $result->fetch_assoc()) {
                            echo "<tr>
          <td>{$class['branch']}</td>
          <td>{$class['username']}</td>  <!-- Replacing class_id with branch -->
          <td>{$class['phone']}</td>
          <td>
              <button class='btn edit-btn' onclick='openEditPopup(" . $class['user_id'] . ")'>Edit</button>
              <button class='btn delete-btn' onclick='openDeletePopup(" . $class['user_id'] . ")'>Delete</button>
          </td>
      </tr>";
                        }

                        $stmt->close();

                        ?>
                        <!-- More rows as needed -->
                    </tbody>
                </table>
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
            <p>Let's take a guided tour of your Add Students.</p>
            <button onclick="startOnboarding()" style="margin: 10px;">Start Tour</button>
        </div>
    </div>


    <script src="admin.js"></script>

    <script>
        function openDeletePopup(student_id) {
            document.getElementById("overlay").style.display = "block";
            document.getElementById("deletePopup").style.display = "block";

            setTimeout(() => {
                document.getElementById("yesBtn").style.display = "inline-block";
            }, 2500);

            document.getElementById("yesBtn").onclick = function() {
                $.post("api/studedit.php", {
                    action: "delete",
                    student_id: student_id
                }, function(response) {
                    console.log("Server Response:", response);
                    try {
                        let result = JSON.parse(response);
                        if (result.success) {
                            alert("Student deleted successfully!");
                            setTimeout(() => location.reload(true), 500); // ✅ Ensure reload
                        } else {
                            alert("Error: " + result.message);
                        }
                    } catch (e) {
                        console.error("Invalid JSON response:", response);
                        console.log("Invalid JSON response. Check console.");
                        setTimeout(() => location.reload(true), 500);
                    }
                }).fail(function() {
                    alert("Error connecting to the server.");
                });
            };
        }

        function closePopup() {
            document.getElementById("overlay").style.display = "none";
            document.getElementById("deletePopup").style.display = "none";
            document.getElementById("yesBtn").style.display = "none";
        }

        function openEditPopup(studentId) {
            $.post("api/studedit.php", {
                action: "get",
                student_id: studentId
            }, function(response) {
                try {
                    console.log("Raw Response:", response);
                    let result = typeof response === "string" ? JSON.parse(response) : response;
                    console.log("Parsed JSON:", result);

                    if (result.success && result.data) { // Check for result.data existence
                        if (result.data.user_id && result.data.username && result.data.roll_number && result.data.phone) { // check properties exist
                            document.getElementById("editStudId").value = result.data.user_id;
                            document.getElementById("editStudName").value = result.data.username;
                            document.getElementById("editStudCode").value = result.data.roll_number;
                            document.getElementById("editStudNo").value = result.data.phone;
                            document.getElementById("editPopup").style.display = "block";
                        } else {
                            console.error("Missing properties in result.data:", result.data);
                            alert("Error: Missing data from server.");
                        }

                    } else if (result.success) { // if result.success is true but result.data is null or undefined
                        console.error("result.data is null or undefined:", result);
                        alert("Error: Missing data from server.");
                    } else {
                        alert("Error: " + result.message);
                    }
                } catch (e) {
                    console.error("Invalid JSON response:", response);
                    console.log("Invalid JSON response. Check the console.");
                    // setTimeout(() => location.reload(true), 300);
                }
            }).fail(function(xhr, status, error) {
                console.error("AJAX Error:", status, error);
                alert("Error connecting to the server.");
            });
        }

        function closeEditPopup() {
            let overlay = document.getElementById("editOverlay");
            let popup = document.getElementById("editPopup");

            if (overlay) overlay.style.display = "none";
            if (popup) popup.style.display = "none";
        }

        function saveChanges() {
            let idField = document.getElementById("editStudId");
            let nameField = document.getElementById("editStudName");
            let codeField = document.getElementById("editStudCode");
            let noField = document.getElementById("editStudNo");

            if (!idField || !nameField || !codeField) {
                console.error("One or more input fields are missing.");
                return;
            }

            let id = idField.value;
            let name = nameField.value;
            let code = codeField.value;
            let no = noField.value;

            if (!id || !name || !code || !no) {
                alert("Please fill in all fields.");
                return;
            }

            $.post("api/studedit.php", {
                action: "edit",
                student_id: id,
                student_name: name,
                student_code: code,
                student_no: no
            }, function(response) {
                try {
                    let result = JSON.parse(response);
                    if (result.success) {
                        alert("Student updated successfully!");
                        location.reload(); // ✅ Reload the page after successful update
                    } else {
                        alert("Error: " + result.message);
                        location.reload();
                    }
                } catch (e) {
                    console.error("Invalid JSON response:", response);
                    location.reload();
                }
            }).fail(function() {
                alert("Error connecting to server.");
            });
        }
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Reset all forms on page load
            document.querySelectorAll("form").forEach(form => form.reset());

            // Prevent form resubmission on refresh
            if (window.history.replaceState) {
                window.history.replaceState(null, null, window.location.href);
            }
        });



        document.addEventListener("DOMContentLoaded", function() {
            console.log("DOM is fully loaded and parsed!");

            // Hide and unhide form script
            const toggleButton = document.getElementById('toggleButton');
            const formContainer = document.getElementById('add-student-form');
            const hide = document.getElementById('hide');

            toggleButton.addEventListener('click', () => {
                if (formContainer.style.display === 'none' || formContainer.style.display === '') {
                    formContainer.style.display = 'block';
                    toggleButton.style.display = 'none';
                }
            });
            hide.addEventListener('click', () => {
                if (formContainer.style.display === 'block') {
                    formContainer.style.display = 'none';
                    toggleButton.style.display = 'block';
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