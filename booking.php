<?php
session_start();
require_once __DIR__ . '/includes/config/constants.php';
require_once __DIR__ . '/includes/functions/booking_functions.php';

// Generate CSRF token
if (!isset($_SESSION['booking_csrf'])) {
    $_SESSION['booking_csrf'] = bin2hex(random_bytes(32));
}

$page_title = 'Book a Table - ' . APP_NAME;
$extra_css = '<link rel="stylesheet" href="' . ASSETS_URL . '/css/booking.css">';
$extra_js = '<script src="' . ASSETS_URL . '/js/booking.js"></script>';
include INCLUDES_PATH . '/templates/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0"><i class="fas fa-calendar-plus"></i> Book Your Table</h3>
            </div>
            <div class="card-body">
                <form id="bookingForm" action="process_booking.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['booking_csrf']; ?>">
                    <input type="hidden" name="step" id="formStep" value="1">
                    
                    <!-- Step 1: Personal Information -->
                    <div id="step1">
                        <h4 class="mb-3">Personal Information</h4>
                        <div class="mb-3">
                            <label for="customer_name" class="form-label">Full Name *</label>
                            <input type="text" class="form-control" id="customer_name" name="customer_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="customer_email" class="form-label">Email Address *</label>
                            <input type="email" class="form-control" id="customer_email" name="customer_email" required>
                        </div>
                        <div class="mb-3">
                            <label for="customer_phone" class="form-label">Phone Number *</label>
                            <input type="tel" class="form-control" id="customer_phone" name="customer_phone" required>
                        </div>
                        <button type="button" class="btn btn-primary" onclick="nextStep(2)">Next</button>
                    </div>
                    
                    <!-- Step 2: Booking Details -->
                    <div id="step2" style="display: none;">
                        <h4 class="mb-3">Booking Details</h4>
                        <div class="mb-3">
                            <label for="booking_date" class="form-label">Date *</label>
                            <input type="date" class="form-control" id="booking_date" name="booking_date" 
                                   min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="booking_time" class="form-label">Time *</label>
                            <select class="form-control" id="booking_time" name="booking_time" required>
                                <option value="">Select time</option>
                                <?php
                                $timeSlots = getAvailableTimeSlots(date('Y-m-d'));
                                foreach ($timeSlots as $time):
                                ?>
                                <option value="<?php echo $time; ?>"><?php echo formatBookingTime($time); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="guests" class="form-label">Number of Guests *</label>
                            <select class="form-control" id="guests" name="guests" required>
                                <option value="">Select</option>
                                <?php for ($i = 1; $i <= MAX_GUESTS; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?> <?php echo $i > 1 ? 'Guests' : 'Guest'; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="table_preference" class="form-label">Table Preference</label>
                            <select class="form-control" id="table_preference" name="table_preference">
                                <option value="">No preference</option>
                                <option value="indoor">Indoor</option>
                                <option value="outdoor">Outdoor</option>
                                <option value="window">Window side</option>
                                <option value="private">Private area</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="special_requests" class="form-label">Special Requests</label>
                            <textarea class="form-control" id="special_requests" name="special_requests" rows="3"></textarea>
                        </div>
                        <button type="button" class="btn btn-secondary" onclick="prevStep(1)">Previous</button>
                        <button type="button" class="btn btn-primary" onclick="nextStep(3)">Review Booking</button>
                    </div>
                    
                    <!-- Step 3: Review & Confirm -->
                    <div id="step3" style="display: none;">
                        <h4 class="mb-3">Review Your Booking</h4>
                        <div class="card mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Personal Information</h5>
                                <p><strong>Name:</strong> <span id="review_name"></span></p>
                                <p><strong>Email:</strong> <span id="review_email"></span></p>
                                <p><strong>Phone:</strong> <span id="review_phone"></span></p>
                            </div>
                        </div>
                        <div class="card mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Booking Details</h5>
                                <p><strong>Date:</strong> <span id="review_date"></span></p>
                                <p><strong>Time:</strong> <span id="review_time"></span></p>
                                <p><strong>Guests:</strong> <span id="review_guests"></span></p>
                                <p><strong>Table Preference:</strong> <span id="review_table_preference"></span></p>
                                <p><strong>Special Requests:</strong> <span id="review_special_requests"></span></p>
                            </div>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="confirm_terms" required>
                            <label class="form-check-label" for="confirm_terms">
                                I confirm that all information provided is correct and agree to the terms and conditions.
                            </label>
                        </div>
                        <button type="button" class="btn btn-secondary" onclick="prevStep(2)">Edit Details</button>
                        <button type="submit" class="btn btn-success" id="submitBooking">Confirm Booking</button>
                    </div>
                </form>
            </div>
            <div class="card-footer">
                <div class="progress">
                    <div class="progress-bar" role="progressbar" style="width: 33%;" id="formProgress">Step 1 of 3</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function nextStep(step) {
    // Validate current step before proceeding
    if (step === 2) {
        if (!validateStep1()) return;
    } else if (step === 3) {
        if (!validateStep2()) return;
        updateReview();
    }
    
    document.getElementById('step1').style.display = 'none';
    document.getElementById('step2').style.display = 'none';
    document.getElementById('step3').style.display = 'none';
    document.getElementById('step' + step).style.display = 'block';
    document.getElementById('formStep').value = step;
    
    // Update progress bar
    const progress = (step / 3) * 100;
    document.getElementById('formProgress').style.width = progress + '%';
    document.getElementById('formProgress').textContent = 'Step ' + step + ' of 3';
}

function prevStep(step) {
    nextStep(step);
}

function validateStep1() {
    const name = document.getElementById('customer_name').value;
    const email = document.getElementById('customer_email').value;
    const phone = document.getElementById('customer_phone').value;
    
    if (!name || !email || !phone) {
        alert('Please fill in all personal information fields');
        return false;
    }
    
    if (!email.includes('@') || !email.includes('.')) {
        alert('Please enter a valid email address');
        return false;
    }
    
    return true;
}

function validateStep2() {
    const date = document.getElementById('booking_date').value;
    const time = document.getElementById('booking_time').value;
    const guests = document.getElementById('guests').value;
    
    if (!date || !time || !guests) {
        alert('Please fill in all booking details');
        return false;
    }
    
    return true;
}

function updateReview() {
    document.getElementById('review_name').textContent = document.getElementById('customer_name').value;
    document.getElementById('review_email').textContent = document.getElementById('customer_email').value;
    document.getElementById('review_phone').textContent = document.getElementById('customer_phone').value;
    
    const date = new Date(document.getElementById('booking_date').value);
    document.getElementById('review_date').textContent = date.toLocaleDateString('en-US', { 
        year: 'numeric', month: 'long', day: 'numeric' 
    });
    
    const timeSelect = document.getElementById('booking_time');
    document.getElementById('review_time').textContent = timeSelect.options[timeSelect.selectedIndex].text;
    
    const guestsSelect = document.getElementById('guests');
    document.getElementById('review_guests').textContent = guestsSelect.options[guestsSelect.selectedIndex].text;
    
    const tablePref = document.getElementById('table_preference');
    document.getElementById('review_table_preference').textContent = tablePref.options[tablePref.selectedIndex].text;
    
    const specialRequests = document.getElementById('special_requests').value;
    document.getElementById('review_special_requests').textContent = specialRequests || 'None';
}
</script>

<?php include INCLUDES_PATH . '/templates/footer.php'; ?>