<?php
require_once __DIR__ . '/../dao/CartDAO.php';
require_once __DIR__ . '/../dao/ProductDAO.php';

class CartService {
    private $cartDAO;
    private $productDAO;

    public function __construct() {
        $this->cartDAO = new CartDAO();
        $this->productDAO = new ProductDAO();
    }

    public function addToCart($user_id, $product_id, $quantity) {
        // Validate input
        if(empty($user_id) || empty($product_id) || empty($quantity)) {
            return array("success" => false, "message" => "All fields are required");
        }

        // Validate quantity
        if(!is_numeric($quantity) || $quantity <= 0) {
            return array("success" => false, "message" => "Invalid quantity");
        }

        // Check if product exists and has enough stock
        $product = $this->productDAO->readOne($product_id);
        if($product->rowCount() == 0) {
            return array("success" => false, "message" => "Product not found");
        }

        $productData = $product->fetch(PDO::FETCH_ASSOC);
        if($productData['stock'] < $quantity) {
            return array("success" => false, "message" => "Not enough stock available");
        }

        if($this->cartDAO->addToCart($user_id, $product_id, $quantity)) {
            return array("success" => true, "message" => "Item added to cart successfully");
        }
        return array("success" => false, "message" => "Failed to add item to cart");
    }

    public function getCartItems($user_id) {
        if(empty($user_id)) {
            return array("success" => false, "message" => "User ID is required");
        }

        $result = $this->cartDAO->getCartItems($user_id);
        $items = array();
        $total = 0;
        
        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $row['subtotal'] = $row['price'] * $row['quantity'];
            $total += $row['subtotal'];
            $items[] = $row;
        }
        
        return array(
            "success" => true, 
            "items" => $items,
            "total" => $total
        );
    }

    public function updateQuantity($user_id, $product_id, $quantity) {
        // Validate input
        if(empty($user_id) || empty($product_id) || empty($quantity)) {
            return array("success" => false, "message" => "All fields are required");
        }

        // Validate quantity
        if(!is_numeric($quantity) || $quantity <= 0) {
            return array("success" => false, "message" => "Invalid quantity");
        }

        // Check if product exists and has enough stock
        $product = $this->productDAO->readOne($product_id);
        if($product->rowCount() == 0) {
            return array("success" => false, "message" => "Product not found");
        }

        $productData = $product->fetch(PDO::FETCH_ASSOC);
        if($productData['stock'] < $quantity) {
            return array("success" => false, "message" => "Not enough stock available");
        }

        if($this->cartDAO->updateQuantity($user_id, $product_id, $quantity)) {
            return array("success" => true, "message" => "Cart updated successfully");
        }
        return array("success" => false, "message" => "Failed to update cart");
    }

    public function removeFromCart($user_id, $product_id) {
        if(empty($user_id) || empty($product_id)) {
            return array("success" => false, "message" => "User ID and Product ID are required");
        }

        if($this->cartDAO->removeFromCart($user_id, $product_id)) {
            return array("success" => true, "message" => "Item removed from cart successfully");
        }
        return array("success" => false, "message" => "Failed to remove item from cart");
    }

    public function clearCart($user_id) {
        if(empty($user_id)) {
            return array("success" => false, "message" => "User ID is required");
        }

        if($this->cartDAO->clearCart($user_id)) {
            return array("success" => true, "message" => "Cart cleared successfully");
        }
        return array("success" => false, "message" => "Failed to clear cart");
    }

    public function getCartTotal($user_id) {
        if(empty($user_id)) {
            return array("success" => false, "message" => "User ID is required");
        }

        $result = $this->cartDAO->getCartItems($user_id);
        $total = 0;
        
        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $total += $row['price'] * $row['quantity'];
        }
        
        return array(
            "success" => true,
            "total" => $total
        );
    }
}
?> 