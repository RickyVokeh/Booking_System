// Admin Panel JavaScript

$(document).ready(function() {
    // Toggle sidebar
    $('#menu-toggle').click(function(e) {
        e.preventDefault();
        $('#wrapper').toggleClass('toggled');
    });
    
    // Initialize DataTables
    if ($('#bookingsTable').length) {
        $('#bookingsTable').DataTable({
            pageLength: 25,
            order: [[3, 'desc']], // Sort by date column
            language: {
                search: "Search bookings:",
                lengthMenu: "Show _MENU_ bookings per page",
                info: "Showing _START_ to _END_ of _TOTAL_ bookings",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            }
        });
    }
    
    // Date range picker for bookings
    if ($('#dateRangePicker').length) {
        $('#dateRangePicker').daterangepicker({
            opens: 'left',
            startDate: moment().subtract(29, 'days'),
            endDate: moment(),
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        }, function(start, end) {
            $('#dateRangePicker span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
            loadBookingsByDate(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
        });
    }
    
    // Email composition
    $('#sendEmailBtn').click(function() {
        if (validateEmailForm()) {
            sendEmail();
        }
    });
    
    // Status update with AJAX
    $('.update-status').change(function() {
        var bookingId = $(this).data('booking-id');
        var newStatus = $(this).val();
        
        updateBookingStatus(bookingId, newStatus);
    });
    
    // Bulk actions
    $('#selectAll').click(function() {
        $('.booking-checkbox').prop('checked', $(this).prop('checked'));
    });
    
    $('#bulkActionBtn').click(function() {
        var action = $('#bulkAction').val();
        var selectedIds = [];
        
        $('.booking-checkbox:checked').each(function() {
            selectedIds.push($(this).val());
        });
        
        if (selectedIds.length === 0) {
            alert('Please select at least one booking');
            return;
        }
        
        if (action) {
            performBulkAction(action, selectedIds);
        }
    });
    
    // Export functionality
    $('#exportBookings').click(function() {
        exportBookings();
    });
    
    // Chart initialization
    if ($('#bookingsChart').length) {
        loadBookingChart();
    }
});

// Update booking status
function updateBookingStatus(bookingId, status) {
    $.ajax({
        url: 'ajax/update_status.php',
        method: 'POST',
        data: {
            booking_id: bookingId,
            status: status,
            csrf_token: $('meta[name="csrf-token"]').attr('content')
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showNotification('Booking status updated successfully', 'success');
                
                // Update status badge
                var statusBadge = $('#status-' + bookingId);
                statusBadge.removeClass().addClass('badge bg-' + getStatusColor(status)).text(status);
            } else {
                showNotification('Error updating status: ' + response.message, 'error');
            }
        },
        error: function() {
            showNotification('Error updating booking status', 'error');
        }
    });
}

// Load bookings by date range
function loadBookingsByDate(startDate, endDate) {
    showLoading();
    
    $.ajax({
        url: 'ajax/get_bookings_by_date.php',
        method: 'POST',
        data: {
            start_date: startDate,
            end_date: endDate
        },
        dataType: 'json',
        success: function(response) {
            updateBookingsTable(response.bookings);
            hideLoading();
        },
        error: function() {
            hideLoading();
            showNotification('Error loading bookings', 'error');
        }
    });
}

// Update bookings table
function updateBookingsTable(bookings) {
    var tbody = $('#bookingsTable tbody');
    tbody.empty();
    
    if (bookings.length > 0) {
        bookings.forEach(function(booking) {
            var row = '<tr>' +
                '<td><input type="checkbox" class="booking-checkbox" value="' + booking.id + '"></td>' +
                '<td>' + booking.booking_number + '</td>' +
                '<td>' + booking.customer_name + '</td>' +
                '<td>' + formatDate(booking.booking_date) + '</td>' +
                '<td>' + formatTime(booking.booking_time) + '</td>' +
                '<td>' + booking.guests + '</td>' +
                '<td><span class="badge bg-' + getStatusColor(booking.status) + '">' + booking.status + '</span></td>' +
                '<td>' +
                '<a href="view_booking.php?id=' + booking.id + '" class="btn btn-sm btn-info" title="View"><i class="fas fa-eye"></i></a> ' +
                '<a href="edit_booking.php?id=' + booking.id + '" class="btn btn-sm btn-warning" title="Edit"><i class="fas fa-edit"></i></a> ' +
                '<button class="btn btn-sm btn-danger delete-booking" data-id="' + booking.id + '" title="Delete"><i class="fas fa-trash"></i></button>' +
                '</td>' +
                '</tr>';
            tbody.append(row);
        });
    } else {
        tbody.html('<tr><td colspan="8" class="text-center">No bookings found</td></tr>');
    }
}

// Send email
function sendEmail() {
    var formData = {
        recipient: $('#recipient').val(),
        subject: $('#subject').val(),
        message: $('#message').val(),
        csrf_token: $('meta[name="csrf-token"]').attr('content')
    };
    
    showLoading();
    
    $.ajax({
        url: 'ajax/send_email.php',
        method: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            hideLoading();
            if (response.success) {
                showNotification('Email sent successfully!', 'success');
                $('#emailForm')[0].reset();
            } else {
                showNotification('Error sending email: ' + response.message, 'error');
            }
        },
        error: function() {
            hideLoading();
            showNotification('Error sending email', 'error');
        }
    });
}

// Validate email form
function validateEmailForm() {
    var recipient = $('#recipient').val();
    var subject = $('#subject').val();
    var message = $('#message').val();
    
    if (!recipient) {
        showNotification('Please select a recipient', 'warning');
        return false;
    }
    
    if (!subject) {
        showNotification('Please enter a subject', 'warning');
        return false;
    }
    
    if (!message) {
        showNotification('Please enter a message', 'warning');
        return false;
    }
    
    return true;
}

// Perform bulk action
function performBulkAction(action, ids) {
    if (!confirm('Are you sure you want to ' + action + ' the selected bookings?')) {
        return;
    }
    
    showLoading();
    
    $.ajax({
        url: 'ajax/bulk_action.php',
        method: 'POST',
        data: {
            action: action,
            booking_ids: ids,
            csrf_token: $('meta[name="csrf-token"]').attr('content')
        },
        dataType: 'json',
        success: function(response) {
            hideLoading();
            if (response.success) {
                showNotification('Bulk action completed successfully', 'success');
                location.reload();
            } else {
                showNotification('Error: ' + response.message, 'error');
            }
        },
        error: function() {
            hideLoading();
            showNotification('Error performing bulk action', 'error');
        }
    });
}

// Export bookings
function exportBookings() {
    var format = $('#exportFormat').val();
    var startDate = $('#dateRangePicker').data('daterangepicker').startDate.format('YYYY-MM-DD');
    var endDate = $('#dateRangePicker').data('daterangepicker').endDate.format('YYYY-MM-DD');
    
    window.location.href = 'export.php?format=' + format + '&start=' + startDate + '&end=' + endDate;
}

// Load booking chart
function loadBookingChart() {
    $.ajax({
        url: 'ajax/get_chart_data.php',
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            var ctx = document.getElementById('bookingsChart').getContext('2d');
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Bookings',
                        data: data.values,
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 2,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }
    });
}

// Helper functions
function getStatusColor(status) {
    var colors = {
        'pending': 'warning',
        'confirmed': 'success',
        'cancelled': 'danger',
        'completed': 'info'
    };
    return colors[status] || 'secondary';
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function formatTime(timeString) {
    return new Date('1970-01-01T' + timeString).toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });
}

function showNotification(message, type) {
    var alertClass = 'alert-' + (type === 'success' ? 'success' : type === 'error' ? 'danger' : 'warning');
    
    var notification = $('<div>')
        .addClass('alert ' + alertClass + ' alert-dismissible fade show position-fixed')
        .css({
            'top': '20px',
            'right': '20px',
            'z-index': '9999',
            'max-width': '400px'
        })
        .html(message + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>');
    
    $('body').append(notification);
    
    setTimeout(function() {
        notification.alert('close');
    }, 5000);
}