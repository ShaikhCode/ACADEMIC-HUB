<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../connect/Exception.php';
require '../connect/PHPMailer.php';
require '../connect/SMTP.php';

include('../connect/config.php');

// Ensure Only Admin Can Access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$user_id=$_SESSION['user_id'];
$c_id = $_SESSION["college_id"];
$onboardingCompleted = 0;
$pageno = 3;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $subject_id = intval($_POST['subject']);
    $role = "staff";
    $password_plain = $_POST['password'];
    $password = password_hash($password_plain, PASSWORD_DEFAULT);

    // Extract class_id and branch
    list($class_id, $branch) = explode(",", $_POST['department']);
    $class_id = (int) $class_id;

    // Check if staff already exists
    $check_staff = "SELECT user_id FROM users WHERE (username = ? OR email = ?) AND college_id=?";
    $stmt_check = $conn->prepare($check_staff);
    $stmt_check->bind_param("ssi", $name, $email, $c_id);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        $message = "Error: Staff with this name or email already exists!";
        $stmt_check->close();
    } else {
        $stmt_check->close();

        // Send Welcome Email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'signinfor78@gmail.com';
            $mail->Password = 'ipxa obqo lpng ofkn'; // App password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('signinfor78@gmail.com', 'Academic-HUB Admin');
            $mail->addAddress($email, $name);
            $mail->isHTML(true);
            $mail->Subject = "Welcome to Academic-HUB!";
            $mail->Body = "
                <h2>Welcome, $name!</h2>
                <p>You have been added to the Academic-HUB system as a staff member.</p>
                <p><strong>Username:</strong> $name</p>
                <p><strong>Password:</strong> $password_plain</p>
                <p><strong>Role:</strong> Staff</p>
                <p>You can log in here: <a href='http://localhost/Acadamic-hub/index.php'>Academic-HUB Login</a></p>
                <br><p>Regards,<br>Academic-HUB Admin</p>
            ";

            if ($mail->send()) {
                mysqli_begin_transaction($conn);
                try {
                    // Insert into users table
                    $sql_user = "INSERT INTO users (college_id, username, email, password, role) VALUES (?, ?, ?, ?, ?)";
                    $stmt_user = $conn->prepare($sql_user);
                    $stmt_user->bind_param("issss", $c_id, $name, $email, $password, $role);
                    $stmt_user->execute();
                    $user_id = mysqli_insert_id($conn);
                    $stmt_user->close();

                    // Insert into staff table
                    $sql_staff = "INSERT INTO staff (user_id, college_id, department, phone) VALUES (?, ?, ?, ?)";
                    $stmt_staff = $conn->prepare($sql_staff);
                    $stmt_staff->bind_param("iiss", $user_id, $c_id, $branch, $phone);
                    $stmt_staff->execute();
                    $staff_id = mysqli_insert_id($conn);
                    $stmt_staff->close();

                    // Assign staff to subject & class
                    $sql_assign = "INSERT INTO staff_subjects_classes (staff_id, subject_id, class_id) VALUES (?, ?, ?)";
                    $stmt_assign = $conn->prepare($sql_assign);
                    $stmt_assign->bind_param("iii", $staff_id, $subject_id, $class_id);
                    $stmt_assign->execute();
                    $stmt_assign->close();

                    mysqli_commit($conn);
                    $message = "✅ Staff added successfully and email sent!";
                } catch (Exception $e) {
                    mysqli_rollback($conn);
                    $message = "❌ Database Error: " . $e->getMessage();
                }
            } else {
                $message = "❌ Failed to send welcome email.";
            }
        } catch (Exception $e) {
            $message = "❌ PHPMailer Error: " . $e->getMessage();
        }
    }
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




// Fetch all staff for display
$sql = "SELECT * FROM users INNER JOIN staff ON users.user_id=staff.user_id WHERE users.college_id='$c_id'";
$result = $conn->query($sql);
?>






<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Staff Dashboard</title>
    <link rel="stylesheet" href="css/addstaff.css">
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="api.css">
    <link rel="shortcut icon" href="../img/favicon.png" type="image/x-icon" />

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <!-- Intro.js CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intro.js/minified/introjs.min.css">

    <!-- Intro.js JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/intro.js/minified/intro.min.js"></script>

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
        <p>Are you sure you want to delete this staff?</p>
        <button class="btn" onclick="closePopup()">No</button>
        <button class="btn" id="yesBtn" onclick="confirmDelete()">Yes</button>
    </div>

    <!-- Edit Subject Popup -->
    <div id="editPopup" class="popup">
        <h2>Edit Staff</h2>
        <input type="hidden" id="editStudId">
        <label>User Name:</label>
        <input type="text" id="editStudName" placeholder="Enter new username">
        <label>Department:</label>
        <input type="text" id="editStudCode" placeholder="Enter new Department">
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
            <h6>Add Staff And Manage Staff:</h6>


            <button id="toggleButton" data-intro=" Here's by clicking this buttton you can able to enter Staff But first U have to Enter A Subject For the staff/teacher" data-step="2">Add New Staff</button>

            <form class="form-container" id="add-staff-form" method="POST" style="display: none;">
                <div class="form-group">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" placeholder="Enter staff name" required />
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" placeholder="Enter email address" required />
                </div>
                <div class="form-group">
                    <label for="subject">Subject:</label>
                    <select id="class_id" name="subject" required>
                        <option value="">Select Subject</option>
                        <?php
                        $classResult = $conn->query("SELECT * FROM subjects where college_id='$c_id'");
                        while ($row = mysqli_fetch_assoc($classResult)) { ?>
                            <option value="<?php echo $row['subject_id']; ?>"><?php echo $row['subject_name']; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="department">Department:</label>
                    <select id="class_id" name="department" required>
                        <option value="">Select Department</option>
                        <?php
                        $classResult = $conn->query("SELECT * FROM classes where college_id='$c_id'");
                        while ($row = mysqli_fetch_assoc($classResult)) { ?>
                            <option value="<?php echo $row['class_id']; ?>,<?php echo $row['branch']; ?>"><?php echo $row['branch']; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="phone">Phone:</label>
                    <input type="tel" id="phone" name="phone" placeholder="Enter phone number" required />
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" placeholder="Create password" required />
                </div>
                <button type="submit">Add Staff</button>
            </form>

            <!-- Staff Management Table -->
            <section id="staff-management" data-intro=" Here's you can see Your added Staff" data-step="3" style="overflow-x: scroll;">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Department</th>
                            <th>Phone</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="staff-table-body">
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['username']) ?></td>
                                <td><?= htmlspecialchars($row['department']) ?></td>
                                <td><?= htmlspecialchars($row['phone']) ?></td>
                                <td>
                                    <button class='btn edit-btn' onclick='openEditPopup("<?= htmlspecialchars($row['user_id']) ?>")'>Edit</button>
                                    <button class='btn delete-btn' onclick='openDeletePopup("<?= htmlspecialchars($row['user_id']) ?>")'>Delete</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>

    <footer class="footer">
        <p>&copy; 2024 Academic Hub. All rights reserved.</p>
    </footer>

    <!-- Onboarding Modal -->
    <div id="onboarding-modal" class="modal">
        <div class="modal-content">
            <h2>Welcome to Academic Hub!</h2>
            <p>Let's take a guided tour of your Add staff page.</p>
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
                $.post("api/staffedit.php", {
                    action: "delete",
                    student_id: student_id
                }, function(response) {
                    console.log("Server Response:", response);
                    try {
                        let result = JSON.parse(response);
                        if (result.success) {
                            alert("staff deleted successfully!");
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
            $.post("api/staffedit.php", {
                action: "get",
                student_id: studentId
            }, function(response) {
                try {
                    console.log("Raw Response:", response);
                    let result = typeof response === "string" ? JSON.parse(response) : response;
                    console.log("Parsed JSON:", result);

                    if (result.success && result.data) { // Check for result.data existence
                        if (result.data.user_id && result.data.username && result.data.department) { // check properties exist
                            document.getElementById("editStudId").value = result.data.user_id;
                            document.getElementById("editStudName").value = result.data.username;
                            document.getElementById("editStudCode").value = result.data.department;
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

            $.post("api/staffedit.php", {
                action: "edit",
                student_id: id,
                student_name: name,
                student_code: code,
                student_no: no
            }, function(response) {
                try {
                    let result = JSON.parse(response);
                    if (result.success) {
                        alert("staff updated successfully!");
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
            const formContainer = document.getElementById('add-staff-form');
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


            // Toggle form visibility
            document.getElementById("toggleButton").addEventListener("click", function() {
                document.getElementById("add-staff-form").style.display = "block";
                this.style.display = "none";
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