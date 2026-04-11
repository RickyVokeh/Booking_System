<?php
session_start();
require_once __DIR__ . '/../includes/config/constants.php';
require_once __DIR__ . '/../includes/classes/Db.php';
require_once __DIR__ . '/includes/auth_check.php';

$db = DatabaseCon::getInstance()->getConnection();

// Get email history with pagination
$page = $_GET['page'] ?? 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$stmt = $db->prepare("
    SELECT el.*, a.username as sent_by_name 
    FROM email_logs el 
    LEFT JOIN admins a ON el.sent_by = a.id 
    ORDER BY el.created_at DESC 
    LIMIT ? OFFSET ?
");
$stmt->execute([$limit, $offset]);
$emails = $stmt->fetchAll();

// Get total count for pagination
$totalStmt = $db->query("SELECT COUNT(*) as total FROM email_logs");
$total = $totalStmt->fetch()['total'];
$totalPages = ceil($total / $limit);

$page_title = 'Email History - ' . APP_NAME;
include INCLUDES_PATH . '/templates/admin_header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-history"></i> Email History</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="send_email.php" class="btn btn-primary me-2">
            <i class="fas fa-envelope"></i> Compose New
        </a>
        <button class="btn btn-secondary" onclick="exportHistory()">
            <i class="fas fa-download"></i> Export
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
                    <option value="">All</option>
                    <option value="sent">Sent</option>
                    <option value="failed">Failed</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" class="form-control" id="searchInput" placeholder="Email, subject...">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-primary w-100" onclick="applyFilters()">
                    <i class="fas fa-filter"></i> Filter
                </button>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-secondary w-100" onclick="clearFilters()">
                    <i class="fas fa-times"></i> Clear
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Email History Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="emailHistoryTable">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Recipient</th>
                        <th>Subject</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Sent By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($emails as $email): ?>
                    <tr class="email-row <?php echo $email['status']; ?>">
                        <td>
                            <?php echo date('M j, Y g:i A', strtotime($email['created_at'])); ?>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($email['recipient_email']); ?></strong>
                            <?php if ($email['recipient_name']): ?>
                                <br><small><?php echo htmlspecialchars($email['recipient_name']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="#" onclick="viewEmail(<?php echo $email['id']; ?>)">
                                <?php echo htmlspecialchars($email['subject']); ?>
                            </a>
                        </td>
                        <td>
                            <span class="badge bg-info">
                                <?php echo ucfirst(str_replace('_', ' ', $email['email_type'])); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-<?php echo $email['status'] == 'sent' ? 'success' : 'danger'; ?>">
                                <?php echo ucfirst($email['status']); ?>
                            </span>
                        </td>
                        <td>
                            <?php echo $email['sent_by_name'] ?? 'System'; ?>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-info" onclick="viewEmail(<?php echo $email['id']; ?>)">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-primary" onclick="resendEmail(<?php echo $email['id']; ?>)">
                                    <i class="fas fa-redo"></i>
                                </button>
                                <button class="btn btn-danger" onclick="deleteEmail(<?php echo $email['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <nav aria-label="Email history pagination" class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                </li>
                
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                
                <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<!-- View Email Modal -->
<div class="modal fade" id="viewEmailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Email Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="emailDetails"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="resendFromModal()">Resend</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
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
});

window.viewEmail =function(id)  {
    $.ajax({
        url: 'ajax/get_email_details.php',
        method: 'GET',
        data: { email_id: id },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                var email = response.email;
                var html = '<div class="email-details">' +
                          '<h6>To: ' + escapeHtml(email.recipient_email) + '</h6>' +
                          '<h6>Subject: ' + escapeHtml(email.subject) + '</h6>' +
                          '<h6>Date: ' + email.created_at + '</h6>' +
                          '<h6>Status: <span class="badge bg-' + 
                          (email.status === 'sent' ? 'success' : 'danger') + '">' + 
                          email.status + '</span></h6>' +
                          '<hr>' +
                          '<div class="email-content p-3 bg-light">' + 
                          nl2br(escapeHtml(email.message)) + 
                          '</div>';
                          
                if (email.error_message) {
                    html += '<hr><div class="alert alert-danger">Error: ' + 
                           escapeHtml(email.error_message) + '</div>';
                }
                
                html += '</div>';
                
                $('#emailDetails').html(html);
                $('#viewEmailModal').modal('show');
                
                // Store current email ID for resend
                $('#viewEmailModal').data('email-id', id);
            }
        }
    });
}

window.resendEmail = function(id)  {
    if (confirm('Are you sure you want to resend this email?')) {
        $.ajax({
            url: 'ajax/resend_email.php',
            method: 'POST',
            data: {
                email_id: id,
                csrf_token: '<?php echo generateCSRFToken(); ?>'
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showNotification('Email resent successfully', 'success');
                } else {
                    showNotification('Error: ' + response.message, 'error');
                }
            }
        });
    }
}

function resendFromModal() {
    var id = $('#viewEmailModal').data('email-id');
    $('#viewEmailModal').modal('hide');
    resendEmail(id);
}

function deleteEmail(id) {
    if (confirm('Are you sure you want to delete this email record?')) {
        $.ajax({
            url: 'ajax/delete_email.php',
            method: 'POST',
            data: {
                email_id: id,
                csrf_token: '<?php echo generateCSRFToken(); ?>'
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showNotification('Email deleted successfully', 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showNotification('Error: ' + response.message, 'error');
                }
            }
        });
    }
}

function exportHistory() {
    var dateRange = $('#dateRangePicker').val();
    var status = $('#statusFilter').val();
    var search = $('#searchInput').val();
    
    window.location.href = 'export_email_history.php?date=' + encodeURIComponent(dateRange) + 
                          '&status=' + status + '&search=' + encodeURIComponent(search);
}

function applyFilters() {
    // Implement filter logic
    var dateRange = $('#dateRangePicker').val();
    var status = $('#statusFilter').val();
    var search = $('#searchInput').val();
    
    // Add filter parameters to URL and reload
    window.location.href = 'email_history.php?date=' + encodeURIComponent(dateRange) + 
                          '&status=' + status + '&search=' + encodeURIComponent(search);
}

function clearFilters() {
    window.location.href = 'email_history.php';
}
</script>

<?php include INCLUDES_PATH . '/templates/footer.php'; ?>