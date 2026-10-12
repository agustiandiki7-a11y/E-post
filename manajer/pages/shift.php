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
?>
<form method="get" class="d-flex gap-2 mb-4 flex-wrap align-items-end">
    <input type="hidden" name="page" value="shift">
    <div>
        <label class="form-label small mb-1">Tanggal shift</label>
        <input type="date" name="tanggal" value="<?= e($tanggal) ?>" class="form-control">
    </div>
    <button class="btn btn-outline-secondary" type="submit"><i class="ti ti-filter"></i> Tampilkan</button>
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
        </div>
    </div>
</div>
<p class="text-secondary small">Transaksi void tidak dihitung. Shift "Siap ditutup" artinya tidak ada pengajuan void yang masih menggantung.</p>
