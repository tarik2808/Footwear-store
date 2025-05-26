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
    public function createOrder($userId, $shippingAddress, $paymentMethod) {
        try {
            // Validate input
            if (empty($shippingAddress) || empty($paymentMethod)) {
                throw new Exception("Shipping address and payment method are required");
            }

            // Get user's cart
            $cart = $this->cartDAO->getCart($userId);
            if (empty($cart)) {
                throw new Exception("Cart is empty");
            }

            // Calculate total
            $total = $this->cartDAO->getCartTotal($userId);

            // Create order object
            $order = new stdClass();
            $order->user_id = $userId;
            $order->shipping_address = $shippingAddress;
            $order->payment_method = $paymentMethod;
            $order->total_amount = $total;
            $order->status = 'pending';

            // Create order items
            $orderItems = [];
            foreach ($cart as $item) {
                $orderItem = new stdClass();
                $orderItem->product_id = $item['product_id'];
                $orderItem->quantity = $item['quantity'];
                $orderItem->price = $item['price'];
                $orderItems[] = $orderItem;
            }

            // Create order
            $orderId = $this->orderDAO->create($order, $orderItems);
            if (!$orderId) {
                throw new Exception("Failed to create order");
            }

            // Clear cart
            $this->cartDAO->clearCart($userId);

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

            // Update status
            if (!$this->orderDAO->updateStatus($orderId, $status)) {
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