<?php
session_start();
require_once __DIR__ . '/../../includes/config/constants.php';
require_once __DIR__ . '/../../includes/classes/Mailer.php';
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
$stmt = $db->prepare("SELECT * FROM email_logs WHERE id = ?");
$stmt->execute([$emailId]);
$email = $stmt->fetch();

if (!$email) {
    echo json_encode(['success' => false, 'message' => 'Email not found']);
    exit();
}

$mailer = new Mailer();
$result = $mailer->sendCustomEmail(
    $email['recipient_email'],
    $email['recipient_name'],
    $email['subject'],
    $email['message']
);

if ($result) {
    require_once __DIR__ . '/../includes/admin_functions.php';
    logAdminAction($_SESSION['admin_id'], 'resend_email', "Resent email #$emailId");
    
    echo json_encode(['success' => true, 'message' => 'Email resent successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error resending email']);
}