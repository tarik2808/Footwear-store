<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/OrderService.php';

class OrderController extends BaseController {
    private $orderService;

    public function __construct() {
        $this->orderService = new OrderService();
    }

    public function createOrder() {
        try {
            $userId = $this->getCurrentUserId();
            $data = Flight::request()->data;
            $this->validateRequiredFields($data, ['shipping_address', 'payment_method']);
            
            $order = $this->orderService->createOrder($userId, $data['shipping_address'], $data['payment_method']);
            $this->sendResponse($order, 201);
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }

    public function getOrder($id) {
        try {
            $userId = $this->getCurrentUserId();
            $userRole = $this->isAdmin() ? 'admin' : 'user';
            
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
            $userId = $this->getCurrentUserId();
            $userRole = $this->isAdmin() ? 'admin' : 'user';
            
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
            $userId = $this->getCurrentUserId();
            $userRole = $this->isAdmin() ? 'admin' : 'user';
            
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
            $userId = $this->getCurrentUserId();
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