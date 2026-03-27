<?php
session_start();
require_once __DIR__ . '/includes/config/constants.php';
require_once __DIR__ . '/includes/classes/Booking.php';

$booking = new Booking();
$featuredBookings = $booking->getUpcomingBookings(5);

$page_title = 'Welcome to ' . APP_NAME;
$extra_css = '<link rel="stylesheet" href="' . ASSETS_PATH . '/css/home.css">';
include INCLUDES_PATH . '/templates/header.php';
?>

<div class="hero-section text-center py-5 mb-4 bg-light rounded">
    <h1 class="display-4">Welcome to Meatlovers Restaurant</h1>
    <p class="lead">Experience the finest dining with us. Book your table now!</p>
    <a href="booking.php" class="btn btn-primary btn-lg">
        <i class="fas fa-calendar-plus"></i> Book a Table
    </a>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-center h-100">
            <div class="card-body">
                <i class="fas fa-clock fa-3x text-primary mb-3"></i>
                <h3>Opening Hours</h3>
                <p class="mb-1">Monday - Friday: 05:00 AM - 11:00 PM</p>
                <p>Saturday - Sunday: 10:00 AM - 12:00 AM</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center h-100">
            <div class="card-body">
                <i class="fas fa-map-marker-alt fa-3x text-primary mb-3"></i>
                <h3>Location</h3>
                <p class="mb-1">Moi Avenue,</p>
                <p>City, Nairobi</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center h-100">
            <div class="card-body">
                <i class="fas fa-phone fa-3x text-primary mb-3"></i>
                <h3>Contact</h3>
                <p class="mb-1">Phone: +254701407860</p>
                <p>Email: info@meatlovers.com</p>
            </div>
        </div>
    </div>
</div>


<?php include INCLUDES_PATH . '/templates/footer.php'; ?>