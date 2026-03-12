<?php
session_start();
require_once __DIR__ . '/../../includes/config/constants.php';
require_once __DIR__ . '/../../includes/functions/user_functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit();
}

$emailId = $_POST['email_id'] ?? 0;

if (!$emailId) {
    echo json_encode(['success' => false, 'message' => 'Email ID required']);
    exit();
}

$db = DatabaseConnection::getInstance()->getConnection();
$stmt = $db->prepare("DELETE FROM email_logs WHERE id = ?");
$result = $stmt->execute([$emailId]);

if ($result) {
    require_once __DIR__ . '/../includes/admin_functions.php';
    logAdminAction($_SESSION['admin_id'], 'delete_email', "Deleted email #$emailId");
    
    echo json_encode(['success' => true, 'message' => 'Email deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error deleting email']);
}