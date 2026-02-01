<?php
session_start();
header('Content-Type: application/json');

require_once 'notification_helper.php';

$cn = mysqli_connect("localhost", "root", "", "pragmanx_onelife_distributor");
if (!$cn) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Database connection failed"]);
    exit;
}

$role = $_SESSION['role'] ?? '';
$retailer_id = intval($_SESSION['user_id'] ?? 0);
if ($role !== 'Retailer' || $retailer_id <= 0) {
    http_response_code(403);
    echo json_encode(["success" => false, "error" => "Only retailers can submit requests"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "error" => "Invalid method"]);
    exit;
}

// Basic input
$name = trim($_POST['name'] ?? '');
$quantity = intval($_POST['quantity'] ?? 0);
$category_id_post = trim($_POST['category_id'] ?? '');
$color = trim($_POST['color'] ?? '');
$specifications = trim($_POST['specifications'] ?? '');

if ($name === '' || $quantity <= 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Name and positive quantity are required"]);
    exit;
}

// Use category_id if provided
$category_id = null;
if ($category_id_post !== '') {
    $category_id = intval($category_id_post);
}

// Find linked distributor
$stmt_rel = mysqli_prepare($cn, "SELECT parent_id AS distributor_id FROM user_relations WHERE relation = 'Distributor-Retailer' AND child_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt_rel, "i", $retailer_id);
mysqli_stmt_execute($stmt_rel);
$res_rel = mysqli_stmt_get_result($stmt_rel);
$rel = mysqli_fetch_assoc($res_rel);
$distributor_id = intval($rel['distributor_id'] ?? 0);
if ($distributor_id <= 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "No distributor linked to this retailer"]);
    exit;
}

// Compose fields for requested_products
$status = 'Pending';
$final_specs = $specifications;

$stmt_ins = mysqli_prepare($cn, "INSERT INTO requested_products (name, category, color, specifications, quantity, status, retailer_id, distributor_id, date_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
$category_param = $category_id !== null ? $category_id : 0;
mysqli_stmt_bind_param($stmt_ins, "sissssii", $name, $category_param, $color, $final_specs, $quantity, $status, $retailer_id, $distributor_id);

if (!mysqli_stmt_execute($stmt_ins)) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Failed to create request"]);
    exit;
}

$request_id = mysqli_insert_id($cn);

// Notify distributor
$retailer_name = $_SESSION['name'] ?? 'Retailer';
$message = $retailer_name . " requested product \"" . $name . "\" (Qty: " . $quantity . ")";
createNotification($message, 'product', (string)$distributor_id, 'Distributor', 'requested_products', $request_id);

echo json_encode(["success" => true, "request_id" => $request_id]);
exit;
?>


