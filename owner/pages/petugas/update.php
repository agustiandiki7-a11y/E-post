<?php
require_once 'function/petugas.php';
require_once 'function/kategori.php';
$petugas = new Petugas();
$cabang = (new Kategori())->semua();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$row = $petugas->cari($id);
if (!$row) {
    redirect('petugas');
}
$pesan = '';
if (isset($_POST['nama'])) {
    $role = in_array($_POST['role'], ['admin', 'kasir', 'manajer']) ? $_POST['role'] : 'kasir';
    $result = $petugas->ubah($id, trim($_POST['nama']), trim($_POST['email']), $_POST['password'], $role, $_POST['branch_id']);
    if ($result) {
        redirect('petugas');
    }
    $pesan = $petugas->error ?: 'Data user gagal diubah.';
    $row = ['nama' => $_POST['nama'], 'email' => $_POST['email'], 'role' => $role, 'branch_id' => $_POST['branch_id']];
}
?>
<main id="content" class="content py-10">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                    <div>
                        <h1 class="fs-3 mb-1">Ubah User</h1>
                        <p class="mb-0">Kasir dan manajer wajib terhubung ke satu cabang</p>
                    </div>
                    <div>
                        <a href="index.php?page=petugas" class="btn btn-primary">Kembali ke Daftar User</a>
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
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nama" class="form-label">Nama</label>
                                    <input type="text" class="form-control" name="nama" id="nama" value="<?= e($row['nama']) ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" id="email" value="<?= e($row['email']) ?>" required>
                                </div>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="password" class="form-label">Password (kosongkan jika tidak diganti)</label>
                                <input type="password" class="form-control" name="password" id="password" >
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="role" class="form-label">Role</label>
                                    <select class="form-select" name="role" id="role" required>
                                        <option value="admin" <?= $row['role'] == 'admin' ? 'selected' : '' ?>>Admin / Owner</option>
                                        <option value="kasir" <?= $row['role'] == 'kasir' ? 'selected' : '' ?>>Kasir</option>
                                        <option value="manajer" <?= $row['role'] == 'manajer' ? 'selected' : '' ?>>Manajer Cabang</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="branch_id" class="form-label">Cabang (kosongkan untuk Admin)</label>
                                    <select class="form-select" name="branch_id" id="branch_id">
                                        <option value="">-- Tidak terikat cabang --</option>
                                        <?php foreach ($cabang as $c) { ?>
                                            <option value="<?= (int) $c['id'] ?>" <?= $row['branch_id'] == $c['id'] && $row['branch_id'] !== '' ? 'selected' : '' ?>><?= e($c['nama_cabang']) ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
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
