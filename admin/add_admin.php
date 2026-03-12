<?php
session_start();
require_once __DIR__ . '/../includes/config/constants.php';
require_once __DIR__ . '/../includes/classes/User.php';
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/admin_functions.php';

// Only super admin can add new admins
if ($_SESSION['admin_role'] !== 'super_admin') {
    $_SESSION['error'] = 'You do not have permission to add admins';
    header('Location: dashboard.php');
    exit();
}

$user = new User();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'admin';
    
    // Validation
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'All fields are required';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (!$user->validatePassword($password)) {
        $error = 'Password must be at least 8 characters and contain letters and numbers';
    } elseif ($user->isUsernameTaken($username)) {
        $error = 'Username already taken';
    } elseif ($user->isEmailTaken($email)) {
        $error = 'Email already registered';
    } else {
        // Create admin
        $adminData = [
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'role' => $role
        ];
        
        if ($user->createUser($adminData)) {
            logAdminAction($_SESSION['admin_id'], 'add_admin', "Added admin: $username");
            $_SESSION['success'] = 'Admin added successfully';
            header('Location: manage_admins.php');
            exit();
        } else {
            $error = 'Error creating admin. Please try again.';
        }
    }
}

$page_title = 'Add Admin - ' . APP_NAME;
include INCLUDES_PATH . '/templates/admin_header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-user-plus"></i> Add New Admin</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="manage_admins.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Admin Information</h5>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="" id="addAdminForm">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username *</label>
                        <input type="text" class="form-control" id="username" name="username" 
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                        <small class="text-muted">Username must be unique</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address *</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-control" id="role" name="role">
                            <option value="admin">Admin</option>
                            <option value="super_admin">Super Admin</option>
                        </select>
                        <small class="text-muted">Super admins can manage other admins</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password *</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <small class="text-muted">
                            Password must be at least 8 characters and contain letters and numbers
                        </small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password *</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="send_welcome_email" name="send_welcome_email" checked>
                            <label class="form-check-label" for="send_welcome_email">
                                Send welcome email with login instructions
                            </label>
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Admin
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Password Strength Meter -->
        <div class="card mt-3">
            <div class="card-body">
                <h6>Password Requirements:</h6>
                <ul class="list-unstyled mb-0">
                    <li id="req-length" class="text-muted">
                        <i class="fas fa-circle"></i> At least 8 characters
                    </li>
                    <li id="req-letter" class="text-muted">
                        <i class="fas fa-circle"></i> Contains at least one letter
                    </li>
                    <li id="req-number" class="text-muted">
                        <i class="fas fa-circle"></i> Contains at least one number
                    </li>
                    <li id="req-match" class="text-muted">
                        <i class="fas fa-circle"></i> Passwords match
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#password, #confirm_password').on('keyup', function() {
        checkPasswordStrength();
    });
});

function checkPasswordStrength() {
    var password = $('#password').val();
    var confirm = $('#confirm_password').val();
    
    // Check length
    if (password.length >= 8) {
        $('#req-length').removeClass('text-muted').addClass('text-success');
        $('#req-length i').removeClass('fa-circle').addClass('fa-check-circle');
    } else {
        $('#req-length').removeClass('text-success').addClass('text-muted');
        $('#req-length i').removeClass('fa-check-circle').addClass('fa-circle');
    }
    
    // Check for letter
    if (/[a-zA-Z]/.test(password)) {
        $('#req-letter').removeClass('text-muted').addClass('text-success');
        $('#req-letter i').removeClass('fa-circle').addClass('fa-check-circle');
    } else {
        $('#req-letter').removeClass('text-success').addClass('text-muted');
        $('#req-letter i').removeClass('fa-check-circle').addClass('fa-circle');
    }
    
    // Check for number
    if (/[0-9]/.test(password)) {
        $('#req-number').removeClass('text-muted').addClass('text-success');
        $('#req-number i').removeClass('fa-circle').addClass('fa-check-circle');
    } else {
        $('#req-number').removeClass('text-success').addClass('text-muted');
        $('#req-number i').removeClass('fa-check-circle').addClass('fa-circle');
    }
    
    // Check match
    if (password && confirm && password === confirm) {
        $('#req-match').removeClass('text-muted').addClass('text-success');
        $('#req-match i').removeClass('fa-circle').addClass('fa-check-circle');
    } else {
        $('#req-match').removeClass('text-success').addClass('text-muted');
        $('#req-match i').removeClass('fa-check-circle').addClass('fa-circle');
    }
}
</script>

<?php include INCLUDES_PATH . '/templates/footer.php'; ?>