<?php
session_start();
$cn = mysqli_connect("localhost", "root", "", "pragmanx_onelife_distributor");

// Include retailer history helper
require_once 'retailer_history_helper.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $product_id = $_POST["product_id"] ?? '';
    $retailer_id = $_POST["retailer_id"] ?? '';
    $revert_reason = $_POST["revert_reason"] ?? '';
    $new_status = $_POST["new_status"] ?? 'Available';
    $performed_by_id = $_SESSION['user_id'] ?? '';
    $performed_by_name = $_SESSION['name'] ?? '';
    $performed_by_role = $_SESSION['role'] ?? '';

    if (empty($product_id) || empty($retailer_id)) {
        echo "❌ Missing required parameters";
        exit();
    }

    // Get product info
    $query = "SELECT * FROM products WHERE id = ?";
    $stmt = mysqli_prepare($cn, $query);
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $product = mysqli_fetch_assoc($result);

    if (!$product) {
        echo "❌ Product not found";
        exit();
    }

    $old_status = $product['status'];

    // Update product status
    $update_query = "UPDATE products SET status = ?, updated_at = NOW() WHERE id = ?";
    $update_stmt = mysqli_prepare($cn, $update_query);
    mysqli_stmt_bind_param($update_stmt, "si", $new_status, $product_id);

    if (mysqli_stmt_execute($update_stmt)) {
        // Add to retailer history
        $action_description = "Product '{$product['name']}' reverted from '{$old_status}' to '{$new_status}'";
        addRetailerHistory(
            $cn, 
            $retailer_id, 
            'product_reverted', 
            $action_description, 
            $performed_by_id, 
            $performed_by_name, 
            $performed_by_role, 
            $product_id, 
            null, 
            $old_status, 
            $new_status, 
            "Reason: {$revert_reason}"
        );

        echo "✅ Product reverted successfully!";
    } else {
        echo "❌ Failed to revert product. Error: " . mysqli_error($cn);
    }
} else {
    echo "❌ Invalid request method";
}
?>
