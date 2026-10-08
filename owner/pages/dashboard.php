<?php
// DASHBOARD OWNER - ringkasan semua cabang
require_once 'function/pembayaran.php';
require_once 'function/penyewaan.php';
require_once 'function/kendaraan.php';

$lap = new Pembayaran();
$dari = '2000-01-01';
$sampai = date('Y-m-d');

$ringkas = $lap->ringkasan($dari, $sampai);
$master = $lap->hitungMaster();
$perCabang = $lap->perCabang($dari, $sampai);
$terbaru = (new Penyewaan())->terbaru(5);
$menipis = (new Kendaraan())->stokMenipis(10);
$omzetMax = 0;
foreach ($perCabang as $c) {
    $omzetMax = max($omzetMax, $c['total_penjualan']);
}
?>
<main id="content" class="content py-10">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="mb-4">
                    <h1 class="fs-3 mb-1">Dashboard Owner</h1>
                    <p class="mb-0">Halo, <?= e($_SESSION['user']['nama']) ?>. Berikut ringkasan seluruh cabang.</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card h-100"><div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="mb-1 text-secondary">Total Penjualan</p>
                            <h3 class="mb-0"><?= rupiah($ringkas['total_penjualan']) ?></h3>
                        </div>
                        <i class="ti ti-cash fs-2 text-primary"></i>
                    </div>
                </div></div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card h-100"><div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="mb-1 text-secondary">Jumlah Transaksi</p>
                            <h3 class="mb-0"><?= (int) $ringkas['jml_transaksi'] ?></h3>
                        </div>
                        <i class="ti ti-receipt fs-2 text-primary"></i>
                    </div>
                </div></div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card h-100"><div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="mb-1 text-secondary">Total Diskon</p>
                            <h3 class="mb-0"><?= rupiah($ringkas['total_diskon']) ?></h3>
                        </div>
                        <i class="ti ti-chart-bar fs-2 text-primary"></i>
                    </div>
                </div></div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card h-100"><div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="mb-1 text-secondary">Cabang / Produk / User</p>
                            <h3 class="mb-0"><?= (int) $master['cabang'] ?> / <?= (int) $master['produk'] ?> / <?= (int) $master['user'] ?></h3>
                        </div>
                        <i class="ti ti-building-store fs-2 text-primary"></i>
                    </div>
                </div></div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-7 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Penjualan per Cabang</h5>
                        <a href="index.php?page=pembayaran" class="small">Laporan lengkap</a>
                    </div>
                    <div class="card-body">
                        <?php foreach ($perCabang as $c) { $persen = $omzetMax > 0 ? round($c['total_penjualan'] / $omzetMax * 100) : 0; ?>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span class="fw-semibold"><?= e($c['nama_cabang']) ?></span>
                                    <span><?= rupiah($c['total_penjualan']) ?> <small class="text-secondary">(<?= (int) $c['jml_transaksi'] ?> transaksi)</small></span>
                                </div>
                                <div class="progress mt-1" style="height:8px"><div class="progress-bar" style="width:<?= $persen ?>%"></div></div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 mb-4">
                <div class="card h-100">
                    <div class="card-header"><h5 class="mb-0">Stok Menipis</h5></div>
                    <div class="table-responsive">
                        <table class="table mb-0 text-nowrap">
                            <thead class="table-light border-light"><tr><th>Produk</th><th>Cabang</th><th class="text-end">Stok</th></tr></thead>
                            <tbody>
                                <?php if (!$menipis) { ?><tr><td colspan="3" class="text-center text-secondary py-3">Semua stok aman.</td></tr><?php } ?>
                                <?php foreach ($menipis as $m) { ?>
                                    <tr>
                                        <td><?= e($m['nama_produk']) ?></td>
                                        <td><?= e($m['nama_cabang']) ?></td>
                                        <td class="text-end"><span class="badge bg-warning text-dark"><?= (int) $m['stok'] ?></span></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Transaksi Terbaru</h5>
                        <a href="index.php?page=penyewaan" class="small">Lihat semua</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table mb-0 text-nowrap">
                            <thead class="table-light border-light">
                                <tr><th>Invoice</th><th>Tanggal</th><th>Cabang</th><th>Kasir</th><th class="text-end">Total</th><th>Status</th></tr>
                            </thead>
                            <tbody>
                                <?php if (!$terbaru) { ?><tr><td colspan="6" class="text-center text-secondary py-3">Belum ada transaksi.</td></tr><?php } ?>
                                <?php foreach ($terbaru as $t) { ?>
                                    <tr>
                                        <td><a href="index.php?page=detailTransaksi&id=<?= (int) $t['id'] ?>"><?= e($t['invoice_number']) ?></a></td>
                                        <td><?= e(date('d/m/Y H:i', strtotime($t['tanggal']))) ?></td>
                                        <td><?= e($t['nama_cabang']) ?></td>
                                        <td><?= e($t['nama_kasir']) ?></td>
                                        <td class="text-end"><?= rupiah($t['total_bayar']) ?></td>
                                        <td><?= badgeStatus($t['status']) ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php include 'components/footer.php'; ?>
    </div>
</main>
