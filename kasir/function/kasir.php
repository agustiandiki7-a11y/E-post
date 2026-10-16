<?php
// Semua query kasir dibatasi ke SATU cabang dan (untuk transaksi) ke kasir yang login.
// Harga, subtotal, dan total SELALU dihitung ulang dari database, tidak dipercaya dari browser.
require_once 'database/connection.php';

class Kasir
{
    private $conn;
    private $branch;
    private $uid;
    public $error = '';

    public function __construct($branch_id, $user_id)
    {
        $database = new Database();
        $this->conn = $database->conn;
        $this->branch = (int) $branch_id;
        $this->uid = (int) $user_id;
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

    // ---------- PRODUK ----------
    public function produk($cari = '')
    {
        $like = '%' . $cari . '%';
        $stmt = $this->conn->prepare(
            "SELECT id, nama_produk, harga_jual, stok, foto FROM products
             WHERE branch_id = ? AND nama_produk LIKE ? ORDER BY nama_produk ASC"
        );
        $stmt->bind_param('is', $this->branch, $like);
        return $this->all($stmt);
    }

    public function stokMenipis($batas = 10)
    {
        $stmt = $this->conn->prepare(
            "SELECT nama_produk, stok, foto FROM products
             WHERE branch_id = ? AND stok <= ? ORDER BY stok ASC LIMIT 6"
        );
        $stmt->bind_param('ii', $this->branch, $batas);
        return $this->all($stmt);
    }

    // ---------- RINGKASAN & RIWAYAT ----------
    public function ringkasan($tanggal)
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) AS jml_transaksi,
                    COALESCE(SUM(total_bayar), 0) AS total_penjualan,
                    COALESCE(SUM(diskon), 0) AS total_diskon
             FROM transactions
             WHERE branch_id = ? AND cashier_id = ? AND status <> 'void' AND DATE(tanggal) = ?"
        );
        $stmt->bind_param('iis', $this->branch, $this->uid, $tanggal);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function terbaru($limit = 6)
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM transactions
             WHERE branch_id = ? AND cashier_id = ? ORDER BY tanggal DESC, id DESC LIMIT ?"
        );
        $stmt->bind_param('iii', $this->branch, $this->uid, $limit);
        return $this->all($stmt);
    }

    public function riwayat($dari, $sampai, $cari)
    {
        $like = '%' . $cari . '%';
        $stmt = $this->conn->prepare(
            "SELECT * FROM transactions
             WHERE branch_id = ? AND cashier_id = ?
               AND DATE(tanggal) BETWEEN ? AND ?
               AND invoice_number LIKE ?
             ORDER BY tanggal DESC, id DESC"
        );
        $stmt->bind_param('iisss', $this->branch, $this->uid, $dari, $sampai, $like);
        return $this->all($stmt);
    }

    public function transaksi($id)
    {
        $stmt = $this->conn->prepare(
            "SELECT t.*, u.nama AS nama_kasir
             FROM transactions t JOIN users u ON u.id = t.cashier_id
             WHERE t.id = ? AND t.branch_id = ? AND t.cashier_id = ?"
        );
        $stmt->bind_param('iii', $id, $this->branch, $this->uid);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function detail($transaction_id)
    {
        $stmt = $this->conn->prepare(
            "SELECT d.*, p.nama_produk, p.foto
             FROM transaction_details d JOIN products p ON p.id = d.product_id
             WHERE d.transaction_id = ?"
        );
        $stmt->bind_param('i', $transaction_id);
        return $this->all($stmt);
    }

    // ---------- TRANSAKSI BARU ----------
    // $items: [product_id => jumlah]. $diskonTipe: 'rupiah' atau 'persen'.
    // Mengembalikan id transaksi, atau false (alasannya ada di $this->error).
    public function simpan($items, $diskonTipe, $diskonNilai)
    {
        $bersih = [];
        foreach ((array) $items as $pid => $qty) {
            $pid = (int) $pid;
            $qty = (int) $qty;
            if ($pid > 0 && $qty > 0) {
                $bersih[$pid] = $qty;
            }
        }
        if (!$bersih) {
            $this->error = 'Keranjang masih kosong.';
            return false;
        }

        $this->conn->begin_transaction();
        try {
            // 1) kunci dan baca produk, hitung subtotal dari harga di database
            $sel = $this->conn->prepare(
                "SELECT id, nama_produk, harga_jual, stok FROM products WHERE id = ? AND branch_id = ? FOR UPDATE"
            );
            $subtotal = 0;
            $baris = [];
            foreach ($bersih as $pid => $qty) {
                $sel->bind_param('ii', $pid, $this->branch);
                $sel->execute();
                $p = $sel->get_result()->fetch_assoc();
                if (!$p) {
                    $this->conn->rollback();
                    $this->error = 'Ada produk yang tidak ditemukan di cabang ini.';
                    return false;
                }
                if ((int) $p['stok'] < $qty) {
                    $this->conn->rollback();
                    $this->error = 'Stok ' . $p['nama_produk'] . ' tidak cukup (sisa ' . (int) $p['stok'] . ').';
                    return false;
                }
                $harga = (float) $p['harga_jual'];
                $sub = $harga * $qty;
                $subtotal += $sub;
                $baris[] = [$pid, $qty, $harga, $sub, (int) $p['stok']];
            }

            // 2) diskon: nominal rupiah atau persen, tidak boleh melebihi subtotal
            $nilai = max(0, (float) $diskonNilai);
            if ($diskonTipe === 'persen') {
                $diskon = round($subtotal * min(100, $nilai) / 100);
            } else {
                $diskon = $nilai;
            }
            $diskon = min($diskon, $subtotal);
            $total = $subtotal - $diskon;

            // 3) simpan transaksi (nomor invoice dicoba ulang kalau bentrok)
            $tanggal = date('Y-m-d H:i:s');
            $tid = 0;
            for ($coba = 1; $coba <= 3 && !$tid; $coba++) {
                $invoice = $this->nomorInvoice();
                try {
                    $ins = $this->conn->prepare(
                        "INSERT INTO transactions (branch_id, cashier_id, invoice_number, total_bayar, diskon, tanggal, status)
                         VALUES (?, ?, ?, ?, ?, ?, 'selesai')"
                    );
                    $ins->bind_param('iisdds', $this->branch, $this->uid, $invoice, $total, $diskon, $tanggal);
                    $ins->execute();
                    $tid = (int) $this->conn->insert_id;
                } catch (mysqli_sql_exception $ex) {
                    if ($ex->getCode() !== 1062 || $coba === 3) {
                        throw $ex;
                    }
                }
            }

            // 4) detail transaksi + kurangi stok
            $det = $this->conn->prepare(
                "INSERT INTO transaction_details (transaction_id, product_id, jumlah, harga_satuan, subtotal)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $kurang = $this->conn->prepare("UPDATE products SET stok = stok - ? WHERE id = ? AND stok >= ?");
            foreach ($baris as $b) {
                list($pid, $qty, $harga, $sub, $stokAwal) = $b;
                $det->bind_param('iiidd', $tid, $pid, $qty, $harga, $sub);
                $det->execute();
                $kurang->bind_param('iii', $qty, $pid, $qty);
                $kurang->execute();
                if ($kurang->affected_rows < 1) {
                    throw new mysqli_sql_exception('Stok tidak mencukupi.');
                }
                stokLogTulis($this->conn, $pid, $this->branch, 'penjualan', -$qty, $stokAwal, $stokAwal - $qty, $invoice);
            }

            $this->conn->commit();
            return $tid;
        } catch (mysqli_sql_exception $ex) {
            $this->conn->rollback();
            $this->error = 'Transaksi gagal disimpan. Coba lagi.';
            return false;
        }
    }

    // Nomor invoice: INV-TahunBulanTanggal-0001, urut per hari di seluruh cabang
    private function nomorInvoice()
    {
        $awal = 'INV-' . date('Ymd') . '-';
        $like = $awal . '%';
        $stmt = $this->conn->prepare(
            "SELECT invoice_number FROM transactions WHERE invoice_number LIKE ?
             ORDER BY invoice_number DESC LIMIT 1 FOR UPDATE"
        );
        $stmt->bind_param('s', $like);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $n = $row ? ((int) substr($row['invoice_number'], -4)) + 1 : 1;
        return $awal . str_pad((string) $n, 4, '0', STR_PAD_LEFT);
    }

    // ---------- VOID ----------
    // Kasir hanya MENGAJUKAN. Manajer cabang yang menyetujui (dan stok dikembalikan di sana).
    public function ajukanVoid($id)
    {
        $stmt = $this->conn->prepare(
            "UPDATE transactions SET status = 'menunggu_void'
             WHERE id = ? AND branch_id = ? AND cashier_id = ? AND status = 'selesai'"
        );
        $stmt->bind_param('iii', $id, $this->branch, $this->uid);
        $stmt->execute();
        if ($stmt->affected_rows < 1) {
            $this->error = 'Transaksi tidak ditemukan atau sudah diajukan void.';
            return false;
        }
        return true;
    }
}
