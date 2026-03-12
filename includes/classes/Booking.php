<?php
require_once __DIR__ . '/Db.php';

class Booking extends Database {
    protected $table = 'bookings';
    
    public function __construct() {
        parent::__construct();
    }
    
    public function generateBookingNumber() {
        $prefix = 'BK';
        $yearMonth = date('Ym');
        
        // Get the last booking number for this month
        $stmt = $this->db->prepare("
            SELECT booking_number FROM {$this->table} 
            WHERE booking_number LIKE ? 
            ORDER BY id DESC LIMIT 1
        ");
        $stmt->execute([$prefix . $yearMonth . '%']);
        $last = $stmt->fetch();
        
        if ($last) {
            $lastNumber = (int)substr($last['booking_number'], -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        
        return $prefix . $yearMonth . $newNumber;
    }
    
    public function createBooking($data) {
        // Generate unique booking number
        $data['booking_number'] = $this->generateBookingNumber();
        
        // Add IP and user agent
        $data['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? null;
        $data['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        return $this->create($data);
    }
    
    public function getBookingsByDate($date) {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table} 
            WHERE booking_date = ? 
            ORDER BY booking_time ASC
        ");
        $stmt->execute([$date]);
        return $stmt->fetchAll();
    }
    
    public function getBookingsByEmail($email) {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table} 
            WHERE customer_email = ? 
            ORDER BY booking_date DESC, booking_time DESC
        ");
        $stmt->execute([$email]);
        return $stmt->fetchAll();
    }
    
    public function getUpcomingBookings($limit = 10) {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table} 
            WHERE booking_date >= CURDATE() 
            AND status IN ('pending', 'confirmed')
            ORDER BY booking_date ASC, booking_time ASC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    public function updateStatus($id, $status) {
        return $this->update($id, ['status' => $status]);
    }
    
    public function checkAvailability($date, $time, $guests) {
        // Check if table is available for given time
        // This is a basic check - you can enhance based on your needs
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total FROM {$this->table} 
            WHERE booking_date = ? 
            AND booking_time = ? 
            AND status IN ('pending', 'confirmed')
        ");
        $stmt->execute([$date, $time]);
        $bookedCount = $stmt->fetch()['total'];
        
        // Assume 10 tables available (you can make this dynamic)
        $totalTables = 10;
        
        return [
            'available' => $bookedCount < $totalTables,
            'booked_tables' => $bookedCount,
            'available_tables' => $totalTables - $bookedCount
        ];
    }
    
    public function getBookingStats($period = 'week') {
        $interval = $period === 'week' ? 'INTERVAL 7 DAY' : 'INTERVAL 30 DAY';
        
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_bookings,
                SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                COUNT(DISTINCT DATE(booking_date)) as total_days,
                AVG(guests) as avg_guests
            FROM {$this->table} 
            WHERE created_at >= DATE_SUB(NOW(), {$interval})
        ");
        $stmt->execute();
        return $stmt->fetch();
    }
}