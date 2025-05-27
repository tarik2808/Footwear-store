<?php
require_once dirname(__DIR__, 2) . '/services/AuthService.php';

class AuthMiddleware {
    private $authService;

    public function __construct() {
        $this->authService = new AuthService();
    }

    public function authenticate() {
        error_log('DEBUG: AuthMiddleware authenticate() called');
        try {
            $headers = getallheaders();
            $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';
            error_log('DEBUG: Auth Header: ' . $authHeader);
            if (empty($authHeader)) {
                throw new Exception('No authorization header');
            }
            $token = str_replace('Bearer ', '', $authHeader);
            error_log('DEBUG: Token: ' . $token);
            $userData = $this->authService->verifyToken($token);
            error_log('DEBUG: userData: ' . print_r($userData, true));
            if (!$userData) {
                throw new Exception('Invalid token');
            }
            Flight::set('user', [
                'id' => $userData['user_id'],
                'role' => $userData['role']
            ]);
            error_log('DEBUG: Flight user set: ' . print_r(Flight::get('user'), true));
            return true;
        } catch (Exception $e) {
            error_log('DEBUG: Auth Exception: ' . $e->getMessage());
            Flight::json([
                'error' => 'Unauthorized',
                'message' => $e->getMessage()
            ], 401);
            return false;
        }
    }

    public function requireAdmin() {
        error_log('DEBUG: AuthMiddleware requireAdmin() called');
        try {
            $headers = getallheaders();
            error_log('DEBUG: All headers: ' . print_r($headers, true));
            
            $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';
            error_log('DEBUG: Auth Header: ' . $authHeader);
            
            if (empty($authHeader)) {
                error_log('DEBUG: No authorization header found');
                Flight::json([
                    'error' => 'Unauthorized',
                    'message' => 'No authorization header'
                ], 401);
                return false;
            }
            
            $token = str_replace('Bearer ', '', $authHeader);
            error_log('DEBUG: Token: ' . $token);
            
            $userData = $this->authService->verifyToken($token);
            error_log('DEBUG: userData: ' . print_r($userData, true));
            
            if (!$userData) {
                error_log('DEBUG: Invalid token');
                Flight::json([
                    'error' => 'Unauthorized',
                    'message' => 'Invalid token'
                ], 401);
                return false;
            }
            
            if (!isset($userData['role']) || $userData['role'] !== 'admin') {
                error_log('DEBUG: Not an admin user');
                Flight::json([
                    'error' => 'Forbidden',
                    'message' => 'Admin access required'
                ], 403);
                return false;
            }

            Flight::set('user', [
                'id' => $userData['user_id'],
                'role' => $userData['role']
            ]);
            
            error_log('DEBUG: Flight user set: ' . print_r(Flight::get('user'), true));
            return true;
        } catch (Exception $e) {
            error_log('DEBUG: Auth Exception: ' . $e->getMessage());
            Flight::json([
                'error' => 'Unauthorized',
                'message' => $e->getMessage()
            ], 401);
            return false;
        }
    }
}
