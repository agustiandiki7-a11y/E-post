<?php
// PERSETUJUAN VOID - pengajuan void kasir di cabang ini
$menunggu = $mgr->daftarVoid('menunggu_void');
$riwayat = $mgr->daftarVoid('void');

$flash = null;
if (isset($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
}
?>
<?php if ($flash) { ?>
    <div class="alert alert-<?= e($flash[0]) ?>"><?= e($flash[1]) ?></div>
<?php } ?>

<div class="row">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Menunggu Persetujuan</h5>
                <span class="badge bg-warning text-dark"><?= count($menunggu) ?></span>
            </div>
            <div class="table-responsive">
                <table class="table mb-0 text-nowrap">
                    <thead class="table-light border-light">
                        <tr><th>Invoice</th><th>Tanggal</th><th>Kasir</th><th class="text-end">Total</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!$menunggu) { ?>
                            <tr><td colspan="5" class="text-center text-secondary py-4">Tidak ada pengajuan void.</td></tr>
                        <?php } ?>
                        <?php foreach ($menunggu as $t) { ?>
                            <tr class="align-middle">
                                <td class="fw-semibold"><a href="index.php?page=detail&id=<?= (int) $t['id'] ?>"><?= e($t['invoice_number']) ?></a></td>
                                <td><?= e(date('d/m/Y H:i', strtotime($t['tanggal']))) ?></td>
                                <td><?= e($t['nama_kasir']) ?></td>
                                <td class="text-end"><?= rupiah($t['total_bayar']) ?></td>
                                <td>
                                    <form method="post" class="d-inline" onsubmit="return confirm('Setujui void? Stok barang akan dikembalikan.')">
                                        <input type="hidden" name="id" value="<?= (int) $t['id'] ?>">
                                        <input type="hidden" name="aksi" value="setujui">
                                        <button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-check"></i> Setujui</button>
                                    </form>
                                    <form method="post" class="d-inline" onsubmit="return confirm('Tolak pengajuan void ini?')">
                                        <input type="hidden" name="id" value="<?= (int) $t['id'] ?>">
                                        <input type="hidden" name="aksi" value="tolak">
                                        <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x"></i> Tolak</button>
                                    </form>
                                </td>
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
            <div class="card-header"><h5 class="mb-0">Riwayat Void</h5></div>
            <div class="table-responsive">
                <table class="table mb-0 text-nowrap">
                    <thead class="table-light border-light">
                        <tr><th>Invoice</th><th>Tanggal</th><th>Kasir</th><th class="text-end">Total</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!$riwayat) { ?>
                            <tr><td colspan="5" class="text-center text-secondary py-4">Belum ada transaksi yang di-void.</td></tr>
                        <?php } ?>
                        <?php foreach ($riwayat as $t) { ?>
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
