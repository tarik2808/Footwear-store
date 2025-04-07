<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once __DIR__ . '/../services/CartService.php';

$cartService = new CartService();

$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));
$endpoint = $request[0] ?? '';

// Debug information
error_log("Cart Request Path: " . print_r($request, true));
error_log("Cart Endpoint: " . $endpoint);
error_log("Full PATH_INFO: " . ($_SERVER['PATH_INFO'] ?? 'none'));

switch($method) {
    case 'POST':
        if($endpoint == 'cart' && isset($request[1]) && $request[1] == 'add') {
            $data = json_decode(file_get_contents("php://input"));
            $result = $cartService->addToCart($data->user_id, $data->product_id, $data->quantity);
            echo json_encode($result);
        }
        break;

    case 'GET':
        // Get cart items for a user
        if($endpoint == 'cart' && isset($request[1]) && $request[1] == 'items' && isset($request[2])) {
            $result = $cartService->getCartItems($request[2]);
            echo json_encode($result);
        }
        // Get cart total for a user
        else if($endpoint == 'cart' && isset($request[1]) && $request[1] == 'total' && isset($request[2])) {
            $result = $cartService->getCartTotal($request[2]);
            echo json_encode($result);
        }
        else {
            http_response_code(404);
            echo json_encode(array("message" => "Invalid cart endpoint", "debug" => array(
                "endpoint" => $endpoint,
                "request" => $request,
                "path_info" => $_SERVER['PATH_INFO'] ?? 'none'
            )));
        }
        break;

    case 'PUT':
        if($endpoint == 'cart' && isset($request[1]) && $request[1] == 'update' && isset($request[2]) && isset($request[3])) {
            $data = json_decode(file_get_contents("php://input"));
            $result = $cartService->updateQuantity($request[2], $request[3], $data->quantity);
            echo json_encode($result);
        }
        break;

    case 'DELETE':
        if($endpoint == 'cart' && isset($request[1]) && $request[1] == 'remove' && isset($request[2]) && isset($request[3])) {
            // Remove specific item
            $result = $cartService->removeFromCart($request[2], $request[3]);
            echo json_encode($result);
        }
        else if($endpoint == 'cart' && isset($request[1]) && $request[1] == 'clear' && isset($request[2])) {
            // Clear entire cart
            $result = $cartService->clearCart($request[2]);
            echo json_encode($result);
        }
        break;

    default:
        http_response_code(404);
        echo json_encode(array("message" => "Endpoint not found"));
        break;
}
?> 