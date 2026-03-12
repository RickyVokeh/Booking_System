<?php
session_start();
require_once __DIR__ . '/../../includes/config/constants.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$templateId = $_GET['template_id'] ?? 0;

if (!$templateId) {
    echo json_encode(['success' => false, 'message' => 'Template ID required']);
    exit();
}

$db = DatabaseConnection::getInstance()->getConnection();
$stmt = $db->prepare("SELECT * FROM email_templates WHERE id = ?");
$stmt->execute([$templateId]);
$template = $stmt->fetch();

if ($template) {
    echo json_encode(['success' => true, 'template' => $template]);
} else {
    echo json_encode(['success' => false, 'message' => 'Template not found']);
}