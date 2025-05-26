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

    protected function sendError($message, $status = 400, $exception = null) {
        $error = ['error' => $message];
        if ($exception) {
            $error['exception'] = get_class($exception);
            $error['trace'] = $exception->getTraceAsString();
        }
        Flight::json($error, $status);
    }

    protected function validateRequiredFields($data, $requiredFields) {
        $missingFields = [];
        foreach ($requiredFields as $field) {
            if ((is_array($data) && (!isset($data[$field]) || $data[$field] === null)) ||
                (is_object($data) && (!isset($data->$field) || $data->$field === null))) {
                $missingFields[] = $field;
            }
        }
        if (!empty($missingFields)) {
            throw new Exception("Missing required fields: " . implode(', ', $missingFields));
        }
    }

    protected function getCurrentUserId() {
        $headers = getallheaders();
        $token = null;
        if (isset($headers['Authorization'])) {
            $token = $headers['Authorization'];
        } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $token = $_SERVER['HTTP_AUTHORIZATION'];
        }
        $token = str_replace('Bearer ', '', $token ?? '');
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
        error_log('=== Authorization Debug ===');
        error_log('Raw Headers: ' . print_r(getallheaders(), true));
        error_log('Raw SERVER: ' . print_r($_SERVER, true));
        error_log('Raw REQUEST: ' . print_r($_REQUEST, true));
        
        $headers = getallheaders();
        $token = null;
        
        // Check all possible locations for the Authorization header
        if (isset($headers['Authorization'])) {
            $token = $headers['Authorization'];
            error_log('Found token in headers[Authorization]');
        } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $token = $_SERVER['HTTP_AUTHORIZATION'];
            error_log('Found token in SERVER[HTTP_AUTHORIZATION]');
        } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $token = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
            error_log('Found token in SERVER[REDIRECT_HTTP_AUTHORIZATION]');
        } elseif (isset($_SERVER['Authorization'])) {
            $token = $_SERVER['Authorization'];
            error_log('Found token in SERVER[Authorization]');
        }
        
        $token = str_replace('Bearer ', '', $token ?? '');
        if (empty($token)) {
            error_log('No authorization token found in any location');
            throw new Exception("No authorization token provided");
        }
        
        $validation = $this->authService->validateToken($token);
        if (!$validation['valid']) {
            error_log('Token validation failed: ' . $validation['error']);
            throw new Exception("Invalid token: " . $validation['error']);
        }
        
        error_log('Token validation successful, role: ' . $validation['role']);
        return $validation['role'] === 'admin';
    }
}
?> 