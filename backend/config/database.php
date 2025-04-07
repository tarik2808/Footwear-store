<?php
class Database {
    private $host = "localhost";
    private $port = "3310";
    private $db_name = "shopdb";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo "Connection Error: " . $e->getMessage() . "<br>";
            echo "Please make sure:<br>";
            echo "1. XAMPP MySQL service is running<br>";
            echo "2. The database 'shopdb' exists<br>";
            echo "3. The user 'root' has proper permissions<br>";
            die();
        }

        return $this->conn;
    }
}
?> 