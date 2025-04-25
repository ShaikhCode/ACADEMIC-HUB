<?php
session_start();
include '../connect/config.php'; // Include your database connection file

// Ensure Only Admin Can Access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$c_id=$_SESSION['college_id'];
$test = 'staff';

// Fetch user data from database
$query = "SELECT * FROM users INNER JOIN $test ON users.user_id = $test.user_id WHERE users.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $_SESSION['avt'] = $user['avt'];
} else {
    echo "User not found!";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $avt = $_POST['avatar'];


    $query = "UPDATE users SET avt = ? WHERE user_id = ? AND role=?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sis", $avt, $user_id, $role);

    if (mysqli_stmt_execute($stmt)) {
        $message = "Success";
        $_SESSION['avt'] = $avt;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $error_message = "Failed!";
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Academic Hub</title>

    <link rel="shortcut icon" href="../img/favicon.png" type="image/x-icon" />

    <link rel="stylesheet" href="css/profile.css">
    <link rel="stylesheet" href="staff.css">
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


        <!-- Main Content -->
        <main class="main-content">


            <section id="student-profile" style="@media (max-width:999px) {
        display: contents;
            }">
                <div class="profile-container">
                    <!-- Profile Image -->
                    <div class="profile-image">
                        <img src="../img/avt/<?php echo $user['avt']; ?>.png" alt="Profile Image" id="profile-img">
                    </div>

                    <!-- Personal Information -->
                    <div class="profile-info">
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

                    <!-- <p><strong>Phone:</strong> <?php // echo $user['phone']; 
                                                    ?></p> -->

                    <!-- Academic Information -->
                    <div class="academic-info" data-intro="Here yours Institute Info" data-step="4">
                        <h3>Academic Information</h3>

                        <p><strong>Course:</strong> <?php echo $user['department']; ?></p>
                        <p><strong>Institute:</strong> <?php echo $user1['college_name']; ?></p>
                        <p><strong>Started on Academic-Hub:</strong> <?php echo date('Y', strtotime($user1['created_at'])); ?></p>
                    </div>


                    <div id="message-box"></div> <!-- Message Box -->


                    <!-- Settings Section -->
                    <div class="settings">
                        <h3>Settings</h3>
                        <!-- <button id="edit-profile">Edit Profile</button> -->
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

    <script src="staff.js"></script>
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






        });
    </script>
</body>

</html>