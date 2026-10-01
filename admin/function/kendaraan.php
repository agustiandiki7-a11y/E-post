<?php

require_once 'database/connection.php';

class Kendaraan
{
    private $conn;

    public function __construct()
    {
        $databas  e = new Database();
        $this->conn = $database->conn;
    }

    public function tambah(
        $nama_kendaraan,
        $harga_sewa,
        $status,

    ) {

        $var_nama_kendaraan = mysqli_real_escape_string(
            $this->conn,
            $nama_kendaraan
        );
        $var_harga_sewa = mysqli_real_escape_string(
            $this->conn,
            $harga_sewa
        );
        $var_status = mysqli_real_escape_string(
            $this->conn,
            $status
        );

        $query = "INSERT INTO kendaraan 
                  (nama_kendaraan, harga_sewa, status)
                  VALUES (
                  '$var_nama_kendaraan',
                  '$var_harga_sewa',
                  '$var_status'
                  )";

        return mysqli_query($this->conn, $query);
    }
}
