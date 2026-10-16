<?php
require_once 'function/produk.php';
require_once 'function/cabang.php';
$produk = new Produk();
$cabang = (new Cabang())->semua();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$row = $produk->cari($id);
if (!$row) {
    redirect('produk', 'danger', 'Data tidak ditemukan.');
}
$pesan = '';
if (isset($_POST['nama_produk'])) {
    $foto = fotoSimpan('foto', $galatFoto);
    if ($foto === false) {
        $pesan = $galatFoto;
    } else {
        // '' = tidak ada unggahan: foto lama dipertahankan kecuali kotak "hapus foto" dicentang
        $fotoBaru = $foto !== '' ? $foto : (isset($_POST['hapus_foto']) ? null : false);
        $result = $produk->ubah($id, (int) $_POST['branch_id'], trim($_POST['nama_produk']), (float) $_POST['harga_jual'], (int) $_POST['stok'], $fotoBaru);
        if ($result) {
            if ($fotoBaru !== false) {
                fotoHapus($row['foto']);
            }
            redirect('produk', 'success', 'Produk berhasil diperbarui.');
        }
        fotoHapus($foto);
        $pesan = $produk->error ?: 'Data produk gagal diubah.';
    }
}
?>
<main id="content" class="content py-10">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                    <div>
                        <h1 class="fs-3 mb-1">Ubah Produk</h1>
                        <p class="mb-0">Harga jual dan stok diatur per cabang</p>
                    </div>
                    <div>
                        <a href="index.php?page=produk" class="btn btn-primary">Kembali ke Daftar Produk</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body p-4">
                        <?php toast('danger', $pesan); ?>
                        <?= fotoGaya() ?>
                        <form method="post" action="" enctype="multipart/form-data">
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
                            <div class="col-md-12 mb-3">
                                <label for="foto" class="form-label">Foto Produk <span class="text-secondary fw-normal">(JPG, PNG, atau WEBP, maksimal 2 MB)</span></label>
                                <div class="d-flex align-items-center gap-3">
                                    <span id="fotoPratinjau"><?= fotoProduk($row['foto'], $row['nama_produk'], 72) ?></span>
                                    <input type="file" class="form-control" name="foto" id="foto" accept="image/jpeg,image/png,image/webp">
                                </div>
                                <?php if (fotoAda($row['foto'])) { ?>
                                    <div class="form-check mt-2"><input class="form-check-input" type="checkbox" name="hapus_foto" id="hapus_foto" value="1"><label class="form-check-label" for="hapus_foto">Hapus foto ini</label></div>
                                <?php } ?>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php include 'components/footer.php'; ?>
    </div>
</main>
<script>
document.getElementById('foto').addEventListener('change', function () {
    var f = this.files[0], box = document.getElementById('fotoPratinjau');
    if (!f || !/^image\/(jpeg|png|webp)$/.test(f.type)) { return; }
    var img = document.createElement('img');
    img.src = URL.createObjectURL(f); img.width = 72; img.height = 72; img.className = 'foto-produk'; img.style.cssText = 'width:72px;height:72px';
    box.innerHTML = ''; box.appendChild(img);
});
</script>
