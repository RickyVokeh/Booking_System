<?php
session_start();
require_once __DIR__ . '/../../includes/config/constants.php';
require_once __DIR__ . '/../../includes/classes/Booking.php';
require_once __DIR__ . '/../../includes/functions/user_functions.php';

header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit();
}

$bookingId = $_POST['booking_id'] ?? 0;
$status = $_POST['status'] ?? '';

if (!$bookingId || !$status) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

$booking = new Booking();
$result = $booking->updateStatus($bookingId, $status);

if ($result) {
    // Log the action
    require_once __DIR__ . '/../includes/admin_functions.php';
    logAdminAction($_SESSION['admin_id'], 'update_booking_status', "Updated booking #$bookingId to $status");
    
    echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error updating status']);
}