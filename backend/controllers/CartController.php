<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/CartService.php';

class CartController extends BaseController {
    private $cartService;

    public function __construct() {
        $this->cartService = new CartService();
    }

    public function addToCart() {
        try {
            $userId = $this->getCurrentUserId();
            $data = Flight::request()->data;
            $this->validateRequiredFields($data, ['product_id', 'quantity']);
            
            $cart = $this->cartService->addToCart($userId, $data['product_id'], $data['quantity']);
            $this->sendResponse($cart);
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }

    public function getCart() {
        try {
            $userId = $this->getCurrentUserId();
            $cart = $this->cartService->getCart($userId);
            $this->sendResponse($cart);
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }

    public function updateQuantity($itemId) {
        try {
            $userId = $this->getCurrentUserId();
            $data = Flight::request()->data;
            $this->validateRequiredFields($data, ['quantity']);
            
            $cart = $this->cartService->updateQuantity($userId, $itemId, $data['quantity']);
            $this->sendResponse($cart);
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }

    public function removeItem($itemId) {
        try {
            $userId = $this->getCurrentUserId();
            $cart = $this->cartService->removeItem($userId, $itemId);
            $this->sendResponse($cart);
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }

    public function clearCart() {
        try {
            $userId = $this->getCurrentUserId();
            $this->cartService->clearCart($userId);
            $this->sendResponse(['message' => 'Cart cleared successfully']);
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }
}
?> 