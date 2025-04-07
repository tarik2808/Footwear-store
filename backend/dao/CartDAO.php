<?php
require_once __DIR__ . '/../config/database.php';

class CartDAO {
    private $conn;
    private $table_name = "cart";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Add item to cart
    public function addToCart($user_id, $product_id, $quantity) {
        // First check if item already exists in cart
        $check_query = "SELECT * FROM " . $this->table_name . " 
                       WHERE user_id = :user_id AND product_id = :product_id";
        
        $check_stmt = $this->conn->prepare($check_query);
        $check_stmt->bindParam(":user_id", $user_id);
        $check_stmt->bindParam(":product_id", $product_id);
        $check_stmt->execute();

        if($check_stmt->rowCount() > 0) {
            // Update quantity if item exists
            $query = "UPDATE " . $this->table_name . " 
                     SET quantity = quantity + :quantity 
                     WHERE user_id = :user_id AND product_id = :product_id";
        } else {
            // Insert new item if it doesn't exist
            $query = "INSERT INTO " . $this->table_name . " 
                     (user_id, product_id, quantity) 
                     VALUES (:user_id, :product_id, :quantity)";
        }

        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":product_id", $product_id);
        $stmt->bindParam(":quantity", $quantity);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Get cart items for a user
    public function getCartItems($user_id) {
        $query = "SELECT c.*, p.name, p.price, p.stock 
                 FROM " . $this->table_name . " c
                 JOIN products p ON c.product_id = p.id
                 WHERE c.user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        return $stmt;
    }

    // Update cart item quantity
    public function updateQuantity($user_id, $product_id, $quantity) {
        $query = "UPDATE " . $this->table_name . " 
                 SET quantity = :quantity 
                 WHERE user_id = :user_id AND product_id = :product_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":quantity", $quantity);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":product_id", $product_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Remove item from cart
    public function removeFromCart($user_id, $product_id) {
        $query = "DELETE FROM " . $this->table_name . " 
                 WHERE user_id = :user_id AND product_id = :product_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":product_id", $product_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Clear cart for a user
    public function clearCart($user_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?> 