<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php';

class Mailer {
    private $mail;
    private $db; // This will hold the PDO connection
    
    public function __construct() {
        $this->mail = new PHPMailer(true);
        $this->setupMailer();
        
        // For logging emails - get PDO connection directly
        require_once __DIR__ . '/Db.php';
        $this->db = DatabaseCon::getInstance()->getConnection(); // FIXED: Get PDO connection directly
    }
    
    private function setupMailer() {
        try {
            // Server settings
            $this->mail->SMTPDebug = SMTP::DEBUG_OFF;
            $this->mail->isSMTP();
            $this->mail->Host = $_ENV['SMTP_HOST'];
            $this->mail->SMTPAuth = true;
            $this->mail->Username = $_ENV['SMTP_USER'];
            $this->mail->Password = $_ENV['SMTP_PASS'];
            $this->mail->SMTPSecure = $_ENV['SMTP_ENCRYPTION'];
            $this->mail->Port = $_ENV['SMTP_PORT'];
            
            // Default sender
            $fromEmail = $_ENV['SMTP_USER'] ?? 'noreply@reservations.com';
            $fromName = defined('APP_NAME') ? APP_NAME : 'Reservation System';
            $this->mail->setFrom($fromEmail, $fromName);
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
            $this->mail->Subject = 'Booking Confirmation - ' . (defined('APP_NAME') ? APP_NAME : 'Reservation System');
            
            // Load email template - check if file exists
            $templateFile = ROOT_PATH . '/emails/booking_confirmation.php';
            if (file_exists($templateFile)) {
                ob_start();
                include $templateFile;
                $this->mail->Body = ob_get_clean();
            } else {
                // Fallback template
                $this->mail->Body = $this->buildDefaultConfirmationEmail($bookingData);
            }
            
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
            
            // Set sent_at based on status
            $data['sent_at'] = $data['status'] === 'sent' ? date('Y-m-d H:i:s') : null;
            
            // Prepare the data with proper field mapping
            $emailData = [
                ':recipient_email' => $data['recipient_email'] ?? null,
                ':recipient_name' => $data['recipient_name'] ?? null,
                ':subject' => $data['subject'] ?? null,
                ':message' => $data['message'] ?? null,
                ':email_type' => $data['email_type'] ?? 'general',
                ':status' => $data['status'] ?? 'sent',
                ':sent_by' => $data['sent_by'] ?? null,
                ':error_message' => $data['error_message'] ?? null,
                ':sent_at' => $data['sent_at']
            ];
            
            $sql = "INSERT INTO email_logs 
                    (recipient_email, recipient_name, subject, message, email_type, status, sent_by, error_message, sent_at) 
                    VALUES 
                    (:recipient_email, :recipient_name, :subject, :message, :email_type, :status, :sent_by, :error_message, :sent_at)";
            
            // FIXED: Use $this->db directly (it's already the PDO connection)
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($emailData);
            
            if (!$result) {
                error_log("Failed to insert email log: " . print_r($stmt->errorInfo(), true));
            }
            
            return $result;
            
        } catch (PDOException $e) {
            error_log("PDO Exception in logEmail: " . $e->getMessage());
            error_log("SQL State: " . $e->errorInfo[0] ?? 'N/A');
            error_log("Error Code: " . $e->errorInfo[1] ?? 'N/A');
            error_log("Error Message: " . $e->errorInfo[2] ?? 'N/A');
            return false;
        } catch (Exception $e) {
            error_log("Failed to log email: " . $e->getMessage());
            return false;
        }
    }
    
    public function sendAdminNotification($bookingData) {
        try {
            $this->mail->clearAddresses();
            $adminEmail = $_ENV['ADMIN_EMAIL'] ?? 'admin@reservations.com';
            $this->mail->addAddress($adminEmail, 'Admin');
            
            $bookingNumber = $bookingData['booking_number'] ?? $bookingData['id'] ?? 'New';
            $this->mail->Subject = 'New Booking Received - ' . $bookingNumber;
            
            // Load email template if exists
            $templateFile = ROOT_PATH . '/emails/admin_notification.php';
            if (file_exists($templateFile)) {
                ob_start();
                include $templateFile;
                $this->mail->Body = ob_get_clean();
            } else {
                // Fallback template
                $this->mail->Body = $this->buildDefaultAdminNotification($bookingData);
            }
            
            $this->mail->send();
            return true;
            
        } catch (Exception $e) {
            error_log("Admin notification failed: " . $e->getMessage());
            return false;
        }
    }
}