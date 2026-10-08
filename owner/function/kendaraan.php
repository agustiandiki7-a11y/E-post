<?php
// KENDARAAN = master PRODUK  (tabel: products)
require_once 'database/connection.php';

class Kendaraan
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->conn;
    }

    public function semua($branch_id = 0, $cari = '')
    {
        $like = '%' . $cari . '%';
        $stmt = $this->conn->prepare(
            "SELECT p.*, b.nama_cabang
             FROM products p
             JOIN branches b ON b.id = p.branch_id
             WHERE (? = 0 OR p.branch_id = ?) AND p.nama_produk LIKE ?
             ORDER BY p.id DESC"
        );
        $stmt->bind_param('iis', $branch_id, $branch_id, $like);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function cari($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function tambah($branch_id, $nama_produk, $harga_jual, $stok)
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO products (branch_id, nama_produk, harga_jual, stok) VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param('isdi', $branch_id, $nama_produk, $harga_jual, $stok);
        return $stmt->execute();
    }

    public function ubah($id, $branch_id, $nama_produk, $harga_jual, $stok)
    {
        $stmt = $this->conn->prepare(
            "UPDATE products SET branch_id = ?, nama_produk = ?, harga_jual = ?, stok = ? WHERE id = ?"
        );
        $stmt->bind_param('isdii', $branch_id, $nama_produk, $harga_jual, $stok, $id);
        return $stmt->execute();
    }

    public function hapus($id)
    {
        try {
            $stmt = $this->conn->prepare("DELETE FROM products WHERE id = ?");
            $stmt->bind_param('i', $id);
            return $stmt->execute();
        } catch (mysqli_sql_exception $ex) {
            // gagal karena produk sudah pernah terjual
            return false;
        }
    }

    public function stokMenipis($batas = 10)
    {
        $stmt = $this->conn->prepare(
            "SELECT p.nama_produk, p.stok, b.nama_cabang
             FROM products p JOIN branches b ON b.id = p.branch_id
             WHERE p.stok <= ? ORDER BY p.stok ASC LIMIT 6"
        );
        $stmt->bind_param('i', $batas);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
