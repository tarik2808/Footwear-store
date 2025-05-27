<?php
require_once __DIR__ . '/../config/database.php';

class CategoryDAO {
    private $conn;
    private $table_name = "categories";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Validate category input
    private function validateCategory($category) {
        if (empty($category->name)) {
            throw new Exception("Category name is required");
        }
        if (empty($category->description)) {
            throw new Exception("Category description is required");
        }
        return true;
    }

    // Create new category
    public function create($category) {
        try {
            $this->validateCategory($category);
            
            $query = "INSERT INTO " . $this->table_name . " 
                     (name, description) 
                     VALUES (:name, :description)";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(":name", $category->name);
            $stmt->bindParam(":description", $category->description);

            if($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            return false;
        } catch (Exception $e) {
            error_log("Category creation error: " . $e->getMessage());
            throw $e;
        }
    }

    // Read all categories with pagination
    public function readAll($page = 1, $limit = 10) {
        try {
            $offset = ($page - 1) * $limit;
            $query = "SELECT * FROM " . $this->table_name . " 
                     ORDER BY name ASC 
                     LIMIT :limit OFFSET :offset";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
            $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error reading categories: " . $e->getMessage());
            throw new Exception("Error reading categories");
        }
    }

    // Read single category
    public function readOne($id) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);
            $stmt->execute();
            
            $category = $stmt->fetch();
            if (!$category) {
                throw new Exception("Category not found");
            }
            return $category;
        } catch (Exception $e) {
            error_log("Error reading category: " . $e->getMessage());
            throw $e;
        }
    }

    // Update category
    public function update($category) {
        try {
            if (empty($category->id)) {
                throw new Exception("Category ID is required");
            }
            $this->validateCategory($category);
            
            $query = "UPDATE " . $this->table_name . " 
                     SET name = :name, description = :description 
                     WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(":name", $category->name);
            $stmt->bindParam(":description", $category->description);
            $stmt->bindParam(":id", $category->id);

            if($stmt->execute()) {
                return true;
            }
            return false;
        } catch (Exception $e) {
            error_log("Error updating category: " . $e->getMessage());
            throw $e;
        }
    }

    // Delete category with transaction
    public function delete($id) {
        try {
            $this->conn->beginTransaction();
            
            // Check if category has products
            $query = "SELECT COUNT(*) as count FROM products WHERE category_id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);
            $stmt->execute();
            
            $count = $stmt->fetch()['count'];
            if ($count > 0) {
                throw new Exception("Cannot delete category with existing products");
            }
            
            // Delete category
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);

            if($stmt->execute()) {
                $this->conn->commit();
                return true;
            }
            return false;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Error deleting category: " . $e->getMessage());
            throw new Exception("Error deleting category");
        }
    }

    // Get total count of categories
    public function getTotalCount() {
        try {
            $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetch()['total'];
        } catch (Exception $e) {
            error_log("Error getting category count: " . $e->getMessage());
            throw new Exception("Error getting category count");
        }
    }

    // Get categories with product count
    public function readAllWithProductCount($page = 1, $limit = 10) {
        try {
            $offset = ($page - 1) * $limit;
            $query = "SELECT c.*, COUNT(p.id) as product_count 
                     FROM " . $this->table_name . " c
                     LEFT JOIN products p ON c.id = p.category_id
                     GROUP BY c.id
                     ORDER BY c.name ASC
                     LIMIT :limit OFFSET :offset";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
            $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error reading categories with product count: " . $e->getMessage());
            throw new Exception("Error reading categories with product count");
        }
    }
}
?> 