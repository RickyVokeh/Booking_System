<?php
session_start();
require_once __DIR__ . '/includes/config/constants.php';

$page_title = 'Contact Us - ' . APP_NAME;
include INCLUDES_PATH . '/templates/header.php';
?>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4><i class="fas fa-envelope"></i> Send us a Message</h4>
            </div>
            <div class="card-body">
                <?php if (isset($_SESSION['contact_success'])): ?>
                    <div class="alert alert-success">
                        <?php 
                        echo $_SESSION['contact_success']; 
                        unset($_SESSION['contact_success']);
                        ?>
                    </div>
                <?php endif; ?>
                
                <form action="send_contact.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo bin2hex(random_bytes(32)); ?>">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Your Name *</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address *</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject *</label>
                        <input type="text" class="form-control" id="subject" name="subject" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="message" class="form-label">Message *</label>
                        <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Send Message
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h4><i class="fas fa-info-circle"></i> Contact Information</h4>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <h5><i class="fas fa-map-marker-alt text-primary"></i> Address</h5>
                    <p>123 Food Street<br>City, State 12345<br>United States</p>
                </div>
                
                <div class="mb-4">
                    <h5><i class="fas fa-phone text-primary"></i> Phone</h5>
                    <p>Reservations: +1 (234) 567-8900<br>General: +1 (234) 567-8901</p>
                </div>
                
                <div class="mb-4">
                    <h5><i class="fas fa-envelope text-primary"></i> Email</h5>
                    <p>General: info@restaurant.com<br>Reservations: bookings@restaurant.com</p>
                </div>
                
                <div class="mb-4">
                    <h5><i class="fas fa-clock text-primary"></i> Opening Hours</h5>
                    <p>
                        Monday - Thursday: 11:00 AM - 10:00 PM<br>
                        Friday - Saturday: 11:00 AM - 11:00 PM<br>
                        Sunday: 10:00 AM - 9:00 PM
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Map Section -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h5><i class="fas fa-map"></i> Find Us</h5>
            </div>
            <div class="card-body p-0">
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3022.9663095343008!2d-73.98510768458414!3d40.75889697932681!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c25855c6480299%3A0x55194ec5a1ae072e!2sTimes%20Square!5e0!3m2!1sen!2sus!4v1620000000000!5m2!1sen!2sus" 
                    width="100%" 
                    height="400" 
                    style="border:0;" 
                    allowfullscreen="" 
                    loading="lazy">
                </iframe>
            </div>
        </div>
    </div>
</div>

<?php include INCLUDES_PATH . '/templates/footer.php'; ?>