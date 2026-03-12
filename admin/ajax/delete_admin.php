<?php
session_start();
require_once __DIR__ . '/../../includes/config/constants.php';
require_once __DIR__ . '/../../includes/classes/User.php';
require_once __DIR__ . '/../../includes/functions/user_functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'super_admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit();
}

$adminId = $_POST['admin_id'] ?? 0;

if (!$adminId) {
    echo json_encode(['success' => false, 'message' => 'Admin ID required']);
    exit();
}

// Prevent deleting yourself
if ($adminId == $_SESSION['admin_id']) {
    echo json_encode(['success' => false, 'message' => 'Cannot delete your own account']);
    exit();
}

$user = new User();
$result = $user->delete($adminId);

if ($result) {
    require_once __DIR__ . '/../includes/admin_functions.php';
    logAdminAction($_SESSION['admin_id'], 'delete_admin', "Deleted admin #$adminId");
    
    echo json_encode(['success' => true, 'message' => 'Admin deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error deleting admin']);
}