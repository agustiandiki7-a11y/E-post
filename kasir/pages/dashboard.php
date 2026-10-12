<?php
// DASHBOARD KASIR - hanya transaksi milik kasir yang login
$hariIni = date('Y-m-d');
$ring = $ksr->ringkasan($hariIni);
$terbaru = $ksr->terbaru(6);
$menipis = $ksr->stokMenipis(10);
?>
<div class="row">
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card h-100"><div class="card-body">
            <div class="d-flex justify-content-between">
                <div>
                    <p class="mb-1 text-secondary">Penjualanku Hari Ini</p>
                    <h3 class="mb-1"><?= rupiah($ring['total_penjualan']) ?></h3>
                    <small class="text-secondary"><?= e(date('d/m/Y')) ?></small>
                </div>
                <i class="ti ti-cash fs-2 text-primary"></i>
            </div>
        </div></div>
    </div>
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card h-100"><div class="card-body">
            <div class="d-flex justify-content-between">
                <div>
                    <p class="mb-1 text-secondary">Jumlah Transaksi</p>
                    <h3 class="mb-1"><?= (int) $ring['jml_transaksi'] ?></h3>
                    <small class="text-secondary">tanpa transaksi void</small>
                </div>
                <i class="ti ti-receipt fs-2 text-primary"></i>
            </div>
        </div></div>
    </div>
    <div class="col-xl-4 col-md-12 mb-4">
        <div class="card h-100"><div class="card-body">
            <div class="d-flex justify-content-between">
                <div>
                    <p class="mb-1 text-secondary">Diskon Diberikan</p>
                    <h3 class="mb-1"><?= rupiah($ring['total_diskon']) ?></h3>
                    <a href="index.php?page=baru" class="small">Buat transaksi baru</a>
                </div>
                <i class="ti ti-discount-2 fs-2 text-primary"></i>
            </div>
        </div></div>
    </div>
</div>

<div class="row">
    <div class="col-lg-7 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Transaksi Terakhirku</h5>
                <a href="index.php?page=riwayat" class="small">Lihat semua</a>
            </div>
            <div class="table-responsive">
                <table class="table mb-0 text-nowrap">
                    <thead class="table-light border-light">
                        <tr><th>Invoice</th><th>Waktu</th><th class="text-end">Total</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!$terbaru) { ?><tr><td colspan="4" class="text-center text-secondary py-4">Belum ada transaksi.</td></tr><?php } ?>
                        <?php foreach ($terbaru as $t) { ?>
                            <tr>
                                <td><a href="index.php?page=struk&id=<?= (int) $t['id'] ?>"><?= e($t['invoice_number']) ?></a></td>
                                <td><?= e(date('d/m/Y H:i', strtotime($t['tanggal']))) ?></td>
                                <td class="text-end"><?= rupiah($t['total_bayar']) ?></td>
                                <td><?= badgeStatus($t['status']) ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
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
                        <?php if (!$menipis) { ?><tr><td colspan="2" class="text-center text-secondary py-4">Semua stok aman.</td></tr><?php } ?>
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
