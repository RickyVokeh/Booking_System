<?php
// Authentication check for admin pages
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id'])) {
    $_SESSION['error'] = 'Please login to access the admin panel';
    header('Location: login.php');
    exit();
}

// Optional: Check if session is expired (30 minutes)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    session_unset();
    session_destroy();
    header('Location: login.php?expired=1');
    exit();
}

$_SESSION['last_activity'] = time();