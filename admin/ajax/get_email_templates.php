<?php
session_start();
require_once __DIR__ . '/../../includes/config/constants.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$db = DatabaseConnection::getInstance()->getConnection();

// Check if email_templates table exists
try {
    $stmt = $db->query("SHOW TABLES LIKE 'email_templates'");
    if ($stmt->rowCount() == 0) {
        // Create table if it doesn't exist
        $db->exec("
            CREATE TABLE IF NOT EXISTS email_templates (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(100) NOT NULL,
                subject VARCHAR(255) NOT NULL,
                message TEXT NOT NULL,
                created_by INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (created_by) REFERENCES admins(id) ON DELETE SET NULL
            )
        ");
    }
} catch (PDOException $e) {
    // Table doesn't exist, create it
    $db->exec("
        CREATE TABLE IF NOT EXISTS email_templates (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            subject VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (created_by) REFERENCES admins(id) ON DELETE SET NULL
        )
    ");
}

$stmt = $db->query("SELECT id, name, subject FROM email_templates ORDER BY name");
$templates = $stmt->fetchAll();

echo json_encode(['success' => true, 'templates' => $templates]);