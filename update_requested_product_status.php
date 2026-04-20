<?php
session_start();
$cn = mysqli_connect("localhost", "root", "", "pragmanx_onelife_distributor");

// Include retailer history helper
require_once 'retailer_history_helper.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $requested_product_id = $_POST["requested_product_id"] ?? '';
    $new_status = $_POST["new_status"] ?? '';
    $notes = $_POST["notes"] ?? '';
    $performed_by_id = $_SESSION['user_id'] ?? '';
    $performed_by_name = $_SESSION['name'] ?? '';
    $performed_by_role = $_SESSION['role'] ?? '';

    if (empty($requested_product_id) || empty($new_status)) {
        echo "❌ Missing required parameters";
        exit();
    }

    // Get current status and retailer info
    $query = "SELECT rp.*, u.name as retailer_name FROM requested_products rp 
              LEFT JOIN users u ON u.id = rp.retailer_id 
              WHERE rp.id = ?";
    $stmt = mysqli_prepare($cn, $query);
    mysqli_stmt_bind_param($stmt, "i", $requested_product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $requested_product = mysqli_fetch_assoc($result);

    if (!$requested_product) {
        echo "❌ Requested product not found";
        exit();
    }

    $old_status = $requested_product['status'];
    $retailer_id = $requested_product['retailer_id'];
    $retailer_name = $requested_product['retailer_name'];

    // Update the status
    $update_query = "UPDATE requested_products SET status = ?, notes = ? WHERE id = ?";
    $update_stmt = mysqli_prepare($cn, $update_query);
    mysqli_stmt_bind_param($update_stmt, "ssi", $new_status, $notes, $requested_product_id);

    if (mysqli_stmt_execute($update_stmt)) {
        // Determine action type based on new status
        $action_type = '';
        $action_description = '';
        
        switch ($new_status) {
            case 'Approved':
                $action_type = 'product_approved';
                $action_description = "Product '{$requested_product['name']}' approved by {$performed_by_name}";
                break;
            case 'Rejected':
                $action_type = 'product_rejected';
                $action_description = "Product '{$requested_product['name']}' rejected by {$performed_by_name}";
                break;
            case 'Delivered':
                $action_type = 'product_delivered';
                $action_description = "Product '{$requested_product['name']}' marked as delivered";
                break;
            default:
                $action_type = 'product_requested';
                $action_description = "Product '{$requested_product['name']}' status updated to '{$new_status}'";
        }

        // Add to retailer history
        addRetailerHistory(
            $cn, 
            $retailer_id, 
            $action_type, 
            $action_description, 
            $performed_by_id, 
            $performed_by_name, 
            $performed_by_role, 
            null, 
            $requested_product_id, 
            $old_status, 
            $new_status, 
            $notes
        );

        echo "✅ Status updated successfully!";
    } else {
        echo "❌ Failed to update status. Error: " . mysqli_error($cn);
    }
} else {
    echo "❌ Invalid request method";
}
?>
