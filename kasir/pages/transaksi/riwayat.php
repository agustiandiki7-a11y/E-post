<?php
// RIWAYAT TRANSAKSI - hanya milik kasir yang login
$hariIni = date('Y-m-d');
$dari = tglValid(isset($_GET['dari']) ? $_GET['dari'] : '', date('Y-m-d', strtotime('-6 days')));
$sampai = tglValid(isset($_GET['sampai']) ? $_GET['sampai'] : '', $hariIni);
if ($dari > $sampai) {
    $tmp = $dari; $dari = $sampai; $sampai = $tmp;
}
$cari = isset($_GET['cari']) ? trim($_GET['cari']) : '';
$rows = $ksr->riwayat($dari, $sampai, $cari);

$total = 0;
foreach ($rows as $r) {
    if ($r['status'] !== 'void') {
        $total += $r['total_bayar'];
    }
}
?>
<div class="card mb-4">
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end">
            <input type="hidden" name="page" value="riwayat">
            <div class="col-md-3">
                <label class="form-label small mb-1">Dari</label>
                <input type="date" name="dari" class="form-control" value="<?= e($dari) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Sampai</label>
                <input type="date" name="sampai" class="form-control" value="<?= e($sampai) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label small mb-1">Cari invoice</label>
                <input type="text" name="cari" class="form-control" placeholder="INV-..." value="<?= e($cari) ?>">
            </div>
            <div class="col-md-2 d-grid">
                <button class="btn btn-primary" type="submit"><i class="ti ti-search"></i> Tampilkan</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><?= count($rows) ?> transaksi</h5>
        <span class="text-secondary">Total (tanpa void): <strong><?= rupiah($total) ?></strong></span>
    </div>
    <div class="table-responsive">
        <table class="table mb-0 text-nowrap align-middle">
            <thead class="table-light border-light">
                <tr><th>Invoice</th><th>Waktu</th><th class="text-end">Diskon</th><th class="text-end">Total</th><th>Status</th><th class="text-end">Aksi</th></tr>
            </thead>
            <tbody>
                <?php if (!$rows) { ?><tr><td colspan="6" class="text-center text-secondary py-4">Tidak ada transaksi pada filter ini.</td></tr><?php } ?>
                <?php foreach ($rows as $r) { ?>
                    <tr>
                        <td class="fw-semibold"><?= e($r['invoice_number']) ?></td>
                        <td><?= e(date('d/m/Y H:i', strtotime($r['tanggal']))) ?></td>
                        <td class="text-end"><?= rupiah($r['diskon']) ?></td>
                        <td class="text-end"><?= rupiah($r['total_bayar']) ?></td>
                        <td><?= badgeStatus($r['status']) ?></td>
                        <td class="text-end">
                            <a href="index.php?page=struk&id=<?= (int) $r['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="ti ti-printer"></i> Struk</a>
                            <?php if ($r['status'] === 'selesai') { ?>
                                <form method="post" action="index.php?page=riwayat" class="d-inline" onsubmit="return confirm('Ajukan void untuk <?= e($r['invoice_number']) ?>? Manajer cabang harus menyetujui.');">
                                    <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
                                    <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="ti ti-ban"></i> Ajukan Void</button>
                                </form>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>
