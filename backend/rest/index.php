<?php
require 'vendor/autoload.php';
require 'config/database.php';
require 'controllers/UserController.php';
require 'controllers/ProductController.php';
require 'controllers/CategoryController.php';
require 'controllers/CartController.php';
require 'controllers/OrderController.php';

// Initialize controllers
$userController = new UserController();
$productController = new ProductController();
$categoryController = new CategoryController();
$cartController = new CartController();
$orderController = new OrderController();

// User routes
Flight::route('POST /api/users/register', [$userController, 'register']);
Flight::route('POST /api/users/login', [$userController, 'login']);
Flight::route('GET /api/users/profile', [$userController, 'getProfile']);
Flight::route('PUT /api/users/profile', [$userController, 'updateProfile']);
Flight::route('DELETE /api/users/profile', [$userController, 'deleteAccount']);
Flight::route('GET /api/users', [$userController, 'listUsers']);

// Product routes
Flight::route('POST /api/products', [$productController, 'createProduct']);
Flight::route('GET /api/products/@id', [$productController, 'getProduct']);
Flight::route('GET /api/products', [$productController, 'listProducts']);
Flight::route('PUT /api/products/@id', [$productController, 'updateProduct']);
Flight::route('DELETE /api/products/@id', [$productController, 'deleteProduct']);
Flight::route('PUT /api/products/@id/stock', [$productController, 'updateStock']);
Flight::route('POST /api/products/bulk', [$productController, 'bulkCreateProducts']);
Flight::route('PUT /api/products/bulk', [$productController, 'bulkUpdateProducts']);

// Category routes
Flight::route('POST /api/categories', [$categoryController, 'createCategory']);
Flight::route('GET /api/categories/@id', [$categoryController, 'getCategory']);
Flight::route('GET /api/categories', [$categoryController, 'listCategories']);
Flight::route('PUT /api/categories/@id', [$categoryController, 'updateCategory']);
Flight::route('DELETE /api/categories/@id', [$categoryController, 'deleteCategory']);
Flight::route('GET /api/categories/with-products', [$categoryController, 'getCategoriesWithProductCount']);

// Cart routes
Flight::route('POST /api/cart/items', [$cartController, 'addToCart']);
Flight::route('GET /api/cart', [$cartController, 'getCart']);
Flight::route('PUT /api/cart/items/@id', [$cartController, 'updateQuantity']);
Flight::route('DELETE /api/cart/items/@id', [$cartController, 'removeItem']);
Flight::route('DELETE /api/cart', [$cartController, 'clearCart']);

// Order routes
Flight::route('POST /api/orders', [$orderController, 'createOrder']);
Flight::route('GET /api/orders/@id', [$orderController, 'getOrder']);
Flight::route('GET /api/orders', [$orderController, 'listOrders']);
Flight::route('PUT /api/orders/@id/status', [$orderController, 'updateOrderStatus']);
Flight::route('GET /api/orders/user', [$orderController, 'getUserOrders']);

// Error handling
Flight::map('error', function(Exception $ex) {
    error_log($ex->getMessage());
    Flight::json(['error' => $ex->getMessage()], 500);
});

// Start the application
Flight::start();
?> 