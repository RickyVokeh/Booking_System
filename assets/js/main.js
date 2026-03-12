// Global JavaScript Functions

// Initialize when DOM is ready
$(document).ready(function() {
    // Enable tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
    
    // Enable popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl)
    });
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
    
    // Format phone numbers
    $('.phone-input').on('input', function() {
        var number = $(this).val().replace(/[^\d]/g, '');
        if (number.length > 0) {
            if (number.length <= 3) {
                number = number;
            } else if (number.length <= 6) {
                number = number.substring(0,3) + '-' + number.substring(3);
            } else {
                number = number.substring(0,3) + '-' + number.substring(3,6) + '-' + number.substring(6,10);
            }
            $(this).val(number);
        }
    });
    
    // Confirm delete actions
    $('.delete-confirm').on('click', function(e) {
        e.preventDefault();
        var message = $(this).data('confirm') || 'Are you sure you want to delete this item?';
        if (confirm(message)) {
            window.location.href = $(this).attr('href');
        }
    });
    
    // Back to top button
    var backToTop = $('<button>')
        .addClass('btn btn-primary back-to-top')
        .html('<i class="fas fa-arrow-up"></i>')
        .css({
            'position': 'fixed',
            'bottom': '20px',
            'right': '20px',
            'display': 'none',
            'z-index': '99',
            'border-radius': '50%',
            'width': '50px',
            'height': '50px'
        });
    
    $('body').append(backToTop);
    
    $(window).scroll(function() {
        if ($(this).scrollTop() > 100) {
            $('.back-to-top').fadeIn();
        } else {
            $('.back-to-top').fadeOut();
        }
    });
    
    $('.back-to-top').click(function() {
        $('html, body').animate({scrollTop: 0}, 600);
        return false;
    });
});

// Form validation helper
function validateForm(formId) {
    var isValid = true;
    var form = $(formId);
    
    form.find('[required]').each(function() {
        if (!$(this).val()) {
            $(this).addClass('is-invalid');
            isValid = false;
        } else {
            $(this).removeClass('is-invalid');
        }
    });
    
    form.find('[type="email"]').each(function() {
        var email = $(this).val();
        if (email && !isValidEmail(email)) {
            $(this).addClass('is-invalid');
            isValid = false;
        }
    });
    
    return isValid;
}

// Email validation
function isValidEmail(email) {
    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
}

// Show loading spinner
function showLoading() {
    $('<div class="loading-spinner">')
        .addClass('position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center')
        .css({
            'background': 'rgba(255,255,255,0.8)',
            'z-index': '9999'
        })
        .html('<div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>')
        .appendTo('body');
}

// Hide loading spinner
function hideLoading() {
    $('.loading-spinner').remove();
}

// Format currency
function formatCurrency(amount) {
    return '$' + parseFloat(amount).toFixed(2);
}

// Get URL parameters
function getUrlParameter(name) {
    name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
    var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
    var results = regex.exec(location.search);
    return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
}

// Debounce function for search inputs
function debounce(func, wait) {
    var timeout;
    return function() {
        var context = this, args = arguments;
        var later = function() {
            timeout = null;
            func.apply(context, args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}