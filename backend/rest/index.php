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

// Initialize controllers and middleware
$userController = new UserController();
$productController = new ProductController();
$categoryController = new CategoryController();
$cartController = new CartController();
$orderController = new OrderController();
$authMiddleware = new AuthMiddleware();

// User routes
Flight::route('POST /api/users/register', [$userController, 'register']);
Flight::route('POST /api/users/login', [$userController, 'login']);
Flight::route('GET /api/users/profile', [$userController, 'getProfile'], true, [$authMiddleware, 'authenticate']);
Flight::route('PUT /api/users/profile', [$userController, 'updateProfile'], true, [$authMiddleware, 'authenticate']);
Flight::route('DELETE /api/users/profile', [$userController, 'deleteAccount'], true, [$authMiddleware, 'authenticate']);
Flight::route('DELETE /api/users/@id', function($id) use ($userController, $authMiddleware) {
    if ($authMiddleware->requireAdmin()) {
        $userController->deleteUser($id);
    }
});
Flight::route('PUT /api/users/@id', function($id) use ($userController, $authMiddleware) {
    if ($authMiddleware->requireAdmin()) {
        $userController->updateUser($id);
    }
});
Flight::route('PUT /api/users/@id/password', function($id) use ($userController, $authMiddleware) {
    if ($authMiddleware->requireAdmin()) {
        $userController->updateUserPassword($id);
    }
});

// Admin routes
Flight::route('GET /api/users', function() use ($userController, $authMiddleware) {
    if ($authMiddleware->requireAdmin()) {
        $userController->listUsers();
    }
});

// Product routes
Flight::route('GET /api/products/@id', [$productController, 'getProduct']);
Flight::route('GET /api/products', [$productController, 'listProducts']);

// Admin product routes
Flight::route('POST /api/products', function() use ($productController, $authMiddleware) {
    if ($authMiddleware->requireAdmin()) {
        $productController->createProduct();
    }
});
Flight::route('POST /api/products/@id', function($id) use ($productController, $authMiddleware) {
    if ($authMiddleware->requireAdmin()) {
        $productController->updateProduct($id);
    }
});
Flight::route('PUT /api/products/@id', function($id) use ($productController, $authMiddleware) {
    if ($authMiddleware->requireAdmin()) {
        $productController->updateProduct($id);
    }
});
Flight::route('DELETE /api/products/@id', function($id) use ($productController, $authMiddleware) {
    if ($authMiddleware->requireAdmin()) {
        $productController->deleteProduct($id);
    }
});
Flight::route('PUT /api/products/@id/stock', function($id) use ($productController, $authMiddleware) {
    if ($authMiddleware->requireAdmin()) {
        $productController->updateStock($id);
    }
});
Flight::route('POST /api/products/bulk', function() use ($productController, $authMiddleware) {
    if ($authMiddleware->requireAdmin()) {
        $productController->bulkCreateProducts();
    }
});
Flight::route('PUT /api/products/bulk', function() use ($productController, $authMiddleware) {
    if ($authMiddleware->requireAdmin()) {
        $productController->bulkUpdateProducts();
    }
});

// Category routes
Flight::route('GET /api/categories/@id', [$categoryController, 'getCategory']);
Flight::route('GET /api/categories', [$categoryController, 'listCategories']);
Flight::route('GET /api/categories/with-products', [$categoryController, 'getCategoriesWithProductCount']);

// Admin category routes
Flight::route('POST /api/categories', function() use ($categoryController, $authMiddleware) {
    if ($authMiddleware->requireAdmin()) {
        $categoryController->createCategory();
    }
});
Flight::route('PUT /api/categories/@id', function($id) use ($categoryController, $authMiddleware) {
    if ($authMiddleware->requireAdmin()) {
        $categoryController->updateCategory($id);
    }
});
Flight::route('DELETE /api/categories/@id', function($id) use ($categoryController, $authMiddleware) {
    if ($authMiddleware->requireAdmin()) {
        $categoryController->deleteCategory($id);
    }
});

// Cart routes
Flight::route('POST /api/cart/items', function() use ($cartController, $authMiddleware) {
    if ($authMiddleware->authenticate()) {
        $cartController->addToCart();
    }
});
Flight::route('GET /api/cart', function() use ($cartController, $authMiddleware) {
    if ($authMiddleware->authenticate()) {
        $cartController->getCart();
    }
});
Flight::route('PUT /api/cart/items/@id', function($id) use ($cartController, $authMiddleware) {
    if ($authMiddleware->authenticate()) {
        $cartController->updateQuantity($id);
    }
});
Flight::route('DELETE /api/cart/items/@id', function($id) use ($cartController, $authMiddleware) {
    if ($authMiddleware->authenticate()) {
        $cartController->removeItem($id);
    }
});
Flight::route('DELETE /api/cart', function() use ($cartController, $authMiddleware) {
    if ($authMiddleware->authenticate()) {
        $cartController->clearCart();
    }
});

// Order routes
Flight::route('POST /api/orders', [$orderController, 'createOrder'], true, [$authMiddleware, 'authenticate']);
Flight::route('GET /api/orders/@id', [$orderController, 'getOrder'], true, [$authMiddleware, 'authenticate']);
Flight::route('GET /api/orders/user', [$orderController, 'getUserOrders'], true, [$authMiddleware, 'authenticate']);

// Admin order routes
Flight::route('GET /api/orders', function() use ($orderController, $authMiddleware) {
    if ($authMiddleware->requireAdmin()) {
        $orderController->listOrders();
    }
});
Flight::route('PUT /api/orders/@id/status', function($id) use ($orderController, $authMiddleware) {
    if ($authMiddleware->requireAdmin()) {
        $orderController->updateOrderStatus($id);
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