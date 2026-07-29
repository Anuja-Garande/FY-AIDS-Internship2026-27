<?php

class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        $this->connect();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function connect() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];
            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            die("Database connection failed. Please try again later.");
        }
    }

    public function getConnection() {
        return $this->conn;
    }

    public function query($sql, $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Query failed: " . $e->getMessage() . " | SQL: " . $sql);
            return false;
        }
    }

    public function fetch($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        if ($stmt) {
            return $stmt->fetch();
        }
        return false;
    }

    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        if ($stmt) {
            return $stmt->fetchAll();
        }
        return [];
    }

    public function insert($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        if ($stmt) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function update($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        if ($stmt) {
            return $stmt->rowCount();
        }
        return false;
    }

    public function delete($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        if ($stmt) {
            return $stmt->rowCount();
        }
        return false;
    }

    public function rowCount($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        if ($stmt) {
            return $stmt->rowCount();
        }
        return 0;
    }

    public function lastInsertId() {
        return $this->conn->lastInsertId();
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
}
