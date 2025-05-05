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
            $data = Flight::request()->data;
            $this->validateRequiredFields($data, ['name', 'email', 'password']);
            
            $user = $this->userService->register($data);
            $token = $this->authService->generateToken($user['id'], $user['role']);
            
            $this->sendResponse([
                'user' => $user,
                'token' => $token
            ], 201);
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }

    public function login() {
        try {
            $data = Flight::request()->data;
            $this->validateRequiredFields($data, ['email', 'password']);
            
            $result = $this->authService->authenticate($data['email'], $data['password']);
            $this->sendResponse($result);
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }

    public function getProfile() {
        try {
            $userId = $this->getCurrentUserId();
            $user = $this->userService->getProfile($userId);
            $this->sendResponse($user);
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }

    public function updateProfile() {
        try {
            $userId = $this->getCurrentUserId();
            $data = Flight::request()->data;
            
            $user = $this->userService->updateProfile($userId, $data);
            $this->sendResponse($user);
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }

    public function deleteAccount() {
        try {
            $userId = $this->getCurrentUserId();
            $this->userService->deleteAccount($userId);
            $this->sendResponse(['message' => 'Account deleted successfully']);
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }

    public function listUsers() {
        try {
            if (!$this->isAdmin()) {
                throw new Exception("Unauthorized");
            }

            $page = Flight::request()->query['page'] ?? 1;
            $limit = Flight::request()->query['limit'] ?? 10;
            $filters = Flight::request()->query['filters'] ?? [];

            $result = $this->userService->listUsers($page, $limit, $filters);
            $this->sendResponse($result);
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }
}
?> 