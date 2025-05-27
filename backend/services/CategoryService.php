<?php
require_once __DIR__ . '/../dao/CategoryDAO.php';
require_once __DIR__ . '/../dao/ProductDAO.php';

class CategoryService {
    private $categoryDAO;
    private $productDAO;

    public function __construct() {
        $this->categoryDAO = new CategoryDAO();
        $this->productDAO = new ProductDAO();
    }

    // Create new category
    public function createCategory($categoryData) {
        try {
            // Validate required fields
            if (empty($categoryData['name']) || empty($categoryData['description'])) {
                throw new Exception("Name and description are required");
            }

            // Create category object
            $category = new stdClass();
            $category->name = $categoryData['name'];
            $category->description = $categoryData['description'];

            // Create category in database
            $categoryId = $this->categoryDAO->create($category);
            if (!$categoryId) {
                throw new Exception("Failed to create category");
            }

            return $this->getCategory($categoryId);
        } catch (Exception $e) {
            error_log("Category creation error: " . $e->getMessage());
            throw $e;
        }
    }

    // Get category by ID
    public function getCategory($categoryId) {
        try {
            $category = $this->categoryDAO->readOne($categoryId);
            if (!$category) {
                throw new Exception("Category not found");
            }
            return $category;
        } catch (Exception $e) {
            error_log("Error getting category: " . $e->getMessage());
            throw $e;
        }
    }

    // List categories with pagination
    public function listCategories($page = 1, $limit = 10) {
        try {
            $categories = $this->categoryDAO->readAll($page, $limit);
            $total = $this->categoryDAO->getTotalCount();

            return [
                'categories' => $categories,
                'total' => $total,
                'page' => $page,
                'limit' => $limit
            ];
        } catch (Exception $e) {
            error_log("Error listing categories: " . $e->getMessage());
            throw $e;
        }
    }

    // Update category
    public function updateCategory($categoryId, $categoryData) {
        try {
            // Check if category exists
            $existingCategory = $this->categoryDAO->readOne($categoryId);
            if (!$existingCategory) {
                throw new Exception("Category not found");
            }

            // Update category object
            $category = new stdClass();
            $category->id = $categoryId;
            $category->name = $categoryData['name'] ?? null;
            $category->description = $categoryData['description'] ?? null;

            if (!$this->categoryDAO->update($category)) {
                throw new Exception("Failed to update category");
            }

            return $this->getCategory($categoryId);
        } catch (Exception $e) {
            error_log("Error updating category: " . $e->getMessage());
            throw $e;
        }
    }

    // Delete category
    public function deleteCategory($categoryId) {
        try {
            // Check if category exists
            $category = $this->categoryDAO->readOne($categoryId);
            if (!$category) {
                throw new Exception("Category not found");
            }

            // Check if category has products
            $products = $this->productDAO->readAll(1, 1, ['category_id' => $categoryId]);
            if (!empty($products['products'])) {
                throw new Exception("Cannot delete category with existing products");
            }

            if (!$this->categoryDAO->delete($categoryId)) {
                throw new Exception("Failed to delete category");
            }

            return true;
        } catch (Exception $e) {
            error_log("Error deleting category: " . $e->getMessage());
            throw $e;
        }
    }

    // Get categories with product count
    public function getCategoriesWithProductCount($page = 1, $limit = 10) {
        try {
            $categories = $this->categoryDAO->readAllWithProductCount($page, $limit);
            $total = $this->categoryDAO->getTotalCount();

            return [
                'categories' => $categories,
                'total' => $total,
                'page' => $page,
                'limit' => $limit
            ];
        } catch (Exception $e) {
            error_log("Error getting categories with product count: " . $e->getMessage());
            throw $e;
        }
    }
}
?> 