<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once __DIR__ . '/../services/UserService.php';
require_once __DIR__ . '/../services/AuthService.php';

$userService = new UserService();

$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));
$endpoint = $request[0] ?? '';

// Debug information
error_log("User Request Path: " . print_r($request, true));
error_log("User Endpoint: " . $endpoint);
error_log("Full PATH_INFO: " . ($_SERVER['PATH_INFO'] ?? 'none'));

switch($method) {
    case 'POST':
        if($endpoint == 'user' && isset($request[1]) && $request[1] == 'register') {
            $data = json_decode(file_get_contents("php://input"));
            $data->role = isset($data->role) ? $data->role : 'user';
            $result = $userService->register($data);
            echo json_encode($result);
        } 
        else if($endpoint == 'user' && isset($request[1]) && $request[1] == 'login') {
            $data = json_decode(file_get_contents("php://input"));
            $authService = new AuthService();
            $result = $authService->authenticate($data->email, $data->password);
            echo json_encode($result);
        }
        else {
            http_response_code(404);
            echo json_encode(array("message" => "Invalid user endpoint", "debug" => array(
                "endpoint" => $endpoint,
                "request" => $request,
                "path_info" => $_SERVER['PATH_INFO'] ?? 'none'
            )));
        }
        break;

    case 'GET':
        // Get all users
        if($endpoint == 'users' || ($endpoint == 'user' && isset($request[1]) && $request[1] == 'users')) {
            $result = $userService->getAllUsers();
            echo json_encode($result);
        }
        // Get single user
        else if($endpoint == 'user' && isset($request[1]) && is_numeric($request[1])) {
            $result = $userService->getUserById($request[1]);
            echo json_encode($result);
        }
        else {
            http_response_code(404);
            echo json_encode(array("message" => "Invalid user endpoint", "debug" => array(
                "endpoint" => $endpoint,
                "request" => $request,
                "path_info" => $_SERVER['PATH_INFO'] ?? 'none'
            )));
        }
        break;

    case 'PUT':
        if($endpoint == 'user' && isset($request[1])) {
            $data = json_decode(file_get_contents("php://input"));
            $data->id = $request[1];
            $result = $userService->updateUser($data);
            echo json_encode($result);
        }
        break;

    case 'DELETE':
        if($endpoint == 'user' && isset($request[1])) {
            $result = $userService->deleteUser($request[1]);
            echo json_encode($result);
        }
        break;

    default:
        http_response_code(404);
        echo json_encode(array("message" => "Endpoint not found"));
        break;
}
?> 