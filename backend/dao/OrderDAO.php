<?php
require_once __DIR__ . '/../config/database.php';

class OrderDAO {
    private $conn;
    private $table_name = "orders";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Create new order
    public function create($order) {
        try {
            $this->conn->beginTransaction();

            // Insert order
            $query = "INSERT INTO " . $this->table_name . " 
                     (user_id, total_price, status) 
                     VALUES (:user_id, :total_price, :status)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $order->user_id);
            $stmt->bindParam(":total_price", $order->total_price);
            $stmt->bindParam(":status", $order->status);
            $stmt->execute();

            $order_id = $this->conn->lastInsertId();

            // Insert order items
            foreach($order->items as $item) {
                $query = "INSERT INTO order_items 
                         (order_id, product_id, quantity, price) 
                         VALUES (:order_id, :product_id, :quantity, :price)";
                
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":order_id", $order_id);
                $stmt->bindParam(":product_id", $item->product_id);
                $stmt->bindParam(":quantity", $item->quantity);
                $stmt->bindParam(":price", $item->price);
                $stmt->execute();
            }

            $this->conn->commit();
            return $order_id;
        } catch(Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    // Get all orders
    public function readAll() {
        $query = "SELECT o.*, u.name as user_name 
                 FROM " . $this->table_name . " o
                 JOIN users u ON o.user_id = u.id
                 ORDER BY o.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Get orders for a specific user
    public function readByUser($user_id) {
        $query = "SELECT o.*, u.name as user_name 
                 FROM " . $this->table_name . " o
                 JOIN users u ON o.user_id = u.id
                 WHERE o.user_id = :user_id
                 ORDER BY o.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        return $stmt;
    }

    // Get single order with items
    public function readOne($id) {
        $query = "SELECT o.*, u.name as user_name 
                 FROM " . $this->table_name . " o
                 JOIN users u ON o.user_id = u.id
                 WHERE o.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($order) {
            // Get order items
            $query = "SELECT oi.*, p.name as product_name 
                     FROM order_items oi
                     JOIN products p ON oi.product_id = p.id
                     WHERE oi.order_id = :order_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":order_id", $id);
            $stmt->execute();
            
            $order['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        return $order;
    }

    // Update order status
    public function updateStatus($id, $status) {
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
    }

    // Delete order
    public function delete($id) {
        try {
            $this->conn->beginTransaction();

            // Delete order items first
            $query = "DELETE FROM order_items WHERE order_id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);
            $stmt->execute();

            // Delete order
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);
            $stmt->execute();

            $this->conn->commit();
            return true;
        } catch(Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }
}
?> 