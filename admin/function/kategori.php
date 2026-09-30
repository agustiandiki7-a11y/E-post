<?php

require_once 'database/connection.php';

class Kategori
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->conn;
    }

    public function tambah(
        $kategori
    ) {

        $var_kategori = mysqli_real_escape_string(
            $this->conn,
            $kategori
        );

        $query = "INSERT INTO kategori 
                  (kategori)
                  VALUES (
                  '$var_kategori'
                  )";

        return mysqli_query($this->conn, $query);
    }
}