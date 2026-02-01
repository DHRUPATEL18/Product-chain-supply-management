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
    echo json_encode(["error" => "Only distributors can process requests"]);
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

// Load requested product
$qr_req = "SELECT * FROM requested_products WHERE id = ? AND distributor_id = ? LIMIT 1";
$stmt_req = mysqli_prepare($cn, $qr_req);
mysqli_stmt_bind_param($stmt_req, "ii", $request_id, $user_id);
mysqli_stmt_execute($stmt_req);
$res_req = mysqli_stmt_get_result($stmt_req);
$req = mysqli_fetch_assoc($res_req);
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

// Try to map request name to product_id from products (exact match)
$qr_prod = "SELECT id, product_name, sku_id, product_category_id, added_by FROM products WHERE product_name = ? LIMIT 1";
$stmt_prod = mysqli_prepare($cn, $qr_prod);
mysqli_stmt_bind_param($stmt_prod, "s", $req['name']);
mysqli_stmt_execute($stmt_prod);
$res_prod = mysqli_stmt_get_result($stmt_prod);
$prod = mysqli_fetch_assoc($res_prod);

// If product known, check distributor stock by product_id in product_assigned_dist
$available = 0;
if ($prod) {
    $qr_stock = "
        SELECT COALESCE(SUM(pad.quantity),0) AS qty
        FROM product_assigned_dist pad
        JOIN batch_distributor bd ON pad.batch_id = bd.id
        WHERE bd.assigned_to = ? AND pad.product_id = ? AND pad.status = 'Ongoing'
    ";
    $stmt_stock = mysqli_prepare($cn, $qr_stock);
    mysqli_stmt_bind_param($stmt_stock, "ii", $user_id, $prod['id']);
    mysqli_stmt_execute($stmt_stock);
    $res_stock = mysqli_stmt_get_result($stmt_stock);
    $row_stock = mysqli_fetch_assoc($res_stock);
    $available = intval($row_stock['qty'] ?? 0);
}

$need = intval($req['quantity'] ?? 1);

// If manufacturer has already approved, treat as available for distributor approval
$manuApproved = (isset($req['status']) && $req['status'] === 'Manufacturer Approved');

// Approve only if manufacturer already approved OR distributor has sufficient stock
if ($manuApproved || (($available >= $need) && $need > 0)) {
    // Mark request as Approved/Available
    $upd = mysqli_prepare($cn, "UPDATE requested_products SET status = 'Approved' WHERE id = ?");
    mysqli_stmt_bind_param($upd, "i", $request_id);
    mysqli_stmt_execute($upd);

    // Notify retailer that distributor can fulfill
    $message = "Your request for \"" . $req['name'] . "\" is approved by Distributor";
    createNotification($message, 'product', (string)$req['retailer_id'], 'Retailer', 'requested_products', $request_id);

    // Email retailer
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
            // ignore email errors
        }
    }

    echo json_encode(["success" => true, "message" => "Stock available. Request approved."]);
    exit;
}

// Not available: forward to manufacturer (product.added_by)
if ($prod) {
    $manufacturer_id = intval($prod['added_by']);
} else {
    // Fallback: notify all manufacturers (role = Manufacture) by leaving user_id null and using role filter
    $manufacturer_id = 0;
}

// Update status
$upd2 = mysqli_prepare($cn, "UPDATE requested_products SET status = 'Forwarded to Manufacturer' WHERE id = ?");
mysqli_stmt_bind_param($upd2, "i", $request_id);
mysqli_stmt_execute($upd2);

// Create notification for manufacturer(s)
$msg_for_manu = "Distributor forwarded request for \"" . $req['name'] . "\" (qty: " . $need . ")";
if ($manufacturer_id > 0) {
    createNotification($msg_for_manu, 'product', (string)$manufacturer_id, 'Manufacture', 'requested_products', $request_id);
} else {
    // Broadcast to role
    createNotification($msg_for_manu, 'product', null, 'Manufacture', 'requested_products', $request_id);
}

// Notify retailer that request has been forwarded
$msg_for_retailer = "Your request for \"" . $req['name'] . "\" is forwarded to Manufacturer";
createNotification($msg_for_retailer, 'product', (string)$req['retailer_id'], 'Retailer', 'requested_products', $request_id);

echo json_encode(["success" => true, "message" => "Not available. Forwarded to manufacturer."]);
exit;
?>


