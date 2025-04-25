<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer files
require 'connect/Exception.php';
require 'connect/PHPMailer.php';
require 'connect/SMTP.php';

// Include database connection
include 'connect/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $college_name = $_POST['college_name'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    $phone=$_POST['phone'];

    // ✅ Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format!";
    } else {
        // ✅ Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // ✅ Check if email or username already exists
        $check_query = "SELECT * FROM users WHERE email = '$email'";
        $check_result = $conn->query($check_query);

        if ($check_result->num_rows > 0) {
            $error_message = "Username or Email already exists!";
        } else {
            // ✅ Insert new college and get new college_id
            $college_query = "INSERT INTO colleges (college_name) VALUES ('$college_name')";
            if ($conn->query($college_query) === TRUE) {
                $college_id = $conn->insert_id; // Get last inserted college_id

                // ✅ Register the Admin with the new college_id
                $query = "INSERT INTO users (username, password, email, role, college_id) 
                          VALUES ('$username', '$hashed_password', '$email', 'admin', '$college_id')";

                if ($conn->query($query) === TRUE) {
                    $at = "Admin Registration successful!";
                    $admin_id = mysqli_insert_id($conn);

                    // Insert into `aadmin` table
                    $query = "INSERT INTO admins (user_id, college_id, phone) VALUES (?,?,  ?)";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, "iis", $admin_id, $college_id, $phone);
                    mysqli_stmt_execute($stmt);

                    // ✅ Send email using PHPMailer
                    $mail = new PHPMailer(true);

                    try {
                        // SMTP settings
                        $mail->isSMTP();
                        $mail->Host = 'smtp.gmail.com'; // Use your SMTP server
                        $mail->SMTPAuth = true;
                        $mail->Username = 'signinfor78@gmail.com'; // Your email
                        $mail->Password = 'ipxa obqo lpng ofkn'; // Your email password
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port = 587;

                        // Email settings
                        $mail->setFrom('signinfor78@gmail.com', 'Academic-HUB Name');
                        $mail->addAddress($email, $username);
                        $mail->isHTML(true);
                        $mail->Subject = "Welcome to Our Website!";
                        $mail->Body = "
                            <h2>Welcome to Our Website, $username!</h2>
                            <p>Thank you for registering. Below are your login details:</p>
                            <p><strong>Username:</strong> $username</p>
                            <p><strong>Password:</strong> $password</p>
                            <p><strong>Note:</strong> Please keep your credentials safe.</p>
                            <p>Visit our website: <a href='http://localhost/Acadamic-hub/index.php'>Click here</a></p>
                            <br>
                            <p>Best Regards,</p>
                            <p>CAPTAIN</p>
                        ";

                        // Send email
                        $mail->send();
                        $success_message = "Registration successful! Check your email for details.";
                    } catch (Exception $e) {
                        $error_message = "Email could not be sent. Mailer Error: " . $mail->ErrorInfo;
                    }
                } else {
                    $error_message = "Error: " . $conn->error;
                }
            } else {
                $error_message = "Error: " . $conn->error;
            }
        }
    }
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Academic Hub</title>
    <link rel="stylesheet" href="css/log.css">
    <link rel="shortcut icon" href="img/favicon.png" type="image/x-icon" style="border-radius: 50%;" />
    <style>
        #message {
            display: none;
            position: fixed;
            background-color: #fffd5a;
            z-index: 9999;
            padding: 12px 20px;
            border-radius: 10px;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 25px;
            animation: bounin 1s ease;
        }

        @keyframes bounin {
            from {
                transform: translateY(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    </style>
</head>

<body>
    <div id="message" style="display: <?php echo (isset($at) || isset($att)) ? 'block' : 'none'; ?>;">
        <p>
            <?php
            if (isset($at)) {
                echo "Registration successful! <a href='log.php'>Login here</a>";
            } elseif (isset($att)) {
                echo $att;
            }

            ?>
        </p>
    </div>
    <div class="login-container">
        <div class="login-box">

            <h1>Register New User</h1>
            <?php if (isset($error_message)): ?>
                <p style="color:red;"><?php echo $error_message; ?></p>
            <?php endif; ?>

            <form action="register.php" method="POST">
                <div class="input-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" placeholder="Enter username" required>
                </div>

                <div class="input-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" placeholder="Enter email" required>
                </div>

                <div class="input-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" placeholder="Enter password" required>
                </div>
                <div class="input-group">
                    <label for="phone">Contact no:</label>
                    <input type="phone" id="password" name="phone" placeholder="Enter Your Contact Number " required>
                </div>

                <div class="input-group">
                    <label for="college_name">College Name:</label>
                    <input type="text" name="college_name" placeholder="Enter the college name" required>
                </div>

                <button type="submit">Register</button>
                <p>Have an account? <a href="Log.php">Loging here</a></p>
            </form>
        </div>
    </div>
</body>

</html>