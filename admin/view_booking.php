<?php
session_start();
require_once __DIR__ . '/../includes/config/constants.php';
require_once __DIR__ . '/../includes/classes/Booking.php';
require_once __DIR__ . '/includes/auth_check.php';

$bookingId = $_GET['id'] ?? 0;
$booking = new Booking();
$bookingDetails = $booking->getById($bookingId);

if (!$bookingDetails) {
    $_SESSION['error'] = 'Booking not found';
    header('Location: bookings.php');
    exit();
}

$page_title = 'View Booking - ' . APP_NAME;
include INCLUDES_PATH . '/templates/admin_header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-eye"></i> View Booking: <?php echo htmlspecialchars($bookingDetails['booking_number']); ?>
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="edit_booking.php?id=<?php echo $bookingId; ?>" class="btn btn-warning me-2">
            <i class="fas fa-edit"></i> Edit
        </a>
        <a href="bookings.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Booking Details -->
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Booking Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="120">Booking #:</th>
                                <td><strong><?php echo htmlspecialchars($bookingDetails['booking_number']); ?></strong></td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $bookingDetails['status'] == 'confirmed' ? 'success' : 
                                            ($bookingDetails['status'] == 'pending' ? 'warning' : 
                                            ($bookingDetails['status'] == 'cancelled' ? 'danger' : 'info')); 
                                    ?>">
                                        <?php echo ucfirst($bookingDetails['status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Date:</th>
                                <td><?php echo date('l, F j, Y', strtotime($bookingDetails['booking_date'])); ?></td>
                            </tr>
                            <tr>
                                <th>Time:</th>
                                <td><?php echo date('g:i A', strtotime($bookingDetails['booking_time'])); ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="120">Guests:</th>
                                <td><?php echo $bookingDetails['guests']; ?> People</td>
                            </tr>
                            <tr>
                                <th>Table Preference:</th>
                                <td><?php echo ucfirst($bookingDetails['table_preference'] ?? 'None'); ?></td>
                            </tr>
                            <tr>
                                <th>Created:</th>
                                <td><?php echo date('M j, Y g:i A', strtotime($bookingDetails['created_at'])); ?></td>
                            </tr>
                            <tr>
                                <th>Last Updated:</th>
                                <td><?php echo date('M j, Y g:i A', strtotime($bookingDetails['updated_at'])); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <?php if (!empty($bookingDetails['special_requests'])): ?>
                <div class="row mt-3">
                    <div class="col-12">
                        <h6>Special Requests:</h6>
                        <div class="p-3 bg-light rounded">
                            <?php echo nl2br(htmlspecialchars($bookingDetails['special_requests'])); ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Customer Details -->
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Customer Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($bookingDetails['customer_name']); ?></p>
                        <p><strong>Email:</strong> 
                            <a href="mailto:<?php echo $bookingDetails['customer_email']; ?>">
                                <?php echo htmlspecialchars($bookingDetails['customer_email']); ?>
                            </a>
                        </p>
                        <p><strong>Phone:</strong> 
                            <a href="tel:<?php echo $bookingDetails['customer_phone']; ?>">
                                <?php echo htmlspecialchars($bookingDetails['customer_phone']); ?>
                            </a>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>IP Address:</strong> <?php echo $bookingDetails['ip_address'] ?? 'N/A'; ?></p>
                        <p><strong>User Agent:</strong> 
                            <small class="text-muted"><?php echo $bookingDetails['user_agent'] ?? 'N/A'; ?></small>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Quick Actions -->
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button class="btn btn-success" onclick="updateStatus(<?php echo $bookingId; ?>, 'confirmed')">
                        <i class="fas fa-check-circle"></i> Confirm Booking
                    </button>
                    <button class="btn btn-warning" onclick="updateStatus(<?php echo $bookingId; ?>, 'pending')">
                        <i class="fas fa-clock"></i> Mark as Pending
                    </button>
                    <button class="btn btn-info" onclick="updateStatus(<?php echo $bookingId; ?>, 'completed')">
                        <i class="fas fa-check-double"></i> Mark as Completed
                    </button>
                    <button class="btn btn-danger" onclick="updateStatus(<?php echo $bookingId; ?>, 'cancelled')">
                        <i class="fas fa-times-circle"></i> Cancel Booking
                    </button>
                    <hr>
                    <button class="btn btn-primary" onclick="sendEmail()">
                        <i class="fas fa-envelope"></i> Send Email
                    </button>
                    <button class="btn btn-secondary" onclick="printBooking()">
                        <i class="fas fa-print"></i> Print Details
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Email History -->
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Email History</h5>
            </div>
            <div class="card-body">
                <div id="emailHistory">
                    <!-- Loaded via AJAX -->
                    <p class="text-center text-muted">Loading...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    loadEmailHistory(<?php echo $bookingId; ?>);
});

function updateStatus(id, status) {
    if (!confirm('Are you sure you want to change the status to ' + status + '?')) {
        return;
    }
    
    $.ajax({
        url: 'ajax/update_booking_status.php',
        method: 'POST',
        data: {
            booking_id: id,
            status: status,
            csrf_token: '<?php echo generateCSRFToken(); ?>'
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showNotification('Status updated successfully', 'success');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                showNotification('Error: ' + response.message, 'error');
            }
        }
    });
}

function loadEmailHistory(bookingId) {
    $.ajax({
        url: 'ajax/get_email_history.php',
        method: 'GET',
        data: { booking_id: bookingId },
        dataType: 'json',
        success: function(response) {
            var html = '';
            if (response.emails && response.emails.length > 0) {
                response.emails.forEach(function(email) {
                    html += '<div class="email-history-item ' + email.status + ' mb-2 p-2 border rounded">' +
                           '<small><strong>' + email.subject + '</strong></small><br>' +
                           '<small class="text-muted">' + email.sent_at + '</small><br>' +
                           '<span class="badge bg-' + (email.status === 'sent' ? 'success' : 'danger') + '">' +
                           email.status + '</span>' +
                           '</div>';
                });
            } else {
                html = '<p class="text-center text-muted">No emails sent</p>';
            }
            $('#emailHistory').html(html);
        },
        error: function() {
            $('#emailHistory').html('<p class="text-center text-danger">Error loading history</p>');
        }
    });
}

function sendEmail() {
    window.location.href = 'send_email.php?booking_id=<?php echo $bookingId; ?>';
}

function printBooking() {
    window.open('print_booking.php?id=<?php echo $bookingId; ?>', '_blank');
}
</script>

<?php include INCLUDES_PATH . '/templates/footer.php'; ?>