<?php
// TRANSAKSI BARU - pilih produk, keranjang, potongan, simpan.
// Perhitungan di browser hanya tampilan; angka final dihitung ulang di server dari harga database.
$produk = $ksr->produk('');
$dataJs = [];
foreach ($produk as $p) {
    $dataJs[(int) $p['id']] = [
        'id'    => (int) $p['id'],
        'nama'  => $p['nama_produk'],
        'harga' => (float) $p['harga_jual'],
        'stok'  => (int) $p['stok'],
    ];
}
?>
<style>
    .pos-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 12px; }
    .pos-item { text-align: left; border: 1px solid var(--bs-border-color, #e5e5e5); background: #fff; border-radius: 12px; padding: 14px; cursor: pointer; transition: .15s; position: relative; min-height: 112px; display: flex; flex-direction: column; justify-content: space-between; }
    .pos-item:hover:not(:disabled) { border-color: #e66239; box-shadow: 0 4px 14px rgba(230,98,57,.15); transform: translateY(-1px); }
    .pos-item:active:not(:disabled) { transform: scale(.98); }
    .pos-item:disabled { opacity: .5; cursor: not-allowed; background: #f6f6f6; }
    .pos-item .nm { font-weight: 600; line-height: 1.25; margin-bottom: 6px; }
    .pos-item .hg { color: #e66239; font-weight: 700; }
    .pos-item .qty { position: absolute; top: 8px; right: 8px; background: #e66239; color: #fff; border-radius: 999px; min-width: 24px; height: 24px; font-size: 12px; font-weight: 700; display: none; align-items: center; justify-content: center; padding: 0 6px; }
    .pos-item.in-cart { border-color: #e66239; background: #fff8f5; }
    .pos-item.in-cart .qty { display: flex; }
    .quick-cash .btn { font-size: 13px; }
    @media (min-width: 992px) { .pos-cart { position: sticky; top: 80px; } }
</style>
<div class="row">
    <!-- DAFTAR PRODUK -->
    <div class="col-lg-7 col-xl-8 mb-4">
        <div class="card mb-3">
            <div class="card-body py-3 d-flex flex-wrap gap-2 align-items-center">
                <div class="flex-grow-1" style="min-width:200px">
                    <input type="text" id="cariProduk" class="form-control form-control-lg" placeholder="Cari produk... (tekan / lalu Enter untuk tambah)" autocomplete="off">
                </div>
                <span class="text-secondary small"><?= count($produk) ?> produk &bull; <?= e($cabang['nama_cabang']) ?></span>
            </div>
        </div>

        <?php if (!$produk) { ?>
            <div class="card"><div class="card-body text-center text-secondary py-5">Belum ada produk di cabang ini. Hubungi owner.</div></div>
        <?php } ?>

        <div class="pos-grid" id="daftarProduk">
            <?php foreach ($produk as $p) { ?>
                <button type="button" class="pos-item btn-tambah" data-id="<?= (int) $p['id'] ?>" data-nama="<?= e(strtolower($p['nama_produk'])) ?>" <?= $p['stok'] <= 0 ? 'disabled' : '' ?>>
                    <span class="qty">0</span>
                    <span>
                        <div class="nm"><?= e($p['nama_produk']) ?></div>
                        <div class="hg"><?= rupiah($p['harga_jual']) ?></div>
                    </span>
                    <span class="small mt-2">
                        <?php if ($p['stok'] <= 0) { ?>
                            <span class="badge bg-danger">Habis</span>
                        <?php } elseif ($p['stok'] <= 10) { ?>
                            <span class="badge bg-warning text-dark">Sisa <?= (int) $p['stok'] ?></span>
                        <?php } else { ?>
                            <span class="text-secondary">Stok <?= (int) $p['stok'] ?></span>
                        <?php } ?>
                    </span>
                </button>
            <?php } ?>
        </div>
        <div id="tidakAda" class="card" style="display:none"><div class="card-body text-center text-secondary py-4">Produk tidak ditemukan.</div></div>
    </div>

    <!-- KERANJANG -->
    <div class="col-lg-5 col-xl-4 mb-4">
        <div class="card pos-cart">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="ti ti-shopping-cart"></i> Keranjang</h5>
                <button type="button" id="btnKosongkan" class="btn btn-sm btn-outline-secondary">Kosongkan</button>
            </div>
            <div class="table-responsive" style="max-height: 280px; overflow-y: auto;">
                <table class="table mb-0 align-middle">
                    <tbody id="isiKeranjang">
                        <tr><td class="text-center text-secondary py-4">Keranjang masih kosong.</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="card-body border-top">
                <div class="row g-2 mb-3">
                    <div class="col-5">
                        <label class="form-label small mb-1" for="diskonTipe">Potongan</label>
                        <select id="diskonTipe" class="form-select">
                            <option value="rupiah">Rp</option>
                            <option value="persen">%</option>
                        </select>
                    </div>
                    <div class="col-7">
                        <label class="form-label small mb-1" for="diskonNilai">Nilai potongan</label>
                        <input type="number" id="diskonNilai" class="form-control" min="0" step="any" value="0">
                    </div>
                </div>
                <div class="d-flex justify-content-between mb-1"><span class="text-secondary">Subtotal</span><span id="tSubtotal">Rp 0</span></div>
                <div class="d-flex justify-content-between mb-2"><span class="text-secondary">Diskon</span><span id="tDiskon">- Rp 0</span></div>
                <div class="d-flex justify-content-between fs-3 fw-bold mb-3"><span>Total</span><span id="tTotal" class="text-primary">Rp 0</span></div>

                <label class="form-label small mb-1" for="uangTerima">Uang diterima (opsional, hanya hitung kembalian)</label>
                <input type="number" id="uangTerima" class="form-control mb-2" min="0" step="any" placeholder="0">
                <div class="quick-cash d-flex flex-wrap gap-1 mb-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-cash="pas">Uang pas</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-cash="20000">20rb</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-cash="50000">50rb</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-cash="100000">100rb</button>
                </div>
                <div class="d-flex justify-content-between mb-3"><span class="text-secondary">Kembalian</span><strong id="tKembali">Rp 0</strong></div>

                <div id="pesanError" class="alert alert-danger py-2 small" style="display:none"></div>
                <button type="button" id="btnSimpan" class="btn btn-primary w-100 btn-lg" disabled>
                    <i class="ti ti-check"></i> Simpan &amp; Cetak Struk
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var PRODUK = <?= json_encode($dataJs, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var CSRF = <?= json_encode($_SESSION['csrf']) ?>;
    var cart = {};   // id produk -> jumlah

    var $ = function (id) { return document.getElementById(id); };
    var rp = function (n) { return 'Rp ' + Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.'); };
    var esc = function (s) {
        return String(s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    };

    function hitung() {
        var sub = 0, id;
        for (id in cart) { sub += PRODUK[id].harga * cart[id]; }
        var nilai = Math.max(0, parseFloat($('diskonNilai').value) || 0);
        var diskon = $('diskonTipe').value === 'persen' ? Math.round(sub * Math.min(100, nilai) / 100) : nilai;
        diskon = Math.min(diskon, sub);
        return { sub: sub, diskon: diskon, total: sub - diskon };
    }

    function render() {
        var html = '', ada = false, id;
        for (id in cart) {
            ada = true;
            var p = PRODUK[id], q = cart[id];
            html += '<tr data-id="' + id + '">'
                + '<td><div class="fw-semibold">' + esc(p.nama) + '</div><small class="text-secondary">' + rp(p.harga) + '</small></td>'
                + '<td class="text-nowrap text-center">'
                + '<button type="button" class="btn btn-sm btn-outline-secondary" data-act="kurang">&minus;</button>'
                + '<span class="mx-2 fw-semibold">' + q + '</span>'
                + '<button type="button" class="btn btn-sm btn-outline-secondary" data-act="tambah">+</button></td>'
                + '<td class="text-end text-nowrap">' + rp(p.harga * q) + '</td>'
                + '<td class="text-end"><button type="button" class="btn btn-sm btn-link link-danger p-0" data-act="hapus" title="Hapus"><i class="ti ti-trash"></i></button></td>'
                + '</tr>';
        }
        $('isiKeranjang').innerHTML = ada ? html : '<tr><td class="text-center text-secondary py-4">Keranjang masih kosong.</td></tr>';
        var h = hitung();
        $('tSubtotal').textContent = rp(h.sub);
        $('tDiskon').textContent = '- ' + rp(h.diskon);
        $('tTotal').textContent = rp(h.total);
        $('btnSimpan').disabled = !ada;
        var uang = parseFloat($('uangTerima').value) || 0;
        $('tKembali').textContent = uang >= h.total && ada ? rp(uang - h.total) : (uang > 0 && ada ? 'Kurang ' + rp(h.total - uang) : 'Rp 0');
        var tombol = document.querySelectorAll('.pos-item');
        for (var i = 0; i < tombol.length; i++) {
            var pid = tombol[i].getAttribute('data-id'), q = cart[pid] || 0;
            tombol[i].classList.toggle('in-cart', q > 0);
            tombol[i].querySelector('.qty').textContent = q;
        }
    }

    function tambah(id) {
        var p = PRODUK[id];
        if (!p) { return; }
        var q = (cart[id] || 0) + 1;
        if (q > p.stok) { tampilError('Stok ' + p.nama + ' hanya ' + p.stok + '.'); return; }
        tampilError('');
        cart[id] = q;
        render();
    }

    function tampilError(teks) {
        var el = $('pesanError');
        el.textContent = teks;
        el.style.display = teks ? 'block' : 'none';
    }

    // klik tombol "Tambah" di daftar produk
    $('daftarProduk').addEventListener('click', function (ev) {
        var b = ev.target.closest('.btn-tambah');
        if (b) { tambah(b.getAttribute('data-id')); }
    });

    // tombol + / - / hapus di keranjang
    $('isiKeranjang').addEventListener('click', function (ev) {
        var b = ev.target.closest('[data-act]');
        if (!b) { return; }
        var id = b.closest('tr').getAttribute('data-id');
        var act = b.getAttribute('data-act');
        if (act === 'tambah') { tambah(id); return; }
        if (act === 'kurang') { cart[id] = cart[id] - 1; if (cart[id] < 1) { delete cart[id]; } }
        if (act === 'hapus') { delete cart[id]; }
        tampilError('');
        render();
    });

    $('btnKosongkan').addEventListener('click', function () { cart = {}; tampilError(''); render(); });
    $('diskonTipe').addEventListener('change', render);
    $('diskonNilai').addEventListener('input', render);
    $('uangTerima').addEventListener('input', render);
    document.querySelector('.quick-cash').addEventListener('click', function (ev) {
        var b = ev.target.closest('[data-cash]');
        if (!b) { return; }
        var v = b.getAttribute('data-cash');
        $('uangTerima').value = v === 'pas' ? hitung().total : v;
        render();
    });

    // pintasan: "/" fokus ke pencarian, Enter menambah produk pertama yang cocok
    document.addEventListener('keydown', function (ev) {
        var tag = (ev.target.tagName || '').toLowerCase();
        if (ev.key === '/' && tag !== 'input' && tag !== 'select') { ev.preventDefault(); $('cariProduk').focus(); }
    });
    $('cariProduk').addEventListener('keydown', function (ev) {
        if (ev.key !== 'Enter') { return; }
        ev.preventDefault();
        var baris = $('daftarProduk').querySelectorAll('.pos-item');
        for (var i = 0; i < baris.length; i++) {
            if (baris[i].style.display !== 'none' && !baris[i].disabled) { tambah(baris[i].getAttribute('data-id')); break; }
        }
    });

    // cari produk
    $('cariProduk').addEventListener('input', function () {
        var kata = this.value.trim().toLowerCase(), tampil = 0;
        var baris = $('daftarProduk').querySelectorAll('.pos-item');
        for (var i = 0; i < baris.length; i++) {
            var cocok = baris[i].getAttribute('data-nama').indexOf(kata) !== -1;
            baris[i].style.display = cocok ? '' : 'none';
            if (cocok) { tampil++; }
        }
        $('tidakAda').style.display = (tampil === 0 && baris.length > 0) ? '' : 'none';
    });

    // simpan transaksi
    $('btnSimpan').addEventListener('click', function () {
        var btn = this, id;
        var fd = new FormData();
        fd.append('csrf', CSRF);
        fd.append('diskon_tipe', $('diskonTipe').value);
        fd.append('diskon_nilai', $('diskonNilai').value || '0');
        for (id in cart) { fd.append('item[' + id + ']', cart[id]); }

        btn.disabled = true;
        tampilError('');
        fetch('index.php?page=baru', { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (j) {
                if (j.ok) {
                    window.location.href = 'index.php?page=struk&id=' + j.id + '&baru=1';
                } else {
                    tampilError(j.msg || 'Transaksi gagal disimpan.');
                    btn.disabled = false;
                }
            })
            .catch(function () {
                tampilError('Gagal menghubungi server. Coba lagi.');
                btn.disabled = false;
            });
    });

    render();
})();
</script>
