<?php
require_once __DIR__ . '/../dao/CategoryDAO.php';

class CategoryService {
    private $categoryDAO;

    public function __construct() {
        $this->categoryDAO = new CategoryDAO();
    }

    public function createCategory($categoryData) {
        // Validate input
        if(empty($categoryData->name)) {
            return array("success" => false, "message" => "Category name is required");
        }

        if($this->categoryDAO->create($categoryData)) {
            return array("success" => true, "message" => "Category created successfully");
        }
        return array("success" => false, "message" => "Failed to create category");
    }

    public function getAllCategories() {
        $result = $this->categoryDAO->readAll();
        $categories = array();
        
        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $categories[] = $row;
        }
        
        return array("success" => true, "categories" => $categories);
    }

    public function getCategoryById($id) {
        $result = $this->categoryDAO->readOne($id);
        
        if($result->rowCount() > 0) {
            $category = $result->fetch(PDO::FETCH_ASSOC);
            return array("success" => true, "category" => $category);
        }
        
        return array("success" => false, "message" => "Category not found");
    }

    public function updateCategory($categoryData) {
        // Validate input
        if(empty($categoryData->id) || empty($categoryData->name)) {
            return array("success" => false, "message" => "Category ID and name are required");
        }

        if($this->categoryDAO->update($categoryData)) {
            return array("success" => true, "message" => "Category updated successfully");
        }
        return array("success" => false, "message" => "Failed to update category");
    }

    public function deleteCategory($id) {
        if($this->categoryDAO->delete($id)) {
            return array("success" => true, "message" => "Category deleted successfully");
        }
        return array("success" => false, "message" => "Failed to delete category");
    }
}
?> 