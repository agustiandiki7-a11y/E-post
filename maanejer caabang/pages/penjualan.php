<?php
// PENJUALAN CABANG - daftar transaksi cabang manajer
$dari = tglValid(isset($_GET['dari']) ? $_GET['dari'] : '', date('Y-m-01'));
$sampai = tglValid(isset($_GET['sampai']) ? $_GET['sampai'] : '', date('Y-m-d'));
$kasir_id = isset($_GET['kasir_id']) ? (int) $_GET['kasir_id'] : 0;
$cari = isset($_GET['cari']) ? trim($_GET['cari']) : '';

$kasirList = $mgr->kasirCabang();
$data = $mgr->penjualan($dari, $sampai, $kasir_id, $cari);

$total = 0;
$diskon = 0;
foreach ($data as $r) {
    if ($r['status'] !== 'void') {
        $total += $r['total_bayar'];
        $diskon += $r['diskon'];
    }
}
?>
<div class="row">
    <div class="col-12">
        <form method="get" class="d-flex gap-2 mb-3 flex-wrap align-items-end">
            <input type="hidden" name="page" value="penjualan">
            <div>
                <label class="form-label small mb-1">Dari</label>
                <input type="date" name="dari" value="<?= e($dari) ?>" class="form-control">
            </div>
            <div>
                <label class="form-label small mb-1">Sampai</label>
                <input type="date" name="sampai" value="<?= e($sampai) ?>" class="form-control">
            </div>
            <div>
                <label class="form-label small mb-1">Kasir</label>
                <select name="kasir_id" class="form-select">
                    <option value="0">Semua kasir</option>
                    <?php foreach ($kasirList as $k) { ?>
                        <option value="<?= (int) $k['id'] ?>" <?= $kasir_id == $k['id'] ? 'selected' : '' ?>><?= e($k['nama']) ?></option>
                    <?php } ?>
                </select>
            </div>
            <div>
                <label class="form-label small mb-1">No. Invoice</label>
                <input type="text" name="cari" value="<?= e($cari) ?>" class="form-control" placeholder="INV-...">
            </div>
            <button class="btn btn-outline-secondary" type="submit"><i class="ti ti-filter"></i> Filter</button>
        </form>

        <div class="card table-responsive">
            <table class="table mb-0 text-nowrap table-hover">
                <thead class="table-light border-light">
                    <tr>
                        <th>Invoice</th>
                        <th>Tanggal</th>
                        <th>Kasir</th>
                        <th class="text-end">Diskon</th>
                        <th class="text-end">Total Bayar</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$data) { ?>
                        <tr><td colspan="7" class="text-center text-secondary py-4">Tidak ada transaksi pada filter ini.</td></tr>
                    <?php } ?>
                    <?php foreach ($data as $row) { ?>
                        <tr class="align-middle">
                            <td class="fw-semibold"><?= e($row['invoice_number']) ?></td>
                            <td><?= e(date('d/m/Y H:i', strtotime($row['tanggal']))) ?></td>
                            <td><?= e($row['nama_kasir']) ?></td>
                            <td class="text-end"><?= rupiah($row['diskon']) ?></td>
                            <td class="text-end"><?= rupiah($row['total_bayar']) ?></td>
                            <td><?= badgeStatus($row['status']) ?></td>
                            <td><a href="index.php?page=detail&id=<?= (int) $row['id'] ?>" title="Lihat detail"><i class="ti ti-eye"></i></a></td>
                        </tr>
                    <?php } ?>
                </tbody>
                <?php if ($data) { ?>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end fw-semibold border-bottom-0">Total (tidak termasuk void)</td>
                            <td class="text-end fw-bold border-bottom-0"><?= rupiah($diskon) ?></td>
                            <td class="text-end fw-bold border-bottom-0"><?= rupiah($total) ?></td>
                            <td colspan="2" class="border-bottom-0"></td>
                        </tr>
                    </tfoot>
                <?php } ?>
            </table>
        </div>
    </div>
</div>
