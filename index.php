<?php
session_start();
require_once __DIR__ . '/includes/config/constants.php';
require_once __DIR__ . '/includes/classes/Booking.php';

$booking = new Booking();
$featuredBookings = $booking->getUpcomingBookings(5);

$page_title = 'Welcome to ' . APP_NAME;
$extra_css = '<link rel="stylesheet" href="' . ASSETS_URL . '/css/home.css">';
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

<div class="row">
    <div class="col-12">
        <h2 class="text-center mb-4">Recent Bookings</h2>
        <?php if ($featuredBookings): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Booking #</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Guests</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($featuredBookings as $booking): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($booking['booking_number']); ?></td>
                            <td><?php echo htmlspecialchars($booking['customer_name']); ?></td>
                            <td><?php echo formatBookingDate($booking['booking_date']); ?></td>
                            <td><?php echo formatBookingTime($booking['booking_time']); ?></td>
                            <td><?php echo $booking['guests']; ?></td>
                            <td><?php echo getBookingStatusBadge($booking['status']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-center">No bookings yet. Be the first to book!</p>
        <?php endif; ?>
    </div>
</div>

<?php include INCLUDES_PATH . '/templates/footer.php'; ?>