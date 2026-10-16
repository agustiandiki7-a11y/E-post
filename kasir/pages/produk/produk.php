<?php
// PRODUK & STOK - hanya lihat (kasir tidak mengubah harga/stok; itu wewenang owner)
$cari = isset($_GET['cari']) ? trim($_GET['cari']) : '';
$fstok = stokFilter();
$rows = array_values(array_filter($ksr->produk($cari), function ($r) use ($fstok) { return stokCocok($r['stok'], $fstok); }));
$hal = halIni();
$jml = count($rows);
$rows = halPotong($rows, $hal);
?>
<?= fotoGaya() ?>
<div class="card">
    <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
        <h5 class="mb-0"><?= $jml ?> produk</h5>
        <form method="get" class="d-flex gap-2 flex-wrap">
            <input type="hidden" name="page" value="produk">
            <input type="text" name="cari" class="form-control" placeholder="Cari produk..." value="<?= e($cari) ?>" style="max-width: 220px;">
            <?= stokSelect($fstok) ?>
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
                        <td class="fw-semibold"><span class="d-inline-flex align-items-center gap-3"><?= fotoProduk($p['foto'], $p['nama_produk'], 48) ?><?= e($p['nama_produk']) ?></span></td>
                        <td class="text-end"><?= rupiah($p['harga_jual']) ?></td>
                        <td class="text-end"><?= (int) $p['stok'] ?> <?= stokBadge($p['stok']) ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <?php halNav($jml, $hal); ?>
</div>
