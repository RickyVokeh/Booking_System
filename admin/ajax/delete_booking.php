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

$bookingId = $_POST['booking_id'] ?? 0;

if (!$bookingId) {
    echo json_encode(['success' => false, 'message' => 'Booking ID required']);
    exit();
}

$booking = new Booking();
$result = $booking->delete($bookingId);

if ($result) {
    require_once __DIR__ . '/../includes/admin_functions.php';
    logAdminAction($_SESSION['admin_id'], 'delete_booking', "Deleted booking #$bookingId");
    
    echo json_encode(['success' => true, 'message' => 'Booking deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error deleting booking']);
}