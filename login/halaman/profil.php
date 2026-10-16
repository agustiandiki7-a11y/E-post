<?php
if (!function_exists("eposCsrfToken")) { http_response_code(403); exit; }
// Halaman PROFIL bersama (owner, manajer, kasir). Form diproses di index.php lewat profilProses().
$u = $_SESSION['user'];
$tokenCsrf = eposCsrfToken();
?>
<div class="row">
    <div class="col-lg-5 mb-4">
        <div class="card h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <?= eposAvatar($u['nama'], $u['role'], 56) ?>
                    <div>
                        <h5 class="mb-0"><?= e($u['nama']) ?></h5>
                        <span class="badge bg-primary-subtle text-primary"><?= e(eposRoleLabel($u['role'])) ?></span>
                    </div>
                </div>
                <p class="text-secondary small mb-1">Email (dipakai untuk login)</p>
                <p class="fw-semibold"><?= e($u['email']) ?></p>
                <hr>
                <form method="post" autocomplete="off">
                    <input type="hidden" name="csrf" value="<?= e($tokenCsrf) ?>">
                    <input type="hidden" name="aksi" value="nama">
                    <label class="form-label" for="pf-nama">Nama tampilan</label>
                    <input type="text" id="pf-nama" name="nama" value="<?= e($u['nama']) ?>" class="form-control mb-3" maxlength="100" required>
                    <button class="btn btn-primary" type="submit">Simpan nama</button>
                </form>
                <p class="text-secondary small mt-3 mb-0">Email dan peran hanya bisa diubah oleh owner lewat menu User.</p>
            </div>
        </div>
    </div>
    <div class="col-lg-7 mb-4">
        <div class="card h-100">
            <div class="card-header"><h5 class="mb-0">Ganti Password</h5></div>
            <div class="card-body p-4">
                <form method="post" autocomplete="off">
                    <input type="hidden" name="csrf" value="<?= e($tokenCsrf) ?>">
                    <input type="hidden" name="aksi" value="password">
                    <div class="mb-3">
                        <label class="form-label" for="pw-lama">Password lama</label>
                        <input type="password" id="pw-lama" name="lama" class="form-control" autocomplete="current-password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="pw-baru">Password baru</label>
                        <input type="password" id="pw-baru" name="baru" class="form-control" autocomplete="new-password" minlength="8" maxlength="72" required>
                        <div class="form-text">Minimal 8 karakter, harus ada huruf dan angka.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="pw-ulang">Ulangi password baru</label>
                        <input type="password" id="pw-ulang" name="ulang" class="form-control" autocomplete="new-password" required>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="pw-lihat">
                        <label class="form-check-label small" for="pw-lihat">Tampilkan password</label>
                    </div>
                    <button class="btn btn-primary" type="submit">Ganti password</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
document.getElementById('pw-lihat').addEventListener('change', function () {
    ['pw-lama', 'pw-baru', 'pw-ulang'].forEach(function (id) {
        document.getElementById(id).type = document.getElementById('pw-lihat').checked ? 'text' : 'password';
    });
});
</script>
