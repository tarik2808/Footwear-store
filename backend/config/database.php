<?php
require_once __DIR__ . '/../config/database.php';

class UserDAO {
    private $conn;
    private $table_name = "users";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Validate user input
    private function validateUser($user) {
        if (empty($user->name) || empty($user->email) || empty($user->password)) {
            throw new Exception("Name, email, and password are required");
        }
        if (!filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }
        if (strlen($user->password) < 8) {
            throw new Exception("Password must be at least 8 characters long");
        }
        return true;
    }

    // Create new user
    public function create($user) {
        try {
            $this->validateUser($user);
            
            // Check if email already exists
            if ($this->emailExists($user->email)) {
                throw new Exception("Email already exists");
            }

            // Hash password
            $hashedPassword = password_hash($user->password, PASSWORD_DEFAULT);
            
            $query = "INSERT INTO " . $this->table_name . " 
                     (name, email, password, role) 
                     VALUES (:name, :email, :password, :role)";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(":name", $user->name);
            $stmt->bindParam(":email", $user->email);
            $stmt->bindParam(":password", $hashedPassword);
            $stmt->bindParam(":role", $user->role);

            if($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            return false;
        } catch (Exception $e) {
            error_log("User creation error: " . $e->getMessage());
            throw $e;
        }
    }

    // Check if email exists
    private function emailExists($email) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // Read all users with pagination
    public function readAll($page = 1, $limit = 10) {
        try {
            $offset = ($page - 1) * $limit;
            $query = "SELECT id, name, email, role, created_at 
                     FROM " . $this->table_name . " 
                     ORDER BY created_at DESC 
                     LIMIT :limit OFFSET :offset";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
            $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error reading users: " . $e->getMessage());
            throw new Exception("Error reading users");
        }
    }

    // Read single user
    public function readOne($id) {
        try {
            $query = "SELECT id, name, email, role, created_at 
                     FROM " . $this->table_name . " 
                     WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);
            $stmt->execute();
            
            $user = $stmt->fetch();
            if (!$user) {
                throw new Exception("User not found");
            }
            return $user;
        } catch (Exception $e) {
            error_log("Error reading user: " . $e->getMessage());
            throw $e;
        }
    }

    // Update user
    public function update($user) {
        try {
            if (empty($user->id)) {
                throw new Exception("User ID is required");
            }
            
            $query = "UPDATE " . $this->table_name . " 
                     SET name = :name, email = :email, role = :role";
            
            // Only update password if provided
            if (!empty($user->password)) {
                if (strlen($user->password) < 8) {
                    throw new Exception("Password must be at least 8 characters long");
                }
                $hashedPassword = password_hash($user->password, PASSWORD_DEFAULT);
                $query .= ", password = :password";
            }
            
            $query .= " WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(":name", $user->name);
            $stmt->bindParam(":email", $user->email);
            $stmt->bindParam(":role", $user->role);
            if (!empty($user->password)) {
                $stmt->bindParam(":password", $hashedPassword);
            }
            $stmt->bindParam(":id", $user->id);

            if($stmt->execute()) {
                return true;
            }
            return false;
        } catch (Exception $e) {
            error_log("Error updating user: " . $e->getMessage());
            throw $e;
        }
    }

    // Delete user
    public function delete($id) {
        try {
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);

            if($stmt->execute()) {
                return true;
            }
            return false;
        } catch (Exception $e) {
            error_log("Error deleting user: " . $e->getMessage());
            throw new Exception("Error deleting user");
        }
    }

    // Login user
    public function login($email, $password) {
        try {
            $query = "SELECT id, name, email, password, role 
                     FROM " . $this->table_name . " 
                     WHERE email = :email";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":email", $email);
            $stmt->execute();
            
            $user = $stmt->fetch();
            
            if (!$user) {
                throw new Exception("Invalid email or password");
            }
            
            if (!password_verify($password, $user['password'])) {
                throw new Exception("Invalid email or password");
            }
            
            // Remove password from returned user data
            unset($user['password']);
            return $user;
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            throw $e;
        }
    }

    // Get total count of users
    public function getTotalCount() {
        try {
            $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetch()['total'];
        } catch (Exception $e) {
            error_log("Error getting user count: " . $e->getMessage());
            throw new Exception("Error getting user count");
        }
    }
}
?> 