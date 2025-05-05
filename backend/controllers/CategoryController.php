<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/CategoryService.php';

class CategoryController extends BaseController {
    private $categoryService;

    public function __construct() {
        $this->categoryService = new CategoryService();
    }

    public function createCategory() {
        try {
            if (!$this->isAdmin()) {
                throw new Exception("Unauthorized");
            }

            $data = Flight::request()->data;
            $this->validateRequiredFields($data, ['name', 'description']);
            
            $category = $this->categoryService->createCategory($data);
            $this->sendResponse($category, 201);
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }

    public function getCategory($id) {
        try {
            $category = $this->categoryService->getCategory($id);
            $this->sendResponse($category);
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }

    public function listCategories() {
        try {
            $page = Flight::request()->query['page'] ?? 1;
            $limit = Flight::request()->query['limit'] ?? 10;

            $result = $this->categoryService->listCategories($page, $limit);
            $this->sendResponse($result);
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }

    public function updateCategory($id) {
        try {
            if (!$this->isAdmin()) {
                throw new Exception("Unauthorized");
            }

            $data = Flight::request()->data;
            $category = $this->categoryService->updateCategory($id, $data);
            $this->sendResponse($category);
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }

    public function deleteCategory($id) {
        try {
            if (!$this->isAdmin()) {
                throw new Exception("Unauthorized");
            }

            $this->categoryService->deleteCategory($id);
            $this->sendResponse(['message' => 'Category deleted successfully']);
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }

    public function getCategoriesWithProductCount() {
        try {
            $page = Flight::request()->query['page'] ?? 1;
            $limit = Flight::request()->query['limit'] ?? 10;

            $result = $this->categoryService->getCategoriesWithProductCount($page, $limit);
            $this->sendResponse($result);
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }
}
?> 