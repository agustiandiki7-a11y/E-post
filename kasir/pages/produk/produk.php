<?php
// PRODUK & STOK - hanya lihat (kasir tidak mengubah harga/stok; itu wewenang owner)
$cari = isset($_GET['cari']) ? trim($_GET['cari']) : '';
$rows = $ksr->produk($cari);
?>
<div class="card">
    <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
        <h5 class="mb-0"><?= count($rows) ?> produk</h5>
        <form method="get" class="d-flex gap-2">
            <input type="hidden" name="page" value="produk">
            <input type="text" name="cari" class="form-control" placeholder="Cari produk..." value="<?= e($cari) ?>">
            <button class="btn btn-primary" type="submit"><i class="ti ti-search"></i></button>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table mb-0 text-nowrap align-middle">
            <thead class="table-light border-light">
                <tr><th>Produk</th><th class="text-end">Harga Jual</th><th class="text-end">Stok</th></tr>
            </thead>
            <tbody>
                <?php if (!$rows) { ?><tr><td colspan="3" class="text-center text-secondary py-4">Produk tidak ditemukan.</td></tr><?php } ?>
                <?php foreach ($rows as $p) { ?>
                    <tr>
                        <td class="fw-semibold"><?= e($p['nama_produk']) ?></td>
                        <td class="text-end"><?= rupiah($p['harga_jual']) ?></td>
                        <td class="text-end">
                            <?php if ($p['stok'] <= 0) { ?>
                                <span class="badge bg-danger">Habis</span>
                            <?php } else { ?>
                                <?= (int) $p['stok'] ?>
                                <?php if ($p['stok'] <= 10) { ?><span class="badge bg-warning text-dark ms-1">Menipis</span><?php } ?>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>
