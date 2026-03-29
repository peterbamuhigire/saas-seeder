<?php
declare(strict_types=1);

namespace App\Config;

final class Database
{
    private string $host = 'localhost';
    private string $dbname = 'saas_seeder';
    private string $username = 'root';
    private string $password = '';
    private ?\PDO $conn = null;

    private static ?self $instance = null;

    /**
     * Get a shared Database instance (reuses PDO connection).
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): \PDO
    {
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
        } catch (\PDOException $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            throw new \RuntimeException('Database connection failed. Check server configuration.');
        }
    }

    public function closeConnection(): void
    {
        $this->conn = null;
    }

    public function beginTransaction(): bool
    {
        return $this->conn->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->conn->commit();
    }

    public function rollback(): bool
    {
        return $this->conn->rollBack();
    }

    public function lastInsertId(): string|false
    {
        return $this->conn->lastInsertId();
    }
}
