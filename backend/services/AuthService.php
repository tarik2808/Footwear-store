<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
require_once __DIR__ . '/../config/database.php';

class AuthService {
    private $secretKey;
    private $userService;
    private $algorithm = 'HS256';
    private $tokenExpiry = 3600; // 1 hour

    public function __construct() {
        $this->secretKey = getenv('JWT_SECRET');
        if (!$this->secretKey) {
            error_log('WARNING: JWT_SECRET environment variable is not set. Using default secret key. This is not recommended for production.');
            $this->secretKey = 'your-secret-key-change-this-in-production';
        }
        $this->userService = new UserService();
    }

    public function generateToken($userId, $role) {
        $issuedAt = time();
        $expire = $issuedAt + $this->tokenExpiry;

        $payload = array(
            'iat' => $issuedAt,
            'exp' => $expire,
            'sub' => $userId,
            'role' => $role
        );

        return JWT::encode($payload, $this->secretKey, $this->algorithm);
    }

    public function verifyToken($token) {
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, $this->algorithm));
            $data = (array) $decoded;
            
            // Check if token is expired
            if (isset($data['exp']) && $data['exp'] < time()) {
                throw new Exception('Token has expired');
            }
            
            return [
                'valid' => true,
                'user_id' => $data['sub'],
                'role' => $data['role']
            ];
        } catch (Exception $e) {
            error_log("Token verification failed: " . $e->getMessage());
            return [
                'valid' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function authenticate($email, $password) {
        try {
            $user = $this->userService->login($email, $password);
            if ($user) {
                $token = $this->generateToken($user['id'], $user['role']);
                return [
                    'token' => $token,
                    'user' => $user
                ];
            }
            throw new Exception("Invalid credentials");
        } catch (Exception $e) {
            throw new Exception("Authentication failed: " . $e->getMessage());
        }
    }
}
?> 