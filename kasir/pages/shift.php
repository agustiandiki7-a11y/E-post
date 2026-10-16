<?php
// SHIFT KASIR - buka/tutup shift dan hitung kas. Variabel dari index.php: $dbShift, $shiftAktif, $BRANCH_ID, $USER_ID
$tokenCsrf = eposCsrfToken();
$jual = $shiftAktif ? shiftPenjualan($dbShift, $BRANCH_ID, $USER_ID, $shiftAktif['buka']) : null;
$harapan = $shiftAktif ? (float) $shiftAktif['kas_awal'] + (float) $jual['total'] : 0;
$riwayat = shiftRiwayatKasir($dbShift, $USER_ID);
$hal = halIni();
$jml = count($riwayat);
$riwayat = halPotong($riwayat, $hal);
?>
<div class="row">
    <div class="col-lg-5 mb-4">
        <div class="card h-100">
            <div class="card-body p-4">
                <?php if (!$shiftAktif) { ?>
                    <h5>Buka Shift</h5>
                    <p class="text-secondary">Hitung uang kas di laci sebelum mulai, lalu isi kas awal.</p>
                    <form method="post" autocomplete="off">
                        <input type="hidden" name="csrf" value="<?= e($tokenCsrf) ?>">
                        <input type="hidden" name="aksi" value="buka">
                        <label class="form-label" for="kas">Kas awal (Rp)</label>
                        <input type="text" inputmode="numeric" id="kas" name="kas" class="form-control mb-3" placeholder="Contoh: 200000" required>
                        <button class="btn btn-primary" type="submit"><i class="ti ti-clock"></i> Buka shift</button>
                    </form>
                <?php } else { ?>
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h5 class="mb-0">Shift Sedang Berjalan</h5>
                        <span class="badge bg-success">Buka</span>
                    </div>
                    <dl class="row mb-3">
                        <dt class="col-6 fw-normal text-secondary">Dibuka</dt>
                        <dd class="col-6 text-end"><?= e(date('d/m/Y H:i', strtotime($shiftAktif['buka']))) ?></dd>
                        <dt class="col-6 fw-normal text-secondary">Kas awal</dt>
                        <dd class="col-6 text-end"><?= rupiah($shiftAktif['kas_awal']) ?></dd>
                        <dt class="col-6 fw-normal text-secondary">Penjualan (<?= (int) $jual['n'] ?> transaksi)</dt>
                        <dd class="col-6 text-end"><?= rupiah($jual['total']) ?></dd>
                        <dt class="col-6">Kas seharusnya</dt>
                        <dd class="col-6 text-end fw-bold"><?= rupiah($harapan) ?></dd>
                    </dl>
                    <form method="post" autocomplete="off" onsubmit="return confirm('Tutup shift sekarang? Setelah ditutup tidak bisa diubah.')">
                        <input type="hidden" name="csrf" value="<?= e($tokenCsrf) ?>">
                        <input type="hidden" name="aksi" value="tutup">
                        <label class="form-label" for="kas">Kas akhir (hitung uang di laci, Rp)</label>
                        <input type="text" inputmode="numeric" id="kas" name="kas" class="form-control mb-3" required>
                        <label class="form-label" for="cat">Catatan (opsional)</label>
                        <input type="text" id="cat" name="catatan" class="form-control mb-3" maxlength="200">
                        <button class="btn btn-danger" type="submit">Tutup shift</button>
                    </form>
                <?php } ?>
            </div>
        </div>
    </div>
    <div class="col-lg-7 mb-4">
        <div class="card h-100">
            <div class="card-header"><h5 class="mb-0">Riwayat Shiftmu</h5></div>
            <div class="table-responsive">
                <table class="table mb-0 text-nowrap">
                    <thead class="table-light border-light">
                        <tr><th>Buka</th><th>Tutup</th><th class="text-end">Kas awal</th><th class="text-end">Penjualan</th><th class="text-end">Kas akhir</th><th>Selisih</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!$riwayat) { ?><tr><td colspan="6" class="text-center text-secondary py-4">Belum ada shift.</td></tr><?php } ?>
                        <?php foreach ($riwayat as $r) { ?>
                            <tr class="align-middle">
                                <td><?= e(date('d/m/Y H:i', strtotime($r['buka']))) ?></td>
                                <td><?= $r['tutup'] ? e(date('d/m/Y H:i', strtotime($r['tutup']))) : '<span class="badge bg-success">Berjalan</span>' ?></td>
                                <td class="text-end"><?= rupiah($r['kas_awal']) ?></td>
                                <td class="text-end"><?= $r['penjualan'] === null ? '-' : rupiah($r['penjualan']) ?></td>
                                <td class="text-end"><?= $r['kas_akhir'] === null ? '-' : rupiah($r['kas_akhir']) ?></td>
                                <td><?= selisihBadge($r['selisih']) ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <?php halNav($jml, $hal); ?>
        </div>
    </div>
</div>
<p class="text-secondary small">Kas seharusnya = kas awal + penjualan selama shift (transaksi void tidak dihitung). Selisih dicatat dan bisa dilihat manajer.</p>
