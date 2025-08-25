<?php
require_once __DIR__ . '/../config/database.php';

class UserDAO {
    private $conn;
    private $table_name = "users";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Create new user
    public function create($user) {
        $query = "INSERT INTO " . $this->table_name . " (name, email, password, role, is_active) VALUES (:name, :email, :password, :role, :is_active)";
        $stmt = $this->conn->prepare($query);

        // Sanitize and bind
        $stmt->bindParam(":name", $user->name);
        $stmt->bindParam(":email", $user->email);
        $stmt->bindParam(":password", $user->password);
        $stmt->bindParam(":role", $user->role);
        $stmt->bindParam(":is_active", $user->is_active);

        if($stmt->execute()) {
            // Fetch the newly created user
            $id = $this->conn->lastInsertId();
            $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return false;
    }

    // Read all users with pagination and optional filters
    public function readAll($page = 1, $limit = 10, $filters = array()) {
        $offset = ($page - 1) * $limit;
        $query = "SELECT * FROM " . $this->table_name . " WHERE 1=1";
        $params = array();

        // Example: add filter for role if needed
        if (!empty($filters['role'])) {
            $query .= " AND role = :role";
            $params[':role'] = $filters['role'];
        }

        $query .= " LIMIT :limit OFFSET :offset";
        $params[':limit'] = (int)$limit;
        $params[':offset'] = (int)$offset;

        $stmt = $this->conn->prepare($query);
        foreach($params as $key => $value) {
            if ($key === ':limit' || $key === ':offset') {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value);
            }
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Read single user
    public function readOne($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update user
    public function update($user) {
        $setParts = [];
        $params = [];
        
        // Build dynamic SET clause based on provided fields
        if (isset($user->name)) {
            $setParts[] = "name = :name";
            $params[':name'] = $user->name;
        }
        if (isset($user->email)) {
            $setParts[] = "email = :email";
            $params[':email'] = $user->email;
        }
        if (isset($user->password) && !empty($user->password)) {
            $setParts[] = "password = :password";
            $params[':password'] = $user->password;
        }
        // Only allow role updates for admin operations (handled separately)
        // if (isset($user->role)) {
        //     $setParts[] = "role = :role";
        //     $params[':role'] = $user->role;
        // }
        if (isset($user->is_active)) {
            $setParts[] = "is_active = :is_active";
            $params[':is_active'] = $user->is_active;
        }
        
        // Add updated_at timestamp
        $setParts[] = "updated_at = CURRENT_TIMESTAMP";
        
        if (empty($setParts)) {
            return false; // No fields to update
        }
        
        $query = "UPDATE " . $this->table_name . " SET " . implode(', ', $setParts) . " WHERE id = :id";
        $params[':id'] = $user->id;
        
                $stmt = $this->conn->prepare($query);
        
        // Bind all parameters
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        if($stmt->execute()) {
            return true;
        }
                return false;
    }
    
    // Admin update user (can change role and other admin fields)
    public function adminUpdate($user) {
        $setParts = [];
        $params = [];
        
        // Build dynamic SET clause based on provided fields
        if (isset($user->name)) {
            $setParts[] = "name = :name";
            $params[':name'] = $user->name;
        }
        if (isset($user->email)) {
            $setParts[] = "email = :email";
            $params[':email'] = $user->email;
        }
        if (isset($user->password) && !empty($user->password)) {
            $setParts[] = "password = :password";
            $params[':password'] = $user->password;
        }
        if (isset($user->role)) {
            $setParts[] = "role = :role";
            $params[':role'] = $user->role;
        }
        if (isset($user->is_active)) {
            $setParts[] = "is_active = :is_active";
            $params[':is_active'] = $user->is_active;
        }
        
        // Add updated_at timestamp
        $setParts[] = "updated_at = CURRENT_TIMESTAMP";
        
        if (empty($setParts)) {
            return false; // No fields to update
        }
        
        $query = "UPDATE " . $this->table_name . " SET " . implode(', ', $setParts) . " WHERE id = :id";
        $params[':id'] = $user->id;
        
        $stmt = $this->conn->prepare($query);
        
        // Bind all parameters
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
    
    // Delete user
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Login user
    public function login($email) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function emailExists($email, $excludeUserId = null) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = :email";
        if ($excludeUserId !== null) {
            $query .= " AND id != :id";
        }
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        if ($excludeUserId !== null) {
            $stmt->bindParam(":id", $excludeUserId);
        }
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // Get total count of users (optionally filtered)
    public function getTotalCount($filters = array()) {
        try {
            $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE 1=1";
            $params = array();

            // Example: add filter for role if needed
            if (!empty($filters['role'])) {
                $query .= " AND role = :role";
                $params[':role'] = $filters['role'];
            }

            $stmt = $this->conn->prepare($query);
            foreach($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();

            return $stmt->fetch()['total'];
        } catch (Exception $e) {
            error_log("Error getting user count: " . $e->getMessage());
            throw new Exception("Error getting user count");
        }
    }
}
?> 