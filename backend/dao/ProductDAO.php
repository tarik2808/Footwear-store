<?php
require_once __DIR__ . '/../config/database.php';

class ProductDAO {
    private $conn;
    private $table_name = "products";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Validate product input
    private function validateProduct($product) {
        if (empty($product->name)) {
            throw new Exception("Product name is required");
        }
        if (empty($product->description)) {
            throw new Exception("Product description is required");
        }
        if (!is_numeric($product->price) || $product->price <= 0) {
            throw new Exception("Product price must be a positive number");
        }
        if (!is_numeric($product->stock) || $product->stock < 0) {
            throw new Exception("Product stock must be a non-negative number");
        }
        if (empty($product->category_id)) {
            throw new Exception("Category ID is required");
        }
        return true;
    }

    // Create new product
    public function create($product) {
        try {
            $this->validateProduct($product);
            
            $query = "INSERT INTO " . $this->table_name . " 
                     (name, description, price, stock, category_id) 
                     VALUES (:name, :description, :price, :stock, :category_id)";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(":name", $product->name);
            $stmt->bindParam(":description", $product->description);
            $stmt->bindParam(":price", $product->price);
            $stmt->bindParam(":stock", $product->stock);
            $stmt->bindParam(":category_id", $product->category_id);

            if($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            return false;
        } catch (Exception $e) {
            error_log("Product creation error: " . $e->getMessage());
            throw $e;
        }
    }

    // Read all products with pagination and filters
    public function readAll($page = 1, $limit = 10, $filters = array()) {
        try {
            $offset = ($page - 1) * $limit;
            $query = "SELECT p.*, c.name as category_name 
                     FROM " . $this->table_name . " p
                     LEFT JOIN categories c ON p.category_id = c.id
                     WHERE 1=1";
            
            $params = array();
            
            // Apply filters
            if (!empty($filters['min_price'])) {
                $query .= " AND p.price >= :min_price";
                $params[':min_price'] = $filters['min_price'];
            }
            if (!empty($filters['max_price'])) {
                $query .= " AND p.price <= :max_price";
                $params[':max_price'] = $filters['max_price'];
            }
            if (!empty($filters['category_id'])) {
                $query .= " AND p.category_id = :category_id";
                $params[':category_id'] = $filters['category_id'];
            }
            if (!empty($filters['search'])) {
                $query .= " AND (p.name LIKE :search OR p.description LIKE :search)";
                $params[':search'] = '%' . $filters['search'] . '%';
            }
            
            // Add sorting
            $sort_by = !empty($filters['sort_by']) ? $filters['sort_by'] : 'name';
            $sort_order = !empty($filters['sort_order']) ? $filters['sort_order'] : 'ASC';
            $query .= " ORDER BY p." . $sort_by . " " . $sort_order;
            
            // Add pagination
            $query .= " LIMIT :limit OFFSET :offset";
            $params[':limit'] = $limit;
            $params[':offset'] = $offset;
            
            $stmt = $this->conn->prepare($query);
            foreach($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error reading products: " . $e->getMessage());
            throw new Exception("Error reading products");
        }
    }

    // Read single product
    public function readOne($id) {
        try {
            $query = "SELECT p.*, c.name as category_name 
                     FROM " . $this->table_name . " p
                     LEFT JOIN categories c ON p.category_id = c.id
                     WHERE p.id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);
            $stmt->execute();
            
            $product = $stmt->fetch();
            if (!$product) {
                throw new Exception("Product not found");
            }
            return $product;
        } catch (Exception $e) {
            error_log("Error reading product: " . $e->getMessage());
            throw $e;
        }
    }

    // Update product
    public function update($product) {
        try {
            if (empty($product->id)) {
                throw new Exception("Product ID is required");
            }
            $this->validateProduct($product);
            
            $query = "UPDATE " . $this->table_name . " 
                     SET name = :name, description = :description, 
                         price = :price, stock = :stock, category_id = :category_id 
                     WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(":name", $product->name);
            $stmt->bindParam(":description", $product->description);
            $stmt->bindParam(":price", $product->price);
            $stmt->bindParam(":stock", $product->stock);
            $stmt->bindParam(":category_id", $product->category_id);
            $stmt->bindParam(":id", $product->id);

            if($stmt->execute()) {
                return true;
            }
            return false;
        } catch (Exception $e) {
            error_log("Error updating product: " . $e->getMessage());
            throw $e;
        }
    }

    // Delete product
    public function delete($id) {
        try {
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);

            if($stmt->execute()) {
                return true;
            }
            return false;
        } catch (Exception $e) {
            error_log("Error deleting product: " . $e->getMessage());
            throw new Exception("Error deleting product");
        }
    }

    // Update stock with transaction
    public function updateStock($id, $quantity) {
        try {
            $this->conn->beginTransaction();
            
            // Check current stock
            $query = "SELECT stock FROM " . $this->table_name . " WHERE id = :id FOR UPDATE";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);
            $stmt->execute();
            
            $currentStock = $stmt->fetch()['stock'];
            if ($currentStock < $quantity) {
                throw new Exception("Insufficient stock");
            }
            
            // Update stock
            $query = "UPDATE " . $this->table_name . " 
                     SET stock = stock - :quantity 
                     WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":quantity", $quantity);
            $stmt->bindParam(":id", $id);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to update stock");
            }
            
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Error updating stock: " . $e->getMessage());
            throw $e;
        }
    }

    // Get total count with filters
    public function getTotalCount($filters = array()) {
        try {
            $query = "SELECT COUNT(*) as total 
                     FROM " . $this->table_name . " p
                     WHERE 1=1";
            
            $params = array();
            
            if (!empty($filters['min_price'])) {
                $query .= " AND p.price >= :min_price";
                $params[':min_price'] = $filters['min_price'];
            }
            if (!empty($filters['max_price'])) {
                $query .= " AND p.price <= :max_price";
                $params[':max_price'] = $filters['max_price'];
            }
            if (!empty($filters['category_id'])) {
                $query .= " AND p.category_id = :category_id";
                $params[':category_id'] = $filters['category_id'];
            }
            if (!empty($filters['search'])) {
                $query .= " AND (p.name LIKE :search OR p.description LIKE :search)";
                $params[':search'] = '%' . $filters['search'] . '%';
            }
            
            $stmt = $this->conn->prepare($query);
            foreach($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            
            return $stmt->fetch()['total'];
        } catch (Exception $e) {
            error_log("Error getting product count: " . $e->getMessage());
            throw new Exception("Error getting product count");
        }
    }

    // Bulk create products with transaction
    public function bulkCreate($products) {
        try {
            $this->conn->beginTransaction();
            
            foreach($products as $product) {
                $this->validateProduct($product);
                
                $query = "INSERT INTO " . $this->table_name . " 
                         (name, description, price, stock, category_id) 
                         VALUES (:name, :description, :price, :stock, :category_id)";
                
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":name", $product->name);
                $stmt->bindParam(":description", $product->description);
                $stmt->bindParam(":price", $product->price);
                $stmt->bindParam(":stock", $product->stock);
                $stmt->bindParam(":category_id", $product->category_id);
                
                if (!$stmt->execute()) {
                    throw new Exception("Failed to create product: " . $product->name);
                }
            }
            
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Error in bulk create: " . $e->getMessage());
            throw $e;
        }
    }

    // Bulk update products with transaction
    public function bulkUpdate($products) {
        try {
            $this->conn->beginTransaction();
            
            foreach($products as $product) {
                if (empty($product->id)) {
                    throw new Exception("Product ID is required for update");
                }
                $this->validateProduct($product);
                
                $query = "UPDATE " . $this->table_name . " 
                         SET name = :name, description = :description, 
                             price = :price, stock = :stock, category_id = :category_id 
                         WHERE id = :id";
                
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":name", $product->name);
                $stmt->bindParam(":description", $product->description);
                $stmt->bindParam(":price", $product->price);
                $stmt->bindParam(":stock", $product->stock);
                $stmt->bindParam(":category_id", $product->category_id);
                $stmt->bindParam(":id", $product->id);
                
                if (!$stmt->execute()) {
                    throw new Exception("Failed to update product: " . $product->name);
                }
            }
            
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Error in bulk update: " . $e->getMessage());
            throw $e;
        }
    }
}
?> 