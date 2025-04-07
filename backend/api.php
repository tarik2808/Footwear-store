<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

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