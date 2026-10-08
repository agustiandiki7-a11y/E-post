<?php
require_once 'function/kendaraan.php';
require_once 'function/kategori.php';
$produk = new Kendaraan();
$cabang = (new Kategori())->semua();
$row = ['branch_id' => '', 'nama_produk' => '', 'harga_jual' => '', 'stok' => '0'];
$pesan = '';
if (isset($_POST['nama_produk'])) {
    $result = $produk->tambah((int) $_POST['branch_id'], trim($_POST['nama_produk']), (float) $_POST['harga_jual'], (int) $_POST['stok']);
    if ($result) {
        redirect('kendaraan');
    }
    $pesan = 'Data produk gagal ditambahkan.';
}
?>
<main id="content" class="content py-10">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                    <div>
                        <h1 class="fs-3 mb-1">Tambah Produk</h1>
                        <p class="mb-0">Harga jual dan stok diatur per cabang</p>
                    </div>
                    <div>
                        <a href="index.php?page=kendaraan" class="btn btn-primary">Kembali ke Daftar Produk</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body p-4">
                        <?php if ($pesan) { ?><div class="alert alert-danger"><?= e($pesan) ?></div><?php } ?>
                        <form method="post" action="">
                            <div class="col-md-12 mb-3">
                                <label for="branch_id" class="form-label">Cabang</label>
                                <select class="form-select" name="branch_id" id="branch_id" required>
                                    <option value="">-- Pilih cabang --</option>
                                    <?php foreach ($cabang as $c) { ?>
                                        <option value="<?= (int) $c['id'] ?>" <?= ($row['branch_id']) == $c['id'] ? 'selected' : '' ?>><?= e($c['nama_cabang']) ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="nama_produk" class="form-label">Nama Produk</label>
                                <input type="text" class="form-control" name="nama_produk" id="nama_produk" value="<?= e($row['nama_produk']) ?>" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="harga_jual" class="form-label">Harga Jual (Rp)</label>
                                    <input type="number" min="0" step="100" class="form-control" name="harga_jual" id="harga_jual" value="<?= e($row['harga_jual'] === '' ? '' : (int) $row['harga_jual']) ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="stok" class="form-label">Stok</label>
                                    <input type="number" min="0" class="form-control" name="stok" id="stok" value="<?= e($row['stok']) ?>" required>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php include 'components/footer.php'; ?>
    </div>
</main>
