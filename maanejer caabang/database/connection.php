<?php
// Koneksi database E-POS (Laragon: user root, password kosong)
class Database
{
    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $database = "e-post";
    public $conn;

    public function __construct()
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $this->conn = new mysqli(
            $this->host,
            $this->username,
            $this->password,
            $this->database
        );
        $this->conn->set_charset('utf8mb4');
    }
}
