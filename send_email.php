<?php
session_start();
// Hide PHP warnings/notices from user-facing AJAX response
@ini_set('display_errors', '0');
@error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);

// Check if user is logged in and is a Manufacture or Distributor
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'Manufacture' && $_SESSION['role'] !== 'Distributor')) {
    echo "Access denied. Only Manufacture and Distributor can send emails.";
    exit();
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "Invalid request method.";
    exit();
}

// Get form data
$fromEmail = $_POST['fromEmail'] ?? '';
$toEmail = $_POST['toEmail'] ?? '';
$subject = $_POST['subject'] ?? '';
$message = $_POST['message'] ?? '';

// Validate inputs
if (empty($fromEmail) || empty($toEmail) || empty($subject) || empty($message)) {
    echo "All fields are required.";
    exit();
}

// Validate email format
if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
    echo "Invalid recipient email address.";
    exit();
}

// Include configuration and PHPMailer
require 'email_config.php';
require 'notification_helper.php';
require 'PHPMailer-6.10.0/src/Exception.php';
require 'PHPMailer-6.10.0/src/PHPMailer.php';
require 'PHPMailer-6.10.0/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

try {
    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);

    // Server settings
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USERNAME;
    // Sanitize in case the app password was pasted with spaces (Google shows it grouped)
    $mail->Password = preg_replace('/\s+/', '', SMTP_PASSWORD);
    $mail->SMTPSecure = SMTP_SECURE === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = SMTP_PORT;
    $mail->CharSet = 'UTF-8';
    // Disable SMTP debug (set to DEBUG_SERVER temporarily only when diagnosing)
    $mail->SMTPDebug = SMTP::DEBUG_OFF;

    // Recipients
    // Always set the SMTP authenticated address as From (required by Gmail)
    $effectiveFrom = DEFAULT_FROM_EMAIL;
    if (strcasecmp(DEFAULT_FROM_EMAIL, SMTP_USERNAME) !== 0) {
        // Force From to match SMTP username to satisfy Gmail policy
        $effectiveFrom = SMTP_USERNAME;
    }
    $mail->setFrom($effectiveFrom, $_SESSION['name'] ?? DEFAULT_FROM_NAME);

    // Use session email for reply-to if valid; otherwise fall back to default
    $replyToEmail = isset($_SESSION['email']) && filter_var($_SESSION['email'], FILTER_VALIDATE_EMAIL)
        ? $_SESSION['email']
        : (filter_var($fromEmail, FILTER_VALIDATE_EMAIL) ? $fromEmail : DEFAULT_FROM_EMAIL);
    $mail->addReplyTo($replyToEmail, $_SESSION['name'] ?? DEFAULT_FROM_NAME);
    $mail->addAddress($toEmail);

    // Content
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body = nl2br($message);
    $mail->AltBody = strip_tags($message);

    // Send email with TLS first; on auth failure, retry with SSL:465
    $sent = false;
    try {
        $mail->send();
        $sent = true;
    } catch (Exception $e1) {
        $authFailed = stripos($mail->ErrorInfo, 'authenticate') !== false || stripos($mail->ErrorInfo, '535') !== false;
        if ($authFailed) {
            try {
                // Retry with a fresh PHPMailer instance using SSL:465
                $mail2 = new PHPMailer(true);
                $mail2->isSMTP();
                $mail2->Host = SMTP_HOST;
                $mail2->SMTPAuth = true;
                $mail2->Username = SMTP_USERNAME;
                $mail2->Password = preg_replace('/\s+/', '', SMTP_PASSWORD);
                $mail2->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail2->Port = 465;
                $mail2->CharSet = 'UTF-8';
                $mail2->SMTPDebug = SMTP::DEBUG_OFF;

                $mail2->setFrom($effectiveFrom, $_SESSION['name'] ?? DEFAULT_FROM_NAME);
                $mail2->addReplyTo($replyToEmail, $_SESSION['name'] ?? DEFAULT_FROM_NAME);
                $mail2->addAddress($toEmail);

                $mail2->isHTML(true);
                $mail2->Subject = $subject;
                $mail2->Body = nl2br($message);
                $mail2->AltBody = strip_tags($message);

                $mail2->send();
                $sent = true;
            } catch (Exception $e2) {
                throw $e2; // bubble up final error
            }
        } else {
            throw $e1; // non-auth related error
        }
    }

    // Log the email if enabled
    if (ENABLE_EMAIL_LOGGING) {
        $cn = mysqli_connect("localhost", "root", "", "pragmanx_onelife_distributor");

        // Create email_logs table if it doesn't exist
        $createTableQuery = "CREATE TABLE IF NOT EXISTS email_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            from_name VARCHAR(255) NOT NULL,
            from_email VARCHAR(255) NOT NULL,
            to_email VARCHAR(255) NOT NULL,
            subject VARCHAR(500) NOT NULL,
            message TEXT NOT NULL,
            sent_at DATETIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        mysqli_query($cn, $createTableQuery);

        $fromName = $_SESSION['name'] ?? 'Unknown';
        $timestamp = date('Y-m-d H:i:s');

        $logQuery = "INSERT INTO email_logs (from_name, from_email, to_email, subject, message, sent_at) 
                      VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($cn, $logQuery);
        mysqli_stmt_bind_param($stmt, "ssssss", $fromName, $fromEmail, $toEmail, $subject, $message, $timestamp);
        mysqli_stmt_execute($stmt);
    }

    // Create email notification
    $emailMessage = "Email sent from " . $_SESSION['name'] . " to " . $toEmail . " - Subject: " . $subject;
    createNotification($emailMessage, 'email', $_SESSION['user_id'], $_SESSION['role']);

    echo "Email sent successfully!";

} catch (Exception $e) {
    $hint = '';
    if (stripos($mail->ErrorInfo, '535') !== false || stripos($mail->ErrorInfo, 'authenticate') !== false) {
        $mismatch = (strcasecmp(DEFAULT_FROM_EMAIL, SMTP_USERNAME) !== 0);
        $hint = "\nHint: ";
        if ($mismatch) { $hint .= "DEFAULT_FROM_EMAIL must equal SMTP_USERNAME. "; }
        $hint .= "Use a Gmail App Password with 2FA, correct port/security (tls:587 or ssl:465).";
    }
    echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}" . $hint;
}
?>