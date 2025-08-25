<?php
require_once __DIR__ . '/../dao/OrderDAO.php';
require_once __DIR__ . '/../dao/CartDAO.php';
require_once __DIR__ . '/../dao/ProductDAO.php';

class OrderService {
    private $orderDAO;
    private $cartDAO;
    private $productDAO;

    public function __construct() {
        $this->orderDAO = new OrderDAO();
        $this->cartDAO = new CartDAO();
        $this->productDAO = new ProductDAO();
    }

    // Create a new order
    public function createOrder($userId, $shippingName, $shippingAddress, $shippingPhone, $shippingCity, $shippingZip, $paymentMethod, $cartItems) {
        try {
            // Validate input
            if (empty($shippingName) || empty($shippingAddress) || empty($shippingPhone) || empty($shippingCity) || empty($shippingZip) || empty($paymentMethod)) {
                throw new Exception("All shipping fields and payment method are required");
            }
            if (empty($cartItems) || !is_array($cartItems)) {
                throw new Exception("Cart items are required");
            }

            // Create order object
            $order = new stdClass();
            $order->user_id = $userId;
            $order->shipping_name = $shippingName;
            $order->shipping_address = $shippingAddress;
            $order->shipping_phone = $shippingPhone;
            $order->shipping_city = $shippingCity;
            $order->shipping_zip = $shippingZip;
            $order->payment_method = $paymentMethod;
            $order->items = $cartItems;
            $order->shipping_state = ''; // Default empty
            $order->shipping_country = 'United States'; // Default

            // Create order
            $orderId = $this->orderDAO->create($order);
            if (!$orderId) {
                throw new Exception("Failed to create order");
            }

            return $this->getOrder($orderId);
        } catch (Exception $e) {
            error_log("Error creating order: " . $e->getMessage());
            throw $e;
        }
    }

    // Get order by ID
    public function getOrder($orderId) {
        try {
            $order = $this->orderDAO->readOne($orderId);
            if (!$order) {
                throw new Exception("Order not found");
            }

            return $order;
        } catch (Exception $e) {
            error_log("Error getting order: " . $e->getMessage());
            throw $e;
        }
    }

    // List orders with pagination and filters
    public function listOrders($page = 1, $limit = 10, $filters = []) {
        try {
            $orders = $this->orderDAO->readAll($page, $limit, $filters);
            $total = $this->orderDAO->getTotalCount($filters);

            return [
                'orders' => $orders,
                'total' => $total,
                'page' => $page,
                'limit' => $limit
            ];
        } catch (Exception $e) {
            error_log("Error listing orders: " . $e->getMessage());
            throw $e;
        }
    }

    // Update order status
    public function updateOrderStatus($orderId, $status, $userId, $userRole) {
        try {
            // Validate status
            $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
            if (!in_array($status, $validStatuses)) {
                throw new Exception("Invalid status");
            }

            // Get order
            $order = $this->orderDAO->readOne($orderId);
            if (!$order) {
                throw new Exception("Order not found");
            }

            // Check permissions
            if ($userRole !== 'admin' && $order['user_id'] != $userId) {
                throw new Exception("Unauthorized to update this order");
            }

            // Update status (stock will be updated automatically in DAO if status is "shipped")
            if (!$this->orderDAO->updateStatus($orderId, $status, $userId)) {
                throw new Exception("Failed to update order status");
            }

            return $this->getOrder($orderId);
        } catch (Exception $e) {
            error_log("Error updating order status: " . $e->getMessage());
            throw $e;
        }
    }

    // Get user's order history
    public function getUserOrders($userId, $page = 1, $limit = 10) {
        try {
            $filters = ['user_id' => $userId];
            return $this->listOrders($page, $limit, $filters);
        } catch (Exception $e) {
            error_log("Error getting user orders: " . $e->getMessage());
            throw $e;
        }
    }
}
?> 