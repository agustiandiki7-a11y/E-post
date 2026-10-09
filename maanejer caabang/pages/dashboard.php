<?php
// DASHBOARD MANAJER - hanya data cabang sendiri
$hariIni = date('Y-m-d');
$awalBulan = date('Y-m-01');
$tujuhHari = date('Y-m-d', strtotime('-6 days'));

$rHari = $mgr->ringkasan($hariIni, $hariIni);
$rBulan = $mgr->ringkasan($awalBulan, $hariIni);
$harian = $mgr->perHari($tujuhHari, $hariIni);
$terbaru = $mgr->terbaru(6);
$menipis = $mgr->stokMenipis(10);

$maks = 0;
foreach ($harian as $h) {
    $maks = max($maks, $h['total_penjualan']);
}
?>
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card h-100"><div class="card-body">
            <div class="d-flex justify-content-between">
                <div>
                    <p class="mb-1 text-secondary">Penjualan Hari Ini</p>
                    <h3 class="mb-1"><?= rupiah($rHari['total_penjualan']) ?></h3>
                    <small class="text-secondary"><?= (int) $rHari['jml_transaksi'] ?> transaksi</small>
                </div>
                <i class="ti ti-cash fs-2 text-primary"></i>
            </div>
        </div></div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card h-100"><div class="card-body">
            <div class="d-flex justify-content-between">
                <div>
                    <p class="mb-1 text-secondary">Penjualan Bulan Ini</p>
                    <h3 class="mb-1"><?= rupiah($rBulan['total_penjualan']) ?></h3>
                    <small class="text-secondary"><?= (int) $rBulan['jml_transaksi'] ?> transaksi &middot; diskon <?= rupiah($rBulan['total_diskon']) ?></small>
                </div>
                <i class="ti ti-chart-bar fs-2 text-primary"></i>
            </div>
        </div></div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card h-100"><div class="card-body">
            <div class="d-flex justify-content-between">
                <div>
                    <p class="mb-1 text-secondary">Menunggu Persetujuan Void</p>
                    <h3 class="mb-1 <?= $voidPending > 0 ? 'text-danger' : '' ?>"><?= (int) $voidPending ?></h3>
                    <a href="index.php?page=void" class="small">Tinjau pengajuan</a>
                </div>
                <i class="ti ti-ban fs-2 text-primary"></i>
            </div>
        </div></div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card h-100"><div class="card-body">
            <div class="d-flex justify-content-between">
                <div>
                    <p class="mb-1 text-secondary">Produk Stok Menipis</p>
                    <h3 class="mb-1"><?= count($menipis) ?></h3>
                    <small class="text-secondary">stok 10 atau kurang</small>
                </div>
                <i class="ti ti-alert-triangle fs-2 text-primary"></i>
            </div>
        </div></div>
    </div>
</div>

<div class="row">
    <div class="col-lg-7 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Penjualan 7 Hari Terakhir</h5>
                <a href="index.php?page=shift" class="small">Rekap shift</a>
            </div>
            <div class="card-body">
                <?php if (!$harian) { ?>
                    <p class="text-secondary text-center my-3">Belum ada penjualan dalam 7 hari terakhir.</p>
                <?php } ?>
                <?php foreach ($harian as $h) { $persen = $maks > 0 ? round($h['total_penjualan'] / $maks * 100) : 0; ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="fw-semibold"><?= e(date('d/m/Y', strtotime($h['tgl']))) ?></span>
                            <span><?= rupiah($h['total_penjualan']) ?> <small class="text-secondary">(<?= (int) $h['jml_transaksi'] ?> transaksi)</small></span>
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
                    <thead class="table-light border-light"><tr><th>Produk</th><th class="text-end">Stok</th></tr></thead>
                    <tbody>
                        <?php if (!$menipis) { ?><tr><td colspan="2" class="text-center text-secondary py-3">Semua stok aman.</td></tr><?php } ?>
                        <?php foreach ($menipis as $m) { ?>
                            <tr>
                                <td><?= e($m['nama_produk']) ?></td>
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
                <a href="index.php?page=penjualan" class="small">Lihat semua</a>
            </div>
            <div class="table-responsive">
                <table class="table mb-0 text-nowrap">
                    <thead class="table-light border-light">
                        <tr><th>Invoice</th><th>Tanggal</th><th>Kasir</th><th class="text-end">Total</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!$terbaru) { ?><tr><td colspan="5" class="text-center text-secondary py-3">Belum ada transaksi.</td></tr><?php } ?>
                        <?php foreach ($terbaru as $t) { ?>
                            <tr>
                                <td><a href="index.php?page=detail&id=<?= (int) $t['id'] ?>"><?= e($t['invoice_number']) ?></a></td>
                                <td><?= e(date('d/m/Y H:i', strtotime($t['tanggal']))) ?></td>
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
