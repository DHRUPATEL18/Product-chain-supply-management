<?php
session_start();

// Validate session email
if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot_password.php");
    exit;
}

$email = trim(strtolower($_SESSION['reset_email']));

// DB connection for escaping and queries
$cn = mysqli_connect("localhost", "root", "", "pragmanx_onelife_distributor");
if (!$cn) {
    $_SESSION['message'] = "Database connection failed. Please try again.";
    $_SESSION['message_type'] = "error";
    header("Location: reset_password.php");
    exit;
}
$email_sql = mysqli_real_escape_string($cn, $email);

// Handle OTP verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_otp'])) {
    $otp = isset($_POST['otp']) ? trim($_POST['otp']) : '';
    $otp = preg_replace('/[^0-9]/', '', $otp);
    $otp = substr($otp, 0, 6);

    $check_otp = "SELECT id FROM password_resets WHERE email = '$email_sql' AND otp = '$otp' AND expiry > NOW() LIMIT 1";
    $result = mysqli_query($cn, $check_otp);

    if ($result && mysqli_num_rows($result) === 1) {
        $_SESSION['otp_verified'] = true;
        $_SESSION['message'] = "OTP verified. Please set a new password.";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['otp_verified'] = false;
        $_SESSION['message'] = "Invalid or expired OTP. Please try again.";
        $_SESSION['message_type'] = "error";
    }

    header("Location: reset_password.php");
    exit;
}

// Handle password reset - requires prior OTP verification
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['reset_password'])) {
    if (!isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true) {
        $_SESSION['message'] = "Please verify OTP before resetting your password.";
        $_SESSION['message_type'] = "error";
        header("Location: reset_password.php");
        exit;
    }

    // Validate captcha
    $captcha_input = isset($_POST['captcha']) ? trim(strtoupper($_POST['captcha'])) : '';
    $captcha_session = isset($_SESSION['captcha_text']) ? strtoupper($_SESSION['captcha_text']) : '';
    if ($captcha_input === '' || $captcha_input !== $captcha_session) {
        $_SESSION['message'] = "Invalid captcha. Please try again.";
        $_SESSION['message_type'] = "error";
        header("Location: reset_password.php");
        exit;
    }

    $new_password = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';
    $confirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';

    if ($new_password === '' || $confirm_password === '') {
        $_SESSION['message'] = "Password fields cannot be empty.";
        $_SESSION['message_type'] = "error";
        header("Location: reset_password.php");
        exit;
    }

    if ($new_password !== $confirm_password) {
        $_SESSION['message'] = "Passwords do not match!";
        $_SESSION['message_type'] = "error";
        header("Location: reset_password.php");
        exit;
    }

    // Update password in users table
    $update_password = "UPDATE users SET password = '$new_password' WHERE email = '$email_sql'";

    if (mysqli_query($cn, $update_password)) {
        // Delete the used OTP and clear verification flag
        mysqli_query($cn, "DELETE FROM password_resets WHERE email = '$email_sql'");
        unset($_SESSION['otp_verified']);
        unset($_SESSION['reset_email']);

        $_SESSION['message'] = "Password updated successfully! You can now login with your new password.";
        $_SESSION['message_type'] = "success";

        mysqli_close($cn);
        header("Location: login.php");
        exit;
    } else {
        $_SESSION['message'] = "Failed to update password: " . mysqli_error($cn);
        $_SESSION['message_type'] = "error";
        header("Location: reset_password.php");
        exit;
    }
}

// Fallback redirect
header("Location: reset_password.php");
exit;
?>