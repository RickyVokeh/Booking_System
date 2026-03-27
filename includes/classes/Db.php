<?php
require_once __DIR__ . '/../config/database.php';

class Database {
    protected $db;
    protected $table;
    
    public function __construct() {
        $this->db = DatabaseCon::getInstance()->getConnection();
    }
    
    // Generic CRUD operations
    public function getAll($orderBy = 'id DESC', $limit = null) {
        $sql = "SELECT * FROM {$this->table} ORDER BY {$orderBy}";
        if ($limit) {
            $sql .= " LIMIT " . (int)$limit;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function create($data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        
        foreach ($data as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        
        return $stmt->execute() ? $this->db->lastInsertId() : false;
    }
    
    public function update($id, $data) {
        $setParts = [];
        foreach (array_keys($data) as $key) {
            $setParts[] = "{$key} = :{$key}";
        }
        $setClause = implode(', ', $setParts);
        
        $sql = "UPDATE {$this->table} SET {$setClause} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        
        $data['id'] = $id;
        foreach ($data as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        
        return $stmt->execute();
    }
    
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function count($conditions = []) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        
        if (!empty($conditions)) {
            $whereParts = [];
            foreach (array_keys($conditions) as $key) {
                $whereParts[] = "{$key} = :{$key}";
            }
            $sql .= " WHERE " . implode(' AND ', $whereParts);
        }
        
        $stmt = $this->db->prepare($sql);
        
        if (!empty($conditions)) {
            foreach ($conditions as $key => $value) {
                $stmt->bindValue(":{$key}", $value);
            }
        }
        
        $stmt->execute();
        return $stmt->fetch()['total'];
    }
    
    // Transaction methods
    public function beginTransaction() {
        return $this->db->beginTransaction();
    }
    
    public function commit() {
        return $this->db->commit();
    }
    
    public function rollback() {
        return $this->db->rollBack();
    }
}