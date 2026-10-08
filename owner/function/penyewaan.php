<?php
// PENYEWAAN = TRANSAKSI penjualan semua cabang (tabel: transactions + transaction_details)
require_once 'database/connection.php';

class Penyewaan
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->conn;
    }

    public function semua($branch_id, $dari, $sampai, $cari = '')
    {
        $like = '%' . $cari . '%';
        $stmt = $this->conn->prepare(
            "SELECT t.*, b.nama_cabang, u.nama AS nama_kasir
             FROM transactions t
             JOIN branches b ON b.id = t.branch_id
             JOIN users u ON u.id = t.cashier_id
             WHERE (? = 0 OR t.branch_id = ?)
               AND DATE(t.tanggal) BETWEEN ? AND ?
               AND t.invoice_number LIKE ?
             ORDER BY t.tanggal DESC"
        );
        $stmt->bind_param('iisss', $branch_id, $branch_id, $dari, $sampai, $like);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function terbaru($limit = 5)
    {
        $stmt = $this->conn->prepare(
            "SELECT t.*, b.nama_cabang, u.nama AS nama_kasir
             FROM transactions t
             JOIN branches b ON b.id = t.branch_id
             JOIN users u ON u.id = t.cashier_id
             ORDER BY t.tanggal DESC LIMIT ?"
        );
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function cari($id)
    {
        $stmt = $this->conn->prepare(
            "SELECT t.*, b.nama_cabang, b.alamat, u.nama AS nama_kasir
             FROM transactions t
             JOIN branches b ON b.id = t.branch_id
             JOIN users u ON u.id = t.cashier_id
             WHERE t.id = ?"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function detail($transaction_id)
    {
        $stmt = $this->conn->prepare(
            "SELECT d.*, p.nama_produk
             FROM transaction_details d
             JOIN products p ON p.id = d.product_id
             WHERE d.transaction_id = ?"
        );
        $stmt->bind_param('i', $transaction_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
