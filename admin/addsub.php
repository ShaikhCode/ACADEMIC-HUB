<?php
session_start();
// Include the database configuration file for PHP
include('../connect/config.php');

// Ensure Only Admin Can Access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$onboardingCompleted = 0;
$pageno = 5;
$c_id = $_SESSION["college_id"];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['form_type']) && $_POST['form_type'] == 'add_subject') {
        $name = $_POST['name'];
        $code = $_POST['code'];

        // Check if subject already exists
        $check_subject = "SELECT * FROM subjects WHERE college_id=? AND (subject_name=? OR subject_code=?)";
        $stmt_check = $conn->prepare($check_subject);
        $stmt_check->bind_param("iss",  $c_id, $name, $code);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $error_message = "Subject already exists!";
            $stmt_check->close();
            exit;
        }
        $stmt_check->close();

        // Begin transaction
        mysqli_begin_transaction($conn);
        try {
            // Insert into Subjects Table
            $sql = "INSERT INTO subjects (college_id, subject_name, subject_code) VALUES (?, ?,?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iss", $c_id, $name, $code);

            if ($stmt->execute()) {
                $message = "Subject added successfully!";
                mysqli_commit($conn); // ✅ Commit transaction if everything is fine
                header("Location: " . $_SERVER['PHP_SELF']); // Refresh page after success
                exit();
            } else {
                throw new Exception("Error adding subject");
            }
        } catch (Exception $e) {
            mysqli_rollback($conn); // ✅ Rollback transaction if any error occurs
            $error_message = "Error: " . $e->getMessage();
        }
    } elseif (isset($_POST['form_type']) && $_POST['form_type'] == 'assign_staff') {
        // Logic for assigning staff
        $staff_id = $_POST['staff'];
        $subject_id = $_POST['subjectiiid'];
        $branch = $_POST['department'];

        $sql = "INSERT INTO staff_subjects_classes (staff_id,subject_id,class_id,college_id) VALUES (?,?,?,?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiii", $staff_id, $subject_id, $branch, $c_id);
        if ($stmt->execute()) {
            $message = "Subject added successfully!";
            header("Location: " . $_SERVER['PHP_SELF']); // Refresh page after success
            exit();
        } else {
            $error_message = "Error adding subject";
        }
    }
}
// Fetch all staff for display
$sql = "SELECT s.subject_id, s.subject_name, s.subject_code, 
               COALESCE(u.username, NULL) AS staff_name
        FROM subjects s
        LEFT JOIN staff_subjects_classes sc ON s.subject_id = sc.subject_id
        LEFT JOIN staff st ON sc.staff_id = st.staff_id
        LEFT JOIN users u ON st.user_id = u.user_id
        WHERE s.college_id = ?
        ORDER BY s.subject_code";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $c_id);
$stmt->execute();
$result = $stmt->get_result();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['exam_set'])) {
    $ename = $_POST['ename'];
    $ty = 'common';
    $sql = "INSERT INTO exam_types (college_id, exam_name, type) VALUES (?, ?,?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $c_id, $ename, $ty);

    if ($stmt->execute()) {
        $message = "Subject added successfully!";
        mysqli_commit($conn); // ✅ Commit transaction if everything is fine
        header("Location: " . $_SERVER['PHP_SELF']); // Refresh page after success
        exit();
    } else {
        $error_message = ("Error adding subject");
    }
}

// page checker ONBOARDING START IF DONE
$sql = "SELECT * FROM admins WHERE user_id='$user_id'";
$result1 = mysqli_query($conn, $sql);
$data1 = mysqli_fetch_assoc($result1);
$check_b = isset($data1['check_b']) ? trim($data1['check_b']) : '';
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
    <title>Add-Subjects</title>
    <link rel="stylesheet" href="css/addsub.css">
    <link rel="stylesheet" href="admin.css">
    <link rel="shortcut icon" href="../img/favicon.png" type="image/x-icon" />

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Intro.js CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intro.js/minified/introjs.min.css">

    <!-- Intro.js JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/intro.js/minified/intro.min.js"></script>


    <style>
        #canncel {
            color: black;
            background-color: red;
            position: absolute;
            top: 0;
            right: 0;
            border-radius: 50%;
            z-index: 13;
            padding: 5px 10px;
            border: 2px solid black;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            margin: 0;
        }

        .btn {
            padding: 10px 15px;
            margin: 5px;
            cursor: pointer;
            border: none;
            border-radius: 5px;
        }

        .delete-btn {
            background-color: red;
            color: white;
        }

        .edit-btn {
            background-color: #f7b731;
            color: white;
        }

        /* Popup Styles */
        .popup,
        .edit-popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
            text-align: center;
        }

        .popup button,
        .edit-popup button {
            margin-top: 10px;
        }

        #yesBtn {
            background-color: red;
            color: white;
            display: none;
        }

        /* Edit Form */
        .edit-popup input {
            display: block;
            width: 100%;
            margin: 10px 0;
            padding: 8px;
        }

        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
        }

        /* Form Styling */
        #addcm {
            display: none;
            position: absolute;
            /* top: 50%; */
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(94px);
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.3);
            width: 350px;
            text-align: center;
        }

        #addcm label {
            display: block;
            font-size: 16px;
            color: white;
            margin-bottom: 8px;
        }

        #addcm input {
            width: 100%;
            padding: 10px;
            margin-bottom: 12px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            background: transparent;
            color: white;
            font-size: 14px;
            border-radius: 6px;
            outline: none;
        }

        input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        /* Button Container */
        #twobtn {
            display: flex;
            justify-content: space-between;
        }

        #subce {
            background: #4CAF50;
            color: white;
        }

        #subce:hover {
            background: #45a049;
        }

        #can {
            background: #ff4b5c;
            color: white;
        }

        #can:hover {
            background: #e0434f;
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
        <input type="hidden" id="editSubId">
        <label>Subject Name:</label>
        <input type="text" id="editSubName">
        <label>Subject Code:</label>
        <input type="text" id="editSubCode">
        <button onclick="saveChanges()">Save</button>
        <button onclick="closeEditPopup()">Cancel</button>
    </div>



    <div class="overlay"></div>
    <!-- Header -->
    <header class="header" data-intro=" Here's your navigation menu" data-step="1">
        <div class="logo">Academic Hub</div>
        <nav class="navbar">
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
            <h6>Add Subjects:</h6>
            <div style=" display: flex; flex-direction: row; flex-wrap: wrap; justify-content: space-between; align-content: center;">
                <button id="toggleButton" style="background-color: #5ec163;" data-intro="By Clicking this button U be able to enter Subject For Assign to a Staff" data-step="2">Add New Subject</button>
                <button style="background-color: #e0434f;" data-intro="By Clicking this button U will redirect to down to the add exam btn" data-step="3"><a href="#examc" style="color: white; text-decoration: none; background-color: #e0434f;">GOTO ADDExam⬇️</a></button>
            </div>

            <form class="form-container" id="add-staff-form" method="POST" style="display: none;" action="">

                <div class="form-group">
                    <label for="name">Subject Name:</label>
                    <input type="text" id="name" name="name" placeholder="Enter Subject name" required />
                </div>
                <div class="form-group">
                    <label for="code">Subject Code:</label>
                    <input type="text" id="code" name="code" placeholder="Enter Subject Code" required />
                </div>
                <input type="hidden" name="form_type" value="add_subject">


                <div style="display: flex; justify-content: space-around;   align-items: center; align-content: center;flex-direction: row;">
                    <button type="submit">Add Subject</button>
                    <button id="cl1">Chancel</button>
                </div>

            </form>

            <form id="form2" action="" method="post" style="display: none;">
                <button id="canncel">X</button>
                <div class="form-group">
                    <label for="staff">staff:</label>
                    <select id="class_id" name="staff" required>
                        <option value="">Select Subject</option>
                        <?php
                        $classResult = $conn->query("SELECT * FROM users u INNER JOIN staff s ON u.user_id=s.user_id  where u.college_id='$c_id' AND u.role='staff' ");
                        while ($row = mysqli_fetch_assoc($classResult)) { ?>
                            <option value="<?php echo $row['staff_id']; ?>"><?php echo $row['username']; ?></option>
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
                            <option value="<?php echo $row['class_id']; ?>"><?php echo $row['branch']; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <input type="hidden" id="subjectiiid" name="subjectiiid" value=0>
                <button type="submit" id="sub2">Assign Staff</button>
                <input type="hidden" name="form_type" value="assign_staff">



            </form>

            <!-- Staff Management Table -->
            <h3 style="margin-top: 24px;">ADDED Subjects:</h3>
            <section id="subject-management" style="overflow-x: scroll;" data-intro="Here U can see all the Subject U Added And also U can Assign To Any Staff/Teacher" data-step="4">


                <table border="1">
                    <thead>
                        <tr>
                            <th>Subject Name</th>
                            <th>Subject Code</th>
                            <th>Assigned Staff</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="subject-table-body">
                        <?php
                        $data = [];
                        while ($row = $result->fetch_assoc()) {
                            $data[] = $row; // Store data in an array for JavaScript
                        }
                        ?>
                    </tbody>
                </table>

                <!-- Pagination Controls -->
                <div id="pagination">
                    <button id="prev-btn" onclick="changePage(-1)" disabled>⬅️ Previous</button>
                    <span id="page-info"></span>
                    <button id="next-btn" onclick="changePage(1)">Next ➡️</button>
                </div>

                </table>


            </section>
            <section id="examc" style="margin-top: 20px;">
                <h6 style="font-size: xx-large;color: #1f2937;">Add Main Exams:</h6>
                <!-- Setion for custom Exams -->
                <button class="mbtn" type="submit" id="unhidex" style="background-color:rgb(89, 225, 91);" data-intro="By Clicking this button U can able to ADD a Comman exam For all Students in any classes" data-step="5">ADD Exams:</button>
                <h3 style="margin-top: 10px;">ADDED EXAMS</h3>
                <form action="" method="POST" id="addcm">
                    <label for="ename">Exam Name:</label>
                    <input type="text" name="ename" placeholder="Enter Name of Your Exam" required>
                    <div id="twobtn">
                        <button type="submit" id="subce" name="exam_set">Add Exams</button>
                        <button type="submit" id="can">Canncel</button>
                    </div>
                </form>



                <div style="overflow-x: scroll;" data-intro="Here U can see all the Comman Exams and Also Manage Changes" data-step="6">
                    <table>
                        <thead>
                            <tr>
                                <th>Exam Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT exam_id as id,exam_name as name FROM exam_types WHERE college_id=$c_id AND type='common' GROUP BY id DESC";
                            $stmt = $conn->prepare($query);
                            $stmt->execute();
                            $ex_result = $stmt->get_result();
                            ?>

                            <?php while ($row = $ex_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td>
                                        <button class="btn edit-btn" onclick="">Edit</button>
                                        <button class="btn delete-btn" onclick="">Delete</button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
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
            <p>Let's take a guided tour of your ADD subject And Exams page.</p>
            <button onclick="startOnboarding()" style="margin: 10px;">Start Tour</button>
        </div>
    </div>


    <script src="admin.js"></script>


    <script>
        //ADD custom EXQAM
        document.addEventListener("DOMContentLoaded", function() {
            let addBtn = document.getElementById("unhidex");
            let form = document.getElementById("addcm");
            let cancelBtn = document.getElementById("can");

            // Show form when "ADD Custom Exams" is clicked
            addBtn.addEventListener("click", function() {
                form.style.display = "block";
            });

            // Hide form when "Cancel" is clicked
            cancelBtn.addEventListener("click", function(event) {
                event.preventDefault(); // Prevent any accidental form submission
                form.style.display = "none";
                form.reset(); // Clear input field
            });

            // Hide form after successful submission
            form.addEventListener("submit", function() {
                setTimeout(() => {
                    form.style.display = "none";
                    form.reset(); // Clear input field after submission
                }, 500); // Small delay for a better user experience
            });
        });
    </script>
    <!-- Pass PHP Data to JavaScript -->
    <script>
        let data = <?php echo json_encode($data); ?>; // Convert PHP array to JS
        let currentPage = 1;
        let rowsPerPage = 4;

        function displayTable() {
            let tableBody = document.getElementById("subject-table-body");
            tableBody.innerHTML = ""; // Clear existing rows
            let start = (currentPage - 1) * rowsPerPage;
            let end = start + rowsPerPage;
            let paginatedItems = data.slice(start, end);

            paginatedItems.forEach(row => {
                let tr = document.createElement("tr");
                tr.innerHTML = `
                <td>${row.subject_name}</td>
                <td>${row.subject_code}</td>
                <td>
                    ${row.staff_name ? row.staff_name : `<button class="assign-btn" onclick="assign(${row.subject_id})">Assign</button>`}
                </td>
                <td>
                    <button class="btn edit-btn" onclick="openEditPopup(${row.subject_id})">Edit</button>
                    <button class="btn delete-btn" onclick="openDeletePopup(${row.subject_id})">Delete</button>
                </td>
            `;
                tableBody.appendChild(tr);
            });

            document.getElementById("page-info").textContent = `Page ${currentPage} of ${Math.ceil(data.length / rowsPerPage)}`;
            document.getElementById("prev-btn").disabled = currentPage === 1;
            document.getElementById("next-btn").disabled = end >= data.length;
        }

        function changePage(step) {
            currentPage += step;
            displayTable();
        }

        displayTable(); // Initial table render
    </script>





    <script>
        function openDeletePopup(subject_id) {
            document.getElementById("overlay").style.display = "block";
            document.getElementById("deletePopup").style.display = "block";

            setTimeout(() => {
                document.getElementById("yesBtn").style.display = "inline-block";
            }, 3000);

            document.getElementById("yesBtn").onclick = function() {
                $.post("api/subjectapi.php", {
                    action: "delete",
                    subject_id: subject_id
                }, function(response) {
                    console.log("Server Response:", response);
                    try {
                        let result = JSON.parse(response);
                        if (result.success) {
                            alert("Subject deleted successfully!");
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

        function openEditPopup(subjectId) {
            $.post("api/subjectapi.php", {
                action: "get",
                subject_id: subjectId
            }, function(response) {
                try {
                    console.log("Raw Response:", response); // Debugging
                    let result = typeof response === "string" ? JSON.parse(response) : response;
                    console.log("Parsed JSON:", result);

                    if (result.success) {
                        document.getElementById("editSubId").value = result.data.subject_id;
                        document.getElementById("editSubName").value = result.data.subject_name;
                        document.getElementById("editSubCode").value = result.data.subject_code;
                        document.getElementById("editPopup").style.display = "block";
                    } else {
                        alert("Error: " + result.message);
                    }
                } catch (e) {
                    console.error("Invalid JSON response:", response);
                    console.log("Invalid JSON response. Check the console.");
                    setTimeout(() => location.reload(true), 300);
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
            let idField = document.getElementById("editSubId");
            let nameField = document.getElementById("editSubName");
            let codeField = document.getElementById("editSubCode");

            if (!idField || !nameField || !codeField) {
                console.error("One or more input fields are missing.");
                return;
            }

            let id = idField.value;
            let name = nameField.value;
            let code = codeField.value;

            if (!id || !name || !code) {
                alert("Please fill in all fields.");
                return;
            }

            $.post("api/subjectapi.php", {
                    action: "edit",
                    subject_id: id,
                    subject_name: name,
                    subject_code: code
                },
                function(response) {
                    try {
                        let result = JSON.parse(response);
                        if (result.success) {
                            alert("Subject updated successfully!");
                            location.reload(); // ✅ Reload the page after successful update
                        } else {
                            alert("Error: " + result.message);
                            location.reload();
                        }
                    } catch (e) {
                        console.error("Invalid JSON response:", response);
                        location.reload();

                    }
                }
            ).fail(function() {
                alert("Error connecting to server.");
            });
        }
    </script>



    <script>
        function assign(id) {
            // Get elements
            const f2 = document.getElementById('form2');
            const sub2 = document.getElementById('sub2');
            const subid = document.getElementById('subjectiiid'); // Hidden input field

            // Check if the form is hidden, then show it
            if (f2.style.display === 'none' || f2.style.display === '') {
                f2.style.display = 'block';
                subid.value = id; // Assign the ID to the hidden input field
            }
        }

        // Hide form on submit
        document.addEventListener("DOMContentLoaded", function() {
            const sub2 = document.getElementById('sub2');
            const f2 = document.getElementById('form2');
            const can = document.getElementById('canncel');


            sub2.addEventListener('click', function(event) {
                f2.style.display = 'none'; // Hide the form
            });
            can.addEventListener('click', function(event) {
                event.preventDefault(); // Prevent default form submission (for testing)
                f2.style.display = 'none'; // Hide the form
            });


        });
    </script>



    <script>
        document.addEventListener("DOMContentLoaded", function() {
            console.log("DOM is fully loaded and parsed!");


            // Hide and unhide form script
            const toggleButton = document.getElementById('toggleButton');
            const formContainer = document.getElementById('add-staff-form');
            const hide = document.getElementById('hide');
            const can1 = document.getElementById('cl1');

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

            can1.addEventListener('click', function(event) {
                event.preventDefault(); // Prevent default form submission (for testing)
                formContainer.style.display = 'none'; // Hide the form
                toggleButton.style.display = 'block';

            });


            // Toggle form visibility
            document.getElementById("toggleButton").addEventListener("click", function() {
                document.getElementById("add-staff-form").style.display = "block";
                this.style.display = "none";
            });
        });

        document.addEventListener("DOMContentLoaded", function() {
            // Reset all forms on page load
            document.querySelectorAll("form").forEach(form => form.reset());

            // Prevent form resubmission on refresh
            if (window.history.replaceState) {
                window.history.replaceState(null, null, window.location.href);
            }
        });
    </script>
    <!-- Place this where your JS is -->
    <script>
        const onboardingCompleted = <?php echo json_encode($onboardingCompleted == 0); ?>;

        if (onboardingCompleted) {
            document.getElementById("onboarding-modal").style.display = 'block';
        }
        const currentPag = <?php echo $pageno; ?>;

        function startOnboarding() {
            const intro = introJs();
            document.getElementById("onboarding-modal").style.display = 'none';

            intro.oncomplete(function() {
                sendCompletionStatus(currentPag);
            });

            intro.onexit(function() {
                sendCompletionStatus(currentPag);
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