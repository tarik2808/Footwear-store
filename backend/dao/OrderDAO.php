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
            if (empty($item->product_id)) {
                throw new Exception("Product ID is required for all items");
            }
            if (!is_numeric($item->quantity) || $item->quantity <= 0) {
                throw new Exception("Quantity must be a positive number for all items");
            }
        }
        return true;
    }

    // Create new order with transaction
    public function create($order) {
        try {
            $this->validateOrder($order);
            
            $this->conn->beginTransaction();
            
            // Calculate total price and check stock
            $total_price = 0;
            foreach ($order->items as $item) {
                // Get product price and check stock
                $query = "SELECT price, stock FROM products WHERE id = :id FOR UPDATE";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":id", $item->product_id);
                $stmt->execute();
                
                $product = $stmt->fetch();
                if (!$product) {
                    throw new Exception("Product not found: " . $item->product_id);
                }
                if ($product['stock'] < $item->quantity) {
                    throw new Exception("Insufficient stock for product: " . $item->product_id);
                }
                
                $total_price += $product['price'] * $item->quantity;
                
                // Update stock
                $query = "UPDATE products 
                         SET stock = stock - :quantity 
                         WHERE id = :id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":quantity", $item->quantity);
                $stmt->bindParam(":id", $item->product_id);
                if (!$stmt->execute()) {
                    throw new Exception("Failed to update stock");
                }
            }
            
            // Create order
            $query = "INSERT INTO " . $this->table_name . " 
                     (user_id, total_price, status) 
                     VALUES (:user_id, :total_price, 'pending')";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $order->user_id);
            $stmt->bindParam(":total_price", $total_price);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to create order");
            }
            
            $order_id = $this->conn->lastInsertId();
            
            // Create order items
            foreach ($order->items as $item) {
                $query = "INSERT INTO " . $this->items_table . " 
                         (order_id, product_id, quantity, price) 
                         VALUES (:order_id, :product_id, :quantity, 
                                (SELECT price FROM products WHERE id = :product_id))";
                
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":order_id", $order_id);
                $stmt->bindParam(":product_id", $item->product_id);
                $stmt->bindParam(":quantity", $item->quantity);
                
                if (!$stmt->execute()) {
                    throw new Exception("Failed to create order item");
                }
            }
            
            // Clear user's cart if successful
            $query = "DELETE FROM cart WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $order->user_id);
            $stmt->execute();
            
            $this->conn->commit();
            return $order_id;
        } catch (Exception $e) {
            $this->conn->rollBack();
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
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error reading orders: " . $e->getMessage());
            throw new Exception("Error reading orders");
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
            $query = "SELECT oi.*, p.name as product_name 
                     FROM " . $this->items_table . " oi
                     JOIN products p ON oi.product_id = p.id
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
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            
            return $stmt->fetch()['total'];
        } catch (Exception $e) {
            error_log("Error getting order count: " . $e->getMessage());
            throw new Exception("Error getting order count");
        }
    }

    // Get user's order history
    public function getUserOrders($user_id, $page = 1, $limit = 10) {
        try {
            $offset = ($page - 1) * $limit;
            $query = "SELECT o.*, 
                            (SELECT COUNT(*) FROM " . $this->items_table . " WHERE order_id = o.id) as items_count 
                     FROM " . $this->table_name . " o
                     WHERE o.user_id = :user_id 
                     ORDER BY o.created_at DESC 
                     LIMIT :limit OFFSET :offset";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
            $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting user orders: " . $e->getMessage());
            throw new Exception("Error getting user orders");
        }
    }
}
?> 