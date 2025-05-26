<?php
require_once __DIR__ . '/rest/config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    echo "Database connection successful!\n";
    
    // Test query to verify we can actually query the database
    $stmt = $conn->query("SELECT 1");
    if ($stmt->fetch()) {
        echo "Database query test successful!\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 