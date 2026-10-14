<?php
// STRUK - halaman mandiri (tanpa sidebar) supaya bersih saat dicetak.
// Dipanggil dari kasir/index.php; $ksr dan $cabang sudah tersedia.
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$trx = $ksr->transaksi($id);
if (!$trx) {
    http_response_code(404);
    echo 'Struk tidak ditemukan.';
    return;
}
$items = $ksr->detail($id);
$subtotal = 0;
foreach ($items as $it) {
    $subtotal += $it['subtotal'];
}
$baru = isset($_GET['baru']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Struk <?= e($trx['invoice_number']) ?></title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #f5f5f5; font-family: "Courier New", Consolas, monospace; font-size: 13px; color: #111; }
        .aksi { max-width: 320px; margin: 16px auto 0; display: flex; gap: 8px; flex-wrap: wrap; font-family: "Segoe UI", Arial, sans-serif; }
        .aksi a, .aksi button { flex: 1; text-align: center; padding: 10px 12px; border-radius: 8px; border: 1px solid #d4d4d4; background: #fff; color: #111; text-decoration: none; font-size: 14px; cursor: pointer; }
        .aksi .kembali { flex: 0 0 100%; text-align: left; border: 0; background: transparent; padding: 4px 2px; color: #555; }
        .aksi .kembali:hover { color: #4942dd; }
        .aksi .baru { background: #111; border-color: #111; color: #fff; }
        .aksi .utama { background: #4942dd; border-color: #4942dd; color: #fff; }
        .ok { max-width: 320px; margin: 16px auto 0; padding: 10px 12px; border-radius: 8px; background: #dcfce7; color: #166534; font-family: "Segoe UI", Arial, sans-serif; font-size: 14px; }
        .struk { width: 320px; margin: 16px auto 32px; background: #fff; padding: 18px 16px 22px; box-shadow: 0 2px 10px rgba(0,0,0,.08); }
        .c { text-align: center; }
        h1 { font-size: 18px; margin: 0; letter-spacing: 2px; }
        hr { border: 0; border-top: 1px dashed #777; margin: 10px 0; }
        .r { display: flex; justify-content: space-between; gap: 8px; }
        .item { margin: 5px 0; }
        .item .nama { word-break: break-word; }
        .total { font-weight: bold; font-size: 15px; }
        .void { margin: 8px 0; padding: 4px; border: 2px solid #b91c1c; color: #b91c1c; text-align: center; font-weight: bold; }
        .kecil { color: #555; font-size: 12px; }
        @media print {
            body { background: #fff; }
            .aksi, .ok { display: none !important; }
            .struk { box-shadow: none; margin: 0 auto; width: 100%; max-width: 80mm; }
            @page { margin: 6mm; }
        }
    </style>
</head>
<body>
    <div class="aksi">
        <a href="<?= $baru ? 'index.php?page=dashboard' : 'index.php?page=riwayat' ?>" class="kembali">&larr; Kembali</a>
        <button type="button" class="utama" onclick="window.print()">Cetak Struk</button>
        <a href="index.php?page=baru" class="baru">+ Transaksi Baru</a>
    </div>

    <div class="struk">
        <div class="c">
            <h1>Nexora POS</h1>
            <div><?= e($cabang['nama_cabang']) ?></div>
            <div class="kecil"><?= e($cabang['alamat']) ?></div>
            <div class="kecil"><?= e($cabang['telepon']) ?></div>
        </div>
        <hr>
        <div class="r"><span>No</span><span><?= e($trx['invoice_number']) ?></span></div>
        <div class="r"><span>Tanggal</span><span><?= e(date('d/m/Y H:i', strtotime($trx['tanggal']))) ?></span></div>
        <div class="r"><span>Kasir</span><span><?= e($trx['nama_kasir']) ?></span></div>
        <?php if ($trx['status'] === 'void') { ?><div class="void">TRANSAKSI DIBATALKAN (VOID)</div><?php } ?>
        <?php if ($trx['status'] === 'menunggu_void') { ?><div class="void">MENUNGGU PERSETUJUAN VOID</div><?php } ?>
        <hr>
        <?php foreach ($items as $it) { ?>
            <div class="item">
                <div class="nama"><?= e($it['nama_produk']) ?></div>
                <div class="r">
                    <span><?= (int) $it['jumlah'] ?> x <?= number_format((float) $it['harga_satuan'], 0, ',', '.') ?></span>
                    <span><?= number_format((float) $it['subtotal'], 0, ',', '.') ?></span>
                </div>
            </div>
        <?php } ?>
        <hr>
        <div class="r"><span>Subtotal</span><span><?= number_format($subtotal, 0, ',', '.') ?></span></div>
        <div class="r"><span>Diskon</span><span>-<?= number_format((float) $trx['diskon'], 0, ',', '.') ?></span></div>
        <div class="r total"><span>TOTAL</span><span><?= rupiah($trx['total_bayar']) ?></span></div>
        <hr>
        <div class="c">Terima kasih<br>Selamat berbelanja kembali</div>
    </div>
<?php toastRender(); ?>
</body>
</html>
