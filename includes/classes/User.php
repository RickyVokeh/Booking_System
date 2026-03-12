<?php
require_once __DIR__ . '/Db.php';

class User extends Database {
    protected $table = 'admins';
    
    public function __construct() {
        parent::__construct();
    }
    
    public function authenticate($username, $password) {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table} 
            WHERE username = ? OR email = ?
        ");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Update last login
            $this->update($user['id'], ['last_login' => date('Y-m-d H:i:s')]);
            
            // Remove password from array
            unset($user['password']);
            return $user;
        }
        
        return false;
    }
    
    public function createUser($data) {
        // Hash password
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Set default role if not specified
        if (!isset($data['role'])) {
            $data['role'] = 'admin';
        }
        
        return $this->create($data);
    }
    
    public function updateUser($id, $data) {
        // Hash password if being updated
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            unset($data['password']);
        }
        
        return $this->update($id, $data);
    }
    
    public function getUserByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    
    public function validatePassword($password) {
        // Password must be at least 8 characters and contain at least one number and one letter
        return strlen($password) >= 8 && 
               preg_match('/[A-Za-z]/', $password) && 
               preg_match('/[0-9]/', $password);
    }
    
    public function isUsernameTaken($username, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE username = ?";
        $params = [$username];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['count'] > 0;
    }
    
    public function isEmailTaken($email, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE email = ?";
        $params = [$email];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['count'] > 0;
    }
    
    public function getAllAdmins() {
        $stmt = $this->db->prepare("
            SELECT id, username, email, role, last_login, created_at 
            FROM {$this->table} 
            ORDER BY id DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}