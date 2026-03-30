<?php
session_start();
require_once __DIR__ . '/includes/config/constants.php';
require_once __DIR__ . '/includes/classes/Booking.php';
require_once __DIR__ . '/includes/functions.php';


// Check if we have a booking ID
if (!isset($_SESSION['last_booking_id'])) {
    header('Location: booking.php'); 
    exit();
}

$booking = new Booking();
$bookingDetails = $booking->getById($_SESSION['last_booking_id']);

if (!$bookingDetails) {
    header('Location: booking.php');
    exit();
}

$page_title = 'Booking Confirmed - ' . APP_NAME;
include INCLUDES_PATH . '/templates/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card text-center border-success">
            <div class="card-header bg-success text-white">
                <h3><i class="fas fa-check-circle"></i> Booking Confirmed!</h3>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
                </div>
                
                <h4 class="card-title">Thank You, <?php echo htmlspecialchars($bookingDetails['customer_name']); ?>!</h4>
                
                <p class="card-text">Your booking has been confirmed. We've sent a confirmation email to:</p>
                <p class="h5"><?php echo htmlspecialchars($bookingDetails['customer_email']); ?></p>
                
                <hr>
                
                <div class="row mt-4">
                    <div class="col-md-6 offset-md-3">
                        <div class="card border-info">
                            <div class="card-header bg-info text-white">
                                <h5>Booking Details</h5>
                            </div>
                            <div class="card-body text-start">
                                <p><strong>Booking Number:</strong> <?php echo $bookingDetails['booking_number']; ?></p>
                                <p><strong>Date:</strong> <?php echo formatBookingDate($bookingDetails['booking_date']); ?></p>
                                <p><strong>Time:</strong> <?php echo formatBookingTime($bookingDetails['booking_time']); ?></p>
                                <p><strong>Guests:</strong> <?php echo $bookingDetails['guests']; ?></p>
                                <?php if (!empty($bookingDetails['special_requests'])): ?>
                                <p><strong>Special Requests:</strong> <?php echo htmlspecialchars($bookingDetails['special_requests']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <div class="mt-4">
                    <h5>Need to make changes?</h5>
                    <p>Please contact us at least 2 hours before your booking time.</p>
                    <p>
                        <i class="fas fa-phone"></i> +254701616385<br>
                        <i class="fas fa-envelope"></i> info@meatlovers.com
                    </p>
                </div>
                
                <a href="index.php" class="btn btn-primary mt-3">
                    <i class="fas fa-home"></i> Return to Home
                </a>
            </div>
        </div>
    </div>
</div>

<?php 
// Clear the session booking ID
unset($_SESSION['last_booking_id']);
include INCLUDES_PATH . '/templates/footer.php'; 
?>