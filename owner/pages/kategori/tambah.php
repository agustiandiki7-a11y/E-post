<?php
require_once 'function/kategori.php';
$pesan = '';
if (isset($_POST['nama_cabang'])) {
    $kategori = new Kategori();
    $result = $kategori->tambah(
        trim($_POST['nama_cabang']),
        trim($_POST['alamat']),
        trim($_POST['telepon'])
    );
    if ($result) {
        redirect('kategori');
    }
    $pesan = 'Data cabang gagal ditambahkan.';
}
?>
<main id="content" class="content py-10">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                    <div>
                        <h1 class="fs-3 mb-1">Tambah Cabang</h1>
                        <p class="mb-0">Isi data cabang baru</p>
                    </div>
                    <div>
                        <a href="index.php?page=kategori" class="btn btn-primary">Kembali ke Daftar Cabang</a>
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
                                <label for="nama_cabang" class="form-label">Nama Cabang</label>
                                <input type="text" class="form-control" name="nama_cabang" id="nama_cabang" placeholder="Contoh: Cabang Ungaran" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="alamat" class="form-label">Alamat</label>
                                <textarea class="form-control" name="alamat" id="alamat" rows="3" placeholder="Alamat lengkap cabang"></textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="telepon" class="form-label">Telepon</label>
                                <input type="text" class="form-control" name="telepon" id="telepon" placeholder="024-xxxxxxx">
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
