<?php
// Halaman PRODUK & HARGA (master products)
require_once 'function/kendaraan.php';
require_once 'function/kategori.php';
$produk = new Kendaraan();
$cabang = (new Kategori())->semua();
$pesan = '';

if (isset($_POST['hapus_id'])) {
    if ($produk->hapus((int) $_POST['hapus_id'])) {
        redirect('kendaraan');
    }
    $pesan = 'Produk tidak bisa dihapus karena sudah pernah terjual.';
}

$branch_id = isset($_GET['branch_id']) ? (int) $_GET['branch_id'] : 0;
$cari = isset($_GET['cari']) ? trim($_GET['cari']) : '';
$data = $produk->semua($branch_id, $cari);
?>
<main id="content" class="content py-10">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="fs-3 mb-1">Master Produk &amp; Harga</h1>
                        <p class="mb-0">Kelola produk, harga jual, dan stok tiap cabang</p>
                    </div>
                    <div>
                        <a href="index.php?page=tambahKendaraan" class="btn btn-primary">Tambah Produk</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <?php if ($pesan) { ?><div class="alert alert-danger"><?= e($pesan) ?></div><?php } ?>
                <form method="get" class="d-flex gap-2 mb-3 flex-wrap">
                    <input type="hidden" name="page" value="kendaraan">
                    <input type="text" name="cari" value="<?= e($cari) ?>" class="form-control" placeholder="Cari nama produk..." style="max-width: 250px;">
                    <select name="branch_id" class="form-select" style="max-width: 220px;">
                        <option value="0">Semua cabang</option>
                        <?php foreach ($cabang as $c) { ?>
                            <option value="<?= (int) $c['id'] ?>" <?= $branch_id == $c['id'] ? 'selected' : '' ?>><?= e($c['nama_cabang']) ?></option>
                        <?php } ?>
                    </select>
                    <button class="btn btn-outline-secondary" type="submit"><i class="ti ti-filter"></i> Filter</button>
                </form>
                <div class="card table-responsive">
                    <table class="table mb-0 text-nowrap table-hover">
                        <thead class="table-light border-light">
                            <tr>
                                <th>No</th>
                                <th>Nama Produk</th>
                                <th>Cabang</th>
                                <th>Harga Jual</th>
                                <th>Stok</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$data) { ?>
                                <tr><td colspan="6" class="text-center text-secondary py-4">Belum ada produk.</td></tr>
                            <?php } ?>
                            <?php $no = 1; foreach ($data as $row) { ?>
                                <tr class="align-middle">
                                    <td><?= $no++ ?></td>
                                    <td class="fw-semibold"><?= e($row['nama_produk']) ?></td>
                                    <td><?= e($row['nama_cabang']) ?></td>
                                    <td><?= rupiah($row['harga_jual']) ?></td>
                                    <td>
                                        <?= (int) $row['stok'] ?>
                                        <?php if ($row['stok'] <= 10) { ?><span class="badge bg-warning text-dark ms-1">Menipis</span><?php } ?>
                                    </td>
                                    <td>
                                        <a href="index.php?page=updateKendaraan&id=<?= (int) $row['id'] ?>"><i class="ti ti-edit"></i></a>
                                        <form method="post" class="d-inline" onsubmit="return confirm('Hapus produk ini?')">
                                            <input type="hidden" name="hapus_id" value="<?= (int) $row['id'] ?>">
                                            <button type="submit" class="btn btn-link link-danger p-0 border-0"><i class="ti ti-trash ms-2"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php include 'components/footer.php'; ?>
    </div>
</main>
