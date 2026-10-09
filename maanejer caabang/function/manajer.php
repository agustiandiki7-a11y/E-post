<?php
// Semua query manajer dibatasi ke SATU cabang ($branch_id dari session).
// Transaksi berstatus 'void' tidak dihitung sebagai penjualan.
require_once 'database/connection.php';

class Manajer
{
    private $conn;
    private $branch;
    public $error = '';

    public function __construct($branch_id)
    {
        $database = new Database();
        $this->conn = $database->conn;
        $this->branch = (int) $branch_id;
    }

    private function all($stmt)
    {
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function cabang()
    {
        $stmt = $this->conn->prepare("SELECT * FROM branches WHERE id = ?");
        $stmt->bind_param('i', $this->branch);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // ---------- DASHBOARD ----------
    public function ringkasan($dari, $sampai)
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) AS jml_transaksi,
                    COALESCE(SUM(total_bayar), 0) AS total_penjualan,
                    COALESCE(SUM(diskon), 0) AS total_diskon
             FROM transactions
             WHERE branch_id = ? AND status <> 'void' AND DATE(tanggal) BETWEEN ? AND ?"
        );
        $stmt->bind_param('iss', $this->branch, $dari, $sampai);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function jumlahMenungguVoid()
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) AS n FROM transactions WHERE branch_id = ? AND status = 'menunggu_void'"
        );
        $stmt->bind_param('i', $this->branch);
        $stmt->execute();
        return (int) $stmt->get_result()->fetch_assoc()['n'];
    }

    public function tanggalTerakhir()
    {
        $stmt = $this->conn->prepare("SELECT DATE(MAX(tanggal)) AS t FROM transactions WHERE branch_id = ?");
        $stmt->bind_param('i', $this->branch);
        $stmt->execute();
        $t = $stmt->get_result()->fetch_assoc()['t'];
        return $t ? $t : date('Y-m-d');
    }

    public function perHari($dari, $sampai)
    {
        $stmt = $this->conn->prepare(
            "SELECT DATE(tanggal) AS tgl, COUNT(*) AS jml_transaksi,
                    SUM(total_bayar) AS total_penjualan, SUM(diskon) AS total_diskon
             FROM transactions
             WHERE branch_id = ? AND status <> 'void' AND DATE(tanggal) BETWEEN ? AND ?
             GROUP BY DATE(tanggal) ORDER BY tgl DESC"
        );
        $stmt->bind_param('iss', $this->branch, $dari, $sampai);
        return $this->all($stmt);
    }

    public function terbaru($limit = 6)
    {
        $stmt = $this->conn->prepare(
            "SELECT t.*, u.nama AS nama_kasir
             FROM transactions t JOIN users u ON u.id = t.cashier_id
             WHERE t.branch_id = ? ORDER BY t.tanggal DESC LIMIT ?"
        );
        $stmt->bind_param('ii', $this->branch, $limit);
        return $this->all($stmt);
    }

    public function stokMenipis($batas = 10)
    {
        $stmt = $this->conn->prepare(
            "SELECT nama_produk, stok FROM products
             WHERE branch_id = ? AND stok <= ? ORDER BY stok ASC LIMIT 6"
        );
        $stmt->bind_param('ii', $this->branch, $batas);
        return $this->all($stmt);
    }

    // ---------- PENJUALAN CABANG ----------
    public function kasirCabang()
    {
        $stmt = $this->conn->prepare(
            "SELECT id, nama FROM users WHERE branch_id = ? AND role = 'kasir' ORDER BY nama"
        );
        $stmt->bind_param('i', $this->branch);
        return $this->all($stmt);
    }

    public function penjualan($dari, $sampai, $kasir_id, $cari)
    {
        $like = '%' . $cari . '%';
        $stmt = $this->conn->prepare(
            "SELECT t.*, u.nama AS nama_kasir
             FROM transactions t JOIN users u ON u.id = t.cashier_id
             WHERE t.branch_id = ?
               AND DATE(t.tanggal) BETWEEN ? AND ?
               AND (? = 0 OR t.cashier_id = ?)
               AND t.invoice_number LIKE ?
             ORDER BY t.tanggal DESC"
        );
        $stmt->bind_param('issiis', $this->branch, $dari, $sampai, $kasir_id, $kasir_id, $like);
        return $this->all($stmt);
    }

    public function transaksi($id)
    {
        $stmt = $this->conn->prepare(
            "SELECT t.*, u.nama AS nama_kasir, b.nama_cabang
             FROM transactions t
             JOIN users u ON u.id = t.cashier_id
             JOIN branches b ON b.id = t.branch_id
             WHERE t.id = ? AND t.branch_id = ?"
        );
        $stmt->bind_param('ii', $id, $this->branch);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function detail($transaction_id)
    {
        $stmt = $this->conn->prepare(
            "SELECT d.*, p.nama_produk
             FROM transaction_details d JOIN products p ON p.id = d.product_id
             WHERE d.transaction_id = ?"
        );
        $stmt->bind_param('i', $transaction_id);
        return $this->all($stmt);
    }

    // ---------- SHIFT KASIR ----------
    // Penutupan shift dihitung dari transaksi: per kasir pada satu tanggal.
    public function shiftKasir($tanggal)
    {
        $stmt = $this->conn->prepare(
            "SELECT u.id, u.nama,
                    COUNT(CASE WHEN t.status <> 'void' THEN 1 END) AS jml_transaksi,
                    COALESCE(SUM(CASE WHEN t.status <> 'void' THEN t.total_bayar END), 0) AS total_penjualan,
                    COALESCE(SUM(CASE WHEN t.status <> 'void' THEN t.diskon END), 0) AS total_diskon,
                    COUNT(CASE WHEN t.status = 'void' THEN 1 END) AS jml_void,
                    COUNT(CASE WHEN t.status = 'menunggu_void' THEN 1 END) AS jml_menunggu,
                    MIN(t.tanggal) AS jam_awal, MAX(t.tanggal) AS jam_akhir
             FROM users u
             LEFT JOIN transactions t
                    ON t.cashier_id = u.id AND t.branch_id = u.branch_id AND DATE(t.tanggal) = ?
             WHERE u.branch_id = ? AND u.role = 'kasir'
             GROUP BY u.id, u.nama
             ORDER BY u.nama"
        );
        $stmt->bind_param('si', $tanggal, $this->branch);
        return $this->all($stmt);
    }

    // ---------- VOID ----------
    public function daftarVoid($status)
    {
        $stmt = $this->conn->prepare(
            "SELECT t.*, u.nama AS nama_kasir
             FROM transactions t JOIN users u ON u.id = t.cashier_id
             WHERE t.branch_id = ? AND t.status = ?
             ORDER BY t.tanggal DESC"
        );
        $stmt->bind_param('is', $this->branch, $status);
        return $this->all($stmt);
    }

    // Setujui void: status jadi 'void' dan stok produk dikembalikan.
    public function setujuiVoid($id)
    {
        $this->conn->begin_transaction();
        try {
            $stmt = $this->conn->prepare(
                "SELECT status FROM transactions WHERE id = ? AND branch_id = ? FOR UPDATE"
            );
            $stmt->bind_param('ii', $id, $this->branch);
            $stmt->execute();
            $trx = $stmt->get_result()->fetch_assoc();
            if (!$trx || $trx['status'] !== 'menunggu_void') {
                $this->conn->rollback();
                $this->error = 'Transaksi tidak ditemukan atau sudah diproses.';
                return false;
            }

            $stmt = $this->conn->prepare(
                "SELECT product_id, SUM(jumlah) AS qty FROM transaction_details
                 WHERE transaction_id = ? GROUP BY product_id"
            );
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            $upd = $this->conn->prepare("UPDATE products SET stok = stok + ? WHERE id = ?");
            foreach ($items as $it) {
                $qty = (int) $it['qty'];
                $pid = (int) $it['product_id'];
                $upd->bind_param('ii', $qty, $pid);
                $upd->execute();
            }

            $stmt = $this->conn->prepare(
                "UPDATE transactions SET status = 'void' WHERE id = ? AND branch_id = ?"
            );
            $stmt->bind_param('ii', $id, $this->branch);
            $stmt->execute();

            $this->conn->commit();
            return true;
        } catch (mysqli_sql_exception $ex) {
            $this->conn->rollback();
            $this->error = 'Terjadi kesalahan database saat memproses void.';
            return false;
        }
    }

    // Tolak void: transaksi kembali 'selesai', stok tidak berubah.
    public function tolakVoid($id)
    {
        $stmt = $this->conn->prepare(
            "UPDATE transactions SET status = 'selesai'
             WHERE id = ? AND branch_id = ? AND status = 'menunggu_void'"
        );
        $stmt->bind_param('ii', $id, $this->branch);
        $stmt->execute();
        if ($stmt->affected_rows < 1) {
            $this->error = 'Transaksi tidak ditemukan atau sudah diproses.';
            return false;
        }
        return true;
    }
}
