<?php
function getAdminStats() {
    $db = DatabaseCon::getInstance()->getConnection();
    
    $stats = [];
    
    // Total bookings
    $stmt = $db->query("SELECT COUNT(*) as total FROM bookings");
    $stats['total_bookings'] = $stmt->fetch()['total'];
    
    // Today's bookings
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM bookings WHERE booking_date = CURDATE()");
    $stmt->execute();
    $stats['today_bookings'] = $stmt->fetch()['total'];
    
    // Pending bookings
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM bookings WHERE status = 'pending'");
    $stmt->execute();
    $stats['pending_bookings'] = $stmt->fetch()['total'];
    
    // Total admins
    $stmt = $db->query("SELECT COUNT(*) as total FROM admins");
    $stats['total_admins'] = $stmt->fetch()['total'];
    
    // Recent bookings (last 7 days)
    $stmt = $db->prepare("
        SELECT COUNT(*) as total 
        FROM bookings 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ");
    $stmt->execute();
    $stats['weekly_bookings'] = $stmt->fetch()['total'];
    
    // Revenue estimate (assuming average $50 per booking)
    $stats['estimated_revenue'] = $stats['total_bookings'] * 50;
    
    return $stats;
}

function getRecentActivities($limit = 10) {
    $db = DatabaseCon::getInstance()->getConnection();
    
    $stmt = $db->prepare("
        (SELECT 'booking' as type, id, customer_name as title, created_at 
         FROM bookings 
         ORDER BY created_at DESC 
         LIMIT ?)
        UNION ALL
        (SELECT 'admin' as type, id, username as title, created_at 
         FROM admins 
         ORDER BY created_at DESC 
         LIMIT ?)
        ORDER BY created_at DESC 
        LIMIT ?
    ");
    $stmt->execute([$limit, $limit, $limit]);
    
    return $stmt->fetchAll();
}

function getBookingChartData($days = 30) {
    $db = DatabaseCon::getInstance()->getConnection();
    
    $stmt = $db->prepare("
        SELECT 
            DATE(booking_date) as date,
            COUNT(*) as total
        FROM bookings 
        WHERE booking_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
        GROUP BY DATE(booking_date)
        ORDER BY date
    ");
    $stmt->execute([$days]);
    
    $data = $stmt->fetchAll();
    
    $labels = [];
    $values = [];
    
    foreach ($data as $row) {
        $labels[] = date('M d', strtotime($row['date']));
        $values[] = $row['total'];
    }
    
    return [
        'labels' => $labels,
        'values' => $values
    ];
}

function logAdminAction($admin_id, $action, $details = '') {
    $db = DatabaseCon::getInstance()->getConnection();
    
    $stmt = $db->prepare("
        INSERT INTO admin_logs (admin_id, action, details, ip_address, user_agent) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $admin_id,
        $action,
        $details,
        $_SERVER['REMOTE_ADDR'] ?? null,
        $_SERVER['HTTP_USER_AGENT'] ?? null
    ]);
}