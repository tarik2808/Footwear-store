<?php
require_once dirname(__FILE__) . '/../config/Database.php';
require_once dirname(__DIR__) . '/services/AuthService.php';

class AuthController {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function login($email, $password) {
        try {
            $query = "SELECT id, name, email, password, role FROM users WHERE email = :email";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":email", $email);
            $stmt->execute();
            
            if($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Verify password
                if(password_verify($password, $row['password'])) {
                    // Start session if not already started
                    if (session_status() === PHP_SESSION_NONE) {
                        session_start();
                    }
                    
                    // Set session variables
                    $_SESSION['user_id'] = $row['id'];
                    $_SESSION['email'] = $row['email'];
                    $_SESSION['name'] = $row['name'];
                    $_SESSION['role'] = $row['role'];
                    
                    // Generate JWT token
                    $authService = new AuthService();
                    $token = $authService->generateToken($row['id'], $row['role']);
                    
                    return [
                        'success' => true,
                        'message' => 'Login successful',
                        'user' => [
                            'id' => $row['id'],
                            'email' => $row['email'],
                            'name' => $row['name'],
                            'role' => $row['role']
                        ],
                        'token' => $token
                    ];
                }
            }
            
            return [
                'success' => false,
                'message' => 'Invalid email or password'
            ];
            
        } catch(PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }
    
    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Unset all session variables
        $_SESSION = array();
        
        // Destroy the session
        session_destroy();
        
        return [
            'success' => true,
            'message' => 'Logged out successfully'
        ];
    }
}
?> 