<?php
// Pemeriksa skema Nexora POS: kalau database/tabel belum sesuai, tampilkan halaman ramah
// yang mengarahkan ke install.php (bukan error fatal).
if (!function_exists('eposHentikan')) {
    function eposHentikan($detail)
    {
        if (!headers_sent()) {
            http_response_code(503);
            header('Content-Type: text/html; charset=utf-8');
            header('Cache-Control: no-store');
        }
        $detail = htmlspecialchars((string) $detail, ENT_QUOTES, 'UTF-8');
        echo '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
            . '<title>Database belum siap - Nexora POS</title></head>'
            . '<body style="font-family:Segoe UI,Arial,sans-serif;background:#f5f5f5;margin:0;padding:40px 16px">'
            . '<div style="max-width:520px;margin:0 auto;background:#fff;border-radius:12px;padding:28px;box-shadow:0 2px 10px rgba(0,0,0,.08)">'
            . '<h2 style="margin:0 0 8px">Database belum siap</h2>'
            . '<p style="color:#555">Tabel Nexora POS belum lengkap di database <b>e-pos</b>. Jalankan installer sekali untuk membuatnya, lalu login lagi.</p>'
            . '<p><a href="../install.php" style="display:inline-block;background:#4942dd;color:#fff;text-decoration:none;padding:10px 18px;border-radius:8px">Buka Installer</a></p>'
            . '<p style="color:#888;font-size:12px;word-break:break-word">Detail: ' . $detail . '</p>'
            . '</div></body></html>';
        exit;
    }
}

function eposCekSkema($conn)
{
    $cek = [
        'branches'            => 'id, nama_cabang, alamat, telepon',
        'users'               => 'id, nama, email, password, role, branch_id',
        'products'            => 'id, branch_id, nama_produk, harga_jual, stok',
        'transactions'        => 'id, branch_id, cashier_id, invoice_number, total_bayar, diskon, tanggal, status',
        'transaction_details' => 'id, transaction_id, product_id, jumlah, harga_satuan, subtotal',
    ];
    foreach ($cek as $tabel => $kolom) {
        try {
            $conn->query("SELECT $kolom FROM $tabel LIMIT 1");
        } catch (mysqli_sql_exception $ex) {
            eposHentikan("Tabel '$tabel' belum ada atau kolomnya tidak sesuai. (" . $ex->getMessage() . ')');
        }
    }

    // Migrasi otomatis: kolom foto produk (database lama dari versi sebelumnya belum punya)
    try {
        $conn->query("SELECT foto FROM products LIMIT 1");
    } catch (mysqli_sql_exception $ex) {
        try {
            $conn->query("ALTER TABLE products ADD COLUMN foto VARCHAR(255) NULL");
            $bawaan = [
                'Indomie Goreng' => 'indomie-goreng', 'Indomie Soto' => 'indomie-soto', 'Aqua 600ml' => 'aqua',
                'Teh Botol' => 'teh-botol', 'Teh Pucuk Harum' => 'teh-pucuk', 'Beras 5kg' => 'beras',
                'Gula Pasir 1kg' => 'gula', 'Minyak Goreng 1L' => 'minyak', 'Kopi Sachet' => 'kopi',
                'Roti Tawar' => 'roti', 'Susu UHT 1L' => 'susu', 'Telur Ayam 1kg' => 'telur',
                'Sabun Mandi' => 'sabun', 'Deterjen 800g' => 'deterjen',
            ];
            $up = $conn->prepare("UPDATE products SET foto = ? WHERE nama_produk = ? AND foto IS NULL");
            foreach ($bawaan as $nama => $berkas) {
                $path = 'assets/produk/' . $berkas . '.svg';
                $up->bind_param('ss', $path, $nama);
                $up->execute();
            }
        } catch (mysqli_sql_exception $ex2) {
            eposHentikan('Kolom foto produk belum bisa dibuat otomatis. Jalankan install.php. (' . $ex2->getMessage() . ')');
        }
    }

    // Migrasi otomatis: tabel riwayat stok dan shift kasir (database lama belum punya)
    $baru = [
        'stock_logs' => "CREATE TABLE stock_logs (
            id INT NOT NULL AUTO_INCREMENT,
            product_id INT NOT NULL,
            branch_id INT NOT NULL,
            user_id INT DEFAULT NULL,
            tipe VARCHAR(20) NOT NULL,
            jumlah INT NOT NULL,
            stok_sebelum INT NOT NULL,
            stok_sesudah INT NOT NULL,
            keterangan VARCHAR(255) DEFAULT NULL,
            dibuat DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_log_produk (product_id),
            KEY idx_log_cabang (branch_id),
            KEY idx_log_dibuat (dibuat)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
        'shifts' => "CREATE TABLE shifts (
            id INT NOT NULL AUTO_INCREMENT,
            branch_id INT NOT NULL,
            cashier_id INT NOT NULL,
            buka DATETIME NOT NULL,
            tutup DATETIME DEFAULT NULL,
            kas_awal DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            kas_akhir DECIMAL(15,2) DEFAULT NULL,
            penjualan DECIMAL(15,2) DEFAULT NULL,
            selisih DECIMAL(15,2) DEFAULT NULL,
            catatan VARCHAR(255) DEFAULT NULL,
            PRIMARY KEY (id),
            KEY idx_shift_kasir (cashier_id),
            KEY idx_shift_cabang (branch_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
    ];
    foreach ($baru as $tabel => $ddl) {
        try {
            $conn->query("SELECT id FROM $tabel LIMIT 1");
        } catch (mysqli_sql_exception $ex) {
            try {
                $conn->query($ddl);
            } catch (mysqli_sql_exception $ex2) {
                eposHentikan("Tabel '$tabel' belum bisa dibuat otomatis. Jalankan install.php. (" . $ex2->getMessage() . ')');
            }
        }
    }
}
