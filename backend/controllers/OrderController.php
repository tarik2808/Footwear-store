<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/OrderService.php';

class OrderController extends BaseController {
    private $orderService;

    public function __construct() {
        $this->orderService = new OrderService();
    }

    public function createOrder() {
        error_log('DEBUG: OrderController createOrder - method called');
        try {
            $user = Flight::get('user');
            $userId = $user['id'];
            
            // Try to get data from Flight first
            $data = Flight::request()->data;
            error_log('DEBUG: Flight request data: ' . print_r($data, true));
            
            // If Flight data is empty, manually read the request body
            if (empty($data)) {
                error_log('DEBUG: Flight data is empty, manually reading request body');
                $rawInput = file_get_contents('php://input');
                error_log('DEBUG: Raw input: ' . $rawInput);
                
                if (!empty($rawInput)) {
                    $data = json_decode($rawInput);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        error_log('DEBUG: Successfully parsed JSON manually');
                    } else {
                        error_log('DEBUG: JSON parse error: ' . json_last_error_msg());
                        throw new Exception('Invalid JSON data: ' . json_last_error_msg());
                    }
                } else {
                    error_log('DEBUG: No raw input available');
                    throw new Exception('No request body received');
                }
            }
            
            error_log('DEBUG: Final data: ' . print_r($data, true));
            $this->validateRequiredFields($data, ['shipping_name', 'shipping_address', 'shipping_phone', 'shipping_city', 'shipping_zip', 'payment_method', 'items']);
            if (!isset($data->items) || !is_array($data->items) || count($data->items) === 0) {
                throw new Exception('items must be a non-empty array');
            }
            $order = $this->orderService->createOrder(
                $userId,
                $data->shipping_name,
                $data->shipping_address,
                $data->shipping_phone,
                $data->shipping_city,
                $data->shipping_zip,
                $data->payment_method,
                $data->items
            );
            $this->sendResponse($order, 201);
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 400, $e);
        }
    }

    public function getOrder($id) {
        try {
            $user = Flight::get('user');
            $userId = $user['id'];
            $userRole = $user['role'];
            
            $order = $this->orderService->getOrder($id);
            if ($userRole !== 'admin' && $order['user_id'] != $userId) {
                throw new Exception("Unauthorized");
            }
            
            $this->sendResponse($order);
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }

    public function listOrders() {
        try {
            $user = Flight::get('user');
            $userId = $user['id'];
            $userRole = $user['role'];
            
            $page = Flight::request()->query['page'] ?? 1;
            $limit = Flight::request()->query['limit'] ?? 10;
            $filters = Flight::request()->query['filters'] ?? [];

            if ($userRole !== 'admin') {
                $filters['user_id'] = $userId;
            }

            $result = $this->orderService->listOrders($page, $limit, $filters);
            $this->sendResponse($result);
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }

    public function updateOrderStatus($id) {
        try {
            $user = Flight::get('user');
            $userId = $user['id'];
            $userRole = $user['role'];
            
            $data = Flight::request()->data;
            $this->validateRequiredFields($data, ['status']);
            
            $order = $this->orderService->updateOrderStatus($id, $data['status'], $userId, $userRole);
            $this->sendResponse($order);
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }

    public function getUserOrders() {
        try {
            $user = Flight::get('user');
            $userId = $user['id'];
            $page = Flight::request()->query['page'] ?? 1;
            $limit = Flight::request()->query['limit'] ?? 10;

            $result = $this->orderService->getUserOrders($userId, $page, $limit);
            $this->sendResponse($result);
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }
}
?> 