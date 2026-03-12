// Booking Page Specific JavaScript

$(document).ready(function() {
    // Initialize date picker with min date
    var today = new Date().toISOString().split('T')[0];
    $('#booking_date').attr('min', today);
    $('#booking_date').val(today); // Set default to today
    
    // Load available time slots for today by default
    loadTimeSlots(today);
    
    // Load available time slots when date changes
    $('#booking_date').on('change', function() {
        var selectedDate = $(this).val();
        loadTimeSlots(selectedDate);
    });
    
    // Time slot selection
    $(document).on('click', '.time-slot:not(.disabled)', function() {
        $('.time-slot').removeClass('selected');
        $(this).addClass('selected');
        $('#booking_time').val($(this).data('time'));
        
        // Trigger availability check
        checkAvailability();
    });
    
    // Guest counter
    $('#guests').on('change', function() {
        $('#guest-count').text($(this).val());
        checkAvailability();
    });
    
    $('#decrease-guests').click(function() {
        var currentGuests = parseInt($('#guest-count').text());
        if (currentGuests > 1) {
            currentGuests--;
            $('#guest-count').text(currentGuests);
            $('#guests').val(currentGuests);
            checkAvailability();
        }
    });
    
    $('#increase-guests').click(function() {
        var currentGuests = parseInt($('#guest-count').text());
        var maxGuests = parseInt($('#guests').attr('max') || 10);
        if (currentGuests < maxGuests) {
            currentGuests++;
            $('#guest-count').text(currentGuests);
            $('#guests').val(currentGuests);
            checkAvailability();
        }
    });
    
    // Table preference selection
    $('.table-preference').click(function() {
        $('.table-preference').removeClass('selected');
        $(this).addClass('selected');
        $('#table_preference').val($(this).data('preference'));
    });
    
    // Special requests character counter
    $('#special_requests').on('input', function() {
        var remaining = 500 - $(this).val().length;
        $('#char-counter').text(remaining + ' characters remaining');
        if (remaining < 50) {
            $('#char-counter').removeClass('text-muted').addClass('text-warning');
        } else {
            $('#char-counter').removeClass('text-warning').addClass('text-muted');
        }
        if (remaining < 0) {
            $(this).val($(this).val().substring(0, 500));
        }
    });
    
    // Form submission
    $('#bookingForm').on('submit', function(e) {
        if (!validateStep3()) {
            e.preventDefault();
            return false;
        }
    });
    
    // Add availability message container if not exists
    if ($('#availability-message').length === 0) {
        $('<div id="availability-message" class="alert mt-3" style="display: none;"></div>')
            .insertAfter('#guests');
    }
});

// Load available time slots
function loadTimeSlots(date) {
    if (!date) return;
    
    showLoading();
    
    $.ajax({
        url: 'ajax/get_time_slots.php',
        method: 'POST',
        data: { date: date },
        dataType: 'json',
        timeout: 10000, // 10 second timeout
        success: function(response) {
            var timeSlotsContainer = $('.time-slots-grid');
            
            if (!timeSlotsContainer.length) {
                // Create time slots container if it doesn't exist
                $('<div class="time-slots-grid mt-3"></div>').insertAfter('#booking_time');
                timeSlotsContainer = $('.time-slots-grid');
            }
            
            timeSlotsContainer.empty();
            
            if (response.success && response.slots && response.slots.length > 0) {
                var availableCount = 0;
                
                response.slots.forEach(function(slot) {
                    var slotClass = 'time-slot';
                    if (!slot.available) {
                        slotClass += ' disabled';
                    } else {
                        availableCount++;
                    }
                    
                    var slotHtml = '<div class="' + slotClass + '" data-time="' + slot.time + '">' +
                                 '<div class="time">' + slot.display + '</div>' +
                                 (slot.available ? 
                                    '<small class="text-success">Available</small>' : 
                                    '<small class="text-muted">Fully Booked</small>') +
                                 '</div>';
                    
                    timeSlotsContainer.append(slotHtml);
                });
                
                // Show summary
                var summaryHtml = '<div class="mt-2 text-muted small">' +
                                 '<i class="fas fa-info-circle"></i> ' +
                                 availableCount + ' time slots available</div>';
                
                if ($('#time-slots-summary').length) {
                    $('#time-slots-summary').html(summaryHtml);
                } else {
                    $('<div id="time-slots-summary">' + summaryHtml + '</div>')
                        .insertAfter(timeSlotsContainer);
                }
                
            } else {
                timeSlotsContainer.html(
                    '<div class="alert alert-warning text-center">' +
                    '<i class="fas fa-exclamation-triangle"></i> ' +
                    'No time slots available for this date. Please select another date.' +
                    '</div>'
                );
            }
            
            hideLoading();
        },
        error: function(xhr, status, error) {
            hideLoading();
            console.error('AJAX Error:', status, error);
            console.error('Response:', xhr.responseText);
            
            var errorMsg = 'Error loading time slots. ';
            if (status === 'timeout') {
                errorMsg += 'Request timed out. Please try again.';
            } else if (status === 'parsererror') {
                errorMsg += 'Invalid server response.';
            } else {
                errorMsg += 'Please try again.';
            }
            
            $('.time-slots-grid').html(
                '<div class="alert alert-danger text-center">' +
                '<i class="fas fa-exclamation-circle"></i> ' +
                errorMsg +
                '</div>'
            );
        }
    });
}

// Check availability in real-time
function checkAvailability() {
    var date = $('#booking_date').val();
    var time = $('#booking_time').val();
    var guests = $('#guests').val();
    
    if (date && time && guests) {
        $('#availability-message').removeClass('alert-success alert-danger alert-info')
            .addClass('alert-info')
            .html('<i class="fas fa-spinner fa-spin"></i> Checking availability...')
            .show();
        
        $.ajax({
            url: 'ajax/check_availability.php',
            method: 'POST',
            data: {
                date: date,
                time: time,
                guests: guests
            },
            dataType: 'json',
            timeout: 8000,
            success: function(response) {
                if (response.success) {
                    if (response.available) {
                        $('#availability-message')
                            .removeClass('alert-info alert-danger')
                            .addClass('alert-success')
                            .html('<i class="fas fa-check-circle"></i> ' +
                                  'Table available! ' + response.available_tables + 
                                  ' tables free at this time.');
                        
                        // Enable next button if in step 2
                        if ($('#step2').is(':visible')) {
                            $('.btn-primary[onclick*="nextStep(3)"]').prop('disabled', false);
                        }
                    } else {
                        var message = '<i class="fas fa-times-circle"></i> ' +
                                     'Sorry, no tables available for this time.';
                        
                        if (response.alternative_slots && response.alternative_slots.length > 0) {
                            message += '<br><br><strong>Alternative times within 2 hours:</strong><br>';
                            response.alternative_slots.forEach(function(slot) {
                                message += '<span class="badge bg-info me-1 mb-1" ' +
                                          'style="cursor:pointer;" ' +
                                          'onclick="selectTime(\'' + slot.time + '\')">' +
                                          slot.display + '</span> ';
                            });
                        }
                        
                        $('#availability-message')
                            .removeClass('alert-info alert-success')
                            .addClass('alert-danger')
                            .html(message);
                        
                        // Disable next button
                        $('.btn-primary[onclick*="nextStep(3)"]').prop('disabled', true);
                    }
                } else {
                    $('#availability-message')
                        .removeClass('alert-info alert-success')
                        .addClass('alert-danger')
                        .html('<i class="fas fa-exclamation-circle"></i> ' +
                              (response.message || 'Error checking availability'));
                }
            },
            error: function(xhr, status, error) {
                console.error('Availability check error:', status, error);
                $('#availability-message')
                    .removeClass('alert-info alert-success')
                    .addClass('alert-warning')
                    .html('<i class="fas fa-exclamation-triangle"></i> ' +
                          'Unable to check availability. Please proceed or try again.');
                
                // Allow proceeding but with warning
                $('.btn-primary[onclick*="nextStep(3)"]').prop('disabled', false);
            }
        });
    }
}

// Helper function to select alternative time
function selectTime(time) {
    $('#booking_time').val(time);
    $('.time-slot').removeClass('selected');
    $('.time-slot[data-time="' + time + '"]').addClass('selected');
    checkAvailability();
}

// Validate step 1
function validateStep1() {
    var name = $('#customer_name').val().trim();
    var email = $('#customer_email').val().trim();
    var phone = $('#customer_phone').val().trim();
    var errors = [];
    
    // Clear previous errors
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').remove();
    
    // Name validation
    if (!name) {
        errors.push('Name is required');
        $('#customer_name').addClass('is-invalid');
        $('#customer_name').after('<div class="invalid-feedback">Name is required</div>');
    } else if (name.length < 2) {
        errors.push('Name must be at least 2 characters');
        $('#customer_name').addClass('is-invalid');
        $('#customer_name').after('<div class="invalid-feedback">Name must be at least 2 characters</div>');
    }
    
    // Email validation
    if (!email) {
        errors.push('Email is required');
        $('#customer_email').addClass('is-invalid');
        $('#customer_email').after('<div class="invalid-feedback">Email is required</div>');
    } else if (!isValidEmail(email)) {
        errors.push('Please enter a valid email address');
        $('#customer_email').addClass('is-invalid');
        $('#customer_email').after('<div class="invalid-feedback">Please enter a valid email address</div>');
    }
    
    // Phone validation
    if (!phone) {
        errors.push('Phone number is required');
        $('#customer_phone').addClass('is-invalid');
        $('#customer_phone').after('<div class="invalid-feedback">Phone number is required</div>');
    } else if (!isValidPhone(phone)) {
        errors.push('Please enter a valid phone number');
        $('#customer_phone').addClass('is-invalid');
        $('#customer_phone').after('<div class="invalid-feedback">Please enter a valid phone number</div>');
    }
    
    if (errors.length > 0) {
        showNotification(errors.join('<br>'), 'error');
        return false;
    }
    
    return true;
}

// Validate step 2
function validateStep2() {
    var date = $('#booking_date').val();
    var time = $('#booking_time').val();
    var guests = $('#guests').val();
    var errors = [];
    
    // Clear previous errors
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').remove();
    
    if (!date) {
        errors.push('Please select a date');
        $('#booking_date').addClass('is-invalid');
        $('#booking_date').after('<div class="invalid-feedback">Please select a date</div>');
    }
    
    if (!time) {
        errors.push('Please select a time');
        $('#booking_time').addClass('is-invalid');
        $('#booking_time').after('<div class="invalid-feedback">Please select a time</div>');
    }
    
    if (!guests) {
        errors.push('Please select number of guests');
        $('#guests').addClass('is-invalid');
        $('#guests').after('<div class="invalid-feedback">Please select number of guests</div>');
    }
    
    if (errors.length > 0) {
        showNotification(errors.join('<br>'), 'error');
        return false;
    }
    
    return true;
}

// Validate step 3
function validateStep3() {
    if (!$('#confirm_terms').is(':checked')) {
        showNotification('Please confirm that the information is correct', 'warning');
        return false;
    }
    return true;
}

// Email validation helper
function isValidEmail(email) {
    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
}

// Phone validation helper
function isValidPhone(phone) {
    var re = /^[\d\s\+\-\(\)]{10,20}$/;
    return re.test(phone);
}

// Step navigation
function nextStep(step) {
    // Validate current step before proceeding
    if (step === 2) {
        if (!validateStep1()) return;
    } else if (step === 3) {
        if (!validateStep2()) return;
        updateReview();
    }
    
    // Hide all steps
    $('#step1, #step2, #step3').hide();
    
    // Show requested step
    $('#step' + step).show();
    $('#formStep').val(step);
    
    // Update progress bar
    var progress = (step / 3) * 100;
    $('#formProgress').css('width', progress + '%');
    $('#formProgress').text('Step ' + step + ' of 3');
    
    // Update step indicators if they exist
    $('.step-indicator').removeClass('active completed');
    for (var i = 1; i <= 3; i++) {
        if (i < step) {
            $('.step-indicator[data-step="' + i + '"]').addClass('completed');
        } else if (i === step) {
            $('.step-indicator[data-step="' + i + '"]').addClass('active');
        }
    }
    
    // Scroll to top of form
    $('html, body').animate({
        scrollTop: $('#bookingForm').offset().top - 100
    }, 500);
}

function prevStep(step) {
    nextStep(step);
}

// Update review section
function updateReview() {
    $('#review_name').text($('#customer_name').val());
    $('#review_email').text($('#customer_email').val());
    $('#review_phone').text($('#customer_phone').val());
    
    var date = new Date($('#booking_date').val());
    $('#review_date').text(date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    }));
    
    var timeSelect = $('#booking_time');
    var timeText = timeSelect.find('option[value="' + timeSelect.val() + '"]').text();
    $('#review_time').text(timeText || timeSelect.val());
    
    var guestsSelect = $('#guests');
    var guestsText = guestsSelect.find('option:selected').text();
    $('#review_guests').text(guestsText);
    
    var tablePref = $('#table_preference');
    var prefText = tablePref.find('option:selected').text();
    $('#review_table_preference').text(prefText);
    
    var specialRequests = $('#special_requests').val();
    $('#review_special_requests').text(specialRequests || 'None');
}

// Show loading spinner
function showLoading() {
    if ($('.loading-overlay').length === 0) {
        var overlay = $('<div class="loading-overlay">' +
                       '<div class="spinner-border text-primary" role="status">' +
                       '<span class="visually-hidden">Loading...</span>' +
                       '</div>' +
                       '</div>').css({
            'position': 'fixed',
            'top': '0',
            'left': '0',
            'width': '100%',
            'height': '100%',
            'background': 'rgba(255,255,255,0.8)',
            'z-index': '9999',
            'display': 'flex',
            'align-items': 'center',
            'justify-content': 'center'
        });
        
        $('body').append(overlay);
    }
}

// Hide loading spinner
function hideLoading() {
    $('.loading-overlay').remove();
}

// Show notification
function showNotification(message, type) {
    var alertClass = 'alert-' + (type === 'success' ? 'success' : 
                                 type === 'error' ? 'danger' : 
                                 type === 'warning' ? 'warning' : 'info');
    
    var notification = $('<div>')
        .addClass('alert ' + alertClass + ' alert-dismissible fade show position-fixed')
        .css({
            'top': '20px',
            'right': '20px',
            'z-index': '10000',
            'max-width': '400px',
            'box-shadow': '0 4px 6px rgba(0,0,0,0.1)'
        })
        .html(message + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>');
    
    $('body').append(notification);
    
    setTimeout(function() {
        notification.fadeOut(function() {
            $(this).remove();
        });
    }, 5000);
}

// Add step indicators to the form
function addStepIndicators() {
    var steps = ['Personal Info', 'Booking Details', 'Confirm'];
    var indicatorHtml = '<div class="step-indicator-wrapper d-flex justify-content-between mb-4">';
    
    for (var i = 0; i < steps.length; i++) {
        var stepNum = i + 1;
        indicatorHtml += '<div class="step-indicator text-center" data-step="' + stepNum + '">' +
                        '<div class="step-circle">' + stepNum + '</div>' +
                        '<div class="step-label">' + steps[i] + '</div>' +
                        '</div>';
        
        if (i < steps.length - 1) {
            indicatorHtml += '<div class="step-connector"></div>';
        }
    }
    
    indicatorHtml += '</div>';
    
    $('#bookingForm').prepend(indicatorHtml);
    
    // Add CSS for step indicators
    var style = '<style>' +
        '.step-indicator-wrapper { align-items: center; }' +
        '.step-indicator { flex: 1; }' +
        '.step-circle { width: 40px; height: 40px; border-radius: 50%; background: #e9ecef; ' +
        'color: #6c757d; display: flex; align-items: center; justify-content: center; ' +
        'margin: 0 auto 10px; font-weight: bold; transition: all 0.3s ease; }' +
        '.step-indicator.active .step-circle { background: #007bff; color: white; }' +
        '.step-indicator.completed .step-circle { background: #28a745; color: white; }' +
        '.step-connector { flex: 0.5; height: 2px; background: #e9ecef; margin: 0 10px; }' +
        '.step-label { font-size: 14px; color: #6c757d; }' +
        '.step-indicator.active .step-label { color: #007bff; font-weight: 600; }' +
        '</style>';
    
    $('head').append(style);
}

// Initialize step indicators on page load
$(document).ready(function() {
    if ($('#bookingForm').length && $('#step1').length) {
        addStepIndicators();
    }
});