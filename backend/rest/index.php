<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
require 'vendor/autoload.php';
require_once dirname(__DIR__) . '/config/Database.php';
require_once dirname(__DIR__) . '/controllers/UserController.php';
require_once dirname(__DIR__) . '/controllers/ProductController.php';
require_once dirname(__DIR__) . '/controllers/CategoryController.php';
require_once dirname(__DIR__) . '/controllers/CartController.php';
require_once dirname(__DIR__) . '/controllers/OrderController.php';
require_once dirname(__DIR__) . '/rest/middleware/AuthMiddleware.php';

// Initialize middleware only (controllers will be created when needed)
$authMiddleware = new AuthMiddleware();

// User routes
Flight::route('POST /api/users/register', function() {
    $controller = new UserController();
    $controller->register();
});
Flight::route('POST /api/users/login', function() {
    $controller = new UserController();
    $controller->login();
});
Flight::route('GET /api/users/profile', function() use ($authMiddleware) {
    if ($authMiddleware->authenticate()) {
        $controller = new UserController();
        $controller->getProfile();
    }
});
Flight::route('PUT /api/users/profile', function() use ($authMiddleware) {
    if ($authMiddleware->authenticate()) {
        $controller = new UserController();
        $controller->updateProfile();
    }
});
Flight::route('DELETE /api/users/profile', function() use ($authMiddleware) {
    if ($authMiddleware->authenticate()) {
        $controller = new UserController();
        $controller->deleteAccount();
    }
});
Flight::route('DELETE /api/users/@id', function($id) use ($authMiddleware) {
    if ($authMiddleware->requireAdmin()) {
        $controller = new UserController();
        $controller->deleteUser($id);
    }
});
Flight::route('PUT /api/users/@id', function($id) use ($authMiddleware) {
    if ($authMiddleware->requireAdmin()) {
        $controller = new UserController();
        $controller->updateUser($id);
    }
});
Flight::route('PUT /api/users/@id/password', function($id) use ($authMiddleware) {
    if ($authMiddleware->requireAdmin()) {
        $controller = new UserController();
        $controller->updateUserPassword($id);
    }
});

// Admin routes
Flight::route('GET /api/users', function() use ($authMiddleware) {
    if ($authMiddleware->requireAdmin()) {
        $controller = new UserController();
        $controller->listUsers();
    }
});

// Product routes
Flight::route('GET /api/products/@id', function($id) {
    $controller = new ProductController();
    $controller->getProduct($id);
});
Flight::route('GET /api/products', function() {
    $controller = new ProductController();
    $controller->listProducts();
});

// Admin product routes
Flight::route('POST /api/products', function() use ($authMiddleware) {
    if ($authMiddleware->requireAdmin()) {
        $controller = new ProductController();
        $controller->createProduct();
    }
});
Flight::route('POST /api/products/@id', function($id) use ($authMiddleware) {
    if ($authMiddleware->requireAdmin()) {
        $controller = new ProductController();
        $controller->updateProduct($id);
    }
});
Flight::route('PUT /api/products/@id', function($id) use ($authMiddleware) {
    if ($authMiddleware->requireAdmin()) {
        $controller = new ProductController();
        $controller->updateProduct($id);
    }
});
Flight::route('DELETE /api/products/@id', function($id) use ($authMiddleware) {
    if ($authMiddleware->requireAdmin()) {
        $controller = new ProductController();
        $controller->deleteProduct($id);
    }
});
Flight::route('PUT /api/products/@id/stock', function($id) use ($authMiddleware) {
    if ($authMiddleware->requireAdmin()) {
        $controller = new ProductController();
        $controller->updateStock($id);
    }
});
Flight::route('POST /api/products/bulk', function() use ($authMiddleware) {
    if ($authMiddleware->requireAdmin()) {
        $controller = new ProductController();
        $controller->bulkCreateProducts();
    }
});
Flight::route('PUT /api/products/bulk', function() use ($authMiddleware) {
    if ($authMiddleware->requireAdmin()) {
        $controller = new ProductController();
        $controller->bulkUpdateProducts();
    }
});

// Category routes
Flight::route('GET /api/categories/@id', function($id) {
    $controller = new CategoryController();
    $controller->getCategory($id);
});
Flight::route('GET /api/categories', function() {
    $controller = new CategoryController();
    $controller->listCategories();
});
Flight::route('GET /api/categories/with-products', function() {
    $controller = new CategoryController();
    $controller->getCategoriesWithProductCount();
});

// Admin category routes
Flight::route('POST /api/categories', function() use ($authMiddleware) {
    if ($authMiddleware->requireAdmin()) {
        $controller = new CategoryController();
        $controller->createCategory();
    }
});
Flight::route('PUT /api/categories/@id', function($id) use ($authMiddleware) {
    if ($authMiddleware->requireAdmin()) {
        $controller = new CategoryController();
        $controller->updateCategory($id);
    }
});
Flight::route('DELETE /api/categories/@id', function($id) use ($authMiddleware) {
    if ($authMiddleware->requireAdmin()) {
        $controller = new CategoryController();
        $controller->deleteCategory($id);
    }
});

// Cart routes
Flight::route('POST /api/cart/items', function() use ($authMiddleware) {
    if ($authMiddleware->authenticate()) {
        $controller = new CartController();
        $controller->addToCart();
    }
});
Flight::route('GET /api/cart', function() use ($authMiddleware) {
    if ($authMiddleware->authenticate()) {
        $controller = new CartController();
        $controller->getCart();
    }
});
Flight::route('PUT /api/cart/items/@id', function($id) use ($authMiddleware) {
    if ($authMiddleware->authenticate()) {
        $controller = new CartController();
        $controller->updateQuantity($id);
    }
});
Flight::route('DELETE /api/cart/items/@id', function($id) use ($authMiddleware) {
    if ($authMiddleware->authenticate()) {
        $controller = new CartController();
        $controller->removeItem($id);
    }
});
Flight::route('DELETE /api/cart', function() use ($authMiddleware) {
    if ($authMiddleware->authenticate()) {
        $controller = new CartController();
        $controller->clearCart();
    }
});

// Order routes
Flight::route('POST /api/orders', function() use ($authMiddleware) {
    if ($authMiddleware->authenticate()) {
        $controller = new OrderController();
        $controller->createOrder();
    }
});
Flight::route('GET /api/orders/@id', function($id) use ($authMiddleware) {
    if ($authMiddleware->authenticate()) {
        $controller = new OrderController();
        $controller->getOrder($id);
    }
});
Flight::route('GET /api/orders/user', function() use ($authMiddleware) {
    if ($authMiddleware->authenticate()) {
        $controller = new OrderController();
        $controller->getUserOrders();
    }
});

// Admin order routes
Flight::route('GET /api/orders', function() use ($authMiddleware) {
    if ($authMiddleware->requireAdmin()) {
        $controller = new OrderController();
        $controller->listOrders();
    }
});
Flight::route('PUT /api/orders/@id/status', function($id) use ($authMiddleware) {
    if ($authMiddleware->requireAdmin()) {
        $controller = new OrderController();
        $controller->updateOrderStatus($id);
    }
});

// Error handling
Flight::map('error', function($ex) {
    error_log($ex->getMessage());
    Flight::json(['error' => $ex->getMessage()], 500);
});

// Start the application
Flight::start();
?>
