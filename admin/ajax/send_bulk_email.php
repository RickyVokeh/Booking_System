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

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['csrf_token']) || !verifyCSRFToken($input['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit();
}

$type = $input['type'] ?? '';
$recipients = $input['recipients'] ?? [];
$subject = $input['subject'] ?? '';
$message = $input['message'] ?? '';
$saveTemplate = $input['save_template'] ?? false;
$templateName = $input['template_name'] ?? '';

if (!$subject || !$message) {
    echo json_encode(['success' => false, 'message' => 'Subject and message are required']);
    exit();
}

$mailer = new Mailer();
$sent = 0;
$failed = 0;

if ($type === 'single' || $type === 'multiple') {
    foreach ($recipients as $email) {
        if ($mailer->sendCustomEmail($email, '', $subject, $message)) {
            $sent++;
        } else {
            $failed++;
        }
    }
} elseif ($type === 'all') {
    // Get all customers from bookings
    $db = DatabaseConnection::getInstance()->getConnection();
    $stmt = $db->query("SELECT DISTINCT customer_email, customer_name FROM bookings");
    $allRecipients = $stmt->fetchAll();
    
    foreach ($allRecipients as $recipient) {
        if ($mailer->sendCustomEmail($recipient['customer_email'], $recipient['customer_name'], $subject, $message)) {
            $sent++;
        } else {
            $failed++;
        }
    }
}

// Save as template if requested
if ($saveTemplate && $templateName) {
    $db = DatabaseConnection::getInstance()->getConnection();
    $stmt = $db->prepare("
        INSERT INTO email_templates (name, subject, message, created_by) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$templateName, $subject, $message, $_SESSION['admin_id']]);
}

require_once __DIR__ . '/../includes/admin_functions.php';
logAdminAction($_SESSION['admin_id'], 'bulk_email', "Sent $sent emails, $failed failed");

echo json_encode([
    'success' => true,
    'sent' => $sent,
    'failed' => $failed,
    'message' => "Emails sent: $sent, Failed: $failed"
]);