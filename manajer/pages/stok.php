<?php
// STOK PRODUK CABANG - daftar stok + jalan pintas restock
$cari = isset($_GET['cari']) ? trim($_GET['cari']) : '';
$fstok = stokFilter();
$data = array_values(array_filter($mgr->produkStok($cari), function ($r) use ($fstok) { return stokCocok($r['stok'], $fstok); }));
$hal = halIni();
$jml = count($data);
$data = halPotong($data, $hal);
?>
<?= fotoGaya() ?>
<form method="get" class="d-flex gap-2 mb-3 flex-wrap">
    <input type="hidden" name="page" value="stok">
    <input type="text" name="cari" value="<?= e($cari) ?>" class="form-control" placeholder="Cari nama produk..." style="max-width: 250px;">
    <?= stokSelect($fstok) ?>
    <button class="btn btn-outline-secondary" type="submit"><i class="ti ti-filter"></i> Filter</button>
    <a href="index.php?page=riwayatStok" class="btn btn-outline-secondary ms-md-auto"><i class="ti ti-history"></i> Riwayat Stok</a>
</form>
<div class="card">
    <div class="table-responsive">
        <table class="table mb-0 text-nowrap table-hover">
            <thead class="table-light border-light">
                <tr><th>No</th><th>Foto</th><th>Produk</th><th class="text-end">Harga</th><th class="text-end">Stok</th><th></th></tr>
            </thead>
            <tbody>
                <?php if (!$data) { ?><tr><td colspan="6" class="text-center text-secondary py-4">Tidak ada produk yang cocok.</td></tr><?php } ?>
                <?php $no = halMulai($hal); foreach ($data as $r) { ?>
                    <tr class="align-middle">
                        <td><?= $no++ ?></td>
                        <td><?= fotoProduk($r['foto'], $r['nama_produk'], 44) ?></td>
                        <td class="fw-semibold"><?= e($r['nama_produk']) ?></td>
                        <td class="text-end"><?= rupiah($r['harga_jual']) ?></td>
                        <td class="text-end"><?= (int) $r['stok'] ?> <?= stokBadge($r['stok']) ?></td>
                        <td class="text-end"><a href="index.php?page=restock&id=<?= (int) $r['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="ti ti-plus"></i> Restock</a></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <?php halNav($jml, $hal); ?>
</div>
