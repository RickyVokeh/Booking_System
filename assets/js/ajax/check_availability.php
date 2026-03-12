<?php
session_start();
require_once __DIR__ . '/../includes/config/constants.php';
require_once __DIR__ . '/../includes/classes/Booking.php';
require_once __DIR__ . '/../includes/functions/booking_functions.php';

header('Content-Type: application/json');

// Get request data
$date = $_POST['date'] ?? '';
$time = $_POST['time'] ?? '';
$guests = isset($_POST['guests']) ? (int)$_POST['guests'] : 0;

// Validate input
if (empty($date) || empty($time) || $guests < 1) {
    echo json_encode([
        'success' => false, 
        'message' => 'Date, time and guests are required'
    ]);
    exit();
}

// Validate date format
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    echo json_encode(['success' => false, 'message' => 'Invalid date format']);
    exit();
}

// Validate time format
if (!preg_match('/^\d{2}:\d{2}$/', $time)) {
    echo json_encode(['success' => false, 'message' => 'Invalid time format']);
    exit();
}

// Check if date is in the past
if (strtotime($date) < strtotime(date('Y-m-d'))) {
    echo json_encode([
        'success' => false, 
        'available' => false,
        'message' => 'Date cannot be in the past'
    ]);
    exit();
}

// Check if time is within business hours
$time_timestamp = strtotime($time);
$opening_time = strtotime('11:00');
$closing_time = strtotime('23:00');

if ($time_timestamp < $opening_time || $time_timestamp > $closing_time) {
    echo json_encode([
        'success' => false,
        'available' => false,
        'message' => 'Selected time is outside business hours (11:00 AM - 11:00 PM)'
    ]);
    exit();
}

try {
    $booking = new Booking();
    
    // Check availability
    $availability = $booking->checkAvailability($date, $time, $guests);
    
    // Get alternative slots if not available
    $alternative_slots = [];
    if (!$availability['available']) {
        // Get all slots for this date
        $allSlots = $booking->getBookingsByDate($date);
        
        // Find available times within 2 hours of requested time
        $requested_time = strtotime($time);
        $two_hours = 2 * 3600; // 2 hours in seconds
        
        $opening = strtotime('11:00');
        $closing = strtotime('23:00');
        $interval = BOOKING_INTERVAL * 60;
        
        for ($t = $opening; $t <= $closing; $t += $interval) {
            // Skip the requested time
            if ($t == $requested_time) continue;
            
            // Check if within 2 hours
            if (abs($t - $requested_time) <= $two_hours) {
                $timeString = date('H:i', $t);
                
                // Check if this alternative slot is available
                $bookedCount = 0;
                foreach ($allSlots as $booked) {
                    if ($booked['booking_time'] == $timeString && 
                        in_array($booked['status'], ['pending', 'confirmed'])) {
                        $bookedCount++;
                    }
                }
                
                if ($bookedCount < 10) { // Available
                    $alternative_slots[] = [
                        'time' => $timeString,
                        'display' => date('g:i A', $t)
                    ];
                }
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'available' => $availability['available'],
        'booked_tables' => $availability['booked_tables'],
        'available_tables' => $availability['available_tables'],
        'total_tables' => 10,
        'message' => $availability['available'] ? 
            'Table available!' : 
            'Sorry, no tables available for this time',
        'alternative_slots' => $alternative_slots,
        'requested_time' => date('g:i A', strtotime($time)),
        'requested_date' => date('F j, Y', strtotime($date))
    ]);
    
} catch (Exception $e) {
    error_log("Error in check_availability.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'available' => false,
        'message' => 'Error checking availability',
        'error' => $e->getMessage()
    ]);
}