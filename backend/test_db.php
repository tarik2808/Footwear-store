<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/Database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    if ($conn) {
        echo "Database connection successful!<br>";
        
        // Test query
        $stmt = $conn->query("SELECT DATABASE() as db");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "Connected to database: " . $result['db'] . "<br>";
        
        // List all tables
        $stmt = $conn->query("SHOW TABLES");
        echo "Tables in database:<br>";
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            echo "- " . $row[0] . "<br>";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
    echo "Error code: " . $e->getCode() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
}
?> 