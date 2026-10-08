<?php
// Halaman USER (admin / kasir / manajer)
require_once 'function/petugas.php';
$petugas = new Petugas();
$pesan = '';

if (isset($_POST['hapus_id'])) {
    if ((int) $_POST['hapus_id'] === (int) $_SESSION['user']['id']) {
        $pesan = 'Akun yang sedang dipakai login tidak bisa dihapus.';
    } elseif ($petugas->hapus((int) $_POST['hapus_id'])) {
        redirect('petugas');
    } else {
        $pesan = 'User tidak bisa dihapus karena sudah punya transaksi.';
    }
}

$role = isset($_GET['role']) ? $_GET['role'] : '';
if (!in_array($role, ['admin', 'kasir', 'manajer'])) {
    $role = '';
}
$cari = isset($_GET['cari']) ? trim($_GET['cari']) : '';
$data = $petugas->semua($role, $cari);
$warna = ['admin' => 'bg-dark', 'kasir' => 'bg-primary', 'manajer' => 'bg-info text-dark'];
?>
<main id="content" class="content py-10">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="fs-3 mb-1">Master User</h1>
                        <p class="mb-0">Kelola akun owner, kasir, dan manajer cabang</p>
                    </div>
                    <div>
                        <a href="index.php?page=tambahPetugas" class="btn btn-primary">Tambah User</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <?php if ($pesan) { ?><div class="alert alert-danger"><?= e($pesan) ?></div><?php } ?>
                <form method="get" class="d-flex gap-2 mb-3 flex-wrap">
                    <input type="hidden" name="page" value="petugas">
                    <input type="text" name="cari" value="<?= e($cari) ?>" class="form-control" placeholder="Cari nama / email..." style="max-width: 250px;">
                    <select name="role" class="form-select" style="max-width: 180px;">
                        <option value="">Semua role</option>
                        <option value="admin" <?= $role == 'admin' ? 'selected' : '' ?>>Admin / Owner</option>
                        <option value="kasir" <?= $role == 'kasir' ? 'selected' : '' ?>>Kasir</option>
                        <option value="manajer" <?= $role == 'manajer' ? 'selected' : '' ?>>Manajer</option>
                    </select>
                    <button class="btn btn-outline-secondary" type="submit"><i class="ti ti-filter"></i> Filter</button>
                </form>
                <div class="card table-responsive">
                    <table class="table mb-0 text-nowrap table-hover">
                        <thead class="table-light border-light">
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Cabang</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$data) { ?>
                                <tr><td colspan="6" class="text-center text-secondary py-4">Belum ada user.</td></tr>
                            <?php } ?>
                            <?php $no = 1; foreach ($data as $row) { ?>
                                <tr class="align-middle">
                                    <td><?= $no++ ?></td>
                                    <td class="fw-semibold"><?= e($row['nama']) ?></td>
                                    <td><?= e($row['email']) ?></td>
                                    <td><span class="badge <?= $warna[$row['role']] ?>"><?= e(ucfirst($row['role'])) ?></span></td>
                                    <td><?= $row['nama_cabang'] ? e($row['nama_cabang']) : '<span class="text-secondary">Semua cabang</span>' ?></td>
                                    <td>
                                        <a href="index.php?page=updatePetugas&id=<?= (int) $row['id'] ?>"><i class="ti ti-edit"></i></a>
                                        <form method="post" class="d-inline" onsubmit="return confirm('Hapus user ini?')">
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
