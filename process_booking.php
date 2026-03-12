<?php
session_start();
require_once __DIR__ . '/includes/config/constants.php';
require_once __DIR__ . '/includes/classes/Booking.php';
require_once __DIR__ . '/includes/classes/Mailer.php';
require_once __DIR__ . '/includes/functions/booking_functions.php';

// Verify CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['booking_csrf']) {
    $_SESSION['error'] = 'Invalid security token';
    header('Location: booking.php');
    exit();
}

// Validate required fields
$required_fields = ['customer_name', 'customer_email', 'customer_phone', 'booking_date', 'booking_time', 'guests'];
foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        $_SESSION['error'] = 'Please fill in all required fields';
        header('Location: booking.php');
        exit();
    }
}

// Sanitize input
$bookingData = [
    'customer_name' => sanitizeInput($_POST['customer_name']),
    'customer_email' => sanitizeInput($_POST['customer_email']),
    'customer_phone' => sanitizeInput($_POST['customer_phone']),
    'booking_date' => $_POST['booking_date'],
    'booking_time' => $_POST['booking_time'],
    'guests' => (int)$_POST['guests'],
    'table_preference' => sanitizeInput($_POST['table_preference'] ?? ''),
    'special_requests' => sanitizeInput($_POST['special_requests'] ?? ''),
    'status' => 'confirmed' // Auto-confirm for now
];

// Validate booking data
$errors = validateBookingData($bookingData);
if (!empty($errors)) {
    $_SESSION['error'] = implode('<br>', $errors);
    header('Location: booking.php');
    exit();
}

// Check availability
$booking = new Booking();
$availability = $booking->checkAvailability(
    $bookingData['booking_date'], 
    $bookingData['booking_time'], 
    $bookingData['guests']
);

if (!$availability['available']) {
    $_SESSION['error'] = 'Sorry, no tables available for selected date and time';
    header('Location: booking.php');
    exit();
}

// Create booking
$bookingId = $booking->createBooking($bookingData);

if ($bookingId) {
    // Get full booking details for email
    $newBooking = $booking->getById($bookingId);
    
    // Send confirmation email
    $mailer = new Mailer();
    $emailSent = $mailer->sendBookingConfirmation($newBooking);
    
    // Send admin notification
    $mailer->sendAdminNotification($newBooking);
    
    // Clear CSRF token
    unset($_SESSION['booking_csrf']);
    
    // Store booking ID in session for success page
    $_SESSION['last_booking_id'] = $bookingId;
    $_SESSION['success'] = 'Booking confirmed successfully! A confirmation email has been sent to your email address.';
    
    header('Location: thanks.php');
    exit();
} else {
    $_SESSION['error'] = 'Sorry, there was an error processing your booking. Please try again.';
    header('Location: booking.php');
    exit();
}