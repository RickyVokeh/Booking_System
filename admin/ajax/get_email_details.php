<?php
session_start();
require_once __DIR__ . '/../../includes/config/constants.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$emailId = $_GET['email_id'] ?? 0;

if (!$emailId) {
    echo json_encode(['success' => false, 'message' => 'Email ID required']);
    exit();
}

$db = DatabaseCon::getInstance()->getConnection();
$stmt = $db->prepare("
    SELECT el.*, a.username as sent_by_name 
    FROM email_logs el 
    LEFT JOIN admins a ON el.sent_by = a.id 
    WHERE el.id = ?
");
$stmt->execute([$emailId]);
$email = $stmt->fetch();

if ($email) {
    echo json_encode(['success' => true, 'email' => $email]);
} else {
    echo json_encode(['success' => false, 'message' => 'Email not found']);
}