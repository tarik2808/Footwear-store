<?php
require_once __DIR__ . '/rest/vendor/autoload.php';
require_once __DIR__ . '/controllers/AuthController.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get the request path
$request_uri = $_SERVER['REQUEST_URI'];
$base_path = '/FootwearStore%20Tarik%20Coralic/backend/api.php';
$path = parse_url($request_uri, PHP_URL_PATH);
$path = str_replace($base_path, '', $path);
$path = trim($path, '/');

// Debug information
error_log("Request URI: " . $request_uri);
error_log("Base Path: " . $base_path);
error_log("Path: " . $path);

// Split the path into segments
$segments = explode('/', $path);
$resource = $segments[0] ?? '';

// Debug information
error_log("Segments: " . print_r($segments, true));
error_log("Resource: " . $resource);

// Initialize the AuthController
$authController = new AuthController();

// Handle login request
if ($resource === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!isset($data['email']) || !isset($data['password'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email and password are required']);
        exit();
    }
    
    $result = $authController->login($data['email'], $data['password']);
    echo json_encode($result);
    exit();
}

// Handle logout request
if ($resource === 'logout' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $authController->logout();
    echo json_encode($result);
    exit();
}

// Route the request to the appropriate handler
switch($resource) {
    case 'user':
        require_once __DIR__ . '/routes/user_routes.php';
        break;
        
    case 'product':
        require_once __DIR__ . '/routes/product_routes.php';
        break;
        
    case 'cart':
        require_once __DIR__ . '/routes/cart_routes.php';
        break;
        
    case 'order':
        require_once __DIR__ . '/routes/order_routes.php';
        break;
        
    case 'category':
        require_once __DIR__ . '/routes/category_routes.php';
        break;
        
    default:
        http_response_code(404);
        echo json_encode(array("message" => "Resource not found"));
        break;
}
?> 