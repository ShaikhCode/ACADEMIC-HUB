<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../connect/Exception.php';
require '../connect/PHPMailer.php';
require '../connect/SMTP.php';

include('../connect/config.php');

// Ensure only staff can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
  header("Location: ../login.php");
  exit();
}

$c_id = $_SESSION['college_id'];

// Fetch available classes
$classQuery = "SELECT class_id, branch FROM classes WHERE college_id = ?";
$classStmt = mysqli_prepare($conn, $classQuery);
mysqli_stmt_bind_param($classStmt, "i", $c_id);
mysqli_stmt_execute($classStmt);
$classResult = mysqli_stmt_get_result($classStmt);
mysqli_stmt_close($classStmt);

$error_message = "";
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name']);
  $email = trim($_POST['email']);
  $class_id = intval($_POST['class_id']);
  $phone = trim($_POST['phone']);
  $roll = trim($_POST['roll']);
  $password_plain = $_POST['password'];
  $password = password_hash($password_plain, PASSWORD_DEFAULT);
  $role = "student";

  // Check if student already exists
  $checkUserQuery = "SELECT * FROM users WHERE college_id = ? AND (username = ? OR email = ?)";
  $checkStmt = mysqli_prepare($conn, $checkUserQuery);
  mysqli_stmt_bind_param($checkStmt, "iss", $c_id, $name, $email);
  mysqli_stmt_execute($checkStmt);
  $checkResult = mysqli_stmt_get_result($checkStmt);

  if (mysqli_num_rows($checkResult) > 0) {
    $error_message = "Student already registered in this class.";
  } else {
    // Send welcome email
    $mail = new PHPMailer(true);
    try {
      $mail->isSMTP();
      $mail->Host = 'smtp.gmail.com';
      $mail->SMTPAuth = true;
      $mail->Username = 'signinfor78@gmail.com';
      $mail->Password = 'ipxa obqo lpng ofkn'; // App password
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
      $mail->Port = 587;

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
        <br><p>Best Regards,<br>CAPTAIN</p>
      ";

      if ($mail->send()) {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        mysqli_begin_transaction($conn);
        try {
          // Insert into users
          $userQuery = "INSERT INTO users (college_id, username, email, password, role) VALUES (?, ?, ?, ?, ?)";
          $userStmt = mysqli_prepare($conn, $userQuery);
          mysqli_stmt_bind_param($userStmt, "issss", $c_id, $name, $email, $password, $role);
          mysqli_stmt_execute($userStmt);
          $user_id = mysqli_insert_id($conn);

          // Insert into students
          $studentQuery = "INSERT INTO students (user_id, college_id, roll_number, class_id, phone) VALUES (?, ?, ?, ?, ?)";
          $studentStmt = mysqli_prepare($conn, $studentQuery);
          mysqli_stmt_bind_param($studentStmt, "iisii", $user_id, $c_id, $roll, $class_id, $phone);
          mysqli_stmt_execute($studentStmt);
          $student_id = mysqli_insert_id($conn);

          // Assign subjects
          $subjectQuery = "INSERT INTO student_subjects (student_id, subject_id) 
                           SELECT ?, subject_id FROM class_subjects 
                           WHERE class_id = ? AND college_id = ?";
          $subjectStmt = mysqli_prepare($conn, $subjectQuery);
          mysqli_stmt_bind_param($subjectStmt, "iii", $student_id, $class_id, $c_id);
          mysqli_stmt_execute($subjectStmt);

          // Update total students
          $updateQuery = "UPDATE classes c SET total = (SELECT COUNT(*) FROM students s WHERE s.class_id = c.class_id)";
          $conn->query($updateQuery);

          mysqli_commit($conn);
          $message = "Student added successfully and assigned to subjects!";
        } catch (Exception $e) {
          mysqli_rollback($conn);
          $error_message = "Database Error: " . $e->getMessage();
        }
      } else {
        $error_message = "Failed to send welcome email.";
      }
    } catch (Exception $e) {
      $error_message = "Mailer Error: " . $mail->ErrorInfo;
    }
  }

  mysqli_stmt_close($checkStmt);
}
?>






<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Student - Staff-dash</title>

  <link rel="shortcut icon" href="../img/favicon.png" type="image/x-icon" />

  <link rel="stylesheet" href="css/addstud.css" />
  <link rel="stylesheet" href="staff.css" />
  <link rel="stylesheet" href="api.css" />

  <!-- Add this inside the <head> or before closing </body> -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


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



  <!-- Delete Confirmation Popup -->
  <div class="overlay" id="overlay"></div>
  <div class="popup" id="deletePopup">
    <p>Are you sure you want to delete this Student?</p>
    <button class="btn" onclick="closePopup()">No</button>
    <button class="btn" id="yesBtn" onclick="confirmDelete()">Yes</button>
  </div>

  <!-- Edit Subject Popup -->
  <div id="editPopup" class="popup">
    <h2>Edit Student</h2>
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
        <div style="display: flex; gap: 5px; flex-wrap:wrap;">
          <button type="submit" class="hide">Add Student</button>
        </div>
      </form>

      <button id="toggleButton">Add New Student</button>

      <!-- Staff Management Section -->
      <section id="stud-management" style="overflow-x: auto;">

        <table>
          <thead>
            <tr>
              <th>Class</th>
              <th>Name</th>
              <th>Phone no</th>
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


  <div id="preloader">
    <h1 class="glowing">Academic-Hub</h1>
  </div>



  <script src="staff.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      console.log("DOM is fully loaded and parsed!");

      // Hide and unhide form script
      const toggleButton = document.getElementById('toggleButton');
      const formContainer = document.getElementById('add-student-form');
      const hide = document.querySelector('.hide');

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

    document.addEventListener("DOMContentLoaded", function() {
      // Reset all forms on page load
      document.querySelectorAll("form").forEach(form => form.reset());

      // Prevent form resubmission on refresh
      if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
      }
    });
  </script>

  <script>
    function openDeletePopup(student_id) {
      document.getElementById("overlay").style.display = "block";
      document.getElementById("deletePopup").style.display = "block";

      setTimeout(() => {
        document.getElementById("yesBtn").style.display = "inline-block";
      }, 2500);

      document.getElementById("yesBtn").onclick = function() {
        $.post("sapi/studapi.php", {
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
      $.post("sapi/studapi.php", {
        action: "get",
        student_id: studentId
      }, function(response) {
        try {
          console.log("Raw Response:", response);
          let result = typeof response === "string" ? JSON.parse(response) : response;
          console.log("Parsed JSON:", result);

          if (result.success && result.data) { // Check for result.data existence
            if (result.data.user_id && result.data.username && result.data.roll_number) { // check properties exist
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

      $.post("sapi/studapi.php", {
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


</body>

</html>