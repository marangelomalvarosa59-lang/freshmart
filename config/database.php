<?php
/**
 * Database Connection Class
 * Update the credentials below with the ones from your InfinityFree
 * control panel (MySQL Databases section) before deploying.
 */
class Database {
    // ---- CHANGE THESE FOR YOUR INFINITYFREE HOSTING ----
    private $host = "localhost";
    private $db_name = "freshmart_db";
    private $username = "root";
    private $password = "";
    // -----------------------------------------------------

    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            die("Connection error: " . $exception->getMessage());
        }
        return $this->conn;
    }
}
