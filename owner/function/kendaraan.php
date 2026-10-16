<?php
// KENDARAAN = master PRODUK  (tabel: products)
require_once 'database/connection.php';

class Kendaraan
{
    private $conn;
    public $error = '';

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

    private function cek($branch_id, $nama, $harga, $stok)
    {
        if ((int) $branch_id < 1) {
            $this->error = 'Pilih cabang untuk produk ini.';
        } elseif (trim($nama) === '' || mb_strlen($nama) > 100) {
            $this->error = 'Nama produk wajib diisi (maksimal 100 karakter).';
        } elseif ($harga < 0 || $harga > 999999999) {
            $this->error = 'Harga jual tidak valid.';
        } elseif ($stok < 0 || $stok > 1000000) {
            $this->error = 'Stok tidak boleh negatif.';
        } else {
            return true;
        }
        return false;
    }

    public function tambah($branch_id, $nama_produk, $harga_jual, $stok, $foto = null)
    {
        if (!$this->cek($branch_id, $nama_produk, $harga_jual, $stok)) {
            return false;
        }
        $stmt = $this->conn->prepare(
            "INSERT INTO products (branch_id, nama_produk, harga_jual, stok, foto) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param('isdis', $branch_id, $nama_produk, $harga_jual, $stok, $foto);
        $ok = $stmt->execute();
        if ($ok && $stok > 0) {
            stokLogTulis($this->conn, (int) $this->conn->insert_id, (int) $branch_id, 'awal', (int) $stok, 0, (int) $stok, 'Produk baru ditambahkan');
        }
        return $ok;
    }

    // $foto: false = foto tidak diubah, null = hapus foto, string = path foto baru
    public function ubah($id, $branch_id, $nama_produk, $harga_jual, $stok, $foto = false)
    {
        if (!$this->cek($branch_id, $nama_produk, $harga_jual, $stok)) {
            return false;
        }
        $lama = $this->cari($id);
        if ($foto === false) {
            $stmt = $this->conn->prepare(
                "UPDATE products SET branch_id = ?, nama_produk = ?, harga_jual = ?, stok = ? WHERE id = ?"
            );
            $stmt->bind_param('isdii', $branch_id, $nama_produk, $harga_jual, $stok, $id);
        } else {
            $stmt = $this->conn->prepare(
                "UPDATE products SET branch_id = ?, nama_produk = ?, harga_jual = ?, stok = ?, foto = ? WHERE id = ?"
            );
            $stmt->bind_param('isdisi', $branch_id, $nama_produk, $harga_jual, $stok, $foto, $id);
        }
        $ok = $stmt->execute();
        if ($ok && $lama && (int) $lama['stok'] !== (int) $stok) {
            stokLogTulis($this->conn, (int) $id, (int) $branch_id, 'koreksi', (int) $stok - (int) $lama['stok'], (int) $lama['stok'], (int) $stok, 'Diubah lewat form produk');
        }
        return $ok;
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
            "SELECT p.nama_produk, p.stok, p.foto, b.nama_cabang
             FROM products p JOIN branches b ON b.id = p.branch_id
             WHERE p.stok <= ? ORDER BY p.stok ASC LIMIT 6"
        );
        $stmt->bind_param('i', $batas);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
