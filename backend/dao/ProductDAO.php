<?php
require_once __DIR__ . '/../config/database.php';

class ProductDAO {
    private $conn;
    private $table_name = "products";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Create new product
    public function create($product) {
        $query = "INSERT INTO " . $this->table_name . " 
                 (name, description, price, stock, category_id) 
                 VALUES (:name, :description, :price, :stock, :category_id)";
        
        $stmt = $this->conn->prepare($query);

        // Sanitize and bind
        $stmt->bindParam(":name", $product->name);
        $stmt->bindParam(":description", $product->description);
        $stmt->bindParam(":price", $product->price);
        $stmt->bindParam(":stock", $product->stock);
        $stmt->bindParam(":category_id", $product->category_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Read all products
    public function readAll() {
        $query = "SELECT p.*, c.name as category_name 
                 FROM " . $this->table_name . " p
                 LEFT JOIN categories c ON p.category_id = c.id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Read products by category
    public function readByCategory($category_id) {
        $query = "SELECT p.*, c.name as category_name 
                 FROM " . $this->table_name . " p
                 LEFT JOIN categories c ON p.category_id = c.id
                 WHERE p.category_id = :category_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":category_id", $category_id);
        $stmt->execute();
        return $stmt;
    }

    // Read single product
    public function readOne($id) {
        $query = "SELECT p.*, c.name as category_name 
                 FROM " . $this->table_name . " p
                 LEFT JOIN categories c ON p.category_id = c.id
                 WHERE p.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt;
    }

    // Update product
    public function update($product) {
        $query = "UPDATE " . $this->table_name . " 
                 SET name = :name, description = :description, 
                     price = :price, stock = :stock, category_id = :category_id 
                 WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);

        // Sanitize and bind
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
    }

    // Delete product
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Update stock
    public function updateStock($id, $quantity) {
        $query = "UPDATE " . $this->table_name . " 
                 SET stock = stock - :quantity 
                 WHERE id = :id AND stock >= :quantity";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":quantity", $quantity);
        $stmt->bindParam(":id", $id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Read all products with filters
    public function readAllWithFilters($min_price = null, $max_price = null, $search = null, $offset = 0, $limit = 10, $sort_by = 'name', $sort_order = 'ASC') {
        $query = "SELECT p.*, c.name as category_name 
                 FROM " . $this->table_name . " p
                 LEFT JOIN categories c ON p.category_id = c.id
                 WHERE 1=1";
        
        $params = array();
        
        if($min_price !== null) {
            $query .= " AND p.price >= :min_price";
            $params[':min_price'] = $min_price;
        }
        
        if($max_price !== null) {
            $query .= " AND p.price <= :max_price";
            $params[':max_price'] = $max_price;
        }
        
        if($search !== null) {
            $query .= " AND (p.name LIKE :search OR p.description LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }
        
        // Add sorting
        $query .= " ORDER BY p." . $sort_by . " " . $sort_order;
        
        // Add pagination
        $query .= " LIMIT :offset, :limit";
        $params[':offset'] = $offset;
        $params[':limit'] = $limit;
        
        $stmt = $this->conn->prepare($query);
        foreach($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        
        $products = array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $products[] = $row;
        }
        return $products;
    }

    public function getTotalCount($min_price = null, $max_price = null, $search = null) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " p WHERE 1=1";
        
        $params = array();
        
        if($min_price !== null) {
            $query .= " AND p.price >= :min_price";
            $params[':min_price'] = $min_price;
        }
        
        if($max_price !== null) {
            $query .= " AND p.price <= :max_price";
            $params[':max_price'] = $max_price;
        }
        
        if($search !== null) {
            $query .= " AND (p.name LIKE :search OR p.description LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }
        
        $stmt = $this->conn->prepare($query);
        foreach($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function bulkCreate($products) {
        try {
            $this->conn->beginTransaction();
            
            foreach($products as $product) {
                $query = "INSERT INTO " . $this->table_name . " 
                         (name, description, price, stock, category_id) 
                         VALUES (:name, :description, :price, :stock, :category_id)";
                
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":name", $product->name);
                $stmt->bindParam(":description", $product->description);
                $stmt->bindParam(":price", $product->price);
                $stmt->bindParam(":stock", $product->stock);
                $stmt->bindParam(":category_id", $product->category_id);
                $stmt->execute();
            }
            
            $this->conn->commit();
            return true;
        } catch(Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function bulkUpdate($products) {
        try {
            $this->conn->beginTransaction();
            
            foreach($products as $product) {
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
                $stmt->execute();
            }
            
            $this->conn->commit();
            return true;
        } catch(Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }
}
?> 