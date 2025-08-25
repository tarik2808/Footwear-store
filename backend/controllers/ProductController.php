<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/ProductService.php';

class ProductController extends BaseController {
    private $productService;

    public function __construct() {
        parent::__construct();
        $this->productService = new ProductService();
    }

    public function createProduct() {
        try {
            $user = Flight::get('user');
            if ($user['role'] !== 'admin') {
                throw new Exception("Unauthorized");
            }
            
            // Handle both FormData and JSON requests
            $data = [];
            if ($_SERVER['CONTENT_TYPE'] && strpos($_SERVER['CONTENT_TYPE'], 'multipart/form-data') !== false) {
                // FormData request
                $data = $_POST;
                // Handle file upload
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = dirname(__DIR__) . '/uploads/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    $filename = uniqid() . '_' . basename($_FILES['image']['name']);
                    $targetFile = $uploadDir . $filename;
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                        $data['image'] = 'uploads/' . $filename;
                    }
                }
            } else {
                // JSON request
                $data = Flight::request()->data->getData();
            }
            
            $this->validateRequiredFields($data, ['name', 'description', 'price', 'category_id']);
            
            $product = $this->productService->createProduct($data);
            $this->sendResponse($product, 201);
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 400, $e);
        }
    }

    public function getProduct($id) {
        try {
            $product = $this->productService->getProduct($id);
            $this->sendResponse($product);
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 400, $e);
        }
    }

    public function listProducts() {
        try {
            $page = Flight::request()->query['page'] ?? 1;
            $limit = Flight::request()->query['limit'] ?? 50;
            $filters = Flight::request()->query['filters'] ?? [];

            $result = $this->productService->listProducts($page, $limit, $filters);
            $this->sendResponse($result);
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 400, $e);
        }
    }

    public function updateProduct($id) {
        try {
            $user = Flight::get('user');
            if ($user['role'] !== 'admin') {
                throw new Exception("Unauthorized");
            }
            
            // Handle both FormData and JSON requests
            $data = [];
            if ($_SERVER['CONTENT_TYPE'] && strpos($_SERVER['CONTENT_TYPE'], 'multipart/form-data') !== false) {
                // FormData request
                $data = $_POST;
                // Handle file upload
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = dirname(__DIR__) . '/uploads/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    $filename = uniqid() . '_' . basename($_FILES['image']['name']);
                    $targetFile = $uploadDir . $filename;
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                        $data['image'] = 'uploads/' . $filename;
                    }
                }
            } else {
                // JSON request
                $data = Flight::request()->data->getData();
            }
            
            $product = $this->productService->updateProduct($id, $data);
            $this->sendResponse($product);
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 400, $e);
        }
    }

    public function deleteProduct($id) {
        try {
            $user = Flight::get('user');
            if ($user['role'] !== 'admin') {
                throw new Exception("Unauthorized");
            }
            $this->productService->deleteProduct($id);
            $this->sendResponse(['message' => 'Product deleted successfully']);
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 400, $e);
        }
    }

    public function updateStock($id) {
        try {
            $user = Flight::get('user');
            if ($user['role'] !== 'admin') {
                throw new Exception("Unauthorized");
            }
            $data = Flight::request()->data;
            $this->validateRequiredFields($data, ['quantity']);
            
            $product = $this->productService->updateStock($id, $data['quantity']);
            $this->sendResponse($product);
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 400, $e);
        }
    }

    public function bulkCreateProducts() {
        try {
            $user = Flight::get('user');
            if ($user['role'] !== 'admin') {
                throw new Exception("Unauthorized");
            }
            $data = Flight::request()->data;
            if (!isset($data['products']) || !is_array($data['products'])) {
                throw new Exception("Products array is required");
            }

            $this->productService->bulkCreateProducts($data['products']);
            $this->sendResponse(['message' => 'Products created successfully'], 201);
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 400, $e);
        }
    }

    public function bulkUpdateProducts() {
        try {
            $user = Flight::get('user');
            if ($user['role'] !== 'admin') {
                throw new Exception("Unauthorized");
            }
            $data = Flight::request()->data;
            if (!isset($data['products']) || !is_array($data['products'])) {
                throw new Exception("Products array is required");
            }

            $this->productService->bulkUpdateProducts($data['products']);
            $this->sendResponse(['message' => 'Products updated successfully']);
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 400, $e);
        }
    }
}
?> 