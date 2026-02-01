<?php
session_start();
require_once 'notification_helper.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['role'] ?? null;

$action = $_GET['action'] ?? 'get';

switch ($action) {
    case 'get':
        $notifications = getNotifications($user_id, $user_role, 20);
        $unread_count = getUnreadNotificationCount($user_id, $user_role);
        
        // Format notifications for display
        $formatted_notifications = [];
        foreach ($notifications as $notification) {
            $style = getNotificationStyle($notification['type']);
            $formatted_notifications[] = [
                'id' => $notification['id'],
                'message' => $notification['message'],
                'type' => $notification['type'],
                'icon' => $style['icon'],
                'color' => $style['color'],
                'is_read' => (bool)$notification['is_read'],
                'time' => formatNotificationTime($notification['created_at']),
                'created_at' => $notification['created_at']
            ];
        }
        
        echo json_encode([
            'notifications' => $formatted_notifications,
            'unread_count' => $unread_count
        ]);
        break;
        
    case 'mark_read':
        $notification_id = $_POST['notification_id'] ?? null;
        if ($notification_id) {
            $result = markNotificationAsRead($notification_id);
            echo json_encode(['success' => $result]);
        } else {
            echo json_encode(['error' => 'Notification ID required']);
        }
        break;
        
    case 'mark_all_read':
        $result = markAllNotificationsAsRead($user_id, $user_role);
        echo json_encode(['success' => $result]);
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        break;
}
?>