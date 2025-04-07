<?php
session_start();
include '../connect/config.php';

// Ensure Only Admin Can Access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
  header("Location: ../login.php");
  exit();
}

$c_id = $_SESSION['college_id'];
$message = "";
$error_message = "";
$user_id = $_SESSION['user_id'];

// Fetch available subjects for staff
$subject_query = "SELECT c.class_id, c.branch, s.subject_id, s.subject_name 
          FROM staff_subjects_classes sc
          JOIN classes c ON sc.class_id = c.class_id
          JOIN subjects s ON sc.subject_id = s.subject_id
          WHERE sc.staff_id = ? AND c.college_id = ?";
$stmt = $conn->prepare($subject_query);
$stmt->bind_param("ii", $_SESSION['staff_id'], $_SESSION['college_id']);
$stmt->execute();
$subjects = $stmt->get_result();

// Fetch students when a class is selected
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['fetch_students'])) {
  $class_id = $_POST['class_id'];
  $subject_id = $_POST['subject_id'];
  $_SESSION['subt'] = $subject_id;
  $_SESSION['classtem'] = $class_id;

  // Fetch students
  $query = "SELECT u.user_id, u.username, s.roll_number 
              FROM users u 
              INNER JOIN students s ON u.user_id = s.user_id 
              WHERE s.class_id = ?
              ORDER BY CONVERT(s.roll_number, UNSIGNED INTEGER) ASC";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("i", $class_id);
  $stmt->execute();
  $result = $stmt->get_result();
}

// Process attendance submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['attendance'])) {
  $staff_id = $_SESSION['staff_id'];
  $c_id = $_SESSION['college_id'];
  $date = date('Y-m-d');
  $current_time = date('H:i:s');



  // Fetch the last recorded attendance time **FOR TODAY**
  $query = "SELECT * FROM attendance 
            WHERE college_id = ? AND subject_id = ? AND date = ? 
            ORDER BY time DESC LIMIT 1";
  $stmt1 = $conn->prepare($query);
  $stmt1->bind_param("iis", $c_id, $_SESSION['subt'], $date); // Added date condition
  $stmt1->execute();
  $result1 = $stmt1->get_result();
  $lastAttendance = $result1->fetch_assoc();

  // Check if attendance was marked within the last 1 hour **FOR TODAY**
  if ($lastAttendance && strtotime($lastAttendance['time']) > strtotime("-1 hour")) {
    $error_message = "Attendance can only be applied after 1 hour!";
  } else if (isset($_SESSION['subt'])) {
    // Insert new attendance records
    if (!empty($_POST['attendance'])) {
      $subject_id = $_SESSION['subt'];
      $query = "INSERT INTO attendance (student_id, subject_id, date, time, status, recorded_by, college_id,class_id) 
                  VALUES (?, ?, ?, ?, ?, ?, ?,?)";
      $stmt = $conn->prepare($query);

      $c_id = $_SESSION['college_id'] ?? null; // Ensure college_id is retrieved

      foreach ($_POST['attendance'] as $student_id => $status) {
        $stmt->bind_param("iisssiii", $student_id, $subject_id, $date, $current_time, $status, $user_id, $c_id, $_SESSION['classtem']);
        $stmt->execute();
      }

      $message = "Attendance recorded successfully!";
      updateStudentSubjects($conn, $c_id, $_SESSION['classtem']);
    } else {
      $error_message = "No students selected for attendance!";
    }
  } else {
    $error_message = "Subject id not retive";
  }
}
?>


<?php
function updateStudentSubjects($conn, $college_id, $class_id)
{
  // SQL query to update sub_info for students in the given college and class
  $query = "UPDATE students s
            JOIN classes c ON s.class_id = c.class_id
            SET s.sub_info = (
                SELECT GROUP_CONCAT(DISTINCT a.subject_id ORDER BY a.subject_id ASC) 
                FROM attendance a 
                WHERE a.student_id = s.user_id
            )
            WHERE s.college_id = ? AND s.class_id = ?";

  // Prepare and execute statement
  if ($stmt = $conn->prepare($query)) {
    $stmt->bind_param("ii", $college_id, $class_id);
    $stmt->execute();
    $stmt->close();
  } else {
    echo "Error: " . $conn->error;
  }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Attendance Dashboard - Academic Hub</title>

  <link rel="shortcut icon" href="../img/favicon.png" type="image/x-icon" />

  <link rel="stylesheet" href="staff.css">
  <link rel="stylesheet" href="css/atten.css">
  <style>
    .cardm {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      align-items: center;
      gap: 10px;
    }


    /* Highlight Selected Card */
    .card.selected {
      background: #28a745;
      color: white;
      border-color: #218838;
    }

    /* Checkmark for Selected Card */
    .card.selected::after {
      content: '✔';
      position: absolute;
      top: 10px;
      right: 15px;
      font-size: 20px;
      font-weight: bold;
    }

    main {
      margin: 0 auto;
      width: 77%;
    }

    .markbtn {
      width: 70%;
      background: #007bff;
      margin: 19px auto;
    }

    .markbtn:hover {
      background: rgb(0, 94, 195);

    }

    @media (max-width:1180px) {
      main {
        width: 100%;
      }
    }
  </style>





  <script>
    function selectCard(card) {
      // Remove 'selected' class from all cards
      document.querySelectorAll('.card').forEach(el => el.classList.remove('selected'));

      // Add 'selected' class to the clicked card
      card.classList.add('selected');

      // Get the class and subject IDs from data attributes
      let classId = card.getAttribute('data-class-id');
      let subjectId = card.getAttribute('data-subject-id');

      // Update hidden input fields with selected values
      document.getElementById('selected_class_id').value = classId;
      document.getElementById('selected_subject_id').value = subjectId;
    }

    function toggleAttendance(checkbox) {
      let hiddenInput = checkbox.parentElement.nextElementSibling;
      hiddenInput.value = checkbox.checked ? "Present" : "Absent";
    }
  </script>
</head>

<body>


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
          }, 3000);
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

    <main>
      <h2>Enter Attendance</h2>
      <form action="" method="POST">
        <label>Select Class & Subject:</label>
        <div class="cardm">
          <?php while ($row = $subjects->fetch_assoc()): ?>
            <div id="class_<?= $row['class_id'] ?>"
              class="card"
              onclick="selectCard(this)"
              style="cursor: pointer;"
              data-class-id="<?= $row['class_id'] ?>"
              data-subject-id="<?= $row['subject_id'] ?>">
              <?= $row['branch'] ?> - <?= $row['subject_name'] ?>
            </div>
          <?php endwhile; ?>
        </div>

        <!-- These inputs will be updated dynamically on click -->
        <input type="hidden" id="selected_class_id" name="class_id" value="">
        <input type="hidden" id="selected_subject_id" name="subject_id" value="">
        <button class="mbtn" type="submit" name="fetch_students">Get Students</button>
      </form>



      <?php if (!empty($subject_id)): ?>
        <form action="" method="POST">
          <table>
            <tr>
              <th>Roll No</th>
              <th>Name</th>
              <th>Status</th>
            </tr>
            <?php if (isset($result) && $result->num_rows > 0): ?>
              <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                  <td><?= $row['roll_number'] ?></td>
                  <td><?= $row['username'] ?></td>
                  <td>
                    <label class="switch">
                      <input type="checkbox" id="checkbox-<?= $row['user_id'] ?>" onchange="toggleAttendance(this)">
                      <span class="slider"></span>
                    </label>
                    <input type="hidden" name="attendance[<?= $row['user_id'] ?>]" id="hidden-<?= $row['user_id'] ?>" value="Absent">
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="3">No students found.</td>
              </tr>
            <?php endif; ?>
          </table>
          <button type="submit">Submit</button>
        </form>
      <?php endif; ?>


    </main>
  </div>


  <script src="staff.js"></script>

</body>

</html>