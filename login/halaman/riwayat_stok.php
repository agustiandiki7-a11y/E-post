<?php
if (!function_exists("eposCsrfToken")) { http_response_code(403); exit; }
// RIWAYAT STOK bersama. Variabel: $db, $SCOPE (0 = semua cabang), $HAL_NAMA ('riwayatStok').
$tipe = stokTipeValid(isset($_GET['tipe']) ? $_GET['tipe'] : '');
$cari = isset($_GET['cari']) ? trim($_GET['cari']) : '';
$dari = tglValid(isset($_GET['dari']) ? $_GET['dari'] : '', date('Y-m-d', strtotime('-30 days')));
$sampai = tglValid(isset($_GET['sampai']) ? $_GET['sampai'] : '', date('Y-m-d'));
$data = stokLogDaftar($db, $SCOPE, $tipe, $cari, $dari, $sampai);
$hal = halIni();
$jml = count($data);
$data = halPotong($data, $hal);
$tipeOpsi = ['' => 'Semua jenis', 'restock' => 'Restock', 'penjualan' => 'Penjualan', 'void' => 'Void (kembali)', 'koreksi' => 'Koreksi', 'awal' => 'Stok awal'];
?>
<form method="get" class="d-flex gap-2 mb-3 flex-wrap align-items-end">
    <input type="hidden" name="page" value="<?= e($HAL_NAMA) ?>">
    <div>
        <label class="form-label small mb-1">Dari</label>
        <input type="date" name="dari" value="<?= e($dari) ?>" class="form-control">
    </div>
    <div>
        <label class="form-label small mb-1">Sampai</label>
        <input type="date" name="sampai" value="<?= e($sampai) ?>" class="form-control">
    </div>
    <div>
        <label class="form-label small mb-1">Jenis</label>
        <select name="tipe" class="form-select">
            <?php foreach ($tipeOpsi as $v => $t) { ?>
                <option value="<?= e($v) ?>" <?= $tipe === $v ? 'selected' : '' ?>><?= e($t) ?></option>
            <?php } ?>
        </select>
    </div>
    <div>
        <label class="form-label small mb-1">Produk</label>
        <input type="text" name="cari" value="<?= e($cari) ?>" class="form-control" placeholder="Cari nama produk...">
    </div>
    <button class="btn btn-outline-secondary" type="submit"><i class="ti ti-filter"></i> Terapkan</button>
</form>
<div class="card">
    <div class="table-responsive">
        <table class="table mb-0 text-nowrap table-hover">
            <thead class="table-light border-light">
                <tr>
                    <th>No</th>
                    <th>Waktu</th>
                    <th>Produk</th>
                    <?php if ($SCOPE == 0) { ?><th>Cabang</th><?php } ?>
                    <th>Jenis</th>
                    <th class="text-end">Jumlah</th>
                    <th class="text-end">Stok</th>
                    <th>Oleh</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$data) { ?>
                    <tr><td colspan="<?= $SCOPE == 0 ? 9 : 8 ?>" class="text-center text-secondary py-4">Belum ada riwayat stok pada filter ini.</td></tr>
                <?php } ?>
                <?php $no = halMulai($hal); foreach ($data as $r) { ?>
                    <tr class="align-middle">
                        <td><?= $no++ ?></td>
                        <td><?= e(date('d/m/Y H:i', strtotime($r['dibuat']))) ?></td>
                        <td class="fw-semibold"><?= e($r['nama_produk']) ?></td>
                        <?php if ($SCOPE == 0) { ?><td><?= e($r['nama_cabang']) ?></td><?php } ?>
                        <td><?= stokTipeLabel($r['tipe']) ?></td>
                        <td class="text-end <?= $r['jumlah'] >= 0 ? 'text-success' : 'text-danger' ?>"><?= ($r['jumlah'] > 0 ? '+' : '') . (int) $r['jumlah'] ?></td>
                        <td class="text-end"><?= (int) $r['stok_sebelum'] ?> &rarr; <?= (int) $r['stok_sesudah'] ?></td>
                        <td><?= e($r['nama_user'] ?: '-') ?></td>
                        <td><?= e($r['keterangan'] ?: '-') ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <?php halNav($jml, $hal); ?>
</div>
