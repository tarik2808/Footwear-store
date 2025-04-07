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

    public function createOrder($user_id) {
        // Get cart items
        $cartItems = $this->cartDAO->getCartItems($user_id);
        if($cartItems->rowCount() == 0) {
            return array("success" => false, "message" => "Cart is empty");
        }

        // Calculate total price
        $total_price = 0;
        $items = array();
        
        while($row = $cartItems->fetch(PDO::FETCH_ASSOC)) {
            $total_price += $row['price'] * $row['quantity'];
            
            // Check stock
            $product = $this->productDAO->readOne($row['product_id']);
            $productData = $product->fetch(PDO::FETCH_ASSOC);
            
            if($productData['stock'] < $row['quantity']) {
                return array(
                    "success" => false, 
                    "message" => "Not enough stock for product: " . $productData['name']
                );
            }
            
            // Add to order items
            $items[] = array(
                "product_id" => $row['product_id'],
                "quantity" => $row['quantity'],
                "price" => $row['price']
            );
        }

        // Create order object
        $order = new stdClass();
        $order->user_id = $user_id;
        $order->total_price = $total_price;
        $order->status = "pending";
        $order->items = $items;

        // Create order
        $order_id = $this->orderDAO->create($order);
        
        if($order_id) {
            // Update stock
            foreach($items as $item) {
                $this->productDAO->updateStock($item['product_id'], $item['quantity']);
            }
            
            // Clear cart
            $this->cartDAO->clearCart($user_id);
            
            return array(
                "success" => true, 
                "message" => "Order created successfully",
                "order_id" => $order_id
            );
        }
        
        return array("success" => false, "message" => "Failed to create order");
    }

    public function getAllOrders() {
        $result = $this->orderDAO->readAll();
        $orders = array();
        
        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $orders[] = $row;
        }
        
        return array("success" => true, "orders" => $orders);
    }

    public function getOrdersByUser($user_id) {
        $result = $this->orderDAO->readByUser($user_id);
        $orders = array();
        
        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $orders[] = $row;
        }
        
        return array("success" => true, "orders" => $orders);
    }

    public function getOrderById($id) {
        $order = $this->orderDAO->readOne($id);
        
        if($order) {
            return array("success" => true, "order" => $order);
        }
        
        return array("success" => false, "message" => "Order not found");
    }

    public function updateOrderStatus($id, $status) {
        // Validate status
        $valid_statuses = array("pending", "processing", "shipped", "delivered", "cancelled");
        if(!in_array($status, $valid_statuses)) {
            return array("success" => false, "message" => "Invalid status");
        }

        if($this->orderDAO->updateStatus($id, $status)) {
            return array("success" => true, "message" => "Order status updated successfully");
        }
        return array("success" => false, "message" => "Failed to update order status");
    }

    public function deleteOrder($id) {
        if($this->orderDAO->delete($id)) {
            return array("success" => true, "message" => "Order deleted successfully");
        }
        return array("success" => false, "message" => "Failed to delete order");
    }
}
?> 