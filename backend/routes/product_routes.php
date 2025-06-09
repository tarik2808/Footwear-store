<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once __DIR__ . '/../services/ProductService.php';

$productService = new ProductService();

$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));
$endpoint = $request[0] ?? '';

// Debug information
error_log("Product Request Path: " . print_r($request, true));
error_log("Product Endpoint: " . $endpoint);
error_log("Full PATH_INFO: " . ($_SERVER['PATH_INFO'] ?? 'none'));

switch($method) {
    case 'POST':
        if($endpoint == 'product') {
            // Check if the request is multipart/form-data (file upload)
            if (isset($_FILES['image'])) {
                $name = $_POST['name'] ?? '';
                $description = $_POST['description'] ?? '';
                $price = $_POST['price'] ?? '';
                $category_id = $_POST['category_id'] ?? '';
                $stock = $_POST['stock'] ?? 0;

                // Handle file upload
                $imagePath = null;
                if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = __DIR__ . '/../uploads/products/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
                    $targetFile = $uploadDir . $fileName;
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                        $imagePath = 'uploads/products/' . $fileName; // Save this in DB
                    }
                }

                // Build data object for ProductService
                $data = new stdClass();
                $data->name = $name;
                $data->description = $description;
                $data->price = $price;
                $data->category_id = $category_id;
                $data->stock = $stock;
                $data->image = $imagePath;

                $result = $productService->createProduct($data);
                echo json_encode($result);
            } else {
                // Fallback: JSON body (no file upload)
                $data = json_decode(file_get_contents("php://input"));
                $result = $productService->createProduct($data);
                echo json_encode($result);
            }
        }
        else if($endpoint == 'products' && isset($request[1]) && $request[1] == 'bulk') {
            $data = json_decode(file_get_contents("php://input"));
            $result = $productService->bulkCreateProducts($data->products);
            echo json_encode($result);
        }
        break;

    case 'GET':
        // Get all products with optional filtering
        if($endpoint == 'products' || ($endpoint == 'product' && isset($request[1]) && $request[1] == 'products')) {
            $min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : null;
            $max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : null;
            $search = isset($_GET['search']) ? $_GET['search'] : null;
            $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
            $sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'name';
            $sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'ASC';
            
            $result = $productService->getAllProducts($min_price, $max_price, $search, $page, $limit, $sort_by, $sort_order);
            echo json_encode($result);
        }
        // Get products by category
        else if($endpoint == 'product' && isset($request[1]) && $request[1] == 'products' && isset($request[2]) && $request[2] == 'category' && isset($request[3])) {
            $min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : null;
            $max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : null;
            $search = isset($_GET['search']) ? $_GET['search'] : null;
            $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
            $sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'name';
            $sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'ASC';
            
            $result = $productService->getProductsByCategory($request[3], $min_price, $max_price, $search, $page, $limit, $sort_by, $sort_order);
            echo json_encode($result);
        }
        // Get single product
        else if($endpoint == 'product' && isset($request[1]) && is_numeric($request[1])) {
            $result = $productService->getProductById($request[1]);
            echo json_encode($result);
        }
        else {
            http_response_code(404);
            echo json_encode(array("message" => "Invalid product endpoint", "debug" => array(
                "endpoint" => $endpoint,
                "request" => $request,
                "path_info" => $_SERVER['PATH_INFO'] ?? 'none'
            )));
        }
        break;

    case 'PUT':
        if($endpoint == 'product' && isset($request[1]) && is_numeric($request[1])) {
            // Support file upload for product update
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $price = $_POST['price'] ?? '';
            $category_id = $_POST['category_id'] ?? '';
            $stock = $_POST['stock'] ?? 0;
            $id = $request[1];

            // Handle file upload
            $imagePath = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../uploads/products/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
                $targetFile = $uploadDir . $fileName;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                    $imagePath = 'uploads/products/' . $fileName;
                }
            }

            // Build data object for ProductService
            $data = new stdClass();
            $data->id = $id;
            $data->name = $name;
            $data->description = $description;
            $data->price = $price;
            $data->category_id = $category_id;
            $data->stock = $stock;
            if ($imagePath) {
                $data->image = $imagePath;
            }

            $result = $productService->updateProduct($data);
            echo json_encode($result);
        }
        else if($endpoint == 'products' && isset($request[1]) && $request[1] == 'bulk') {
            $data = json_decode(file_get_contents("php://input"));
            $result = $productService->bulkUpdateProducts($data->products);
            echo json_encode($result);
        }
        break;

    case 'DELETE':
        if($endpoint == 'product' && isset($request[1]) && is_numeric($request[1])) {
            $result = $productService->deleteProduct($request[1]);
            echo json_encode($result);
        }
        break;

    default:
        http_response_code(404);
        echo json_encode(array("message" => "Endpoint not found"));
        break;
}
?> 