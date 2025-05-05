<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once __DIR__ . '/../services/CategoryService.php';

$categoryService = new CategoryService();

$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));
$endpoint = $request[0] ?? '';

// Debug information
error_log("Request: " . print_r($request, true));
error_log("Endpoint: " . $endpoint);

switch($method) {
    case 'POST':
        if($endpoint == 'category') {
            $data = json_decode(file_get_contents("php://input"));
            $result = $categoryService->createCategory($data);
            echo json_encode($result);
        }
        break;

    case 'GET':
        // Check if we're requesting all categories
        if($endpoint == 'categories' || ($endpoint == 'category' && isset($request[1]) && $request[1] == 'categories')) {
            $result = $categoryService->getAllCategories();
            echo json_encode($result);
        }
        // Check if we're requesting a specific category by ID
        else if($endpoint == 'category' && isset($request[1]) && is_numeric($request[1])) {
            $result = $categoryService->getCategoryById($request[1]);
            echo json_encode($result);
        }
        else {
            http_response_code(404);
            echo json_encode(array("message" => "Invalid endpoint"));
        }
        break;

    case 'PUT':
        if($endpoint == 'category' && isset($request[1]) && is_numeric($request[1])) {
            $data = json_decode(file_get_contents("php://input"));
            $data->id = $request[1];
            $result = $categoryService->updateCategory($data);
            echo json_encode($result);
        }
        break;

    case 'DELETE':
        if($endpoint == 'category' && isset($request[1]) && is_numeric($request[1])) {
            $result = $categoryService->deleteCategory($request[1]);
            echo json_encode($result);
        }
        break;

    default:
        http_response_code(404);
        echo json_encode(array("message" => "Endpoint not found"));
        break;
}
?> 