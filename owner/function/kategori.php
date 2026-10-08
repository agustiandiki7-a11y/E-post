<?php
// KATEGORI = master CABANG  (tabel: branches)
require_once 'database/connection.php';

class Kategori
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->conn;
    }

    public function semua($cari = '')
    {
        $like = '%' . $cari . '%';
        $stmt = $this->conn->prepare(
            "SELECT b.*,
                    (SELECT COUNT(*) FROM products p WHERE p.branch_id = b.id) AS jml_produk,
                    (SELECT COUNT(*) FROM users u WHERE u.branch_id = b.id) AS jml_user
             FROM branches b
             WHERE b.nama_cabang LIKE ? OR b.alamat LIKE ?
             ORDER BY b.id ASC"
        );
        $stmt->bind_param('ss', $like, $like);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function cari($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM branches WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function tambah($nama_cabang, $alamat, $telepon)
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO branches (nama_cabang, alamat, telepon) VALUES (?, ?, ?)"
        );
        $stmt->bind_param('sss', $nama_cabang, $alamat, $telepon);
        return $stmt->execute();
    }

    public function ubah($id, $nama_cabang, $alamat, $telepon)
    {
        $stmt = $this->conn->prepare(
            "UPDATE branches SET nama_cabang = ?, alamat = ?, telepon = ? WHERE id = ?"
        );
        $stmt->bind_param('sssi', $nama_cabang, $alamat, $telepon, $id);
        return $stmt->execute();
    }

    public function hapus($id)
    {
        try {
            $stmt = $this->conn->prepare("DELETE FROM branches WHERE id = ?");
            $stmt->bind_param('i', $id);
            return $stmt->execute();
        } catch (mysqli_sql_exception $ex) {
            // gagal karena masih dipakai produk / user / transaksi
            return false;
        }
    }
}
