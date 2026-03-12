<?php
session_start();
require_once __DIR__ . '/../../includes/config/constants.php';
require_once __DIR__ . '/../../includes/classes/User.php';
require_once __DIR__ . '/../../includes/functions/user_functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'super_admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit();
}

$adminId = $_POST['admin_id'] ?? 0;
$username = $_POST['username'] ?? '';
$email = $_POST['email'] ?? '';
$role = $_POST['role'] ?? 'admin';
$password = $_POST['password'] ?? '';

if (!$adminId || !$username || !$email) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

$user = new User();

// Check if username is taken by another admin
if ($user->isUsernameTaken($username, $adminId)) {
    echo json_encode(['success' => false, 'message' => 'Username already taken']);
    exit();
}

// Check if email is taken by another admin
if ($user->isEmailTaken($email, $adminId)) {
    echo json_encode(['success' => false, 'message' => 'Email already registered']);
    exit();
}

$data = [
    'username' => $username,
    'email' => $email,
    'role' => $role
];

if (!empty($password)) {
    if (!$user->validatePassword($password)) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters and contain letters and numbers']);
        exit();
    }
    $data['password'] = $password;
}

$result = $user->updateUser($adminId, $data);

if ($result) {
    require_once __DIR__ . '/../includes/admin_functions.php';
    logAdminAction($_SESSION['admin_id'], 'update_admin', "Updated admin #$adminId");
    
    echo json_encode(['success' => true, 'message' => 'Admin updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error updating admin']);
}