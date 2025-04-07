<?php
require_once __DIR__ . '/../dao/UserDAO.php';

class UserService {
    private $userDAO;

    public function __construct() {
        $this->userDAO = new UserDAO();
    }

    public function register($userData) {
        // Validate input
        if(empty($userData->name) || empty($userData->email) || empty($userData->password)) {
            return array("success" => false, "message" => "All fields are required");
        }

        // Validate email format
        if(!filter_var($userData->email, FILTER_VALIDATE_EMAIL)) {
            return array("success" => false, "message" => "Invalid email format");
        }

        // Hash password
        $userData->password = password_hash($userData->password, PASSWORD_DEFAULT);
        
        // Set default role
        $userData->role = "user";

        // Create user
        if($this->userDAO->create($userData)) {
            return array("success" => true, "message" => "User registered successfully");
        }
        return array("success" => false, "message" => "Failed to register user");
    }

    public function login($email, $password) {
        // Validate input
        if(empty($email) || empty($password)) {
            return array("success" => false, "message" => "Email and password are required");
        }

        // Get user
        $result = $this->userDAO->login($email, $password);
        
        if($result->rowCount() > 0) {
            $user = $result->fetch(PDO::FETCH_ASSOC);
            
            // Verify password
            if(password_verify($password, $user['password'])) {
                // Remove password from response
                unset($user['password']);
                return array("success" => true, "user" => $user);
            }
        }
        
        return array("success" => false, "message" => "Invalid email or password");
    }

    public function getUserById($id) {
        $result = $this->userDAO->readOne($id);
        
        if($result->rowCount() > 0) {
            $user = $result->fetch(PDO::FETCH_ASSOC);
            unset($user['password']);
            return array("success" => true, "user" => $user);
        }
        
        return array("success" => false, "message" => "User not found");
    }

    public function updateUser($userData) {
        // Validate input
        if(empty($userData->id) || empty($userData->name) || empty($userData->email)) {
            return array("success" => false, "message" => "Required fields are missing");
        }

        // If password is provided, hash it
        if(!empty($userData->password)) {
            $userData->password = password_hash($userData->password, PASSWORD_DEFAULT);
        }

        if($this->userDAO->update($userData)) {
            return array("success" => true, "message" => "User updated successfully");
        }
        return array("success" => false, "message" => "Failed to update user");
    }

    public function deleteUser($id) {
        if($this->userDAO->delete($id)) {
            return array("success" => true, "message" => "User deleted successfully");
        }
        return array("success" => false, "message" => "Failed to delete user");
    }

    public function getAllUsers() {
        $result = $this->userDAO->readAll();
        $users = array();
        
        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            unset($row['password']);
            $users[] = $row;
        }
        
        return array("success" => true, "users" => $users);
    }
}
?> 