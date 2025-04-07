<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ecommerce');

// Upload configuration
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);

// API configuration
define('API_VERSION', 'v1');
define('CORS_ALLOWED_ORIGINS', ['http://localhost']);

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
