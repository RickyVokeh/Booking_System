<?php
function sendBookingEmail($bookingData) {
    $mailer = new Mailer();
    return $mailer->sendBookingConfirmation($bookingData);
}

function sendAdminNewBookingEmail($bookingData) {
    $mailer = new Mailer();
    return $mailer->sendAdminNotification($bookingData);
}

function getEmailTemplate($template, $data = []) {
    ob_start();
    extract($data);
    include ROOT_PATH . "/emails/{$template}.php";
    return ob_get_clean();
}

function logEmailDelivery($emailData) {
    // Already handled in Mailer class
}

function getEmailHistory($limit = 50) {
    $db = DatabaseConnection::getInstance()->getConnection();
    $stmt = $db->prepare("
        SELECT el.*, a.username as sent_by_name 
        FROM email_logs el 
        LEFT JOIN admins a ON el.sent_by = a.id 
        ORDER BY el.created_at DESC 
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}