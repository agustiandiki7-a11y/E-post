<?php
// Halaman LAPORAN AGREGAT penjualan semua cabang
require_once 'function/pembayaran.php';
$lap = new Pembayaran();

$dari = isset($_GET['dari']) && $_GET['dari'] !== '' ? $_GET['dari'] : date('Y-m-01');
$sampai = isset($_GET['sampai']) && $_GET['sampai'] !== '' ? $_GET['sampai'] : date('Y-m-d');

$ringkas = $lap->ringkasan($dari, $sampai);
$cabang = $lap->perCabang($dari, $sampai);
$harian = $lap->perHari($dari, $sampai);
$terlaris = $lap->produkTerlaris($dari, $sampai, 5);
$omzetMax = 0;
foreach ($cabang as $c) {
    $omzetMax = max($omzetMax, $c['total_penjualan']);
}
?>
<main id="content" class="content py-10">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                    <div>
                        <h1 class="fs-3 mb-1">Laporan Agregat</h1>
                        <p class="mb-0">Ringkasan penjualan seluruh cabang, <?= e(date('d/m/Y', strtotime($dari))) ?> s/d <?= e(date('d/m/Y', strtotime($sampai))) ?> (transaksi void tidak dihitung)</p>
                    </div>
                    <div>
                        <button class="btn btn-outline-secondary" onclick="window.print()"><i class="ti ti-printer"></i> Cetak</button>
                    </div>
                </div>
            </div>
        </div>
        <form method="get" class="d-flex gap-2 mb-4 flex-wrap align-items-end">
            <input type="hidden" name="page" value="pembayaran">
            <div>
                <label class="form-label small mb-1">Dari</label>
                <input type="date" name="dari" value="<?= e($dari) ?>" class="form-control">
            </div>
            <div>
                <label class="form-label small mb-1">Sampai</label>
                <input type="date" name="sampai" value="<?= e($sampai) ?>" class="form-control">
            </div>
            <button class="btn btn-outline-secondary" type="submit"><i class="ti ti-filter"></i> Terapkan</button>
        </form>

        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="card h-100"><div class="card-body">
                    <p class="mb-1 text-secondary">Total Penjualan</p>
                    <h3 class="mb-0"><?= rupiah($ringkas['total_penjualan']) ?></h3>
                </div></div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card h-100"><div class="card-body">
                    <p class="mb-1 text-secondary">Jumlah Transaksi</p>
                    <h3 class="mb-0"><?= (int) $ringkas['jml_transaksi'] ?></h3>
                </div></div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card h-100"><div class="card-body">
                    <p class="mb-1 text-secondary">Total Diskon Diberikan</p>
                    <h3 class="mb-0"><?= rupiah($ringkas['total_diskon']) ?></h3>
                </div></div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-7 mb-4">
                <div class="card">
                    <div class="card-header"><h5 class="mb-0">Penjualan per Cabang</h5></div>
                    <div class="table-responsive">
                        <table class="table mb-0 text-nowrap">
                            <thead class="table-light border-light">
                                <tr><th>Cabang</th><th class="text-end">Transaksi</th><th class="text-end">Diskon</th><th class="text-end">Penjualan</th><th style="min-width:140px"></th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cabang as $c) { $persen = $omzetMax > 0 ? round($c['total_penjualan'] / $omzetMax * 100) : 0; ?>
                                    <tr class="align-middle">
                                        <td class="fw-semibold"><?= e($c['nama_cabang']) ?></td>
                                        <td class="text-end"><?= (int) $c['jml_transaksi'] ?></td>
                                        <td class="text-end"><?= rupiah($c['total_diskon']) ?></td>
                                        <td class="text-end"><?= rupiah($c['total_penjualan']) ?></td>
                                        <td>
                                            <div class="progress" style="height:8px"><div class="progress-bar" style="width:<?= $persen ?>%"></div></div>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 mb-4">
                <div class="card">
                    <div class="card-header"><h5 class="mb-0">5 Produk Terlaris</h5></div>
                    <div class="table-responsive">
                        <table class="table mb-0 text-nowrap">
                            <thead class="table-light border-light">
                                <tr><th>Produk</th><th>Cabang</th><th class="text-end">Terjual</th></tr>
                            </thead>
                            <tbody>
                                <?php if (!$terlaris) { ?><tr><td colspan="3" class="text-center text-secondary py-3">Belum ada penjualan.</td></tr><?php } ?>
                                <?php foreach ($terlaris as $t) { ?>
                                    <tr>
                                        <td><?= e($t['nama_produk']) ?></td>
                                        <td><?= e($t['nama_cabang']) ?></td>
                                        <td class="text-end"><?= (int) $t['qty'] ?></td>
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
                    <div class="card-header"><h5 class="mb-0">Penjualan per Hari</h5></div>
                    <div class="table-responsive">
                        <table class="table mb-0 text-nowrap">
                            <thead class="table-light border-light">
                                <tr><th>Tanggal</th><th class="text-end">Transaksi</th><th class="text-end">Diskon</th><th class="text-end">Penjualan</th></tr>
                            </thead>
                            <tbody>
                                <?php if (!$harian) { ?><tr><td colspan="4" class="text-center text-secondary py-3">Belum ada penjualan pada periode ini.</td></tr><?php } ?>
                                <?php foreach ($harian as $h) { ?>
                                    <tr>
                                        <td><?= e(date('d/m/Y', strtotime($h['tgl']))) ?></td>
                                        <td class="text-end"><?= (int) $h['jml_transaksi'] ?></td>
                                        <td class="text-end"><?= rupiah($h['total_diskon']) ?></td>
                                        <td class="text-end"><?= rupiah($h['total_penjualan']) ?></td>
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
