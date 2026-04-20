<?php
// Retailer History Helper Functions

function addRetailerHistory($cn, $retailer_id, $action_type, $action_description, $performed_by_id, $performed_by_name, $performed_by_role, $product_id = null, $requested_product_id = null, $old_status = null, $new_status = null, $notes = null) {
    $query = "INSERT INTO retailer_history (retailer_id, product_id, requested_product_id, action_type, action_description, performed_by_id, performed_by_name, performed_by_role, old_status, new_status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($cn, $query);
    if (!$stmt) {
        error_log("Failed to prepare statement: " . mysqli_error($cn));
        return false;
    }
    
    mysqli_stmt_bind_param($stmt, "iiisssissss", 
        $retailer_id, 
        $product_id, 
        $requested_product_id, 
        $action_type, 
        $action_description, 
        $performed_by_id, 
        $performed_by_name, 
        $performed_by_role, 
        $old_status, 
        $new_status, 
        $notes
    );
    
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    return $result;
}

function getRetailerHistory($cn, $retailer_id, $limit = 50) {
    $query = "SELECT h.*, 
                     p.name as product_name,
                     rp.name as requested_product_name
              FROM retailer_history h
              LEFT JOIN products p ON p.id = h.product_id
              LEFT JOIN requested_products rp ON rp.id = h.requested_product_id
              WHERE h.retailer_id = ?
              ORDER BY h.created_at DESC
              LIMIT ?";
    
    $stmt = mysqli_prepare($cn, $query);
    if (!$stmt) {
        return false;
    }
    
    mysqli_stmt_bind_param($stmt, "ii", $retailer_id, $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $history = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $history[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    return $history;
}

function getActionTypeIcon($action_type) {
    $icons = [
        'product_requested' => '📝',
        'product_approved' => '✅',
        'product_rejected' => '❌',
        'product_sold' => '💰',
        'product_reverted' => '🔄',
        'product_delivered' => '🚚',
        'product_returned' => '↩️'
    ];
    
    return $icons[$action_type] ?? '📋';
}

function getActionTypeColor($action_type) {
    $colors = [
        'product_requested' => '#2196F3',
        'product_approved' => '#4CAF50',
        'product_rejected' => '#F44336',
        'product_sold' => '#FF9800',
        'product_reverted' => '#9C27B0',
        'product_delivered' => '#00BCD4',
        'product_returned' => '#795548'
    ];
    
    return $colors[$action_type] ?? '#607D8B';
}

function formatActionDescription($action_type, $action_description, $old_status = null, $new_status = null) {
    $formatted = $action_description;
    
    if ($old_status && $new_status) {
        $formatted .= " (Status changed from '{$old_status}' to '{$new_status}')";
    }
    
    return $formatted;
}
?>
