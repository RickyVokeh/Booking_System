<?php
// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ' . APP_URL . '/admin/login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - <?php echo APP_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    
    <!-- Custom Admin CSS -->
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/admin.css">
</head>
<body>
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div class="bg-dark text-white" id="sidebar-wrapper" style="min-width: 250px;">
            <div class="sidebar-heading text-center py-4 primary-text fs-4 fw-bold text-uppercase border-bottom">
                <i class="fas fa-utensils me-2"></i>Admin Panel
            </div>
            <div class="list-group list-group-flush my-3">
                <a href="<?php echo APP_URL; ?>/admin/dashboard.php" class="list-group-item list-group-item-action bg-dark text-white">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                </a>
                <a href="<?php echo APP_URL; ?>/admin/bookings.php" class="list-group-item list-group-item-action bg-dark text-white">
                    <i class="fas fa-calendar-check me-2"></i>Bookings
                </a>
                <a href="<?php echo APP_URL; ?>/admin/add_admin.php" class="list-group-item list-group-item-action bg-dark text-white">
                    <i class="fas fa-user-plus me-2"></i>Add Admin
                </a>
                <a href="<?php echo APP_URL; ?>/admin/manage_admins.php" class="list-group-item list-group-item-action bg-dark text-white">
                    <i class="fas fa-users-cog me-2"></i>Manage Admins
                </a>
                <a href="<?php echo APP_URL; ?>/admin/send_email.php" class="list-group-item list-group-item-action bg-dark text-white">
                    <i class="fas fa-envelope me-2"></i>Send Email
                </a>
                <a href="<?php echo APP_URL; ?>/admin/email_history.php" class="list-group-item list-group-item-action bg-dark text-white">
                    <i class="fas fa-history me-2"></i>Email History
                </a>
                <a href="<?php echo APP_URL; ?>/admin/logout.php" class="list-group-item list-group-item-action bg-dark text-white">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </div>
        </div>
        
        <!-- Page Content -->
        <div id="page-content-wrapper" class="w-100">
            <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
                <div class="container-fluid">
                    <button class="btn btn-primary" id="menu-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="dropdown ms-auto">
                        <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-2"></i><?php echo $_SESSION['admin_username']; ?>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/admin/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                </div>
            </nav>
            
            <div class="container-fluid px-4">
                <?php displayFlashMessage(); ?>