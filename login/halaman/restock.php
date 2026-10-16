<?php
if (!function_exists("eposCsrfToken")) { http_response_code(403); exit; }
// Form RESTOCK bersama (owner = semua cabang, manajer = cabangnya). Variabel: $db, $SCOPE, $KEMBALI (nama halaman daftar).
$pid = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$p = stokProduk($db, $pid, $SCOPE);
$tokenCsrf = eposCsrfToken();
?>
<div class="row">
    <div class="col-lg-6 mb-4">
        <?php if (!$p) { ?>
            <div class="card"><div class="card-body p-4">
                <p class="mb-3">Produk tidak ditemukan atau bukan dari cabangmu.</p>
                <a href="index.php?page=<?= e($KEMBALI) ?>" class="btn btn-outline-secondary">Kembali</a>
            </div></div>
        <?php } else { ?>
            <?= fotoGaya() ?>
            <div class="card">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <?= fotoProduk($p['foto'], $p['nama_produk'], 64) ?>
                        <div>
                            <h5 class="mb-0"><?= e($p['nama_produk']) ?></h5>
                            <small class="text-secondary"><?= e($p['nama_cabang']) ?></small>
                            <div class="mt-1">Stok sekarang: <strong><?= (int) $p['stok'] ?></strong> <?= stokBadge($p['stok']) ?></div>
                        </div>
                    </div>
                    <form method="post" autocomplete="off">
                        <input type="hidden" name="csrf" value="<?= e($tokenCsrf) ?>">
                        <input type="hidden" name="product_id" value="<?= (int) $p['id'] ?>">
                        <div class="mb-3">
                            <label class="form-label" for="rs-jml">Jumlah barang masuk</label>
                            <input type="number" id="rs-jml" name="jumlah" class="form-control" min="1" max="100000" step="1" required autofocus>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="rs-ket">Keterangan (opsional)</label>
                            <input type="text" id="rs-ket" name="keterangan" class="form-control" maxlength="200" placeholder="Contoh: kiriman supplier 12 Okt">
                        </div>
                        <button class="btn btn-primary" type="submit">Simpan restock</button>
                        <a href="index.php?page=<?= e($KEMBALI) ?>" class="btn btn-outline-secondary">Batal</a>
                    </form>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
