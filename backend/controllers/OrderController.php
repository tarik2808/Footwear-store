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
            $data = Flight::request()->data;
            error_log('DEBUG: OrderController createOrder - data: ' . print_r($data, true));
            $this->validateRequiredFields($data, ['shipping_address', 'payment_method', 'cart_items']);
            if (!isset($data->cart_items) || !is_array($data->cart_items) || count($data->cart_items) === 0) {
                throw new Exception('cart_items must be a non-empty array');
            }
            $order = $this->orderService->createOrder($userId, $data->shipping_address, $data->payment_method, $data->cart_items);
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