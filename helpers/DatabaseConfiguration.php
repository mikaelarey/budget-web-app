<?php

class DatabaseConfiguration {
    // private $servername = "localhost";
    // private $username   = "twpyqqel_pwise";
    // private $password   = "a[%]5uMLjA%n";
    // private $database   = "twpyqqel_pwise";
    private $servername = "localhost";
    private $username   = "root";
    private $password   = "";
    private $database   = "pwise";
    protected $conn;

    public function __construct() {
        $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->database);

        if ($this->conn->connect_error) {
          die("Connection failed: " . $conn->connect_error);
        }
    }
}

?>