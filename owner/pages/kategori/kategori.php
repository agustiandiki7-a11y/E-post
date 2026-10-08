<?php
// Halaman CABANG (master branches)
require_once 'function/kategori.php';
$kategori = new Kategori();
$pesan = '';

if (isset($_POST['hapus_id'])) {
    if ($kategori->hapus((int) $_POST['hapus_id'])) {
        redirect('kategori');
    }
    $pesan = 'Cabang tidak bisa dihapus karena masih dipakai produk, user, atau transaksi.';
}

$cari = isset($_GET['cari']) ? trim($_GET['cari']) : '';
$data = $kategori->semua($cari);
?>
<main id="content" class="content py-10">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="fs-3 mb-1">Master Cabang</h1>
                        <p class="mb-0">Kelola data cabang toko</p>
                    </div>
                    <div>
                        <a href="index.php?page=tambahKategori" class="btn btn-primary">Tambah Cabang</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <?php if ($pesan) { ?><div class="alert alert-danger"><?= e($pesan) ?></div><?php } ?>
                <form method="get" class="d-flex gap-2 mb-3">
                    <input type="hidden" name="page" value="kategori">
                    <input type="text" name="cari" value="<?= e($cari) ?>" class="form-control" placeholder="Cari nama / alamat cabang..." style="max-width: 280px;">
                    <button class="btn btn-outline-secondary" type="submit"><i class="ti ti-search"></i> Cari</button>
                </form>
                <div class="card table-responsive">
                    <table class="table mb-0 text-nowrap table-hover">
                        <thead class="table-light border-light">
                            <tr>
                                <th>No</th>
                                <th>Nama Cabang</th>
                                <th>Alamat</th>
                                <th>Telepon</th>
                                <th>Produk</th>
                                <th>User</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$data) { ?>
                                <tr><td colspan="7" class="text-center text-secondary py-4">Belum ada data cabang.</td></tr>
                            <?php } ?>
                            <?php $no = 1; foreach ($data as $row) { ?>
                                <tr class="align-middle">
                                    <td><?= $no++ ?></td>
                                    <td class="fw-semibold"><?= e($row['nama_cabang']) ?></td>
                                    <td><?= e($row['alamat']) ?></td>
                                    <td><?= e($row['telepon']) ?></td>
                                    <td><?= (int) $row['jml_produk'] ?></td>
                                    <td><?= (int) $row['jml_user'] ?></td>
                                    <td>
                                        <a href="index.php?page=updateKategori&id=<?= (int) $row['id'] ?>"><i class="ti ti-edit"></i></a>
                                        <form method="post" class="d-inline" onsubmit="return confirm('Hapus cabang ini?')">
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
