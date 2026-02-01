<?php
session_start();
require_once 'notification_helper.php';

$user = $_POST['user'] ?? '';
$password = $_POST['password'] ?? '';
$role = $_POST['role'] ?? '';

if (empty($user) || empty($password) || empty($role)) {
    header("Location: login.php?error=empty_fields");
    exit();
}

$cn = mysqli_connect('localhost', 'root', '', 'pragmanx_onelife_distributor');
if (!$cn) {
    die('Database connection failed: ' . mysqli_connect_error());
}

$query = "SELECT * FROM users WHERE (email = ? OR username = ?) AND role = ?";
$stmt = mysqli_prepare($cn, $query);

if (!$stmt) {
    header("Location: login.php?error=database_error");
    exit();
}

mysqli_stmt_bind_param($stmt, "sss", $user, $user, $role);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    // Plain text password check (same as your existing logic)
    if ($row['password'] === $password) {
        // Clear any existing session data
        session_unset();
        
        // Set session variables
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['email'] = $row['email'];
        $_SESSION['role'] = $row['role'];
        $_SESSION['user_name'] = $row['username'];
        $_SESSION['login_time'] = time();
        $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'];
        
        // Regenerate session ID for security
        session_regenerate_id(true);

        // Create login notification
        $loginMessage = $row['name'] . " (" . $row['role'] . ") logged in";
        createNotification($loginMessage, 'login', $row['id'], $row['role']);

        // ✅ ASM Attendance Insert Logic
        if (strtolower($row['role']) === 'asm' || strtolower($row['role']) === 'area sales manager') {
            $asm_id = $row['id'] . ' - ' . $row['username'];
            $attendance = 'Present';
            $date_time = date('Y-m-d H:i:s');
            $today = date('Y-m-d');

            // Optional: Get location from POST (if you send it from login form)
            $location = $_POST['location'] ?? 'Unknown Location';

            // Check if already marked for today
            $check_query = "SELECT id FROM asm_attendance WHERE asm_id = ? AND DATE(date_time) = ?";
            $check_stmt = mysqli_prepare($cn, $check_query);
            mysqli_stmt_bind_param($check_stmt, "ss", $asm_id, $today);
            mysqli_stmt_execute($check_stmt);
            mysqli_stmt_store_result($check_stmt);

            if (mysqli_stmt_num_rows($check_stmt) === 0) {
                $insert_query = "INSERT INTO asm_attendance (asm_id, attendance, location, date_time)
                                 VALUES (?, ?, ?, ?)";
                $insert_stmt = mysqli_prepare($cn, $insert_query);
                mysqli_stmt_bind_param($insert_stmt, "ssss", $asm_id, $attendance, $location, $date_time);
                mysqli_stmt_execute($insert_stmt);
                mysqli_stmt_close($insert_stmt);
            }

            mysqli_stmt_close($check_stmt);
        }

        header("Location: tablegrid.php");
        exit();
    } else {
        header("Location: login.php?error=invalid_password");
        exit();
    }
} else {
    header("Location: login.php?error=user_not_found");
    exit();
}

mysqli_stmt_close($stmt);
mysqli_close($cn);
?>
