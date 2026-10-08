<?php
// Halaman TRANSAKSI semua cabang (read-only untuk owner)
require_once 'function/penyewaan.php';
require_once 'function/kategori.php';
$trx = new Penyewaan();
$cabang = (new Kategori())->semua();

$branch_id = isset($_GET['branch_id']) ? (int) $_GET['branch_id'] : 0;
$dari = isset($_GET['dari']) && $_GET['dari'] !== '' ? $_GET['dari'] : date('Y-m-01');
$sampai = isset($_GET['sampai']) && $_GET['sampai'] !== '' ? $_GET['sampai'] : date('Y-m-d');
$cari = isset($_GET['cari']) ? trim($_GET['cari']) : '';
$data = $trx->semua($branch_id, $dari, $sampai, $cari);

$total = 0;
foreach ($data as $r) {
    if ($r['status'] !== 'void') {
        $total += $r['total_bayar'];
    }
}
?>
<main id="content" class="content py-10">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="fs-3 mb-1">Transaksi Penjualan</h1>
                        <p class="mb-0">Seluruh transaksi dari semua cabang</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <form method="get" class="d-flex gap-2 mb-3 flex-wrap align-items-end">
                    <input type="hidden" name="page" value="penyewaan">
                    <div>
                        <label class="form-label small mb-1">Cabang</label>
                        <select name="branch_id" class="form-select">
                            <option value="0">Semua cabang</option>
                            <?php foreach ($cabang as $c) { ?>
                                <option value="<?= (int) $c['id'] ?>" <?= $branch_id == $c['id'] ? 'selected' : '' ?>><?= e($c['nama_cabang']) ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label small mb-1">Dari</label>
                        <input type="date" name="dari" value="<?= e($dari) ?>" class="form-control">
                    </div>
                    <div>
                        <label class="form-label small mb-1">Sampai</label>
                        <input type="date" name="sampai" value="<?= e($sampai) ?>" class="form-control">
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
                                <th>Cabang</th>
                                <th>Kasir</th>
                                <th>Diskon</th>
                                <th>Total Bayar</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$data) { ?>
                                <tr><td colspan="8" class="text-center text-secondary py-4">Tidak ada transaksi pada filter ini.</td></tr>
                            <?php } ?>
                            <?php foreach ($data as $row) { ?>
                                <tr class="align-middle">
                                    <td class="fw-semibold"><?= e($row['invoice_number']) ?></td>
                                    <td><?= e(date('d/m/Y H:i', strtotime($row['tanggal']))) ?></td>
                                    <td><?= e($row['nama_cabang']) ?></td>
                                    <td><?= e($row['nama_kasir']) ?></td>
                                    <td><?= rupiah($row['diskon']) ?></td>
                                    <td><?= rupiah($row['total_bayar']) ?></td>
                                    <td><?= badgeStatus($row['status']) ?></td>
                                    <td><a href="index.php?page=detailTransaksi&id=<?= (int) $row['id'] ?>" title="Lihat detail"><i class="ti ti-eye"></i></a></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" class="text-end fw-semibold border-bottom-0">Total penjualan (tidak termasuk void)</td>
                                <td colspan="3" class="fw-bold border-bottom-0"><?= rupiah($total) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        <?php include 'components/footer.php'; ?>
    </div>
</main>
