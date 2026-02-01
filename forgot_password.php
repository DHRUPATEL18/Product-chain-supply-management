<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['send_otp'])) {
    require 'email_config.php';
    require 'PHPMailer-6.10.0/src/Exception.php';
    require 'PHPMailer-6.10.0/src/PHPMailer.php';
    require 'PHPMailer-6.10.0/src/SMTP.php';


    // Connect to database
    $cn = mysqli_connect("localhost", "root", "", "pragmanx_onelife_distributor");
    if (!$cn) {
        $_SESSION['message'] = "Database connection failed.";
        $_SESSION['message_type'] = "error";
        header("Location: forgot_password.php");
        exit;
    }

    // Normalize and escape email
    $email_raw = trim(strtolower($_POST['email']));
    $email_sql = mysqli_real_escape_string($cn, $email_raw);

    // Check if email exists
    $check_query = "SELECT id FROM users WHERE email = '$email_sql' LIMIT 1";
    $result = mysqli_query($cn, $check_query);

    if ($result && mysqli_num_rows($result) === 1) {
        // Generate OTP
        $otp = rand(100000, 999999);

        // Ensure table exists
        $create_table = "CREATE TABLE IF NOT EXISTS password_resets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL,
            otp VARCHAR(6) NOT NULL,
            expiry DATETIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        mysqli_query($cn, $create_table);

        // Remove existing OTP for this email
        mysqli_query($cn, "DELETE FROM password_resets WHERE email = '$email_sql'");

        // Insert new OTP expiring in 15 minutes (server time)
        $insert_otp = "INSERT INTO password_resets (email, otp, expiry) VALUES ('$email_sql', '$otp', DATE_ADD(NOW(), INTERVAL 15 MINUTE))";
        if (mysqli_query($cn, $insert_otp)) {
            // Send email
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = SMTP_HOST;
                $mail->SMTPAuth = true;
                $mail->Username = SMTP_USERNAME;
                $mail->Password = SMTP_PASSWORD;
                $mail->SMTPSecure = SMTP_SECURE;
                $mail->Port = SMTP_PORT;

                $mail->setFrom(DEFAULT_FROM_EMAIL, DEFAULT_FROM_NAME);
                $mail->addAddress($email_raw);
                $mail->isHTML(true);
                $mail->Subject = 'Password Reset OTP';
                $mail->Body = "
                    <h2>Password Reset Request</h2>
                    <p>Your OTP is: <strong style='font-size: 24px; color: #4CAF50;'>$otp</strong></p>
                    <p>This OTP will expire in 15 minutes.</p>
                ";

                $mail->send();

                $_SESSION['message'] = "OTP sent successfully! Check your email.";
                $_SESSION['message_type'] = "success";
                $_SESSION['reset_email'] = $email_raw;
                $_SESSION['otp_verified'] = false;

                header("Location: reset_password.php");
                exit;
            } catch (Exception $e) {
                $_SESSION['message'] = "Failed to send OTP. Please try again.";
                $_SESSION['message_type'] = "error";
                header("Location: forgot_password.php");
                exit;
            }
        } else {
            $_SESSION['message'] = "Failed to generate OTP. Please try again.";
            $_SESSION['message_type'] = "error";
            header("Location: forgot_password.php");
            exit;
        }
    } else {
        $_SESSION['message'] = "Email not found.";
        $_SESSION['message_type'] = "error";
        header("Location: forgot_password.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }

        .container {
            display: flex;
            height: 100vh;
        }

        .sidebar {
            width: 250px;
            background-color: #e0e0e0;
            padding: 30px;
            border-right: 1px solid #ccc;
        }

        .sidebar h2 {
            margin-top: 0;
            font-size: 24px;
            color: #333;
        }

        .form-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
        }

        .forgot-form {
            background-color: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        .forgot-form h3 {
            margin-bottom: 20px;
            font-size: 22px;
            color: #444;
            text-align: center;
        }

        .forgot-form label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: bold;
        }

        .forgot-form input[type="email"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #aaa;
            border-radius: 4px;
            font-size: 14px;
        }

        .forgot-form input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #333;
            color: white;
            font-weight: bold;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .forgot-form input[type="submit"]:hover {
            background-color: #555;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #4CAF50;
            text-decoration: none;
            font-size: 14px;
        }

        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
            text-align: center;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="sidebar">
            <h2>Password Recovery</h2>
            <p>Enter your email address to receive a password reset OTP.</p>
        </div>

        <div class="form-container">
            <form class="forgot-form" action="forgot_password.php" method="post">
                <h3>Forgot Password</h3>

                <?php
                if (isset($_SESSION['message'])) {
                    echo '<div class="message ' . $_SESSION['message_type'] . '">' . $_SESSION['message'] . '</div>';
                    unset($_SESSION['message']);
                    unset($_SESSION['message_type']);
                }
                ?>

                <label for="email">Email Address:</label>
                <input type="email" name="email" id="email" required>

                <input type="submit" value="Send OTP" name="send_otp">

                <div class="back-link">
                    <a href="login.php"><i class="fas fa-arrow-left"></i> Back to Login</a>
                </div>
            </form>
        </div>
    </div>

    <?php /* processing moved to top */ ?>

</body>

</html>