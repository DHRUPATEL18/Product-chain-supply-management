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

// Must be logged in as Retailer
$role = $_SESSION['role'] ?? '';
$retailer_id = intval($_SESSION['user_id'] ?? 0);
if ($role !== 'Retailer' || $retailer_id <= 0) {
    http_response_code(403);
    echo json_encode(["error" => "Only retailers can request products"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Invalid method"]);
    exit;
}

$product_id = intval($_POST['product_id'] ?? 0);
if ($product_id <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid product"]);
    exit;
}

// Find retailer's distributor via user_relations (Distributor-Retailer)
$qr_rel = "SELECT parent_id AS distributor_id FROM user_relations WHERE relation = 'Distributor-Retailer' AND child_id = ? LIMIT 1";
$stmt_rel = mysqli_prepare($cn, $qr_rel);
mysqli_stmt_bind_param($stmt_rel, "i", $retailer_id);
mysqli_stmt_execute($stmt_rel);
$res_rel = mysqli_stmt_get_result($stmt_rel);
$rel = mysqli_fetch_assoc($res_rel);
$distributor_id = intval($rel['distributor_id'] ?? 0);
if ($distributor_id <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "No distributor linked to this retailer"]);
    exit;
}

// Fetch product details
$qr_prod = "SELECT id, product_category_id, product_name, sku_id FROM products WHERE id = ? LIMIT 1";
$stmt_prod = mysqli_prepare($cn, $qr_prod);
mysqli_stmt_bind_param($stmt_prod, "i", $product_id);
mysqli_stmt_execute($stmt_prod);
$res_prod = mysqli_stmt_get_result($stmt_prod);
$prod = mysqli_fetch_assoc($res_prod);
if (!$prod) {
    http_response_code(404);
    echo json_encode(["error" => "Product not found"]);
    exit;
}

// Insert into requested_products with minimal info; default quantity=1, status=Pending
$name = $prod['product_name'];
$category = intval($prod['product_category_id']);
$color = '';
$spec = 'Auto request from Products table';
$quantity = 1;
$status = 'Pending';

$qr_ins = "INSERT INTO requested_products (name, category, color, specifications, quantity, status, retailer_id, distributor_id, date_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
$stmt_ins = mysqli_prepare($cn, $qr_ins);
mysqli_stmt_bind_param($stmt_ins, "sissssii", $name, $category, $color, $spec, $quantity, $status, $retailer_id, $distributor_id);

if (!mysqli_stmt_execute($stmt_ins)) {
    http_response_code(500);
    echo json_encode(["error" => "Failed to create request"]);
    exit;
}

$request_id = mysqli_insert_id($cn);

// Create notification for Distributor
$retailer_name = $_SESSION['name'] ?? 'Retailer';
$message = $retailer_name . " requested product \"" . $name . "\" (SKU: " . ($prod['sku_id'] ?? 'N/A') . ")";
createNotification($message, 'product', (string)$distributor_id, 'Distributor', 'requested_products', $request_id);

echo json_encode(["success" => true, "request_id" => $request_id]);
exit;
?>


