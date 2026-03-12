<?php
// Application constants
define('APP_NAME', $_ENV['APP_NAME'] ?? 'Restaurant Booking');
define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost');
define('APP_ENV', $_ENV['APP_ENV'] ?? 'development');
define('APP_DEBUG', filter_var($_ENV['APP_DEBUG'] ?? true, FILTER_VALIDATE_BOOLEAN));

// Timezone
date_default_timezone_set($_ENV['TIMEZONE'] ?? 'Africa/Nairobi');

// Path constants
define('ROOT_PATH', dirname(__DIR__, 2));
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('ADMIN_PATH', ROOT_PATH . '/admin');
define('ASSETS_PATH', APP_URL . '/assets');

// Booking settings
define('MAX_GUESTS', $_ENV['MAX_GUESTS_PER_BOOKING'] ?? 10);
define('MIN_HOURS_ADVANCE', $_ENV['MIN_HOURS_ADVANCE'] ?? 2);
define('BOOKING_INTERVAL', $_ENV['BOOKING_TIME_INTERVAL'] ?? 30);

// Security
define('SECRET_KEY', $_ENV['SECRET_KEY'] ?? 'your-secret-key');
define('CSRF_KEY', $_ENV['CSRF_KEY'] ?? 'your-csrf-key');

// Session settings
//ini_set('session.cookie_httponly', 1);
//ini_set('session.use_only_cookies', 1);
//ini_set('session.cookie_secure', APP_ENV === 'production');

// Error reporting
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}