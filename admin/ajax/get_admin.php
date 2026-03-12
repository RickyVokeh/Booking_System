<?php
session_start();
require_once __DIR__ . '/../../includes/config/constants.php';
require_once __DIR__ . '/../../includes/classes/User.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'super_admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$adminId = $_GET['admin_id'] ?? 0;

if (!$adminId) {
    echo json_encode(['success' => false, 'message' => 'Admin ID required']);
    exit();
}

$user = new User();
$admin = $user->getById($adminId);

if ($admin) {
    // Remove sensitive data
    unset($admin['password']);
    echo json_encode(['success' => true, 'admin' => $admin]);
} else {
    echo json_encode(['success' => false, 'message' => 'Admin not found']);
}