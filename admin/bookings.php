<?php
session_start();
require_once __DIR__ . '/../includes/config/constants.php';
require_once __DIR__ . '/../includes/classes/Booking.php';
require_once __DIR__ . '/includes/auth_check.php';

$booking = new Booking();
$bookings = $booking->getAll('booking_date DESC, booking_time DESC');

$page_title = 'Manage Bookings - ' . APP_NAME;
include INCLUDES_PATH . '/templates/admin_header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-calendar-alt"></i> Manage Bookings</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm btn-outline-primary me-2" onclick="exportBookings()">
            <i class="fas fa-download"></i> Export
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.location.reload()">
            <i class="fas fa-sync-alt"></i> Refresh
        </button>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <label class="form-label">Date Range</label>
                <input type="text" class="form-control" id="dateRangePicker">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select class="form-control" id="statusFilter">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="cancelled">Cancelled</option>
                    <option value="completed">Completed</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Guests</label>
                <select class="form-control" id="guestsFilter">
                    <option value="">Any</option>
                    <?php for ($i = 1; $i <= 10; $i++): ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?> <?php echo $i > 1 ? 'Guests' : 'Guest'; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" class="form-control" id="searchInput" placeholder="Name, email, phone...">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-primary w-100" onclick="applyFilters()">
                    <i class="fas fa-filter"></i> Apply Filters
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Actions -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="selectAll">
                    <label class="form-check-label" for="selectAll">Select All</label>
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-control" id="bulkAction">
                    <option value="">Bulk Actions</option>
                    <option value="confirm">Confirm Selected</option>
                    <option value="cancel">Cancel Selected</option>
                    <option value="delete">Delete Selected</option>
                    <option value="email">Send Email</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary" id="applyBulkAction">
                    <i class="fas fa-check"></i> Apply
                </button>
            </div>
            <div class="col-md-5 text-end">
                <span id="selectedCount" class="text-muted">0 items selected</span>
            </div>
        </div>
    </div>
</div>

<!-- Bookings Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="bookingsTable">
                <thead>
                    <tr>
                        <th width="30"></th>
                        <th>Booking #</th>
                        <th>Customer</th>
                        <th>Contact</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Guests</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking): ?>
                    <tr id="booking-<?php echo $booking['id']; ?>">
                        <td>
                            <input type="checkbox" class="booking-checkbox" value="<?php echo $booking['id']; ?>">
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($booking['booking_number']); ?></strong>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($booking['customer_name']); ?></strong>
                        </td>
                        <td>
                            <small>
                                <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($booking['customer_email']); ?><br>
                                <i class="fas fa-phone"></i> <?php echo htmlspecialchars($booking['customer_phone']); ?>
                            </small>
                        </td>
                        <td><?php echo date('M j, Y', strtotime($booking['booking_date'])); ?></td>
                        <td><?php echo date('g:i A', strtotime($booking['booking_time'])); ?></td>
                        <td><?php echo $booking['guests']; ?></td>
                        <td>
                            <select class="form-select form-select-sm update-status" 
                                    data-booking-id="<?php echo $booking['id']; ?>"
                                    style="width: 120px;">
                                <option value="pending" <?php echo $booking['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="confirmed" <?php echo $booking['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                <option value="cancelled" <?php echo $booking['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                <option value="completed" <?php echo $booking['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                            </select>
                        </td>
                        <td><?php echo date('M j, Y', strtotime($booking['created_at'])); ?></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="view_booking.php?id=<?php echo $booking['id']; ?>" 
                                   class="btn btn-info" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="edit_booking.php?id=<?php echo $booking['id']; ?>" 
                                   class="btn btn-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-danger" 
                                        onclick="deleteBooking(<?php echo $booking['id']; ?>)"
                                        title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize date range picker
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
    });
    
    // Select all checkbox
    $('#selectAll').change(function() {
        $('.booking-checkbox').prop('checked', $(this).prop('checked'));
        updateSelectedCount();
    });
    
    // Individual checkbox change
    $(document).on('change', '.booking-checkbox', function() {
        updateSelectedCount();
    });
    
    // Bulk action apply
    $('#applyBulkAction').click(function() {
        var action = $('#bulkAction').val();
        var selectedIds = getSelectedIds();
        
        if (selectedIds.length === 0) {
            alert('Please select at least one booking');
            return;
        }
        
        if (!action) {
            alert('Please select an action');
            return;
        }
        
        performBulkAction(action, selectedIds);
    });
    
    // Status update
    $('.update-status').change(function() {
        var bookingId = $(this).data('booking-id');
        var newStatus = $(this).val();
        updateBookingStatus(bookingId, newStatus);
    });
});

function updateSelectedCount() {
    var count = $('.booking-checkbox:checked').length;
    $('#selectedCount').text(count + ' item' + (count !== 1 ? 's' : '') + ' selected');
}

function getSelectedIds() {
    var ids = [];
    $('.booking-checkbox:checked').each(function() {
        ids.push($(this).val());
    });
    return ids;
}

function applyFilters() {
    var dateRange = $('#dateRangePicker').val();
    var status = $('#statusFilter').val();
    var guests = $('#guestsFilter').val();
    var search = $('#searchInput').val();
    
    // Implement filter logic or AJAX call
    window.location.href = 'bookings.php?date=' + encodeURIComponent(dateRange) + 
                          '&status=' + status + '&guests=' + guests + '&search=' + encodeURIComponent(search);
}

function exportBookings() {
    var format = prompt('Enter export format (csv/excel/pdf):', 'csv');
    if (format) {
        window.location.href = 'export_bookings.php?format=' + format;
    }
}

function deleteBooking(id) {
    if (confirm('Are you sure you want to delete this booking?')) {
        $.ajax({
            url: 'ajax/delete_booking.php',
            method: 'POST',
            data: {
                booking_id: id,
                csrf_token: '<?php echo generateCSRFToken(); ?>'
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#booking-' + id).fadeOut();
                    showNotification('Booking deleted successfully', 'success');
                } else {
                    showNotification('Error: ' + response.message, 'error');
                }
            }
        });
    }
}

function performBulkAction(action, ids) {
    if (!confirm('Are you sure you want to ' + action + ' the selected bookings?')) {
        return;
    }
    
    $.ajax({
        url: 'ajax/bulk_booking_action.php',
        method: 'POST',
        data: {
            action: action,
            booking_ids: ids,
            csrf_token: '<?php echo generateCSRFToken(); ?>'
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showNotification('Bulk action completed successfully', 'success');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                showNotification('Error: ' + response.message, 'error');
            }
        }
    });
}
</script>

<?php include INCLUDES_PATH . '/templates/footer.php'; ?>