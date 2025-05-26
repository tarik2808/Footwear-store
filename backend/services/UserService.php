<?php
require_once __DIR__ . '/../dao/UserDAO.php';

class UserService {
    private $userDAO;

    public function __construct() {
        $this->userDAO = new UserDAO();
    }

    private function validateUser($data, $isUpdate = false) {
        $errors = [];

        // Required fields
        if (!$isUpdate) {
            if (empty($data->name)) {
                $errors[] = "Name is required";
            }
            if (empty($data->email)) {
                $errors[] = "Email is required";
            }
            if (empty($data->password)) {
                $errors[] = "Password is required";
            }
        }

        // Email format
        if (!empty($data->email) && !filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        }

        // Password strength
        if (!empty($data->password)) {
            if (strlen($data->password) < 8) {
                $errors[] = "Password must be at least 8 characters long";
            }
            if (!preg_match("/[A-Z]/", $data->password)) {
                $errors[] = "Password must contain at least one uppercase letter";
            }
            if (!preg_match("/[a-z]/", $data->password)) {
                $errors[] = "Password must contain at least one lowercase letter";
            }
            if (!preg_match("/[0-9]/", $data->password)) {
                $errors[] = "Password must contain at least one number";
            }
            if (!preg_match("/[^A-Za-z0-9]/", $data->password)) {
                $errors[] = "Password must contain at least one special character";
            }
        }

        // Name validation
        if (!empty($data->name)) {
            if (strlen($data->name) < 2) {
                $errors[] = "Name must be at least 2 characters long";
            }
            if (strlen($data->name) > 50) {
                $errors[] = "Name must not exceed 50 characters";
            }
            if (!preg_match("/^[a-zA-Z\s]+$/", $data->name)) {
                $errors[] = "Name can only contain letters and spaces";
            }
        }

        if (!empty($errors)) {
            throw new Exception(implode(", ", $errors));
        }
    }

    // Register a new user
    public function register($data) {
        try {
            $this->validateUser($data);
            
            if ($this->userDAO->emailExists($data->email)) {
                throw new Exception("Email already exists");
            }

            $data->password = password_hash($data->password, PASSWORD_DEFAULT);
            $data->role = 'user'; // Default role

            return $this->userDAO->create($data);
        } catch (Exception $e) {
            throw new Exception("Registration failed: " . $e->getMessage());
        }
    }

    // Login user
    public function login($email, $password) {
        try {
            $user = $this->userDAO->login($email);
            if (!$user || !password_verify($password, $user['password'])) {
                throw new Exception("Invalid credentials");
            }
            unset($user['password']);
            return $user;
        } catch (Exception $e) {
            throw new Exception("Login failed: " . $e->getMessage());
        }
    }

    // Get user profile
    public function getProfile($userId) {
        try {
            $user = $this->userDAO->readOne($userId);
            if (!$user) {
                throw new Exception("User not found");
            }
            unset($user['password']);
            return $user;
        } catch (Exception $e) {
            throw new Exception("Failed to get profile: " . $e->getMessage());
        }
    }

    // Update user profile
    public function updateProfile($userId, $data) {
        try {
            $this->validateUser($data, true);
            
            if (isset($data->email) && $this->userDAO->emailExists($data->email, $userId)) {
                throw new Exception("Email already exists");
            }

            if (isset($data->password) && !empty($data->password)) {
                $data->password = password_hash($data->password, PASSWORD_DEFAULT);
            } else {
                if (is_array($data) && array_key_exists('password', $data)) {
                    unset($data['password']);
                } elseif (is_object($data) && property_exists($data, 'password')) {
                    unset($data->password);
                }
            }

            $data->id = $userId;
            $user = $this->userDAO->update($data);
            if (!$user) {
                throw new Exception("Failed to update profile");
            }
            if (is_array($user) && isset($user['password'])) {
                unset($user['password']);
            } elseif (is_object($user) && isset($user->password)) {
                unset($user->password);
            }
            return $user;
        } catch (Exception $e) {
            throw new Exception("Failed to update profile: " . $e->getMessage());
        }
    }

    // Delete user account
    public function deleteAccount($userId) {
        try {
            if (!$this->userDAO->delete($userId)) {
                throw new Exception("Failed to delete account");
            }
        } catch (Exception $e) {
            throw new Exception("Failed to delete account: " . $e->getMessage());
        }
    }

    // List users (admin only)
    public function listUsers($page = 1, $limit = 10, $filters = []) {
        try {
            $total = $this->userDAO->getTotalCount($filters);
            $users = $this->userDAO->readAll($page, $limit, $filters);
            if (!is_array($users)) {
                $users = [];
            }
            foreach ($users as &$user) {
                if (is_array($user) && array_key_exists('password', $user)) {
                    unset($user['password']);
                } elseif (is_object($user) && property_exists($user, 'password')) {
                    unset($user->password);
                }
            }

            return [
                'users' => $users,
                'total' => $total,
                'page' => $page,
                'limit' => $limit
            ];
        } catch (Exception $e) {
            throw new Exception("Failed to list users: " . $e->getMessage());
        }
    }
}
?> 