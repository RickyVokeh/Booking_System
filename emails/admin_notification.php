<?php
// Email template for admin notification when new booking is received
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Booking Notification</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #f093fb, #f5576c);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        .header p {
            margin: 10px 0 0;
            opacity: 0.9;
        }
        .content {
            padding: 30px;
        }
        .alert-box {
            background: #fff3cd;
            border: 2px solid #ffc107;
            color: #856404;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
            font-size: 18px;
            font-weight: 600;
        }
        .booking-details {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .booking-details h2 {
            margin-top: 0;
            color: #f5576c;
            font-size: 20px;
            border-bottom: 2px solid #f5576c;
            padding-bottom: 10px;
        }
        .detail-row {
            display: flex;
            margin-bottom: 12px;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 8px;
        }
        .detail-label {
            font-weight: 600;
            width: 130px;
            color: #555;
        }
        .detail-value {
            flex: 1;
            color: #333;
        }
        .booking-number {
            background: #e9ecef;
            padding: 15px;
            text-align: center;
            border-radius: 5px;
            margin: 20px 0;
            font-size: 24px;
            font-weight: bold;
            color: #f5576c;
            letter-spacing: 2px;
            border: 2px dashed #f5576c;
        }
        .customer-info {
            background: #e8f4fd;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }
        .customer-info h3 {
            margin-top: 0;
            color: #0d6efd;
        }
        .status-badge {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 14px;
        }
        .status-pending {
            background: #ffc107;
            color: #856404;
        }
        .action-buttons {
            text-align: center;
            margin: 30px 0;
        }
        .action-button {
            display: inline-block;
            padding: 12px 25px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            margin: 0 10px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }
        .btn-success {
            background: #28a745;
            color: white;
        }
        .btn-warning {
            background: #ffc107;
            color: #856404;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 14px;
            color: #6c757d;
            border-top: 1px solid #dee2e6;
        }
        .footer a {
            color: #f5576c;
            text-decoration: none;
            font-weight: 600;
        }
        .quick-stats {
            display: flex;
            justify-content: space-between;
            margin: 20px 0;
            text-align: center;
        }
        .stat-box {
            flex: 1;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 0 5px;
        }
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #f5576c;
        }
        .stat-label {
            font-size: 12px;
            color: #6c757d;
        }
        @media (max-width: 600px) {
            .detail-row {
                flex-direction: column;
            }
            .detail-label {
                width: 100%;
                margin-bottom: 5px;
            }
            .quick-stats {
                flex-direction: column;
            }
            .stat-box {
                margin: 5px 0;
            }
            .action-button {
                display: block;
                margin: 10px 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔔 New Booking Alert</h1>
            <p>A new reservation has been received</p>
        </div>
        
        <div class="content">
            <div class="alert-box">
                <span style="font-size: 30px;">🆕</span><br>
                New booking received on <?php echo date('F j, Y \a\t g:i A'); ?>
            </div>
            
            <div class="booking-number">
                <?php echo $bookingData['booking_number'] ?? 'BK2024030001'; ?>
            </div>
            
            <div class="quick-stats">
                <div class="stat-box">
                    <div class="stat-number">
                        <?php 
                        // Count today's bookings (simplified for email)
                        echo rand(5, 15); // This would be dynamic in real implementation
                        ?>
                    </div>
                    <div class="stat-label">Today's Bookings</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number">
                        <?php echo date('g:i A', strtotime($bookingData['booking_time'] ?? '19:00')); ?>
                    </div>
                    <div class="stat-label">Booking Time</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number">
                        <?php echo $bookingData['guests'] ?? 2; ?>
                    </div>
                    <div class="stat-label">Guests</div>
                </div>
            </div>
            
            <div class="booking-details">
                <h2>📋 Booking Details</h2>
                
                <div class="detail-row">
                    <span class="detail-label">Booking Number:</span>
                    <span class="detail-value">
                        <strong><?php echo $bookingData['booking_number'] ?? 'BK2024030001'; ?></strong>
                    </span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Date:</span>
                    <span class="detail-value">
                        <?php echo date('l, F j, Y', strtotime($bookingData['booking_date'] ?? 'today')); ?>
                    </span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Time:</span>
                    <span class="detail-value">
                        <?php echo date('g:i A', strtotime($bookingData['booking_time'] ?? '19:00')); ?>
                    </span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Duration:</span>
                    <span class="detail-value">2 hours (standard seating)</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Guests:</span>
                    <span class="detail-value">
                        <?php echo $bookingData['guests'] ?? 2; ?> people
                    </span>
                </div>
                
                <?php if (!empty($bookingData['table_preference'])): ?>
                <div class="detail-row">
                    <span class="detail-label">Table Preference:</span>
                    <span class="detail-value">
                        <span class="status-badge" style="background: #e9ecef; color: #495057;">
                            <?php echo ucfirst($bookingData['table_preference']); ?>
                        </span>
                    </span>
                </div>
                <?php endif; ?>
                
                <div class="detail-row">
                    <span class="detail-label">Status:</span>
                    <span class="detail-value">
                        <span class="status-badge status-pending">Pending</span>
                    </span>
                </div>
                
                <?php if (!empty($bookingData['special_requests'])): ?>
                <div class="detail-row">
                    <span class="detail-label">Special Requests:</span>
                    <span class="detail-value">
                        <em>"<?php echo nl2br(htmlspecialchars($bookingData['special_requests'])); ?>"</em>
                    </span>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="customer-info">
                <h3>👤 Customer Information</h3>
                
                <div class="detail-row">
                    <span class="detail-label">Name:</span>
                    <span class="detail-value">
                        <strong><?php echo htmlspecialchars($bookingData['customer_name'] ?? 'Customer Name'); ?></strong>
                    </span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Email:</span>
                    <span class="detail-value">
                        <a href="mailto:<?php echo $bookingData['customer_email'] ?? ''; ?>">
                            <?php echo htmlspecialchars($bookingData['customer_email'] ?? 'email@example.com'); ?>
                        </a>
                    </span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Phone:</span>
                    <span class="detail-value">
                        <a href="tel:<?php echo $bookingData['customer_phone'] ?? ''; ?>">
                            <?php echo htmlspecialchars($bookingData['customer_phone'] ?? '+1234567890'); ?>
                        </a>
                    </span>
                </div>
            </div>
            
            <div class="action-buttons">
                <a href="<?php echo APP_URL ?? '#'; ?>/admin/view_booking.php?id=<?php echo $bookingData['id'] ?? 0; ?>" 
                   class="action-button btn-primary">
                    👁️ View Full Details
                </a>
                <a href="<?php echo APP_URL ?? '#'; ?>/admin/bookings.php" 
                   class="action-button btn-success">
                    📋 Manage Bookings
                </a>
                <a href="<?php echo APP_URL ?? '#'; ?>/admin/send_email.php?booking_id=<?php echo $bookingData['id'] ?? 0; ?>" 
                   class="action-button btn-warning">
                    ✉️ Contact Customer
                </a>
            </div>
            
            <div style="background: #e8f4fd; padding: 15px; border-radius: 5px; margin: 20px 0;">
                <p style="margin: 0; color: #0d6efd;">
                    <strong>📊 Quick Actions:</strong><br>
                    • <a href="<?php echo APP_URL ?? '#'; ?>/admin/ajax/update_booking_status.php?id=<?php echo $bookingData['id'] ?? 0; ?>&status=confirmed">Confirm this booking</a><br>
                    • <a href="<?php echo APP_URL ?? '#'; ?>/admin/calendar.php?date=<?php echo $bookingData['booking_date'] ?? ''; ?>">View schedule for this date</a><br>
                    • <a href="<?php echo APP_URL ?? '#'; ?>/admin/analytics.php">View booking analytics</a>
                </p>
            </div>
        </div>
        
        <div class="footer">
            <p><strong><?php echo APP_NAME ?? 'Restaurant Management System'; ?></strong></p>
            <p>This is an automated notification. Please do not reply to this email.</p>
            <p style="margin-top: 15px;">
                <a href="<?php echo APP_URL ?? '#'; ?>/admin/login.php">Login to Admin Panel</a> | 
                <a href="<?php echo APP_URL ?? '#'; ?>/admin/settings.php">Notification Settings</a>
            </p>
            <p style="margin-top: 15px; font-size: 12px;">
                Notification sent: <?php echo date('F j, Y \a\t g:i:s A'); ?><br>
                IP Address: <?php echo $_SERVER['REMOTE_ADDR'] ?? 'Unknown'; ?>
            </p>
        </div>
    </div>
</body>
</html>