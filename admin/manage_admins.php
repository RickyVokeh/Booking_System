<?php
session_start();
require_once __DIR__ . '/../includes/config/constants.php';
require_once __DIR__ . '/../includes/classes/User.php';
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/admin_functions.php';

// Only super admin can manage admins
if ($_SESSION['admin_role'] !== 'super_admin') {
    $_SESSION['error'] = 'You do not have permission to manage admins';
    header('Location: dashboard.php');
    exit();
}

$user = new User();
$admins = $user->getAllAdmins();

$page_title = 'Manage Admins - ' . APP_NAME;
include INCLUDES_PATH . '/templates/admin_header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-users-cog"></i> Manage Admins</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="add_admin.php" class="btn btn-primary">
            <i class="fas fa-user-plus"></i> Add New Admin
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="adminsTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Last Login</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($admins as $admin): ?>
                    <tr>
                        <td>#<?php echo $admin['id']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($admin['username']); ?></strong>
                            <?php if ($admin['id'] == $_SESSION['admin_id']): ?>
                                <span class="badge bg-info">You</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($admin['email']); ?></td>
                        <td>
                            <span class="badge bg-<?php echo $admin['role'] == 'super_admin' ? 'danger' : 'primary'; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $admin['role'])); ?>
                            </span>
                        </td>
                        <td>
                            <?php echo $admin['last_login'] ? date('M j, Y g:i A', strtotime($admin['last_login'])) : 'Never'; ?>
                        </td>
                        <td><?php echo date('M j, Y', strtotime($admin['created_at'])); ?></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-warning" 
                                        onclick="editAdmin(<?php echo $admin['id']; ?>)"
                                        <?php echo $admin['id'] == $_SESSION['admin_id'] ? 'disabled' : ''; ?>>
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-danger" 
                                        onclick="deleteAdmin(<?php echo $admin['id']; ?>, '<?php echo $admin['username']; ?>')"
                                        <?php echo $admin['id'] == $_SESSION['admin_id'] ? 'disabled' : ''; ?>>
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

<!-- Edit Admin Modal -->
<div class="modal fade" id="editAdminModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Admin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editAdminForm">
                    <input type="hidden" name="admin_id" id="edit_admin_id">
                    
                    <div class="mb-3">
                        <label for="edit_username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="edit_username" name="username" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="edit_email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_role" class="form-label">Role</label>
                        <select class="form-control" id="edit_role" name="role">
                            <option value="admin">Admin</option>
                            <option value="super_admin">Super Admin</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_password" class="form-label">New Password (leave blank to keep current)</label>
                        <input type="password" class="form-control" id="edit_password" name="password">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveAdminChanges()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#adminsTable').DataTable({
        pageLength: 25,
        order: [[0, 'desc']],
        language: {
            search: "Search admins:",
            lengthMenu: "Show _MENU_ admins per page"
        }
    });
});

function editAdmin(id) {
    $.ajax({
        url: 'ajax/get_admin.php',
        method: 'GET',
        data: { admin_id: id },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#edit_admin_id').val(response.admin.id);
                $('#edit_username').val(response.admin.username);
                $('#edit_email').val(response.admin.email);
                $('#edit_role').val(response.admin.role);
                $('#editAdminModal').modal('show');
            } else {
                showNotification('Error loading admin data', 'error');
            }
        }
    });
}

function saveAdminChanges() {
    var formData = $('#editAdminForm').serialize();
    formData += '&csrf_token=<?php echo generateCSRFToken(); ?>';
    
    $.ajax({
        url: 'ajax/update_admin.php',
        method: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#editAdminModal').modal('hide');
                showNotification('Admin updated successfully', 'success');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                showNotification('Error: ' + response.message, 'error');
            }
        }
    });
}

function deleteAdmin(id, username) {
    if (confirm('Are you sure you want to delete admin: ' + username + '?')) {
        $.ajax({
            url: 'ajax/delete_admin.php',
            method: 'POST',
            data: {
                admin_id: id,
                csrf_token: '<?php echo generateCSRFToken(); ?>'
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showNotification('Admin deleted successfully', 'success');
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
</script>

<?php include INCLUDES_PATH . '/templates/footer.php'; ?>