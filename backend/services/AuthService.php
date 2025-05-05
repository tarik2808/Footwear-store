<?php
require_once __DIR__ . '/../config/database.php';

class AuthService {
    private $secretKey;
    private $userService;

    public function __construct() {
        $this->secretKey = getenv('JWT_SECRET_KEY') ?: 'your-secret-key';
        $this->userService = new UserService();
    }

    public function generateToken($userId, $role) {
        $issuedAt = time();
        $expirationTime = $issuedAt + 3600; // Token valid for 1 hour

        $payload = [
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'sub' => $userId,
            'role' => $role
        ];

        return JWT::encode($payload, $this->secretKey, 'HS256');
    }

    public function validateToken($token) {
        try {
            $decoded = JWT::decode($token, $this->secretKey, ['HS256']);
            return [
                'valid' => true,
                'user_id' => $decoded->sub,
                'role' => $decoded->role
            ];
        } catch (Exception $e) {
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