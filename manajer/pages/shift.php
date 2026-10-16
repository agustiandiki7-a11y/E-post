<?php
// PENUTUPAN SHIFT KASIR - rekap per kasir pada satu tanggal (dihitung dari transaksi)
$tanggal = tglValid(isset($_GET['tanggal']) ? $_GET['tanggal'] : '', $mgr->tanggalTerakhir());
$data = $mgr->shiftKasir($tanggal);

$sumTrx = 0;
$sumTotal = 0;
$sumDiskon = 0;
foreach ($data as $r) {
    $sumTrx += $r['jml_transaksi'];
    $sumTotal += $r['total_penjualan'];
    $sumDiskon += $r['total_diskon'];
}
$hal = halIni();
$jml = count($data);
$data = halPotong($data, $hal);
$logShift = shiftCabang(eposDb(), $BRANCH_ID, $tanggal);
$halS = halIni('hal_s');
$jmlS = count($logShift);
$logShift = halPotong($logShift, $halS);
?>
<form method="get" class="d-flex gap-2 mb-4 flex-wrap align-items-end">
    <input type="hidden" name="page" value="shift">
    <div>
        <label class="form-label small mb-1">Tanggal shift</label>
        <input type="date" name="tanggal" value="<?= e($tanggal) ?>" class="form-control">
    </div>
    <button class="btn btn-outline-secondary" type="submit"><i class="ti ti-filter"></i> Tampilkan</button>
    <a class="btn btn-outline-secondary" href="?<?= e(http_build_query(['page' => 'shift', 'tanggal' => $tanggal, 'unduh' => 'csv'])) ?>"><i class="ti ti-download"></i> Unduh CSV</a>
</form>

<div class="row mb-2">
    <div class="col-md-4 mb-3">
        <div class="card h-100"><div class="card-body">
            <p class="mb-1 text-secondary">Total Penjualan</p>
            <h3 class="mb-0"><?= rupiah($sumTotal) ?></h3>
        </div></div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card h-100"><div class="card-body">
            <p class="mb-1 text-secondary">Jumlah Transaksi</p>
            <h3 class="mb-0"><?= (int) $sumTrx ?></h3>
        </div></div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card h-100"><div class="card-body">
            <p class="mb-1 text-secondary">Total Diskon</p>
            <h3 class="mb-0"><?= rupiah($sumDiskon) ?></h3>
        </div></div>
    </div>
</div>

<div class="row">
    <div class="col-12 mb-3">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Rekap Kasir &mdash; <?= e(date('d/m/Y', strtotime($tanggal))) ?></h5></div>
            <div class="table-responsive">
                <table class="table mb-0 text-nowrap">
                    <thead class="table-light border-light">
                        <tr>
                            <th>Kasir</th>
                            <th>Jam Awal</th>
                            <th>Jam Akhir</th>
                            <th class="text-end">Transaksi</th>
                            <th class="text-end">Diskon</th>
                            <th class="text-end">Total Penjualan</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$data) { ?>
                            <tr><td colspan="7" class="text-center text-secondary py-4">Belum ada kasir di cabang ini.</td></tr>
                        <?php } ?>
                        <?php foreach ($data as $r) { ?>
                            <tr class="align-middle">
                                <td class="fw-semibold"><?= e($r['nama']) ?></td>
                                <td><?= $r['jam_awal'] ? e(date('H:i', strtotime($r['jam_awal']))) : '-' ?></td>
                                <td><?= $r['jam_akhir'] ? e(date('H:i', strtotime($r['jam_akhir']))) : '-' ?></td>
                                <td class="text-end"><?= (int) $r['jml_transaksi'] ?></td>
                                <td class="text-end"><?= rupiah($r['total_diskon']) ?></td>
                                <td class="text-end"><?= rupiah($r['total_penjualan']) ?></td>
                                <td>
                                    <?php if ($r['jml_menunggu'] > 0) { ?>
                                        <span class="badge bg-warning text-dark"><?= (int) $r['jml_menunggu'] ?> menunggu void</span>
                                    <?php } ?>
                                    <?php if ($r['jml_void'] > 0) { ?>
                                        <span class="badge bg-danger"><?= (int) $r['jml_void'] ?> void</span>
                                    <?php } ?>
                                    <?php if ($r['jml_menunggu'] == 0 && $r['jml_void'] == 0 && $r['jml_transaksi'] == 0) { ?>
                                        <span class="text-secondary">Tidak ada transaksi</span>
                                    <?php } elseif ($r['jml_menunggu'] == 0 && $r['jml_void'] == 0) { ?>
                                        <span class="badge bg-success">Siap ditutup</span>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <?php halNav($jml, $hal); ?>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12 mb-3">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Buka &amp; Tutup Shift &mdash; <?= e(date('d/m/Y', strtotime($tanggal))) ?></h5></div>
            <div class="table-responsive">
                <table class="table mb-0 text-nowrap">
                    <thead class="table-light border-light">
                        <tr><th>Kasir</th><th>Buka</th><th>Tutup</th><th class="text-end">Kas awal</th><th class="text-end">Penjualan</th><th class="text-end">Kas akhir</th><th>Selisih</th><th>Catatan</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!$logShift) { ?><tr><td colspan="8" class="text-center text-secondary py-4">Belum ada shift yang dibuka pada tanggal ini.</td></tr><?php } ?>
                        <?php foreach ($logShift as $r) { ?>
                            <tr class="align-middle">
                                <td class="fw-semibold"><?= e($r['nama_kasir']) ?></td>
                                <td><?= e(date('d/m H:i', strtotime($r['buka']))) ?></td>
                                <td><?= $r['tutup'] ? e(date('d/m H:i', strtotime($r['tutup']))) : '<span class="badge bg-warning text-dark">Masih buka</span>' ?></td>
                                <td class="text-end"><?= rupiah($r['kas_awal']) ?></td>
                                <td class="text-end"><?= $r['penjualan'] === null ? '-' : rupiah($r['penjualan']) ?></td>
                                <td class="text-end"><?= $r['kas_akhir'] === null ? '-' : rupiah($r['kas_akhir']) ?></td>
                                <td><?= selisihBadge($r['selisih']) ?></td>
                                <td><?= e($r['catatan'] ?: '-') ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <?php halNav($jmlS, $halS, EPOS_PER_HAL, 'hal_s'); ?>
        </div>
    </div>
</div>
<p class="text-secondary small">Transaksi void tidak dihitung. Shift "Siap ditutup" artinya tidak ada pengajuan void yang masih menggantung.</p>
