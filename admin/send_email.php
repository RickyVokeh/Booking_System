<?php
session_start();
require_once __DIR__ . '/../includes/config/constants.php';
require_once __DIR__ . '/../includes/classes/Booking.php';
require_once __DIR__ . '/../includes/classes/Mailer.php';
require_once __DIR__ . '/includes/auth_check.php';

$booking = new Booking();

// Get booking if specified
$bookingId = $_GET['booking_id'] ?? 0;
$bookingDetails = null;
if ($bookingId) {
    $bookingDetails = $booking->getById($bookingId);
}

// Get all customers for dropdown
$customers = $booking->getAll('customer_name ASC');

$page_title = 'Send Email - ' . APP_NAME;
include INCLUDES_PATH . '/templates/admin_header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-envelope"></i> Send Email</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="email_history.php" class="btn btn-info me-2">
            <i class="fas fa-history"></i> Email History
        </a>
        <a href="bookings.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Compose Email</h5>
            </div>
            <div class="card-body">
                <form id="emailForm">
                    <div class="mb-3">
                        <label for="recipient_type" class="form-label">Send To</label>
                        <select class="form-control" id="recipient_type" onchange="toggleRecipientType()">
                            <option value="single">Single Customer</option>
                            <option value="multiple">Multiple Customers</option>
                            <option value="all">All Customers</option>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="single_recipient">
                        <label for="recipient" class="form-label">Select Customer</label>
                        <select class="form-control" id="recipient" name="recipient">
                            <option value="">Select a customer</option>
                            <?php foreach ($customers as $customer): ?>
                                <option value="<?php echo $customer['customer_email']; ?>" 
                                        data-name="<?php echo htmlspecialchars($customer['customer_name']); ?>"
                                        <?php echo ($bookingDetails && $bookingDetails['customer_email'] == $customer['customer_email']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($customer['customer_name']); ?> 
                                    (<?php echo htmlspecialchars($customer['customer_email']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="multiple_recipient" style="display: none;">
                        <label for="recipients" class="form-label">Select Customers</label>
                        <select class="form-control" id="recipients" name="recipients[]" multiple size="5">
                            <?php foreach ($customers as $customer): ?>
                                <option value="<?php echo $customer['customer_email']; ?>">
                                    <?php echo htmlspecialchars($customer['customer_name']); ?> 
                                    (<?php echo htmlspecialchars($customer['customer_email']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject</label>
                        <input type="text" class="form-control" id="subject" name="subject" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="message" class="form-label">Message</label>
                        <textarea class="form-control" id="message" name="message" rows="10" required></textarea>
                        <small class="text-muted">
                            You can use variables: {name}, {email}, {booking_number}, {date}, {time}
                        </small>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="save_template" name="save_template">
                            <label class="form-check-label" for="save_template">
                                Save as template
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3" id="template_name_div" style="display: none;">
                        <label for="template_name" class="form-label">Template Name</label>
                        <input type="text" class="form-control" id="template_name" name="template_name">
                    </div>
                    
                    <button type="button" class="btn btn-primary" onclick="sendEmail()">
                        <i class="fas fa-paper-plane"></i> Send Email
                    </button>
                    
                    <button type="button" class="btn btn-secondary" onclick="previewEmail()">
                        <i class="fas fa-eye"></i> Preview
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Templates -->
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Email Templates</h5>
            </div>
            <div class="card-body">
                <div class="list-group" id="templatesList">
                    <!-- Loaded via AJAX -->
                    <p class="text-center text-muted">Loading templates...</p>
                </div>
            </div>
        </div>
        
        <!-- Preview -->
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Preview</h5>
            </div>
            <div class="card-body">
                <div id="emailPreview" class="border p-3 bg-light" style="min-height: 200px;">
                    <p class="text-center text-muted">Enter message to see preview</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Email Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="previewContent"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="sendEmail()">Send Now</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    loadTemplates();
    
    // Live preview
    $('#message').on('keyup', function() {
        updatePreview();
    });
    
    $('#save_template').change(function() {
        $('#template_name_div').toggle(this.checked);
    });
    
    <?php if ($bookingDetails): ?>
    // Pre-fill subject for booking
    $('#subject').val('Regarding your booking #<?php echo $bookingDetails['booking_number']; ?>');
    <?php endif; ?>
});

function toggleRecipientType() {
    var type = $('#recipient_type').val();
    
    $('#single_recipient').hide();
    $('#multiple_recipient').hide();
    
    if (type === 'single') {
        $('#single_recipient').show();
    } else if (type === 'multiple') {
        $('#multiple_recipient').show();
    }
}

function loadTemplates() {
    $.ajax({
        url: 'ajax/get_email_templates.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            var html = '';
            if (response.templates && response.templates.length > 0) {
                response.templates.forEach(function(template) {
                    html += '<a href="#" class="list-group-item list-group-item-action" ' +
                           'onclick="loadTemplate(' + template.id + ')">' +
                           '<h6 class="mb-1">' + template.name + '</h6>' +
                           '<small class="text-muted">' + template.subject + '</small>' +
                           '</a>';
                });
            } else {
                html = '<p class="text-center text-muted">No templates saved</p>';
            }
            $('#templatesList').html(html);
        }
    });
}

function loadTemplate(id) {
    $.ajax({
        url: 'ajax/get_email_template.php',
        method: 'GET',
        data: { template_id: id },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#subject').val(response.template.subject);
                $('#message').val(response.template.message);
                updatePreview();
            }
        }
    });
}

function updatePreview() {
    var message = $('#message').val();
    if (message) {
        $('#emailPreview').html('<div class="preview-content">' + 
                               nl2br(escapeHtml(message)) + '</div>');
    } else {
        $('#emailPreview').html('<p class="text-center text-muted">Enter message to see preview</p>');
    }
}

function previewEmail() {
    var subject = $('#subject').val();
    var message = $('#message').val();
    
    if (!subject || !message) {
        showNotification('Please enter subject and message', 'warning');
        return;
    }
    
    var preview = '<div class="email-preview">' +
                  '<h3>' + escapeHtml(subject) + '</h3>' +
                  '<hr>' +
                  '<div>' + nl2br(escapeHtml(message)) + '</div>' +
                  '</div>';
    
    $('#previewContent').html(preview);
    $('#previewModal').modal('show');
}

function sendEmail() {
    var type = $('#recipient_type').val();
    var recipients = [];
    
    if (type === 'single') {
        var recipient = $('#recipient').val();
        if (!recipient) {
            showNotification('Please select a recipient', 'warning');
            return;
        }
        recipients = [recipient];
    } else if (type === 'multiple') {
        recipients = $('#recipients').val();
        if (!recipients || recipients.length === 0) {
            showNotification('Please select at least one recipient', 'warning');
            return;
        }
    }
    
    var subject = $('#subject').val();
    var message = $('#message').val();
    
    if (!subject || !message) {
        showNotification('Please enter subject and message', 'warning');
        return;
    }
    
    var formData = {
        type: type,
        recipients: recipients,
        subject: subject,
        message: message,
        save_template: $('#save_template').is(':checked'),
        template_name: $('#template_name').val(),
        csrf_token: '<?php echo generateCSRFToken(); ?>'
    };
    
    showLoading();
    
    $.ajax({
        url: 'ajax/send_bulk_email.php',
        method: 'POST',
        data: JSON.stringify(formData),
        contentType: 'application/json',
        dataType: 'json',
        success: function(response) {
            hideLoading();
            if (response.success) {
                showNotification('Emails sent successfully! Sent to ' + response.sent + ' recipients', 'success');
                $('#emailForm')[0].reset();
            } else {
                showNotification('Error: ' + response.message, 'error');
            }
        },
        error: function() {
            hideLoading();
            showNotification('Error sending emails', 'error');
        }
    });
}

function escapeHtml(text) {
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
}

function nl2br(text) {
    return text.replace(/\n/g, '<br>');
}
</script>
