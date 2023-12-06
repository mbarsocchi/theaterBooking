<?php

class Database {

    private $conn;

    public function __construct() {
        include_once(__DIR__ . '/../config.php');

        $this->conn = new mysqli($servername, $username, $password, $dbname);
        if ($this->conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
    }

    public function getConnection() {
        return $this->conn;
    }

}
