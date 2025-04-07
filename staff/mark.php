<?php
session_start();
include '../connect/config.php';

if (!isset($_SESSION['staff_id'], $_SESSION['college_id'])) {
  die("Unauthorized access.");
}

$staff_id = $_SESSION['staff_id'];
$college_id = $_SESSION['college_id'];

// Fetch classes and subjects assigned to staff
$query = "SELECT c.class_id, c.branch, s.subject_id, s.subject_name 
          FROM staff_subjects_classes sc
          JOIN classes c ON sc.class_id = c.class_id
          JOIN subjects s ON sc.subject_id = s.subject_id
          WHERE sc.staff_id = ? AND c.college_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $staff_id, $college_id);
$stmt->execute();
$subjects = $stmt->get_result();

// Fetch exam types
$query = "
    (SELECT exam_id, exam_name FROM exam_types WHERE college_id = ? AND type = 'common')
    UNION
    (SELECT exam_id, exam_name FROM exam_types WHERE college_id = ? AND type = 'private' AND staff_id = ?)
";

$stmt = $conn->prepare($query);
$stmt->bind_param("iii", $college_id, $college_id, $staff_id);
$stmt->execute();
$exams = $stmt->get_result();


// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_marks'])) {
  $class_id = $_POST['class_id'];
  $subject_id = $_POST['subject_id'];
  $exam_id = $_POST['exam_id'];
  $total_marks = $_POST['total_marks'];
  $_SESSION['subject_td'] = $subject_id;

  foreach ($_POST['marks'] as $student_id => $marks_obtained) {

    // Check if an entry already exists
    $check_query = "SELECT COUNT(*) FROM marks WHERE student_id = ? AND subject_id = ? AND exam_id = ?";
    $stmt_check = $conn->prepare($check_query);
    $stmt_check->bind_param("iii", $student_id, $subject_id, $exam_id);
    $stmt_check->execute();
    $stmt_check->bind_result($existing_count);
    $stmt_check->fetch();

    $record_allowed = ($existing_count == 0);

    $stmt_check->close();

    if ($record_allowed) {
      $subject_id = $_SESSION['subject_td'];
      // Insert marks if more than an hour has passed
      $query = "INSERT INTO marks (student_id, subject_id, exam_id, marks_obtained, total_marks, recorded_by, college_id, date_recorded)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
      $stmt = $conn->prepare($query);
      $stmt->bind_param("iiiiiii", $student_id, $subject_id, $exam_id, $marks_obtained, $total_marks, $staff_id, $college_id);
      $stmt->execute();

      $message = "Marks added successfully!";
    } else {
      $error_message = "Marks ADDED Cannot Add Again ";
    }
  }
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_ce'])) {
  $cename = trim($_POST['cename']);

  // Check if exam name already exists
  $query = "SELECT COUNT(*) FROM exam_types WHERE college_id = ? AND exam_name = ? AND staff_id=$staff_id";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("is", $college_id, $cename);
  $stmt->execute();
  $stmt->bind_result($count);
  $stmt->fetch();
  $stmt->close();

  if ($count > 0) {
    $error_message = "Exam name already exists!";
  } else {
    // Insert into database
    $insertQuery = "INSERT INTO exam_types (exam_name, college_id, type,staff_id) VALUES (?, ?, 'private',?)";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("sii", $cename, $college_id, $staff_id);

    if ($stmt->execute()) {
      $message = "Exam added successfully!";
      header("Location: " . $_SERVER['PHP_SELF']);
      exit();
    } else {
      $error_message = "Failed to add exam.";
    }
    $stmt->close();
  }

  $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MARKS Dashboard - Academic Hub</title>

  <link rel="shortcut icon" href="../img/favicon.png" type="image/x-icon" />

  <link rel="stylesheet" href="css/mark.css">
  <link rel="stylesheet" href="staff.css">

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


    /* Form Styling */
    #addcm {
      display: none;
      position: absolute;
      top: 50%;
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




  <script>
    function selectCard(card, type) {
      // Depending on the type, we will remove the selected class only from the relevant section
      if (type === 'class') {
        document.querySelectorAll('.sel').forEach(el => el.classList.remove('selected'));
      } else if (type === 'exam') {
        document.querySelectorAll('.exam').forEach(el => el.classList.remove('selected'));
      }

      // Add 'selected' class to the clicked card
      card.classList.add('selected');

      // Check if the card is a class/subject or an exam card
      if (type === 'class') {
        // Class/subject card clicked
        let classId = card.getAttribute('data-class-id');
        let subjectId = card.getAttribute('data-subject-id');

        // Update hidden input fields with class/subject values
        document.getElementById('selected_class_id').value = classId;
        document.getElementById('selected_subject_id').value = subjectId; // Update subject_id field
      } else if (type === 'exam') {
        // Exam card clicked
        let examId = card.getAttribute('data-exam-id');

        // Update hidden input field with exam value
        document.getElementById('selected_exam_id').value = examId;
      }
    }
  </script>


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
      <h2>Enter Marks</h2>

      <form action="" method="POST">


        <label>Select Class & Subject:</label>
        <div class="cardm">
          <?php while ($row = $subjects->fetch_assoc()): ?>
            <div id="class_<?= $row['class_id'] ?>"
              class="card sel"
              onclick="selectCard(this, 'class')"
              style="cursor: pointer;"
              data-class-id="<?= $row['class_id'] ?>"
              data-subject-id="<?= $row['subject_id'] ?>">
              <?= $row['branch'] ?> - <?= $row['subject_name'] ?>
            </div>
          <?php endwhile; ?>
        </div>

        <label>Select Exam Type:</label>
        <div class="cardm">
          <?php while ($row = $exams->fetch_assoc()): ?>
            <div id="exam_<?= $row['exam_id'] ?>"
              class="card exam"
              onclick="selectCard(this, 'exam')"
              style="cursor: pointer;"
              data-exam-id="<?= $row['exam_id'] ?>">
              <?= $row['exam_name'] ?>
            </div>
          <?php endwhile; ?>

          <!-- These inputs will be updated dynamically on click -->
          <input type="hidden" id="selected_class_id" name="class_id" value="">
          <input type="hidden" id="selected_subject_id" name="subject_id" value="">
          <input type="hidden" id="selected_exam_id" name="exam_id" value="">
        </div>

        <button class="mbtn" type="submit" name="fetch_students">Get Students</button>
      </form>

      <!-- Setion for custom Exams -->
      <button class="mbtn" type="submit" id="unhidex">ADD Custom Exams:</button>

      <form action="" method="POST" id="addcm">
        <label for="cename">Exam Name:</label>
        <input type="text" name="cename" placeholder="Enter Name of Your Exam" required>
        <div id="twobtn">
          <button type="submit" id="subce" name="submit_ce">Add Exams</button>
          <button type="submit" id="can">Canncel</button>
        </div>
      </form>



      <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['fetch_students'])):
        $class_id = $_POST['class_id'];
        $subject_id = $_POST['subject_id'];
        $exam_id = $_POST['exam_id'];
        if (isset($class_id) && isset($subject_id) && isset($exam_id)) {
          $check_query = "SELECT * FROM marks WHERE college_id = ? AND exam_id = ? AND subject_id = ?";
          $stmt = $conn->prepare($check_query);
          $stmt->bind_param("iii", $college_id, $exam_id, $subject_id);
          $stmt->execute();
          $students = $stmt->get_result();

          if ($students->num_rows > 0) { // Check if any rows were returned
            $error_message = "Marks have already been entered for this subject and exam.";
          }
        }

        $query = "SELECT u.user_id, u.username, s.roll_number 
          FROM users u 
          INNER JOIN students s ON u.user_id = s.user_id 
          WHERE s.class_id = ? 
          ORDER BY CONVERT(s.roll_number, UNSIGNED INTEGER) ASC"; // Sorting in Ascending Order

        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $class_id);
        $stmt->execute();
        $students = $stmt->get_result();


      ?>
        <form action="" method="POST" style="display: flex; flex-direction: column; margin: 0px auto;">
          <input type="hidden" name="class_id" value="<?= htmlspecialchars($class_id) ?>">
          <input type="hidden" name="subject_id" value="<?= htmlspecialchars($subject_id) ?>">
          <input type="hidden" name="exam_id" value="<?= htmlspecialchars($_POST['exam_id']) ?>">

          <div style="margin: 15px auto; display: flex; flex-wrap: wrap;">
            <label>Total Marks:</label>
            <input type="number" id="total_marks" name="total_marks" placeholder="Enter Total Marks" required style="text-align: center;">
          </div>

          <section style="overflow-x: auto;">
            <table>
              <thead>
                <tr>
                  <th>Roll No</th>
                  <th>Name</th>
                  <th>Marks Obtained</th>

                </tr>
              </thead>
              <tbody>
                <?php while ($row = $students->fetch_assoc()): ?>
                  <tr>
                    <td><?= $row['roll_number'] ?></td>
                    <td><?= $row['username'] ?></td>
                    <td>
                      <input type="number" class="marks_obtained" name="marks[<?= $row['user_id'] ?>]" required>
                      <p class="error_message" style="color: red;"></p>
                    </td>

                  </tr>
                <?php endwhile; ?>
              </tbody>

            </table>
          </section>

          <button class="markbtn" type="submit" name="submit_marks">Submit Marks</button>
        </form>
      <?php endif; ?>

    </main>
  </div>

  <!-- Footer -->
  <footer class="footer">
    <p>&copy; 2025 Academic Hub. All rights reserved.</p>
  </footer>

  <script src="staff.js"></script>

  <div id="editExamPopup" style="display: none;">
    <input type="hidden" id="editExamId">
    <label>Exam Name:</label>
    <input type="text" id="editExamName">
    <button onclick="saveExamChanges()">Save</button>
    <button onclick="closeEditExamPopup()">Cancel</button>
  </div>


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


  <script>
    // Toggle hamburger menu
    const hamburger = document.querySelector('.hamburger');
    const navbar = document.querySelector('.navbar');

    hamburger.addEventListener('click', () => {
      hamburger.classList.toggle('active');
      navbar.classList.toggle('active');

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
    document.addEventListener("DOMContentLoaded", function() {
      let totalMarksInput = document.getElementById("total_marks");

      function validateMarks(input) {
        let totalMarks = parseInt(totalMarksInput.value) || 0;
        let obtainedMarks = parseInt(input.value) || 0;
        let errorMessage = input.closest("tr").querySelector(".error_message");

        if (obtainedMarks > totalMarks) {
          errorMessage.innerText = "Marks cannot exceed total marks.";
          input.value = totalMarks; // Auto-correct to max allowed
        } else if (obtainedMarks < 0) {
          errorMessage.innerText = "Marks cannot be negative.";
          input.value = 0; // Auto-correct to min allowed
        } else {
          errorMessage.innerText = ""; // Clear error if valid
        }
      }

      document.addEventListener("input", function(event) {
        if (event.target.classList.contains("marks_obtained")) {
          validateMarks(event.target);
        }
      });

      totalMarksInput.addEventListener("input", function() {
        document.querySelectorAll(".error_message").forEach(msg => msg.innerText = "");
      });
    });
  </script>

</body>

</html>