<?php

require_once 'database/connection.php';

class Pembayaran
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->conn;
    }

    public function tambah(
        $id_penyewaan,
        $total_bayar,

    ) {

        $var_id_penyewaan = mysqli_real_escape_string(
            $this->conn,
            $id_penyewaan
        );
        $var_total_bayar = mysqli_real_escape_string(
            $this->conn,
            $total_bayar
        );

        $query = "INSERT INTO pembayaran 
                  (id_penyewaan, total_bayar)
                  VALUES (
                  '$var_id_penyewaan',
                  '$var_total_bayar'
                  )";

        return mysqli_query($this->conn, $query);
    }
}
