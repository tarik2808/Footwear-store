<?php
require_once __DIR__ . '/../services/AuthService.php';

class BaseController {
    protected $authService;

    public function __construct() {
        $this->authService = new AuthService();
    }

    protected function sendResponse($data, $status = 200) {
        Flight::json($data, $status);
    }

    protected function sendError($message, $status = 400) {
        Flight::json(['error' => $message], $status);
    }

    protected function validateRequiredFields($data, $requiredFields) {
        $missingFields = [];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $missingFields[] = $field;
            }
        }
        if (!empty($missingFields)) {
            throw new Exception("Missing required fields: " . implode(', ', $missingFields));
        }
    }

    protected function getCurrentUserId() {
        $headers = getallheaders();
        $token = str_replace('Bearer ', '', $headers['Authorization'] ?? '');
        
        if (empty($token)) {
            throw new Exception("No authorization token provided");
        }

        $validation = $this->authService->validateToken($token);
        if (!$validation['valid']) {
            throw new Exception("Invalid token: " . $validation['error']);
        }

        return $validation['user_id'];
    }

    protected function isAdmin() {
        $headers = getallheaders();
        $token = str_replace('Bearer ', '', $headers['Authorization'] ?? '');
        
        if (empty($token)) {
            throw new Exception("No authorization token provided");
        }

        $validation = $this->authService->validateToken($token);
        if (!$validation['valid']) {
            throw new Exception("Invalid token: " . $validation['error']);
        }

        return $validation['role'] === 'admin';
    }
}
?> 