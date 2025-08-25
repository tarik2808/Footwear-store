<?php
require_once __DIR__ . '/../config/database.php';

class OrderDAO {
    private $conn;
    private $table_name = "orders";
    private $items_table = "order_items";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Validate order input
    private function validateOrder($order) {
        if (empty($order->user_id)) {
            throw new Exception("User ID is required");
        }
        if (empty($order->items) || !is_array($order->items)) {
            throw new Exception("Order must contain items");
        }
        foreach ($order->items as $item) {
            // Handle both array and object access
            $productId = is_array($item) ? $item['product_id'] : $item->product_id;
            $quantity = is_array($item) ? $item['quantity'] : $item->quantity;
            
            if (empty($productId)) {
                throw new Exception("Product ID is required for all items");
            }
            if (!is_numeric($quantity) || $quantity <= 0) {
                throw new Exception("Quantity must be a positive number for all items");
            }
        }
        return true;
    }

    // Create new order with transaction
    public function create($order) {
        try {
            // Handle Flight Collection object
            if (is_object($order) && method_exists($order, 'getData')) {
                $orderData = $order->getData();
            } else {
                $orderData = $order;
            }
            
            // Validate the converted data
            $this->validateOrder($orderData);
            
            $this->conn->beginTransaction();
            
            // Calculate total price and check stock availability
            $total_price = 0;
            foreach ($orderData->items as $item) {
                // Get product price and check stock availability
                $productId = is_array($item) ? $item['product_id'] : $item->product_id;
                $quantity = is_array($item) ? $item['quantity'] : $item->quantity;
                
                $query = "SELECT price, stock FROM products WHERE id = :id FOR UPDATE";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":id", $productId);
                $stmt->execute();
                
                $product = $stmt->fetch();
                if (!$product) {
                    throw new Exception("Product not found: " . $productId);
                }
                if ($product['stock'] < $quantity) {
                    throw new Exception("Insufficient stock for product: " . $productId);
                }
                
                $total_price += $product['price'] * $quantity;
                
                // Note: Stock will be updated when order status changes to "shipped"
                // This ensures stock is only reduced after admin approval
            }
            
            // Generate unique order number
            $orderNumber = 'ORD-' . date('Ymd') . '-' . str_pad($orderData->user_id, 4, '0', STR_PAD_LEFT) . '-' . time();
            
            // Create order
            $query = "INSERT INTO " . $this->table_name . " 
                     (user_id, order_number, total_price, subtotal, status, payment_method, shipping_name, shipping_address, shipping_phone, shipping_city, shipping_zip, shipping_state, shipping_country) 
                     VALUES (:user_id, :order_number, :total_price, :subtotal, 'pending', :payment_method, :shipping_name, :shipping_address, :shipping_phone, :shipping_city, :shipping_zip, :shipping_state, :shipping_country)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $orderData->user_id);
            $stmt->bindParam(":order_number", $orderNumber);
            $stmt->bindParam(":total_price", $total_price);
            $stmt->bindParam(":subtotal", $total_price); // For now, subtotal = total_price
            $stmt->bindParam(":payment_method", $orderData->payment_method);
            $stmt->bindParam(":shipping_name", $orderData->shipping_name);
            $stmt->bindParam(":shipping_address", $orderData->shipping_address);
            $stmt->bindParam(":shipping_phone", $orderData->shipping_phone);
            $stmt->bindParam(":shipping_city", $orderData->shipping_city);
            $stmt->bindParam(":shipping_zip", $orderData->shipping_zip);
            $shippingState = $orderData->shipping_state ?? '';
            $shippingCountry = $orderData->shipping_country ?? 'United States';
            $stmt->bindParam(":shipping_state", $shippingState);
            $stmt->bindParam(":shipping_country", $shippingCountry);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to create order");
            }
            
            $order_id = $this->conn->lastInsertId();
            
            // Create order items
            foreach ($orderData->items as $item) {
                $productId = is_array($item) ? $item['product_id'] : $item->product_id;
                $quantity = is_array($item) ? $item['quantity'] : $item->quantity;
                $productName = is_array($item) ? ($item['product_name'] ?? 'Unknown Product') : ($item->product_name ?? 'Unknown Product');
                $price = is_array($item) ? $item['price'] : $item->price;
                $size = is_array($item) ? ($item['size'] ?? '') : ($item->size ?? '');
                
                $query = "INSERT INTO " . $this->items_table . " 
                         (order_id, product_id, product_name, product_price, quantity, selected_size) 
                         VALUES (:order_id, :product_id, :product_name, :product_price, :quantity, :selected_size)";
                
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":order_id", $order_id);
                $stmt->bindParam(":product_id", $productId);
                $stmt->bindParam(":product_name", $productName);
                $productPrice = floatval($price);
                $stmt->bindParam(":product_price", $productPrice);
                $stmt->bindParam(":quantity", $quantity);
                $stmt->bindParam(":selected_size", $size);
                
                if (!$stmt->execute()) {
                    throw new Exception("Failed to create order item");
                }
            }
            
            // Clear user's cart if successful
            $query = "DELETE FROM cart WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $orderData->user_id);
            $stmt->execute();
            
            $this->conn->commit();
            return $order_id;
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log("Order creation error: " . $e->getMessage());
            throw $e;
        }
    }

    // Read all orders with pagination and filters
    public function readAll($page = 1, $limit = 10, $filters = array()) {
        try {
            $offset = ($page - 1) * $limit;
            $query = "SELECT o.*, u.name as user_name 
                     FROM " . $this->table_name . " o
                     JOIN users u ON o.user_id = u.id
                     WHERE 1=1";
            
            $params = array();
            
            if (!empty($filters['user_id'])) {
                $query .= " AND o.user_id = :user_id";
                $params[':user_id'] = $filters['user_id'];
            }
            if (!empty($filters['status'])) {
                $query .= " AND o.status = :status";
                $params[':status'] = $filters['status'];
            }
            if (!empty($filters['date_from'])) {
                $query .= " AND o.created_at >= :date_from";
                $params[':date_from'] = $filters['date_from'];
            }
            if (!empty($filters['date_to'])) {
                $query .= " AND o.created_at <= :date_to";
                $params[':date_to'] = $filters['date_to'];
            }
            
            $query .= " ORDER BY o.created_at DESC LIMIT :limit OFFSET :offset";
            $params[':limit'] = $limit;
            $params[':offset'] = $offset;
            
            $stmt = $this->conn->prepare($query);
            foreach($params as $key => $value) {
                if ($key === ':limit' || $key === ':offset') {
                    $stmt->bindValue($key, (int)$value, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key, $value);
                }
            }
            $stmt->execute();
            
            $orders = $stmt->fetchAll();
            
            // For each order, get the items with product images
            foreach ($orders as &$order) {
                $order['items'] = $this->getOrderItemsWithImages($order['id']);
            }
            
            return $orders;
        } catch (Exception $e) {
            error_log("Error reading orders: " . $e->getMessage());
            throw new Exception("Error reading orders");
        }
    }
    
    // Get order items with product images
    private function getOrderItemsWithImages($orderId) {
        try {
            $query = "SELECT oi.*, p.image 
                     FROM " . $this->items_table . " oi
                     LEFT JOIN products p ON oi.product_id = p.id
                     WHERE oi.order_id = :order_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":order_id", $orderId);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting order items with images: " . $e->getMessage());
            return [];
        }
    }

    // Read single order with items
    public function readOne($id) {
        try {
            // Get order details
            $query = "SELECT o.*, u.name as user_name 
                     FROM " . $this->table_name . " o
                     JOIN users u ON o.user_id = u.id
                     WHERE o.id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);
            $stmt->execute();
            
            $order = $stmt->fetch();
            if (!$order) {
                throw new Exception("Order not found");
            }
            
            // Get order items
            $query = "SELECT oi.*, oi.product_name, oi.product_price, oi.selected_size
                     FROM " . $this->items_table . " oi
                     WHERE oi.order_id = :order_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":order_id", $id);
            $stmt->execute();
            
            $order['items'] = $stmt->fetchAll();
            
            return $order;
        } catch (Exception $e) {
            error_log("Error reading order: " . $e->getMessage());
            throw $e;
        }
    }

    // Update order status
    public function updateStatus($id, $status, $user_id) {
        try {
            // Check if order belongs to user or user is admin
            $query = "SELECT user_id FROM " . $this->table_name . " 
                     WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);
            $stmt->execute();
            
            $order = $stmt->fetch();
            if (!$order) {
                throw new Exception("Order not found");
            }
            
            // Only allow status update if user owns the order or is admin
            if ($order['user_id'] != $user_id) {
                $query = "SELECT role FROM users WHERE id = :user_id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":user_id", $user_id);
                $stmt->execute();
                
                $user = $stmt->fetch();
                if ($user['role'] != 'admin') {
                    throw new Exception("Access denied");
                }
            }
            
            $query = "UPDATE " . $this->table_name . " 
                     SET status = :status 
                     WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":status", $status);
            $stmt->bindParam(":id", $id);

            if($stmt->execute()) {
                // If status is being changed to "shipped", update product stock
                if (strtolower($status) === 'shipped') {
                    $this->updateStockForShippedOrder($id);
                }
                return true;
            }
            return false;
        } catch (Exception $e) {
            error_log("Error updating order status: " . $e->getMessage());
            throw $e;
        }
    }

    // Get total count with filters
    public function getTotalCount($filters = array()) {
        try {
            $query = "SELECT COUNT(*) as total 
                     FROM " . $this->table_name . " o
                     WHERE 1=1";
            
            $params = array();
            
            if (!empty($filters['user_id'])) {
                $query .= " AND o.user_id = :user_id";
                $params[':user_id'] = $filters['user_id'];
            }
            if (!empty($filters['status'])) {
                $query .= " AND o.status = :status";
                $params[':status'] = $filters['status'];
            }
            if (!empty($filters['date_from'])) {
                $query .= " AND o.created_at >= :date_from";
                $params[':date_from'] = $filters['date_from'];
            }
            if (!empty($filters['date_to'])) {
                $query .= " AND o.created_at <= :date_to";
                $params[':date_to'] = $filters['date_to'];
            }
            
            $stmt = $this->conn->prepare($query);
            foreach($params as $key => $value) {
                if ($key === ':limit' || $key === ':offset') {
                    $stmt->bindValue($key, (int)$value, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key, $value);
                }
            }
            $stmt->execute();
            
            return $stmt->fetch()['total'];
        } catch (Exception $e) {
            error_log("Error getting order count: " . $e->getMessage());
            throw new Exception("Error getting order count");
        }
    }


    
    // Update stock when order is marked as shipped
    private function updateStockForShippedOrder($orderId) {
        try {
            // Get order items
            $query = "SELECT product_id, quantity FROM " . $this->items_table . " WHERE order_id = :order_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":order_id", $orderId);
            $stmt->execute();
            
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Update stock for each item
            foreach ($items as $item) {
                $query = "UPDATE products 
                         SET stock = stock - :quantity 
                         WHERE id = :product_id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":quantity", $item['quantity']);
                $stmt->bindParam(":product_id", $item['product_id']);
                
                if (!$stmt->execute()) {
                    error_log("Failed to update stock for product ID: " . $item['product_id']);
                }
            }
            
            error_log("Stock updated for shipped order ID: " . $orderId);
        } catch (Exception $e) {
            error_log("Error updating stock for shipped order: " . $e->getMessage());
        }
    }
}
?> 