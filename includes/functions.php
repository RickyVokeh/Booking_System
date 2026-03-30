<?php
// includes/functions.php

/**
 * Format booking date for display
 * @param string $date The date string to format
 * @param string $format The desired format (default: 'F j, Y')
 * @return string Formatted date
 */
function formatBookingDate($date, $format = 'F j, Y') {
    if (empty($date)) {
        return 'N/A';
    }
    
    try {
        $timestamp = strtotime($date);
        if ($timestamp === false) {
            return $date;
        }
        return date($format, $timestamp);
    } catch (Exception $e) {
        return $date;
    }
}

/**
 * Format booking time for display
 * @param string $time The time string to format
 * @param string $format The desired format (default: 'g:i A')
 * @return string Formatted time
 */
function formatBookingTime($time, $format = 'g:i A') {
    if (empty($time)) {
        return 'N/A';
    }
    
    try {
        $timestamp = strtotime($time);
        if ($timestamp === false) {
            return $time;
        }
        return date($format, $timestamp);
    } catch (Exception $e) {
        return $time;
    }
}

/**
 * Format phone number for display
 * @param string $phone The phone number to format
 * @return string Formatted phone number
 */
function formatPhoneNumber($phone) {
    if (empty($phone)) {
        return 'N/A';
    }
    
    // Remove non-numeric characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Format based on length
    $length = strlen($phone);
    if ($length === 10) {
        return '(' . substr($phone, 0, 3) . ') ' . substr($phone, 3, 3) . '-' . substr($phone, 6, 4);
    } elseif ($length === 11) {
        return '+' . substr($phone, 0, 1) . ' (' . substr($phone, 1, 3) . ') ' . substr($phone, 4, 3) . '-' . substr($phone, 7, 4);
    }
    
    return $phone;
}

/**
 * Format guests count with proper pluralization
 * @param int $guests Number of guests
 * @return string Formatted guest count
 */
function formatGuests($guests) {
    $guests = (int)$guests;
    return $guests . ' ' . ($guests === 1 ? 'Guest' : 'Guests');
}

/**
 * Get status badge class
 * @param string $status Booking status
 * @return string CSS class
 */
function getStatusBadgeClass($status) {
    switch (strtolower($status)) {
        case 'confirmed':
            return 'success';
        case 'pending':
            return 'warning';
        case 'cancelled':
            return 'danger';
        case 'completed':
            return 'info';
        default:
            return 'secondary';
    }
}

/**
 * Sanitize input (if not already defined)
 */
if (!function_exists('sanitizeInput')) {
    function sanitizeInput($input) {
        if (is_array($input)) {
            return array_map('sanitizeInput', $input);
        }
        return trim(htmlspecialchars($input, ENT_QUOTES, 'UTF-8'));
    }
}

/**
 * Get booking status text
 * @param string $status Status code
 * @return string Human readable status
 */
function getBookingStatusText($status) {
    $statuses = [
        'confirmed' => 'Confirmed',
        'pending' => 'Pending Approval',
        'cancelled' => 'Cancelled',
        'completed' => 'Completed',
        'waiting' => 'Waiting List'
    ];
    
    return $statuses[strtolower($status)] ?? ucfirst($status);
}