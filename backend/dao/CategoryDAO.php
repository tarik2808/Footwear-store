<?php
require_once __DIR__ . '/../config/database.php';

class CategoryDAO {
    private $conn;
    private $table_name = "categories";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Create new category
    public function create($category) {
        $query = "INSERT INTO " . $this->table_name . " 
                 (name, description) 
                 VALUES (:name, :description)";
        
        $stmt = $this->conn->prepare($query);

        // Sanitize and bind
        $stmt->bindParam(":name", $category->name);
        $stmt->bindParam(":description", $category->description);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Read all categories
    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Read single category
    public function readOne($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt;
    }

    // Update category
    public function update($category) {
        $query = "UPDATE " . $this->table_name . " 
                 SET name = :name, description = :description 
                 WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);

        // Sanitize and bind
        $stmt->bindParam(":name", $category->name);
        $stmt->bindParam(":description", $category->description);
        $stmt->bindParam(":id", $category->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Delete category
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?> 