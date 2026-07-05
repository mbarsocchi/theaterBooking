<?php

class Database {

    protected static $conn;

    protected function __construct() {
        
    }

    public static function getConnection() {
        include_once(__DIR__ . '/../config.php');
        if (empty(self::$conn)) {
            try {
                self::$conn = new mysqli($dbHost, $username, $password, $dbname);
            } catch (PDOException $error) {
                echo $error->getMessage();
            }
        }

        if (self::$conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        return self::$conn;
    }

    public static function executeQuery($stmt) {
        global $debug;
        try{
            $stmt->execute();
            if($stmt->errno && $debug){
                echo "Error: " . $stmt->error;
                die();
            }
        }catch( mysqli_sql_exception $e ){
            global $debug;
            if($debug){
                echo $e->getMessage();
                die;
            }
        }
    }   

}
