<?php
namespace App\Config;

class Database {
    private $host = 'localhost';
    private $dbname = 'saas_seeder';
    private $username = 'root';
    private $password = '';
    private $conn = null;

    public function getConnection(): \PDO {
        // Load from environment variables if available
        $this->host = $_ENV['DB_HOST'] ?? $_SERVER['DB_HOST'] ?? $this->host;
        $this->dbname = $_ENV['DB_NAME'] ?? $_SERVER['DB_NAME'] ?? $this->dbname;
        $this->username = $_ENV['DB_USER'] ?? $_SERVER['DB_USER'] ?? $this->username;
        $this->password = $_ENV['DB_PASSWORD'] ?? $_SERVER['DB_PASSWORD'] ?? $this->password;

        try {
            if ($this->conn === null) {
                $this->conn = new \PDO(
                    "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4",
                    $this->username,
                    $this->password
                );
                $this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                $this->conn->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
            }
            return $this->conn;
        } catch(\PDOException $e) {
            throw new \Exception("Connection failed: " . $e->getMessage());
        }
    }

    public function closeConnection() {
        $this->conn = null;
    }

    public function beginTransaction() {
        return $this->conn->beginTransaction();
    }

    public function commit() {
        return $this->conn->commit();
    }

    public function rollback() {
        return $this->conn->rollBack();
    }

    public function lastInsertId() {
        return $this->conn->lastInsertId();
    }
}
