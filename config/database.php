<?php
// Phase 2: Secure PDO Database Connection
require_once __DIR__ . '/config.php';

class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;

    private $dbh;
    private $error;

    public function __construct() {
        // Set DSN (Data Source Name)
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname . ';charset=utf8mb4';
        
        // PDO Options
        $options = array(
            PDO::ATTR_PERSISTENT => true,                 // Persistent connections
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,  // Throw exceptions on errors
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC // Fetch as associative array by default
        );

        // Create a new PDO instance
        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            die("Database Connection Error: " . $e->getMessage());
        }
    }

    // Return the active PDO connection
    public function getConnection() {
        return $this->dbh;
    }
}
