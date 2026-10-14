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
}
