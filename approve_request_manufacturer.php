<?php
session_start();
header('Content-Type: application/json');

require_once 'notification_helper.php';

$cn = mysqli_connect("localhost", "root", "", "pragmanx_onelife_distributor");
if (!$cn) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

$role = $_SESSION['role'] ?? '';
$user_id = intval($_SESSION['user_id'] ?? 0);
if ($role !== 'Manufacture' || $user_id <= 0) {
    http_response_code(403);
    echo json_encode(["error" => "Only manufacturers can approve"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Invalid method"]);
    exit;
}

$request_id = intval($_POST['request_id'] ?? 0);
if ($request_id <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid request id"]);
    exit;
}

// Fetch request
$qr = "SELECT * FROM requested_products WHERE id = ? LIMIT 1";
$stmt = mysqli_prepare($cn, $qr);
$stmt && mysqli_stmt_bind_param($stmt, "i", $request_id);
$stmt && mysqli_stmt_execute($stmt);
$res = $stmt ? mysqli_stmt_get_result($stmt) : false;
$req = $res ? mysqli_fetch_assoc($res) : null;
if (!$req) {
    http_response_code(404);
    echo json_encode(["error" => "Request not found"]);
    exit;
}

// Approve by manufacturer -> status becomes 'Manufacturer Approved'
$upd = mysqli_prepare($cn, "UPDATE requested_products SET status = 'Manufacturer Approved' WHERE id = ?");
mysqli_stmt_bind_param($upd, "i", $request_id);
mysqli_stmt_execute($upd);

// Notify distributor
$msg_dis = "Manufacturer approved request for \"" . $req['name'] . "\" (qty: " . intval($req['quantity'] ?? 1) . ")";
createNotification($msg_dis, 'product', (string)$req['distributor_id'], 'Distributor', 'requested_products', $request_id);

echo json_encode(["success" => true, "message" => "Request approved by manufacturer."]);
exit;
?>


