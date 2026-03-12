<?php
session_start();
require_once __DIR__ . '/../includes/config/constants.php';
require_once __DIR__ . '/../includes/classes/Booking.php';
require_once __DIR__ . '/../includes/functions/booking_functions.php';

header('Content-Type: application/json');

// Get the requested date
$date = $_POST['date'] ?? '';

if (empty($date)) {
    echo json_encode(['success' => false, 'message' => 'Date is required']);
    exit();
}

// Validate date format
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    echo json_encode(['success' => false, 'message' => 'Invalid date format']);
    exit();
}

// Check if date is in the past
if (strtotime($date) < strtotime(date('Y-m-d'))) {
    echo json_encode(['success' => false, 'message' => 'Date cannot be in the past']);
    exit();
}

try {
    $booking = new Booking();
    
    // Get all available time slots for the date
    $opening_time = strtotime('11:00');
    $closing_time = strtotime('23:00');
    $interval = BOOKING_INTERVAL * 60; // Convert to seconds
    $slots = [];
    
    // Get booked times for this date
    $bookedSlots = $booking->getBookingsByDate($date);
    $bookedTimes = array_column($bookedSlots, 'booking_time');
    
    // Generate time slots
    for ($time = $opening_time; $time <= $closing_time; $time += $interval) {
        $timeString = date('H:i', $time);
        $displayTime = date('g:i A', $time);
        
        // Check if slot is available (less than 10 bookings for this time)
        $bookedCount = 0;
        foreach ($bookedSlots as $booked) {
            if ($booked['booking_time'] == $timeString && 
                in_array($booked['status'], ['pending', 'confirmed'])) {
                $bookedCount++;
            }
        }
        
        $available = $bookedCount < 10; // Assume 10 tables max
        
        $slots[] = [
            'time' => $timeString,
            'display' => $displayTime,
            'available' => $available,
            'booked_count' => $bookedCount
        ];
    }
    
    echo json_encode([
        'success' => true,
        'date' => $date,
        'slots' => $slots,
        'total_slots' => count($slots),
        'available_slots' => count(array_filter($slots, function($slot) {
            return $slot['available'];
        }))
    ]);
    
} catch (Exception $e) {
    error_log("Error in get_time_slots.php: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Error loading time slots',
        'error' => $e->getMessage()
    ]);
}