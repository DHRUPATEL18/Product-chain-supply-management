<?php
session_start();
header('Content-Type: application/json');

require_once 'notification_helper.php';
require_once 'email_config.php';
require 'PHPMailer-6.10.0/src/Exception.php';
require 'PHPMailer-6.10.0/src/PHPMailer.php';
require 'PHPMailer-6.10.0/src/SMTP.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$cn = mysqli_connect("localhost", "root", "", "pragmanx_onelife_distributor");
if (!$cn) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

$role = $_SESSION['role'] ?? '';
$user_id = intval($_SESSION['user_id'] ?? 0);
if ($role !== 'Distributor' || $user_id <= 0) {
    http_response_code(403);
    echo json_encode(["error" => "Only distributors can send to retailer"]);
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

// Fetch requested row
$qr = "SELECT * FROM requested_products WHERE id = ? AND distributor_id = ? LIMIT 1";
$stmt = mysqli_prepare($cn, $qr);
mysqli_stmt_bind_param($stmt, "ii", $request_id, $user_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$req = mysqli_fetch_assoc($res);
if (!$req) {
    http_response_code(404);
    echo json_encode(["error" => "Request not found"]);
    exit;
}

// Enforce relationship: the retailer in this request must be linked to this distributor
$rel_chk_sql = "SELECT 1 FROM user_relations WHERE relation = 'Distributor-Retailer' AND parent_id = ? AND child_id = ? LIMIT 1";
$rel_chk = mysqli_prepare($cn, $rel_chk_sql);
mysqli_stmt_bind_param($rel_chk, "ii", $user_id, $req['retailer_id']);
mysqli_stmt_execute($rel_chk);
$rel_ok = mysqli_stmt_get_result($rel_chk);
if (!$rel_ok || mysqli_num_rows($rel_ok) === 0) {
    http_response_code(403);
    echo json_encode(["error" => "Retailer not linked to this distributor"]);
    exit;
}

// Verify stock strictly by product_id; if insufficient, block approval
$need = intval($req['quantity'] ?? 1);
// Map request name to product_id
$prod_id = 0;
$map_stmt = mysqli_prepare($cn, "SELECT id FROM products WHERE product_name = ? LIMIT 1");
mysqli_stmt_bind_param($map_stmt, "s", $req['name']);
mysqli_stmt_execute($map_stmt);
$map_res = mysqli_stmt_get_result($map_stmt);
if ($map_row = mysqli_fetch_assoc($map_res)) { $prod_id = intval($map_row['id']); }

$available = 0;
if ($prod_id > 0) {
    $qr_stock = "SELECT COALESCE(SUM(pad.quantity),0) AS qty
                 FROM product_assigned_dist pad
                 JOIN batch_distributor bd ON pad.batch_id = bd.id
                 WHERE bd.assigned_to = ? AND pad.product_id = ? AND pad.status = 'Ongoing'";
    $stmt_stock = mysqli_prepare($cn, $qr_stock);
    mysqli_stmt_bind_param($stmt_stock, "ii", $user_id, $prod_id);
    mysqli_stmt_execute($stmt_stock);
    $rs_stock = mysqli_stmt_get_result($stmt_stock);
    $row_stock = $rs_stock ? mysqli_fetch_assoc($rs_stock) : null;
    $available = intval($row_stock['qty'] ?? 0);
}
if (!($available >= $need && $need > 0)) {
    http_response_code(400);
    echo json_encode(["error" => "Insufficient stock to fulfill request"]);
    exit;
}

// Mark request Approved
$upd = mysqli_prepare($cn, "UPDATE requested_products SET status = 'Approved' WHERE id = ?");
mysqli_stmt_bind_param($upd, "i", $request_id);
mysqli_stmt_execute($upd);

// Notify retailer
$msg = "Your request for \"" . $req['name'] . "\" is approved by Distributor";
createNotification($msg, 'product', (string)$req['retailer_id'], 'Retailer', 'requested_products', $request_id);

// Send email to retailer (if email present)
$retailerRes = mysqli_query($cn, "SELECT email, name FROM users WHERE id = " . intval($req['retailer_id']) . " LIMIT 1");
$retailer = $retailerRes ? mysqli_fetch_assoc($retailerRes) : null;
if ($retailer && !empty($retailer['email']) && filter_var($retailer['email'], FILTER_VALIDATE_EMAIL)) {
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = SMTP_PORT;

        $mail->setFrom(DEFAULT_FROM_EMAIL, $_SESSION['name'] ?? DEFAULT_FROM_NAME);
        $mail->addAddress($retailer['email'], $retailer['name'] ?? 'Retailer');

        $mail->isHTML(true);
        $mail->Subject = 'Requested product approved';
        $mail->Body = nl2br("Hello " . ($retailer['name'] ?? 'Retailer') . ",\n\nYour requested product '" . $req['name'] . "' (qty: " . $need . ") has been approved by the distributor.\n\nThanks,\nPragmaX OneLife");
        $mail->AltBody = "Your requested product '" . $req['name'] . "' (qty: " . $need . ") has been approved by the distributor.";
        $mail->send();
    } catch (Exception $e) {
        // Silently ignore mail errors for workflow continuity
    }
}

echo json_encode(["success" => true, "message" => "Sent to retailer and approved."]);
exit;
?>


