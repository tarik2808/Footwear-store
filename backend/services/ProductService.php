<?php
require_once __DIR__ . '/../dao/ProductDAO.php';

class ProductService {
    private $productDAO;

    public function __construct() {
        $this->productDAO = new ProductDAO();
    }

    public function createProduct($productData) {
        // Validate input
        if(empty($productData->name) || empty($productData->price) || 
           empty($productData->stock) || empty($productData->category_id)) {
            return array("success" => false, "message" => "All fields are required");
        }

        // Validate price and stock
        if(!is_numeric($productData->price) || $productData->price <= 0) {
            return array("success" => false, "message" => "Invalid price");
        }

        if(!is_numeric($productData->stock) || $productData->stock < 0) {
            return array("success" => false, "message" => "Invalid stock quantity");
        }

        if($this->productDAO->create($productData)) {
            return array("success" => true, "message" => "Product created successfully");
        }
        return array("success" => false, "message" => "Failed to create product");
    }

    public function getAllProducts($min_price = null, $max_price = null, $search = null, $page = 1, $limit = 10, $sort_by = 'name', $sort_order = 'ASC') {
        try {
            $offset = ($page - 1) * $limit;
            $products = $this->productDAO->readAllWithFilters($min_price, $max_price, $search, $offset, $limit, $sort_by, $sort_order);
            $total = $this->productDAO->getTotalCount($min_price, $max_price, $search);
            
            return [
                'success' => true,
                'products' => $products,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'total_pages' => ceil($total / $limit)
                ],
                'sorting' => [
                    'sort_by' => $sort_by,
                    'sort_order' => $sort_order
                ]
            ];
        } catch (Exception $e) {
            error_log("Error in getAllProducts: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error retrieving products'];
        }
    }

    public function getProductsByCategory($category_id, $min_price = null, $max_price = null, $search = null, $page = 1, $limit = 10, $sort_by = 'name', $sort_order = 'ASC') {
        try {
            $offset = ($page - 1) * $limit;
            $products = $this->productDAO->readAllWithFilters($min_price, $max_price, $search, $offset, $limit, $sort_by, $sort_order, $category_id);
            $total = $this->productDAO->getTotalCount($min_price, $max_price, $search, $category_id);
            
            return [
                'success' => true,
                'products' => $products,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'total_pages' => ceil($total / $limit)
                ],
                'sorting' => [
                    'sort_by' => $sort_by,
                    'sort_order' => $sort_order
                ]
            ];
        } catch (Exception $e) {
            error_log("Error in getProductsByCategory: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error retrieving products by category'];
        }
    }

    public function getProductById($id) {
        $result = $this->productDAO->readOne($id);
        
        if($result->rowCount() > 0) {
            $product = $result->fetch(PDO::FETCH_ASSOC);
            return array("success" => true, "product" => $product);
        }
        
        return array("success" => false, "message" => "Product not found");
    }

    public function updateProduct($productData) {
        // Validate input
        if(empty($productData->id) || empty($productData->name) || 
           empty($productData->price) || empty($productData->stock) || 
           empty($productData->category_id)) {
            return array("success" => false, "message" => "All fields are required");
        }

        // Validate price and stock
        if(!is_numeric($productData->price) || $productData->price <= 0) {
            return array("success" => false, "message" => "Invalid price");
        }

        if(!is_numeric($productData->stock) || $productData->stock < 0) {
            return array("success" => false, "message" => "Invalid stock quantity");
        }

        if($this->productDAO->update($productData)) {
            return array("success" => true, "message" => "Product updated successfully");
        }
        return array("success" => false, "message" => "Failed to update product");
    }

    public function deleteProduct($id) {
        if($this->productDAO->delete($id)) {
            return array("success" => true, "message" => "Product deleted successfully");
        }
        return array("success" => false, "message" => "Failed to delete product");
    }

    public function updateStock($id, $quantity) {
        if($this->productDAO->updateStock($id, $quantity)) {
            return array("success" => true, "message" => "Stock updated successfully");
        }
        return array("success" => false, "message" => "Failed to update stock");
    }

    public function bulkCreateProducts($products) {
        try {
            foreach ($products as $product) {
                if (!$this->validateProduct($product)) {
                    return ['success' => false, 'message' => 'Invalid product data'];
                }
            }
            
            $result = $this->productDAO->bulkCreate($products);
            return ['success' => true, 'message' => 'Products created successfully', 'products' => $result];
        } catch (Exception $e) {
            error_log("Error in bulkCreateProducts: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error creating products'];
        }
    }

    public function bulkUpdateProducts($products) {
        try {
            foreach ($products as $product) {
                if (!isset($product->id) || !$this->validateProduct($product)) {
                    return ['success' => false, 'message' => 'Invalid product data'];
                }
            }
            
            $result = $this->productDAO->bulkUpdate($products);
            return ['success' => true, 'message' => 'Products updated successfully', 'products' => $result];
        } catch (Exception $e) {
            error_log("Error in bulkUpdateProducts: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error updating products'];
        }
    }

    private function validateProduct($product) {
        return isset($product->name) && 
               isset($product->description) && 
               isset($product->price) && 
               isset($product->stock) && 
               isset($product->category_id) &&
               strlen($product->name) <= 255 &&
               strlen($product->description) <= 1000 &&
               is_numeric($product->price) &&
               is_numeric($product->stock) &&
               is_numeric($product->category_id);
    }
}
?> 