<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php';

class Mailer {
    private $mail;
    private $db;
    
    public function __construct() {
        $this->mail = new PHPMailer(true);
        $this->setupMailer();
        
        // For logging emails
        require_once __DIR__ . '/Db.php';
        $this->db = new Database();
    }
    
    private function setupMailer() {
        try {
            // Server settings
            $this->mail->SMTPDebug = APP_DEBUG ? SMTP::DEBUG_SERVER : SMTP::DEBUG_OFF;
            $this->mail->isSMTP();
            $this->mail->Host = $_ENV['SMTP_HOST'];
            $this->mail->SMTPAuth = true;
            $this->mail->Username = $_ENV['SMTP_USER'];
            $this->mail->Password = $_ENV['SMTP_PASS'];
            $this->mail->SMTPSecure = $_ENV['SMTP_ENCRYPTION'];
            $this->mail->Port = $_ENV['SMTP_PORT'];
            
            // Default sender
            $this->mail->setFrom($_ENV['SMTP_USER'], APP_NAME);
            $this->mail->isHTML(true);
            
        } catch (Exception $e) {
            error_log("Mailer setup error: " . $e->getMessage());
        }
    }
    
    public function sendBookingConfirmation($bookingData) {
        try {
            // Clear recipients
            $this->mail->clearAddresses();
            
            // Recipient
            $this->mail->addAddress($bookingData['customer_email'], $bookingData['customer_name']);
            
            // Content
            $this->mail->Subject = 'Booking Confirmation - ' . APP_NAME;
            
            // Load email template
            ob_start();
            include ROOT_PATH . '/emails/booking_confirmation.php';
            $this->mail->Body = ob_get_clean();
            
            $this->mail->AltBody = strip_tags(str_replace(['<br>', '</p>'], ["\n", "\n\n"], $this->mail->Body));
            
            $this->mail->send();
            
            // Log successful email
            $this->logEmail([
                'recipient_email' => $bookingData['customer_email'],
                'recipient_name' => $bookingData['customer_name'],
                'subject' => $this->mail->Subject,
                'message' => $this->mail->Body,
                'email_type' => 'booking_confirmation',
                'status' => 'sent'
            ]);
            
            return true;
            
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            
            // Log failed email
            $this->logEmail([
                'recipient_email' => $bookingData['customer_email'],
                'recipient_name' => $bookingData['customer_name'],
                'subject' => 'Booking Confirmation',
                'message' => 'Failed to send email',
                'email_type' => 'booking_confirmation',
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);
            
            return false;
        }
    }
    
    public function sendCustomEmail($to, $name, $subject, $message) {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($to, $name);
            $this->mail->Subject = $subject;
            $this->mail->Body = $message;
            $this->mail->AltBody = strip_tags($message);
            
            $this->mail->send();
            
            $this->logEmail([
                'recipient_email' => $to,
                'recipient_name' => $name,
                'subject' => $subject,
                'message' => $message,
                'email_type' => 'custom',
                'status' => 'sent'
            ]);
            
            return true;
            
        } catch (Exception $e) {
            error_log("Custom email failed: " . $e->getMessage());
            
            $this->logEmail([
                'recipient_email' => $to,
                'recipient_name' => $name,
                'subject' => $subject,
                'message' => $message,
                'email_type' => 'custom',
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);
            
            return false;
        }
    }
    
    private function logEmail($data) {
        try {
            // Add sent_by if admin is logged in
            if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['admin_id'])) {
                $data['sent_by'] = $_SESSION['admin_id'];
            }
            
            $data['sent_at'] = $data['status'] === 'sent' ? date('Y-m-d H:i:s') : null;
            
            $sql = "INSERT INTO email_logs 
                    (recipient_email, recipient_name, subject, message, email_type, status, sent_by, error_message, sent_at) 
                    VALUES 
                    (:recipient_email, :recipient_name, :subject, :message, :email_type, :status, :sent_by, :error_message, :sent_at)";
            
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute($data);
            
        } catch (Exception $e) {
            error_log("Failed to log email: " . $e->getMessage());
        }
    }
    
    public function sendAdminNotification($bookingData) {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($_ENV['ADMIN_EMAIL'], 'Admin');
            
            $this->mail->Subject = 'New Booking Received - ' . $bookingData['booking_number'];
            
            ob_start();
            include ROOT_PATH . '/emails/admin_notification.php';
            $this->mail->Body = ob_get_clean();
            
            $this->mail->send();
            return true;
            
        } catch (Exception $e) {
            error_log("Admin notification failed: " . $e->getMessage());
            return false;
        }
    }
}