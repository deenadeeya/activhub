<?php
require_once '../includes/session_check.php';
require_once '../config/connect.php';
require_once '../includes/NotificationService.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (!isset($_SESSION['user_ic'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$user_ic = $_SESSION['user_ic'];
$notificationService = new NotificationService($conn);

$input = json_decode(file_get_contents('php://input'), true);

if (isset($input['mark_all']) && $input['mark_all']) {
    // Mark all notifications as read
    if ($notificationService->markAllAsRead($user_ic)) {
        echo json_encode(['success' => true, 'message' => 'All notifications marked as read']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to mark all as read']);
    }
} elseif (isset($input['notification_id'])) {
    // Mark specific notification as read
    $notification_id = intval($input['notification_id']);
    
    if ($notificationService->markAsRead($notification_id, $user_ic)) {
        echo json_encode(['success' => true, 'message' => 'Notification marked as read']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to mark notification as read']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
}
?>
