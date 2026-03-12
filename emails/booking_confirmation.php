<?php
// Email template for booking confirmation sent to customers
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation</title>
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
            background: linear-gradient(135deg, #667eea, #764ba2);
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
        .booking-details {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .booking-details h2 {
            margin-top: 0;
            color: #667eea;
            font-size: 20px;
        }
        .detail-row {
            display: flex;
            margin-bottom: 12px;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 8px;
        }
        .detail-label {
            font-weight: 600;
            width: 120px;
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
            color: #667eea;
            letter-spacing: 2px;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            margin: 20px 0;
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
            color: #667eea;
            text-decoration: none;
        }
        .info-box {
            background: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .info-box i {
            margin-right: 10px;
        }
        @media (max-width: 600px) {
            .container {
                margin: 10px;
            }
            .detail-row {
                flex-direction: column;
            }
            .detail-label {
                width: 100%;
                margin-bottom: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🍽️ <?php echo APP_NAME ?? 'Restaurant Booking System'; ?></h1>
            <p>Your table has been successfully reserved!</p>
        </div>
        
        <div class="content">
            <div style="text-align: center; margin-bottom: 30px;">
                <h2 style="color: #28a745; margin-bottom: 10px;">
                    <span style="font-size: 50px;">✓</span><br>
                    Booking Confirmed!
                </h2>
                <p>Thank you for choosing <?php echo APP_NAME ?? 'our restaurant'; ?>. 
                   We look forward to serving you!</p>
            </div>
            
            <div class="booking-number">
                <?php echo $bookingData['booking_number'] ?? 'BK2024030001'; ?>
            </div>
            
            <div class="booking-details">
                <h2><i class="fas fa-calendar-check"></i> Booking Details</h2>
                
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
                    <span class="detail-label">Guests:</span>
                    <span class="detail-value">
                        <?php echo $bookingData['guests'] ?? 2; ?> 
                        <?php echo ($bookingData['guests'] ?? 2) > 1 ? 'people' : 'person'; ?>
                    </span>
                </div>
                
                <?php if (!empty($bookingData['table_preference'])): ?>
                <div class="detail-row">
                    <span class="detail-label">Table Preference:</span>
                    <span class="detail-value"><?php echo ucfirst($bookingData['table_preference']); ?></span>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($bookingData['special_requests'])): ?>
                <div class="detail-row">
                    <span class="detail-label">Special Requests:</span>
                    <span class="detail-value"><?php echo nl2br(htmlspecialchars($bookingData['special_requests'])); ?></span>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="info-box">
                <strong>📌 Important Information:</strong>
                <ul style="margin-top: 10px; margin-bottom: 0; padding-left: 20px;">
                    <li>Please arrive 10 minutes before your booking time</li>
                    <li>Tables are held for 15 minutes past the booking time</li>
                    <li>For changes or cancellations, please contact us at least 2 hours in advance</li>
                    <li>Please present this confirmation email at the restaurant</li>
                </ul>
            </div>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="<?php echo APP_URL ?? '#'; ?>/cancel_booking.php?number=<?php echo $bookingData['booking_number'] ?? ''; ?>" 
                   class="cta-button" style="background: #dc3545; margin-right: 10px;">
                    ❌ Cancel Booking
                </a>
                <a href="<?php echo APP_URL ?? '#'; ?>/contact.php" class="cta-button" style="background: #6c757d;">
                    📞 Contact Us
                </a>
            </div>
        </div>
        
        <div class="footer">
            <p><strong><?php echo APP_NAME ?? 'Restaurant Name'; ?></strong></p>
            <p>123 Food Street, City, State 12345</p>
            <p>Phone: (555) 123-4567 | Email: info@restaurant.com</p>
            <p style="margin-top: 15px;">
                <a href="<?php echo APP_URL ?? '#'; ?>">Visit our website</a> | 
                <a href="<?php echo APP_URL ?? '#'; ?>/privacy.php">Privacy Policy</a> | 
                <a href="<?php echo APP_URL ?? '#'; ?>/terms.php">Terms & Conditions</a>
            </p>
            <p style="margin-top: 15px; font-size: 12px;">
                This email was sent on <?php echo date('F j, Y \a\t g:i A'); ?><br>
                &copy; <?php echo date('Y'); ?> <?php echo APP_NAME ?? 'Restaurant Booking System'; ?>. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>