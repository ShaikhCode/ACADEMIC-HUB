<?php
include 'connect/config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    

    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = $conn->query($query);

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $id = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['college_id'] = $user['college_id'];
            $c_id = $user['college_id'];
            $_SESSION['avt'] = $user['avt'];

            if ($user['role'] == 'admin') {
                header("Location: admin/admin.php");


            } elseif ($user['role'] == 'staff') {
                $query = "SELECT u.*, s.*, c.* 
                FROM users u 
                INNER JOIN staff s ON u.user_id = s.user_id 
                INNER JOIN staff_subjects_classes c ON s.staff_id = c.staff_id 
                WHERE u.college_id = '$c_id' AND u.user_id = '$id'";
                $result = $conn->query($query);
                if ($user = mysqli_fetch_assoc($result)) {
                    $_SESSION["staff_id"] = $user["staff_id"];
                    $_SESSION["username"] = $user["username"];
                    $_SESSION["subject_id"] = $user["subject_id"];
                    $_SESSION["class_id"] = $user["class_id"];
                    header("Location: staff/staff.php");
                    exit();
                }
                
            } else {
                $query = "SELECT u.*, s.*
                FROM users u 
                INNER JOIN students s ON u.user_id = s.user_id 
                WHERE u.college_id = '$c_id' AND u.user_id = '$id'";
                $result = $conn->query($query);
                if ($user = mysqli_fetch_assoc($result)) {
                    $_SESSION["student_id"] = $user['student_id'];
                    $_SESSION['roll_number'] = $user['roll_number'];
                    $_SESSION['class_id'] = $user['class_id'];
                    header("Location: student/stud.php");
                }
            }
            exit();
        } else {
            $error_message = "Invalid password.";
        }
    }
    $error_message = "No such user found .";
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Academic Hub</title>
    <link rel="shortcut icon" href="img/favicon.png" type="image/x-icon" />
    <link rel="stylesheet" href="css/log.css">
</head>

<body>

    <div class="login-container">
        <div class="login-box">
            <h1>Login to Academic Hub</h1>

            <?php if (isset($error_message)): ?>
                <p style="color:red;"><?php echo $error_message; ?></p>
            <?php endif; ?>

            <form method="POST">
                <div class="input-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" placeholder="Enter your username" required>
                </div>

                <div class="input-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>

                <button type="submit" name="submit">Login</button>
                <p>Don't have an account? <a href="register.php">Sign up here</a></p>
            </form>
        </div>
    </div>

</body>

</html>