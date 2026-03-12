<?php
// Email template for booking reminders (can be used for future enhancement)
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Reminder</title>
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
            background: linear-gradient(135deg, #4facfe, #00f2fe);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .content {
            padding: 30px;
        }
        .reminder-box {
            background: #e8f4fd;
            border-left: 4px solid #4facfe;
            padding: 20px;
            margin: 20px 0;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 14px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⏰ Booking Reminder</h1>
            <p>Your reservation is coming up soon!</p>
        </div>
        <div class="content">
            <div class="reminder-box">
                <h2>Dear <?php echo htmlspecialchars($bookingData['customer_name'] ?? 'Valued Customer'); ?>,</h2>
                <p>This is a friendly reminder about your upcoming reservation at <?php echo APP_NAME ?? 'our restaurant'; ?>.</p>
                <p><strong>Date:</strong> <?php echo date('l, F j, Y', strtotime($bookingData['booking_date'] ?? 'today')); ?></p>
                <p><strong>Time:</strong> <?php echo date('g:i A', strtotime($bookingData['booking_time'] ?? '19:00')); ?></p>
                <p><strong>Guests:</strong> <?php echo $bookingData['guests'] ?? 2; ?></p>
                <p>We look forward to serving you!</p>
            </div>
        </div>
        <div class="footer">
            <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME ?? 'Restaurant'; ?></p>
        </div>
    </div>
</body>
</html>