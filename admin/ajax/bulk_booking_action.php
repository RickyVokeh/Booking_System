<?php
session_start();
require_once __DIR__ . '/../../includes/config/constants.php';
require_once __DIR__ . '/../../includes/classes/Booking.php';
require_once __DIR__ . '/../../includes/functions/user_functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit();
}

$action = $_POST['action'] ?? '';
$bookingIds = $_POST['booking_ids'] ?? [];

if (!$action || empty($bookingIds)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

$booking = new Booking();
$success = true;
$message = '';

foreach ($bookingIds as $id) {
    if ($action === 'delete') {
        $result = $booking->delete($id);
    } else {
        $result = $booking->updateStatus($id, $action);
    }
    
    if (!$result) {
        $success = false;
        $message = "Error processing booking #$id";
        break;
    }
}

if ($success) {
    require_once __DIR__ . '/../includes/admin_functions.php';
    logAdminAction($_SESSION['admin_id'], 'bulk_action', "Performed $action on " . count($bookingIds) . " bookings");
    
    echo json_encode(['success' => true, 'message' => 'Bulk action completed successfully']);
} else {
    echo json_encode(['success' => false, 'message' => $message]);
}