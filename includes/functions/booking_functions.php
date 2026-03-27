<?php
function validateBookingData($data) {
    $errors = [];
    
    // Validate name
    if (empty($data['customer_name'])) {
        $errors[] = "Name is required";
    } elseif (strlen($data['customer_name']) < 2) {
        $errors[] = "Name must be at least 2 characters";
    }
    
    // Validate email
    if (empty($data['customer_email'])) {
        $errors[] = "Email is required";
    } elseif (!filter_var($data['customer_email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    // Validate phone
    if (empty($data['customer_phone'])) {
        $errors[] = "Phone number is required";
    } elseif (!preg_match('/^[0-9+\-\s()]{10,20}$/', $data['customer_phone'])) {
        $errors[] = "Invalid phone number format";
    }
    
    // Validate date
    if (empty($data['booking_date'])) {
        $errors[] = "Booking date is required";
    } elseif (strtotime($data['booking_date']) < strtotime(date('Y-m-d'))) {
        $errors[] = "Booking date cannot be in the past";
    }
    
    // Validate time
    if (empty($data['booking_time'])) {
        $errors[] = "Booking time is required";
    }
    
    // Validate guests
    if (empty($data['guests'])) {
        $errors[] = "Number of guests is required";
    } elseif ($data['guests'] < 1 || $data['guests'] > MAX_GUESTS) {
        $errors[] = "Guests must be between 1 and " . MAX_GUESTS;
    }
    
    return $errors;
}

function formatBookingTime($time) {
    return date('g:i A', strtotime($time));
}

function formatBookingDate($date) {
    return date('F j, Y', strtotime($date));
}

function getBookingStatusBadge($status) {
    $badges = [
        'pending' => 'warning',
        'confirmed' => 'success',
        'cancelled' => 'danger',
        'completed' => 'info'
    ];
    
    $color = $badges[$status] ?? 'secondary';
    return "<span class='badge bg-{$color}'>{$status}</span>";
}

function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    
    // Trim whitespace
    $input = trim($input);
    
    // Remove slashes if magic quotes is on
    //if (get_magic_quotes_gpc()) {
        //$input = stripslashes($input);
    //}
    
    // Convert special characters to HTML entities
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    
    return $input;
}

function isTableAvailable($date, $time, $guests) {
    // Implementation depends on your business logic
    $booking = new Booking();
    $availability = $booking->checkAvailability($date, $time, $guests);
    return $availability['available'];
}

function getAvailableTimeSlots($date) {
    $opening_time = strtotime('11:00');
    $closing_time = strtotime('23:00');
    $interval = BOOKING_INTERVAL * 60; // Convert to seconds
    $slots = [];
    
    for ($time = $opening_time; $time <= $closing_time; $time += $interval) {
        $slots[] = date('H:i', $time);
    }
    
    return $slots;
}