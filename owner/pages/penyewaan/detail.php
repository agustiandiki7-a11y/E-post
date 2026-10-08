<?php
require_once 'function/penyewaan.php';
$trx = new Penyewaan();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$head = $trx->cari($id);
if (!$head) {
    redirect('penyewaan');
}
$items = $trx->detail($id);
$subtotal = 0;
foreach ($items as $it) {
    $subtotal += $it['subtotal'];
}
?>
<main id="content" class="content py-10">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                    <div>
                        <h1 class="fs-3 mb-1">Detail Transaksi</h1>
                        <p class="mb-0"><?= e($head['invoice_number']) ?></p>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-secondary" onclick="window.print()"><i class="ti ti-printer"></i> Cetak</button>
                        <a href="index.php?page=penyewaan" class="btn btn-primary">Kembali ke Transaksi</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-body p-4">
                        <dl class="mb-0">
                            <dt>Cabang</dt><dd><?= e($head['nama_cabang']) ?></dd>
                            <dt>Kasir</dt><dd><?= e($head['nama_kasir']) ?></dd>
                            <dt>Tanggal</dt><dd><?= e(date('d/m/Y H:i', strtotime($head['tanggal']))) ?></dd>
                            <dt>Status</dt><dd class="mb-0"><?= badgeStatus($head['status']) ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="col-lg-8 mb-4">
                <div class="card table-responsive">
                    <table class="table mb-0 text-nowrap">
                        <thead class="table-light border-light">
                            <tr>
                                <th>Produk</th>
                                <th class="text-end">Jumlah</th>
                                <th class="text-end">Harga Satuan</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $it) { ?>
                                <tr>
                                    <td><?= e($it['nama_produk']) ?></td>
                                    <td class="text-end"><?= (int) $it['jumlah'] ?></td>
                                    <td class="text-end"><?= rupiah($it['harga_satuan']) ?></td>
                                    <td class="text-end"><?= rupiah($it['subtotal']) ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                        <tfoot>
                            <tr><td colspan="3" class="text-end">Subtotal</td><td class="text-end"><?= rupiah($subtotal) ?></td></tr>
                            <tr><td colspan="3" class="text-end">Diskon</td><td class="text-end">- <?= rupiah($head['diskon']) ?></td></tr>
                            <tr><td colspan="3" class="text-end fw-bold border-bottom-0">Total Bayar</td><td class="text-end fw-bold border-bottom-0"><?= rupiah($head['total_bayar']) ?></td></tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        <?php include 'components/footer.php'; ?>
    </div>
</main>
