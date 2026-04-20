<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Helper: determine if request expects JSON (AJAX/fetch)
$acceptHeader = isset($_SERVER['HTTP_ACCEPT']) ? strtolower($_SERVER['HTTP_ACCEPT']) : '';
$isJsonRequest = (strpos($acceptHeader, 'application/json') !== false)
    || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || !isset($_SESSION['login_time'])) {
    if ($isJsonRequest) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'unauthorized']);
        exit();
    }
    header("Location: login.php");
    exit();
}

// Check session timeout (1 hour = 3600 seconds)
if (time() - $_SESSION['login_time'] > 3600) {
    session_unset();
    session_destroy();
    if ($isJsonRequest) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'timeout']);
        exit();
    }
    header("Location: login.php?error=timeout");
    exit();
}

// Check if IP address matches (basic security)
if (!isset($_SESSION['user_ip']) || $_SESSION['user_ip'] !== $_SERVER['REMOTE_ADDR']) {
    session_unset();
    session_destroy();
    if ($isJsonRequest) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'security']);
        exit();
    }
    header("Location: login.php?error=security");
    exit();
}

// Regenerate session ID every 30 minutes for security
if (time() - $_SESSION['login_time'] > 1800) {
    session_regenerate_id(true);
    $_SESSION['login_time'] = time();
}
?>
