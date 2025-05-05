<?php
require_once __DIR__ . '/../config/database.php';

class CartDAO {
    private $conn;
    private $table_name = "cart";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Validate cart item input
    private function validateCartItem($item) {
        if (empty($item->user_id)) {
            throw new Exception("User ID is required");
        }
        if (empty($item->product_id)) {
            throw new Exception("Product ID is required");
        }
        if (!is_numeric($item->quantity) || $item->quantity <= 0) {
            throw new Exception("Quantity must be a positive number");
        }
        return true;
    }

    // Add item to cart with transaction
    public function addItem($item) {
        try {
            $this->validateCartItem($item);
            
            $this->conn->beginTransaction();
            
            // Check if product exists and has enough stock
            $query = "SELECT stock FROM products WHERE id = :product_id FOR UPDATE";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":product_id", $item->product_id);
            $stmt->execute();
            
            $product = $stmt->fetch();
            if (!$product) {
                throw new Exception("Product not found");
            }
            if ($product['stock'] < $item->quantity) {
                throw new Exception("Insufficient stock");
            }
            
            // Check if item already exists in cart
            $query = "SELECT id, quantity FROM " . $this->table_name . " 
                     WHERE user_id = :user_id AND product_id = :product_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $item->user_id);
            $stmt->bindParam(":product_id", $item->product_id);
            $stmt->execute();
            
            if ($existingItem = $stmt->fetch()) {
                // Update quantity if item exists
                $newQuantity = $existingItem['quantity'] + $item->quantity;
                if ($product['stock'] < $newQuantity) {
                    throw new Exception("Insufficient stock");
                }
                
                $query = "UPDATE " . $this->table_name . " 
                         SET quantity = :quantity 
                         WHERE id = :id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":quantity", $newQuantity);
                $stmt->bindParam(":id", $existingItem['id']);
            } else {
                // Insert new item if it doesn't exist
                $query = "INSERT INTO " . $this->table_name . " 
                         (user_id, product_id, quantity) 
                         VALUES (:user_id, :product_id, :quantity)";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":user_id", $item->user_id);
                $stmt->bindParam(":product_id", $item->product_id);
                $stmt->bindParam(":quantity", $item->quantity);
            }
            
            if($stmt->execute()) {
                $this->conn->commit();
                return true;
            }
            return false;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Error adding item to cart: " . $e->getMessage());
            throw $e;
        }
    }

    // Get user's cart with product details
    public function getCart($user_id) {
        try {
            $query = "SELECT c.*, p.name, p.price, p.stock, 
                            (p.price * c.quantity) as total_price 
                     FROM " . $this->table_name . " c
                     JOIN products p ON c.product_id = p.id
                     WHERE c.user_id = :user_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting cart: " . $e->getMessage());
            throw new Exception("Error getting cart");
        }
    }

    // Update cart item quantity with transaction
    public function updateQuantity($id, $quantity, $user_id) {
        try {
            if (!is_numeric($quantity) || $quantity <= 0) {
                throw new Exception("Quantity must be a positive number");
            }
            
            $this->conn->beginTransaction();
            
            // Get cart item and check ownership
            $query = "SELECT product_id FROM " . $this->table_name . " 
                     WHERE id = :id AND user_id = :user_id FOR UPDATE";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->execute();
            
            $cartItem = $stmt->fetch();
            if (!$cartItem) {
                throw new Exception("Cart item not found or access denied");
            }
            
            // Check product stock
            $query = "SELECT stock FROM products WHERE id = :product_id FOR UPDATE";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":product_id", $cartItem['product_id']);
            $stmt->execute();
            
            $product = $stmt->fetch();
            if ($product['stock'] < $quantity) {
                throw new Exception("Insufficient stock");
            }
            
            // Update quantity
            $query = "UPDATE " . $this->table_name . " 
                     SET quantity = :quantity 
                     WHERE id = :id AND user_id = :user_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":quantity", $quantity);
            $stmt->bindParam(":id", $id);
            $stmt->bindParam(":user_id", $user_id);

            if($stmt->execute()) {
                $this->conn->commit();
                return true;
            }
            return false;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Error updating cart quantity: " . $e->getMessage());
            throw $e;
        }
    }

    // Remove item from cart
    public function removeItem($id, $user_id) {
        try {
            $query = "DELETE FROM " . $this->table_name . " 
                     WHERE id = :id AND user_id = :user_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);
            $stmt->bindParam(":user_id", $user_id);

            if($stmt->execute()) {
                return true;
            }
            return false;
        } catch (Exception $e) {
            error_log("Error removing cart item: " . $e->getMessage());
            throw new Exception("Error removing cart item");
        }
    }

    // Clear user's cart
    public function clearCart($user_id) {
        try {
            $query = "DELETE FROM " . $this->table_name . " WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $user_id);

            if($stmt->execute()) {
                return true;
            }
            return false;
        } catch (Exception $e) {
            error_log("Error clearing cart: " . $e->getMessage());
            throw new Exception("Error clearing cart");
        }
    }

    // Get cart total
    public function getCartTotal($user_id) {
        try {
            $query = "SELECT SUM(p.price * c.quantity) as total 
                     FROM " . $this->table_name . " c
                     JOIN products p ON c.product_id = p.id
                     WHERE c.user_id = :user_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->execute();
            
            return $stmt->fetch()['total'] ?: 0;
        } catch (Exception $e) {
            error_log("Error getting cart total: " . $e->getMessage());
            throw new Exception("Error getting cart total");
        }
    }
}
?> 