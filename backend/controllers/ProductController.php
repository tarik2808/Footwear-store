<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/ProductService.php';

class ProductController extends BaseController {
    private $productService;

    public function __construct() {
        $this->productService = new ProductService();
    }

    public function createProduct() {
        try {
            if (!$this->isAdmin()) {
                throw new Exception("Unauthorized");
            }

            $data = Flight::request()->data;
            $this->validateRequiredFields($data, ['name', 'description', 'price', 'category_id']);
            
            $product = $this->productService->createProduct($data);
            $this->sendResponse($product, 201);
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }

    public function getProduct($id) {
        try {
            $product = $this->productService->getProduct($id);
            $this->sendResponse($product);
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }

    public function listProducts() {
        try {
            $page = Flight::request()->query['page'] ?? 1;
            $limit = Flight::request()->query['limit'] ?? 10;
            $filters = Flight::request()->query['filters'] ?? [];

            $result = $this->productService->listProducts($page, $limit, $filters);
            $this->sendResponse($result);
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }

    public function updateProduct($id) {
        try {
            if (!$this->isAdmin()) {
                throw new Exception("Unauthorized");
            }

            $data = Flight::request()->data;
            $product = $this->productService->updateProduct($id, $data);
            $this->sendResponse($product);
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }

    public function deleteProduct($id) {
        try {
            if (!$this->isAdmin()) {
                throw new Exception("Unauthorized");
            }

            $this->productService->deleteProduct($id);
            $this->sendResponse(['message' => 'Product deleted successfully']);
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }

    public function updateStock($id) {
        try {
            if (!$this->isAdmin()) {
                throw new Exception("Unauthorized");
            }

            $data = Flight::request()->data;
            $this->validateRequiredFields($data, ['quantity']);
            
            $product = $this->productService->updateStock($id, $data['quantity']);
            $this->sendResponse($product);
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }

    public function bulkCreateProducts() {
        try {
            if (!$this->isAdmin()) {
                throw new Exception("Unauthorized");
            }

            $data = Flight::request()->data;
            if (!isset($data['products']) || !is_array($data['products'])) {
                throw new Exception("Products array is required");
            }

            $this->productService->bulkCreateProducts($data['products']);
            $this->sendResponse(['message' => 'Products created successfully'], 201);
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }

    public function bulkUpdateProducts() {
        try {
            if (!$this->isAdmin()) {
                throw new Exception("Unauthorized");
            }

            $data = Flight::request()->data;
            if (!isset($data['products']) || !is_array($data['products'])) {
                throw new Exception("Products array is required");
            }

            $this->productService->bulkUpdateProducts($data['products']);
            $this->sendResponse(['message' => 'Products updated successfully']);
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }
}
?> 