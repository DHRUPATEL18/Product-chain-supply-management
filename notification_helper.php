<?php
// Database connection
function getDBConnection() {
    $cn = mysqli_connect("localhost", "root", "", "pragmanx_onelife_distributor");
    if (!$cn) {
        die("Database connection failed: " . mysqli_connect_error());
    }
    return $cn;
}

// Create notifications table if it doesn't exist
function createNotificationsTable() {
    $cn = getDBConnection();
    
    $sql = "CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id VARCHAR(255),
        user_role VARCHAR(100),
        message TEXT NOT NULL,
        type VARCHAR(50) NOT NULL,
        is_read BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        related_table VARCHAR(100),
        related_id INT
    )";
    
    mysqli_query($cn, $sql);
    mysqli_close($cn);
}

// Create a new notification
function createNotification($message, $type, $user_id = null, $user_role = null, $related_table = null, $related_id = null) {
    $cn = getDBConnection();
    
    // Ensure table exists
    createNotificationsTable();
    
    $sql = "INSERT INTO notifications (user_id, user_role, message, type, related_table, related_id) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($cn, $sql);
    mysqli_stmt_bind_param($stmt, "sssssi", $user_id, $user_role, $message, $type, $related_table, $related_id);
    
    $result = mysqli_stmt_execute($stmt);
    mysqli_close($cn);
    
    return $result;
}

// Get notifications for a specific user/role
function getNotifications($user_id = null, $user_role = null, $limit = 10) {
    $cn = getDBConnection();
    
    $sql = "SELECT * FROM notifications 
            WHERE (user_id = ? OR user_role = ? OR (user_id IS NULL AND user_role IS NULL))
            ORDER BY created_at DESC 
            LIMIT ?";
    
    $stmt = mysqli_prepare($cn, $sql);
    mysqli_stmt_bind_param($stmt, "ssi", $user_id, $user_role, $limit);
    mysqli_stmt_execute($stmt);
    
    $result = mysqli_stmt_get_result($stmt);
    $notifications = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $notifications[] = $row;
    }
    
    mysqli_close($cn);
    return $notifications;
}

// Get unread notification count
function getUnreadNotificationCount($user_id = null, $user_role = null) {
    $cn = getDBConnection();
    
    $sql = "SELECT COUNT(*) as count 
            FROM notifications 
            WHERE is_read = FALSE 
            AND (user_id = ? OR user_role = ? OR (user_id IS NULL AND user_role IS NULL))";
    
    $stmt = mysqli_prepare($cn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $user_id, $user_role);
    mysqli_stmt_execute($stmt);
    
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    
    mysqli_close($cn);
    return $row['count'];
}

// Mark notification as read
function markNotificationAsRead($notification_id) {
    $cn = getDBConnection();
    
    $sql = "UPDATE notifications SET is_read = TRUE WHERE id = ?";
    $stmt = mysqli_prepare($cn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $notification_id);
    
    $result = mysqli_stmt_execute($stmt);
    mysqli_close($cn);
    
    return $result;
}

// Mark all notifications as read for a user
function markAllNotificationsAsRead($user_id = null, $user_role = null) {
    $cn = getDBConnection();
    
    $sql = "UPDATE notifications 
            SET is_read = TRUE 
            WHERE (user_id = ? OR user_role = ? OR (user_id IS NULL AND user_role IS NULL))";
    
    $stmt = mysqli_prepare($cn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $user_id, $user_role);
    
    $result = mysqli_stmt_execute($stmt);
    mysqli_close($cn);
    
    return $result;
}

// Format timestamp for display
function formatNotificationTime($timestamp) {
    $time = strtotime($timestamp);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) {
        return "Just now";
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return $minutes . " minute" . ($minutes > 1 ? "s" : "") . " ago";
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . " hour" . ($hours > 1 ? "s" : "") . " ago";
    } else {
        $days = floor($diff / 86400);
        return $days . " day" . ($days > 1 ? "s" : "") . " ago";
    }
}

// Get notification icon and color based on type
function getNotificationStyle($type) {
    $styles = [
        'login' => ['icon' => 'fas fa-sign-in-alt', 'color' => '#9C27B0'],
        'insert' => ['icon' => 'fas fa-plus', 'color' => '#4CAF50'],
        'update' => ['icon' => 'fas fa-edit', 'color' => '#2196F3'],
        'delete' => ['icon' => 'fas fa-trash', 'color' => '#F44336'],
        'report' => ['icon' => 'fas fa-download', 'color' => '#FF9800'],
        'batch' => ['icon' => 'fas fa-layer-group', 'color' => '#607D8B'],
        'email' => ['icon' => 'fas fa-envelope', 'color' => '#2196F3'],
        'product' => ['icon' => 'fas fa-box', 'color' => '#4CAF50'],
        'offer' => ['icon' => 'fas fa-gift', 'color' => '#E91E63'],
        'sold' => ['icon' => 'fas fa-shopping-cart', 'color' => '#F44336']
    ];
    
    return $styles[$type] ?? ['icon' => 'fas fa-bell', 'color' => '#666'];
}

// Get target table for notification type
function getNotificationTargetTable($type, $related_table = null, $user_role = null) {
    // If related_table is provided, use it directly
    if ($related_table) {
        return $related_table;
    }
    
    // Map notification types to their target tables based on user role
    $type_to_table = [
        'login' => null, // No specific table for login
        'insert' => null, // Depends on context
        'update' => null, // Depends on context
        'delete' => null, // Depends on context
        'report' => null, // No specific table for reports
        'email' => null, // No specific table for emails
    ];
    
    // Role-specific mappings
    if ($user_role === 'Distributor') {
        $distributor_tables = [
            'batch' => 'batch_distributor',
            'product' => 'products',
            'offer' => 'offers',
            'sold' => 'sold_products'
        ];
        $type_to_table = array_merge($type_to_table, $distributor_tables);
    } elseif ($user_role === 'Manufacture') {
        $manufacture_tables = [
            'batch' => 'batch_distributor', // Show batch distributor table
            'product' => 'products', // Show products table
            'offer' => 'offers', // Show offers table
            'sold' => 'sold_products' // Show sold products table
        ];
        $type_to_table = array_merge($type_to_table, $manufacture_tables);
    } elseif ($user_role === 'Retailer') {
        $retailer_tables = [
            'batch' => 'batch_retailer', // Show batch retailer table
            'product' => 'products', // Show products table
            'offer' => 'offers', // Show offers table
            'sold' => 'sold_products' // Show sold products table
        ];
        $type_to_table = array_merge($type_to_table, $retailer_tables);
    } elseif ($user_role === 'Area Sales Manager') {
        $asm_tables = [
            'batch' => 'batch_retailer', // Show batch retailer table
            'product' => 'products', // Show products table
            'offer' => 'offers', // Show offers table
            'sold' => 'sold_products' // Show sold products table
        ];
        $type_to_table = array_merge($type_to_table, $asm_tables);
    }
    
    return $type_to_table[$type] ?? null;
}
?>
