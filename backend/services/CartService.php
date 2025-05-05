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

    // Add item to cart
    public function addToCart($userId, $productId, $quantity) {
        try {
            // Validate input
            if (!is_numeric($quantity) || $quantity <= 0) {
                throw new Exception("Quantity must be a positive number");
            }

            // Check if product exists and has enough stock
            $product = $this->productDAO->readOne($productId);
            if (!$product) {
                throw new Exception("Product not found");
            }

            if ($product['stock'] < $quantity) {
                throw new Exception("Insufficient stock");
            }

            // Create cart item object
            $item = new stdClass();
            $item->user_id = $userId;
            $item->product_id = $productId;
            $item->quantity = $quantity;

            // Add item to cart
            if (!$this->cartDAO->addItem($item)) {
                throw new Exception("Failed to add item to cart");
            }

            return $this->getCart($userId);
        } catch (Exception $e) {
            error_log("Error adding to cart: " . $e->getMessage());
            throw $e;
        }
    }

    // Get user's cart
    public function getCart($userId) {
        try {
            $cart = $this->cartDAO->getCart($userId);
            $total = $this->cartDAO->getCartTotal($userId);

            return [
                'items' => $cart,
                'total' => $total
            ];
        } catch (Exception $e) {
            error_log("Error getting cart: " . $e->getMessage());
            throw $e;
        }
    }

    // Update cart item quantity
    public function updateQuantity($userId, $itemId, $quantity) {
        try {
            // Validate input
            if (!is_numeric($quantity) || $quantity <= 0) {
                throw new Exception("Quantity must be a positive number");
            }

            // Get cart item
            $cart = $this->cartDAO->getCart($userId);
            $item = null;
            foreach ($cart as $cartItem) {
                if ($cartItem['id'] == $itemId) {
                    $item = $cartItem;
                    break;
                }
            }

            if (!$item) {
                throw new Exception("Cart item not found");
            }

            // Check product stock
            $product = $this->productDAO->readOne($item['product_id']);
            if (!$product) {
                throw new Exception("Product not found");
            }

            if ($product['stock'] < $quantity) {
                throw new Exception("Insufficient stock");
            }

            // Update quantity
            if (!$this->cartDAO->updateQuantity($itemId, $quantity, $userId)) {
                throw new Exception("Failed to update quantity");
            }

            return $this->getCart($userId);
        } catch (Exception $e) {
            error_log("Error updating cart quantity: " . $e->getMessage());
            throw $e;
        }
    }

    // Remove item from cart
    public function removeItem($userId, $itemId) {
        try {
            // Get cart item
            $cart = $this->cartDAO->getCart($userId);
            $item = null;
            foreach ($cart as $cartItem) {
                if ($cartItem['id'] == $itemId) {
                    $item = $cartItem;
                    break;
                }
            }

            if (!$item) {
                throw new Exception("Cart item not found");
            }

            // Remove item
            if (!$this->cartDAO->removeItem($itemId, $userId)) {
                throw new Exception("Failed to remove item from cart");
            }

            return $this->getCart($userId);
        } catch (Exception $e) {
            error_log("Error removing cart item: " . $e->getMessage());
            throw $e;
        }
    }

    // Clear user's cart
    public function clearCart($userId) {
        try {
            if (!$this->cartDAO->clearCart($userId)) {
                throw new Exception("Failed to clear cart");
            }

            return true;
        } catch (Exception $e) {
            error_log("Error clearing cart: " . $e->getMessage());
            throw $e;
        }
    }
}
?> 