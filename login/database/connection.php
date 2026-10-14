<?php
// Koneksi database Nexora POS (Laragon: user root, password kosong)
require_once __DIR__ . '/../../login/database/cek_skema.php';

class Database
{
    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $database = "e-pos";

    public $conn;

    public function __construct()
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        try {
            $this->conn = new mysqli(
                $this->host,
                $this->username,
                $this->password,
                $this->database
            );
        } catch (mysqli_sql_exception $ex) {
            eposHentikan('Tidak bisa terhubung ke database "' . $this->database . '". (' . $ex->getMessage() . ')');
        }
        $this->conn->set_charset('utf8mb4');
        eposCekSkema($this->conn);
    }
}
