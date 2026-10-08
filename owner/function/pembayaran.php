<?php
// PEMBAYARAN = LAPORAN AGREGAT penjualan (dihitung dari transactions)
// Transaksi berstatus 'void' tidak dihitung.
require_once 'database/connection.php';

class Pembayaran
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->conn;
    }

    public function ringkasan($dari, $sampai)
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) AS jml_transaksi,
                    COALESCE(SUM(total_bayar), 0) AS total_penjualan,
                    COALESCE(SUM(diskon), 0) AS total_diskon
             FROM transactions
             WHERE status <> 'void' AND DATE(tanggal) BETWEEN ? AND ?"
        );
        $stmt->bind_param('ss', $dari, $sampai);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function perCabang($dari, $sampai)
    {
        $stmt = $this->conn->prepare(
            "SELECT b.id, b.nama_cabang,
                    COUNT(t.id) AS jml_transaksi,
                    COALESCE(SUM(t.total_bayar), 0) AS total_penjualan,
                    COALESCE(SUM(t.diskon), 0) AS total_diskon
             FROM branches b
             LEFT JOIN transactions t
                    ON t.branch_id = b.id AND t.status <> 'void'
                   AND DATE(t.tanggal) BETWEEN ? AND ?
             GROUP BY b.id, b.nama_cabang
             ORDER BY total_penjualan DESC"
        );
        $stmt->bind_param('ss', $dari, $sampai);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function perHari($dari, $sampai)
    {
        $stmt = $this->conn->prepare(
            "SELECT DATE(tanggal) AS tgl, COUNT(*) AS jml_transaksi,
                    SUM(total_bayar) AS total_penjualan, SUM(diskon) AS total_diskon
             FROM transactions
             WHERE status <> 'void' AND DATE(tanggal) BETWEEN ? AND ?
             GROUP BY DATE(tanggal)
             ORDER BY tgl DESC"
        );
        $stmt->bind_param('ss', $dari, $sampai);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function produkTerlaris($dari, $sampai, $limit = 5)
    {
        $stmt = $this->conn->prepare(
            "SELECT p.nama_produk, b.nama_cabang,
                    SUM(d.jumlah) AS qty, SUM(d.subtotal) AS omzet
             FROM transaction_details d
             JOIN transactions t ON t.id = d.transaction_id
             JOIN products p ON p.id = d.product_id
             JOIN branches b ON b.id = p.branch_id
             WHERE t.status <> 'void' AND DATE(t.tanggal) BETWEEN ? AND ?
             GROUP BY p.id, p.nama_produk, b.nama_cabang
             ORDER BY qty DESC LIMIT ?"
        );
        $stmt->bind_param('ssi', $dari, $sampai, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function hitungMaster()
    {
        $r = $this->conn->query(
            "SELECT (SELECT COUNT(*) FROM branches) AS cabang,
                    (SELECT COUNT(*) FROM products) AS produk,
                    (SELECT COUNT(*) FROM users)    AS user"
        );
        return $r->fetch_assoc();
    }
}
