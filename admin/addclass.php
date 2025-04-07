<?php
session_start();
include('../connect/config.php');

$c_id = $_SESSION['college_id'];

// Ensure Only Admin Can Access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$onboardingCompleted = 0;
$pageno = 2;
// Handle adding a new class
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['Branch'])) {
    $branch = $conn->real_escape_string($_POST['Branch']);

    // Secure query using prepared statements
    $stmt = $conn->prepare("SELECT * FROM classes WHERE college_id = ? AND branch = ?");
    $stmt->bind_param("is", $c_id, $branch);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $error_message = "Class or department already exists!";
    } else {
        $stmt = $conn->prepare("INSERT INTO classes (college_id, branch) VALUES (?, ?)");
        $stmt->bind_param("is", $c_id, $branch);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            header("Location: addclass.php?success=1");
            exit();
        } else {
            $error_message = "Error adding class.";
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



// Fetch all classes from database
$classes = $conn->query("SELECT * FROM classes WHERE college_id='$c_id'");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Management</title>
    <link rel="stylesheet" href="css/addclass.css">
    <link rel="shortcut icon" href="../img/favicon.png" type="image/x-icon" />
    <link rel="stylesheet" href="admin.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <!-- Intro.js CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intro.js/minified/introjs.min.css">

    <!-- Intro.js JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/intro.js/minified/intro.min.js"></script>
    <style>
        .form-container {
            display: none;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 30px auto;
            font-family: 'Arial', sans-serif;
            animation: bounceIn 1s ease;
            text-align: center;
        }

        @keyframes bounceIn {
            from {
                transform: scale(0.5);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        #edit-class-modal {
            position: fixed;
            top: 30%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0px 0px 10px #888;
            z-index: 9999;
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
        <aside class="sidebar" data-intro="Here your sidebare" data-step="2">
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
            <h2>Class Management</h2>

            <form class="form-container" id="add-class-form" method="POST">
                <div class="form-group" style="display: flex; flex-direction: column;">
                    <label for="Branch">Branch:</label>
                    <input type="text" id="Branch" name="Branch" placeholder="Enter Branch/Name of Class" required>
                </div>
                <button type="submit">Add Class</button>
                <button type="button" id="hide">Cancel</button>
            </form>

            <button id="add-class-btn" class="btn-add-class" data-intro="BY clicking this button u can able to fill a form to add a new class" data-step="3">Add Class</button>

            <div class="class-cards" data-intro="If u not make a class then notting will appears else classes card will appears where onclick u can manage it" data-step="4">
                <?php while ($class = $classes->fetch_assoc()): ?>
                    <div class="class-card" data-branch="<?php echo $class['branch']; ?>" data-class-id="<?php echo $class['class_id']; ?>">
                        <h3 class="branch-name"><?php echo htmlspecialchars($class['branch']); ?></h3>
                        <p>Total Students:
                            <?php
                            $branch_name = $class['class_id'];
                            $student_count = $conn->query("SELECT COUNT(*) AS total FROM students WHERE class_id='$branch_name' AND college_id='$c_id'")->fetch_assoc();
                            echo $student_count['total'];
                            ?>
                        </p>
                        <button class="edit-class-btn" style="background-color:rgb(176, 200, 237);">Edit</button> <!-- Added Edit Button -->
                    </div>


                <?php endwhile; ?>
            </div>

            <div class="student-table-container" id="student-table" style="display: none;">
                <div style="display: flex;justify-content: space-between;">
                    <h3 id="class-title">Students in Class</h3>
                    <!-- <button>Edit</button>-->
                </div>
                <div style="overflow-x: scroll;">
                    <table>
                        <thead>
                            <tr>
                                <th>Roll no</th>
                                <th>Student Name</th>
                                <th>Average</th>
                                <th>Contact</th>
                            </tr>
                        </thead>
                        <tbody id="student-list"></tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <footer class="footer">
        <p>&copy; 2024 Academic Hub. All rights reserved.</p>
    </footer>
    <div id="edit-class-modal" style="display: none;">
        <input type="text" id="edit-class-input" placeholder="Enter new class name" />
        <div style="display: flex;justify-content: space-around;flex-wrap: wrap;align-content: center;">
            <button id="save-class-name">Save</button>
            <button onclick="document.getElementById('edit-class-modal').style.display='none';">Cancel</button>
        </div>
    </div>


    <!-- Onboarding Modal -->
    <div id="onboarding-modal" class="modal">
        <div class="modal-content">
            <h2>Welcome to Academic Hub!</h2>
            <p>Let's take a guided tour of your ADD classes page.</p>
            <button onclick="startOnboarding()" style="margin: 10px;">Start Tour</button>
        </div>
    </div>


    <script src="admin.js"></script>

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

            //hide UNHIDE
            document.getElementById("add-class-btn").addEventListener("click", () => {
                document.getElementById("add-class-form").style.display = "block";
                document.getElementById("add-class-btn").style.display = "none";
            });

            document.getElementById("hide").addEventListener("click", () => {
                document.getElementById("add-class-form").style.display = "none";
                document.getElementById("add-class-btn").style.display = "block";
            });

            document.querySelectorAll(".class-card").forEach(card => {


                card.addEventListener("click", function() {
                    let branch = this.getAttribute("data-branch");
                    let class_id = this.getAttribute("data-class-id");

                    viewClassDetails(branch, class_id);
                });
            });

            async function viewClassDetails(branch, class_id) {


                document.getElementById("class-title").innerText = `Students in ${branch}`;
                document.getElementById("student-table").style.display = "block";

                try {
                    let response = await fetch(`fetch_students.php?class_id=${class_id}`);
                    let text = await response.text(); // Get raw response

                    let data;
                    try {
                        data = JSON.parse(text); // Attempt JSON parsing
                    } catch (jsonError) {
                        console.error("JSON Parse Error:", jsonError);
                        document.getElementById("student-list").innerHTML = `<tr><td colspan="4">Invalid JSON response</td></tr>`;
                        return;
                    }

                    let tableBody = document.getElementById("student-list");
                    tableBody.innerHTML = "";

                    if (data.error || data.length === 0) {
                        tableBody.innerHTML = `<tr><td colspan="4">${data.error || "No data found"}</td></tr>`;
                        return;
                    }

                    data.forEach(student => {
                        let row = `<tr>
                <td>${student.roll_number}</td>
                <td>${student.username}</td>
                <td>${student.avg}</td>
                <td>${student.phone}</td>
            </tr>`;
                        tableBody.innerHTML += row;
                    });

                } catch (error) {
                    console.error("Fetch Error:", error);
                    document.getElementById("student-list").innerHTML = `<tr><td colspan="4">Failed to load data</td></tr>`;
                }
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
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let editModal = document.getElementById("edit-class-modal");
            let editInput = document.getElementById("edit-class-input");
            let saveButton = document.getElementById("save-class-name");
            let popupMessage = document.getElementById("popup-message"); // Get the popup element

            let currentClassId = null;
            let currentCard = null;

            // Function to show a popup message
            function showPopupMessage(message, type = 'error') {
                if (popupMessage) {
                    popupMessage.textContent = message;
                    popupMessage.className = `popup-message ${type}-message show-popup`;
                    setTimeout(() => {
                        popupMessage.classList.remove("show-popup");
                        popupMessage.classList.add("hide-popup");
                    }, 3000);
                } else {
                    alert(message); // Fallback if popup element is not found
                }
            }

            // Handle Edit Button Click
            document.querySelectorAll(".edit-class-btn").forEach(btn => {
                btn.addEventListener("click", function(e) {
                    e.stopPropagation(); // Prevent parent card click
                    currentCard = btn.closest(".class-card");
                    currentClassId = currentCard.getAttribute("data-class-id");

                    const currentName = currentCard.querySelector(".branch-name").textContent;
                    editInput.value = currentName;
                    editModal.style.display = "block";
                });
            });

            // Save new class name
            saveButton.addEventListener("click", function() {
                const newName = editInput.value.trim();
                if (newName && currentClassId) {
                    fetch("api/renameclass.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: new URLSearchParams({
                                class_id: currentClassId,
                                new_branch: newName
                            })
                        })
                        .then(res => {
                            // We don't need to process JSON here anymore
                            window.location.href = "addclass.php"; // Redirect after the fetch completes
                        })
                        .catch(err => {
                            console.error("Rename error:", err);
                            alert("Failed to rename. Please check the console."); // Basic error for network issues
                        });
                }
            });
        });
    </script>


</body>

</html>