<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once __DIR__ . '/../services/OrderService.php';

$orderService = new OrderService();

$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));
$endpoint = $request[0] ?? '';

// Debug information
error_log("Order Request Path: " . print_r($request, true));
error_log("Order Endpoint: " . $endpoint);
error_log("Full PATH_INFO: " . ($_SERVER['PATH_INFO'] ?? 'none'));

switch($method) {
    case 'POST':
        if($endpoint == 'order' && isset($request[1])) {
            $result = $orderService->createOrder($request[1]);
            echo json_encode($result);
        }
        break;

    case 'GET':
        // Get all orders
        if($endpoint == 'orders' || ($endpoint == 'order' && isset($request[1]) && $request[1] == 'orders')) {
            $result = $orderService->getAllOrders();
            echo json_encode($result);
        }
        // Get single order
        else if($endpoint == 'order' && isset($request[1]) && is_numeric($request[1])) {
            $result = $orderService->getOrderById($request[1]);
            echo json_encode($result);
        }
        // Get orders by user
        else if($endpoint == 'order' && isset($request[1]) && $request[1] == 'user' && isset($request[2])) {
            $result = $orderService->getOrdersByUser($request[2]);
            echo json_encode($result);
        }
        else {
            http_response_code(404);
            echo json_encode(array("message" => "Invalid order endpoint", "debug" => array(
                "endpoint" => $endpoint,
                "request" => $request,
                "path_info" => $_SERVER['PATH_INFO'] ?? 'none'
            )));
        }
        break;

    case 'PUT':
        if($endpoint == 'order' && isset($request[1]) && isset($request[2])) {
            $data = json_decode(file_get_contents("php://input"));
            $result = $orderService->updateOrderStatus($request[1], $data->status);
            echo json_encode($result);
        }
        break;

    case 'DELETE':
        if($endpoint == 'order' && isset($request[1])) {
            $result = $orderService->deleteOrder($request[1]);
            echo json_encode($result);
        }
        break;

    default:
        http_response_code(404);
        echo json_encode(array("message" => "Endpoint not found"));
        break;
}
?> 