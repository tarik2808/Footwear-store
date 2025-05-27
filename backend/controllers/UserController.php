<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/UserService.php';

class UserController extends BaseController {
    private $userService;

    public function __construct() {
        parent::__construct();
        $this->userService = new UserService();
    }

    public function register() {
        try {
            $data = (object) Flight::request()->data->getData();
            $this->validateRequiredFields($data, ['name', 'email', 'password']);
            
            $user = $this->userService->register($data);
            $token = $this->authService->generateToken($user['id'], $user['role']);
            
            $this->sendResponse([
                'user' => $user,
                'token' => $token
            ], 201);
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 400, $e);
        }
    }

    public function login() {
        try {
            $data = Flight::request()->data;
            $this->validateRequiredFields($data, ['email', 'password']);
            
            $result = $this->authService->authenticate($data['email'], $data['password']);
            $this->sendResponse($result);
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 400, $e);
        }
    }

    public function getProfile() {
        try {
            $user = Flight::get('user');
            $userId = $user['id'];
            $userData = $this->userService->getProfile($userId);
            $this->sendResponse($userData);
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 400, $e);
        }
    }

    public function updateProfile() {
        try {
            $user = Flight::get('user');
            $userId = $user['id'];
            $data = Flight::request()->data;
            $userData = $this->userService->updateProfile($userId, $data);
            $this->sendResponse($userData);
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 400, $e);
        }
    }

    public function deleteAccount() {
        try {
            $user = Flight::get('user');
            $userId = $user['id'];
            $this->userService->deleteAccount($userId);
            $this->sendResponse(['message' => 'Account deleted successfully']);
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 400, $e);
        }
    }

    public function listUsers() {
        try {
            $user = Flight::get('user');
            error_log('DEBUG: UserController listUsers - Flight user: ' . print_r($user, true));
            
            if (!$user || !isset($user['role'])) {
                throw new Exception("User authentication required");
            }
            
            if ($user['role'] !== 'admin') {
                throw new Exception("Unauthorized: Admin access required");
            }

            $query = Flight::request()->query;
            $page = (is_object($query) && isset($query->page)) ? $query->page : 1;
            $limit = (is_object($query) && isset($query->limit)) ? $query->limit : 10;
            $filters = (is_object($query) && isset($query->filters)) ? $query->filters : [];
            if (!is_array($filters)) {
                $filters = [];
            }
            
            error_log('DEBUG: UserController listUsers - Calling userService->listUsers');
            $result = $this->userService->listUsers($page, $limit, $filters);
            error_log('DEBUG: UserController listUsers - Result: ' . print_r($result, true));

            $this->sendResponse($result);
        } catch (Exception $e) {
            error_log('DEBUG: UserController listUsers - Error: ' . $e->getMessage());
            $this->sendError($e->getMessage(), 400, $e);
        }
    }

    public function deleteUser($id) {
        try {
            $user = Flight::get('user');
            if (!$user || $user['role'] !== 'admin') {
                throw new Exception("Unauthorized: Admin access required");
            }
            $this->userService->deleteAccount($id);
            $this->sendResponse(['message' => 'User deleted successfully']);
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 400, $e);
        }
    }

    public function updateUser($id) {
        try {
            $user = Flight::get('user');
            if (!$user || $user['role'] !== 'admin') {
                throw new Exception("Unauthorized: Admin access required");
            }
            $data = json_decode(json_encode(Flight::request()->data->getData()));
            $updatedUser = $this->userService->updateProfile($id, $data);
            $this->sendResponse($updatedUser);
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 400, $e);
        }
    }

    public function updateUserPassword($id) {
        try {
            $user = Flight::get('user');
            if (!$user || $user['role'] !== 'admin') {
                throw new Exception("Unauthorized: Admin access required");
            }
            $data = Flight::request()->data;
            if (empty($data->password)) {
                throw new Exception("Password is required");
            }
            // Fetch current user data
            $currentUser = (object) $this->userService->getProfile($id);
            $currentUser->password = $data->password;
            $this->userService->updateProfile($id, $currentUser);
            $this->sendResponse(["message" => "Password updated successfully"]);
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 400, $e);
        }
    }
}
?> 