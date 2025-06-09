<?php
require_once __DIR__ . '/../dao/ProductDAO.php';
require_once __DIR__ . '/../dao/CategoryDAO.php';

class ProductService {
    private $productDAO;
    private $categoryDAO;

    public function __construct() {
        $this->productDAO = new ProductDAO();
        $this->categoryDAO = new CategoryDAO();
    }

    // Create new product
    public function createProduct($productData) {
        try {
            // Validate required fields
            if (empty($productData['name']) || empty($productData['description']) || 
                empty($productData['price']) || empty($productData['category_id'])) {
                throw new Exception("All fields are required");
            }

            // Validate price
            if (!is_numeric($productData['price']) || $productData['price'] <= 0) {
                throw new Exception("Price must be a positive number");
            }

            // Validate stock
            if (!empty($productData['stock']) && (!is_numeric($productData['stock']) || $productData['stock'] < 0)) {
                throw new Exception("Stock must be a non-negative number");
            }

            // Check if category exists
            $category = $this->categoryDAO->readOne($productData['category_id']);
            if (!$category) {
                throw new Exception("Category not found");
            }

            // Create product object
            $product = new stdClass();
            $product->name = $productData['name'];
            $product->description = $productData['description'];
            $product->price = $productData['price'];
            $product->stock = $productData['stock'] ?? 0;
            $product->category_id = $productData['category_id'];
            if (isset($productData['image']) && !empty($productData['image'])) {
                $product->image = $productData['image'];
            }

            // Create product in database
            $productId = $this->productDAO->create($product);
            if (!$productId) {
                throw new Exception("Failed to create product");
            }

            return $this->getProduct($productId);
        } catch (Exception $e) {
            error_log("Product creation error: " . $e->getMessage());
            throw $e;
        }
    }

    // Get product by ID
    public function getProduct($productId) {
        try {
            $product = $this->productDAO->readOne($productId);
            if (!$product) {
                throw new Exception("Product not found");
            }
            return $product;
        } catch (Exception $e) {
            error_log("Error getting product: " . $e->getMessage());
            throw $e;
        }
    }

    // List products with filters and pagination
    public function listProducts($page = 1, $limit = 10, $filters = []) {
        try {
            $products = $this->productDAO->readAll($page, $limit, $filters);
            $total = $this->productDAO->getTotalCount($filters);

            return [
                'products' => $products,
                'total' => $total,
                'page' => $page,
                'limit' => $limit
            ];
        } catch (Exception $e) {
            error_log("Error listing products: " . $e->getMessage());
            throw $e;
        }
    }

    // Update product
    public function updateProduct($productId, $productData) {
        try {
            // Check if product exists
            $existingProduct = $this->productDAO->readOne($productId);
            if (!$existingProduct) {
                throw new Exception("Product not found");
            }

            // Validate price if provided
            if (!empty($productData['price']) && (!is_numeric($productData['price']) || $productData['price'] <= 0)) {
                throw new Exception("Price must be a positive number");
            }

            // Validate stock if provided
            if (!empty($productData['stock']) && (!is_numeric($productData['stock']) || $productData['stock'] < 0)) {
                throw new Exception("Stock must be a non-negative number");
            }

            // Check if category exists if provided
            if (!empty($productData['category_id'])) {
                $category = $this->categoryDAO->readOne($productData['category_id']);
                if (!$category) {
                    throw new Exception("Category not found");
                }
            }

            // Update product object
            $product = new stdClass();
            $product->id = $productId;
            $product->name = $productData['name'] ?? null;
            $product->description = $productData['description'] ?? null;
            $product->price = $productData['price'] ?? null;
            $product->stock = $productData['stock'] ?? null;
            $product->category_id = $productData['category_id'] ?? null;
            if (isset($productData['image']) && !empty($productData['image'])) {
                $product->image = $productData['image'];
            }

            if (!$this->productDAO->update($product)) {
                throw new Exception("Failed to update product");
            }

            return $this->getProduct($productId);
        } catch (Exception $e) {
            error_log("Error updating product: " . $e->getMessage());
            throw $e;
        }
    }

    // Delete product
    public function deleteProduct($productId) {
        try {
            // Check if product exists
            $product = $this->productDAO->readOne($productId);
            if (!$product) {
                throw new Exception("Product not found");
            }

            if (!$this->productDAO->delete($productId)) {
                throw new Exception("Failed to delete product");
            }

            return true;
        } catch (Exception $e) {
            error_log("Error deleting product: " . $e->getMessage());
            throw $e;
        }
    }

    // Update product stock
    public function updateStock($productId, $quantity) {
        try {
            if (!is_numeric($quantity)) {
                throw new Exception("Quantity must be a number");
            }

            if (!$this->productDAO->updateStock($productId, $quantity)) {
                throw new Exception("Failed to update stock");
            }

            return $this->getProduct($productId);
        } catch (Exception $e) {
            error_log("Error updating stock: " . $e->getMessage());
            throw $e;
        }
    }

    // Bulk create products
    public function bulkCreateProducts($products) {
        try {
            if (!is_array($products) || empty($products)) {
                throw new Exception("Products array is required");
            }

            if (!$this->productDAO->bulkCreate($products)) {
                throw new Exception("Failed to create products");
            }

            return true;
        } catch (Exception $e) {
            error_log("Error in bulk create: " . $e->getMessage());
            throw $e;
        }
    }

    // Bulk update products
    public function bulkUpdateProducts($products) {
        try {
            if (!is_array($products) || empty($products)) {
                throw new Exception("Products array is required");
            }

            if (!$this->productDAO->bulkUpdate($products)) {
                throw new Exception("Failed to update products");
            }

            return true;
        } catch (Exception $e) {
            error_log("Error in bulk update: " . $e->getMessage());
            throw $e;
        }
    }
}
?> 