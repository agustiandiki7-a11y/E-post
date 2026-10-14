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
    .pos { --indigo: #4942dd; --indigo-d: #2f29a8; --tint: #f1f0ff; --ink: #1c1c2e; --muted: #6b6b85; --line: #dfe1f3; }
    .mono { font-family: "IBM Plex Mono", ui-monospace, Consolas, monospace; font-variant-numeric: tabular-nums; }
    .pos-search { position: relative; margin-bottom: 16px; }
    .pos-search .ti { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--muted); width: 1.3em; height: 1.3em; }
    .pos-search input { width: 100%; height: 54px; border: 1.5px solid var(--line); border-radius: 14px; padding: 0 90px 0 48px; font-size: 16px; background: #fff; outline: none; transition: border-color .15s, box-shadow .15s; }
    .pos-search input:focus { border-color: var(--indigo); box-shadow: 0 0 0 4px rgba(73,66,221,.14); }
    .pos-search kbd { position: absolute; right: 14px; top: 50%; transform: translateY(-50%); background: #eef0fb; color: var(--muted); border: 0; border-radius: 6px; padding: 3px 8px; font-size: 12px; }
    .pos-meta { display: flex; justify-content: space-between; color: var(--muted); font-size: 13px; margin: -4px 2px 12px; }
    .pos-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(168px, 1fr)); gap: 12px; }
    .pos-item { text-align: left; border: 1.5px solid var(--line); background: #fff; border-radius: 14px; padding: 14px; cursor: pointer; transition: border-color .15s, background .15s, box-shadow .15s, transform .1s; position: relative; min-height: 124px; display: flex; flex-direction: column; justify-content: space-between; gap: 10px; color: var(--ink); }
    .pos-item:hover:not(:disabled) { border-color: var(--indigo); box-shadow: 0 6px 18px rgba(73,66,221,.14); }
    .pos-item:active:not(:disabled) { transform: scale(.98); }
    .pos-item:focus-visible { outline: 3px solid rgba(73,66,221,.45); outline-offset: 2px; }
    .pos-item:disabled { cursor: not-allowed; background: #f4f5fa; color: #9a9bb3; }
    .pos-item:disabled .hg { color: #9a9bb3; }
    .pos-item .nm { font-weight: 700; line-height: 1.25; font-size: 15px; padding-right: 26px; }
    .pos-item .hg { color: var(--indigo); font-weight: 600; font-size: 15px; margin-top: 4px; }
    .pos-item .stk { font-size: 12px; color: var(--muted); }
    .pos-item .stk b { display: inline-block; border-radius: 999px; padding: 2px 9px; font-weight: 600; }
    .pos-item .stk .tipis { background: #fff3cd; color: #7a5a00; }
    .pos-item .stk .habis { background: #fde4e1; color: #a1271b; }
    .pos-item .qty { position: absolute; top: 10px; right: 10px; background: var(--indigo); color: #fff; border-radius: 999px; min-width: 26px; height: 26px; font-size: 13px; font-weight: 700; display: none; align-items: center; justify-content: center; padding: 0 7px; }
    .pos-item.in-cart { border-color: var(--indigo); background: var(--tint); }
    .pos-item.in-cart .qty { display: flex; }

    /* keranjang berbentuk struk */
    .slip-wrap { filter: drop-shadow(0 10px 22px rgba(35,31,120,.16)); }
    .slip { background: #fff; clip-path: polygon(0 0, 100% 0, 100.00% 100%, 95.45% calc(100% - 8px), 90.91% 100%, 86.36% calc(100% - 8px), 81.82% 100%, 77.27% calc(100% - 8px), 72.73% 100%, 68.18% calc(100% - 8px), 63.64% 100%, 59.09% calc(100% - 8px), 54.55% 100%, 50.00% calc(100% - 8px), 45.45% 100%, 40.91% calc(100% - 8px), 36.36% 100%, 31.82% calc(100% - 8px), 27.27% 100%, 22.73% calc(100% - 8px), 18.18% 100%, 13.64% calc(100% - 8px), 9.09% 100%, 4.55% calc(100% - 8px), 0.00% 100%); padding: 20px 20px 34px; }
    @media (min-width: 992px) { .pos-cart { position: sticky; top: 76px; } }
    .slip h5 { margin: 0; font-size: 17px; font-weight: 800; display: flex; align-items: center; gap: 8px; }
    .slip-top { display: flex; justify-content: space-between; align-items: center; }
    .btn-kosong { border: 0; background: #eef0fb; color: var(--muted); border-radius: 8px; padding: 6px 12px; font-size: 13px; font-weight: 600; cursor: pointer; }
    .btn-kosong:hover { background: #e2e4f6; color: var(--ink); }
    .dash { border: 0; border-top: 1.5px dashed var(--line); margin: 12px 0; }
    #isiKeranjang { max-height: clamp(110px, calc(100vh - 620px), 240px); overflow-y: auto; margin: 0 -4px; padding: 0 4px; }
    .kosong { text-align: center; color: var(--muted); padding: 26px 8px; font-size: 14px; }
    .line { padding: 9px 0; border-bottom: 1px dotted var(--line); }
    .line:last-child { border-bottom: 0; }
    .ln-top { display: flex; justify-content: space-between; gap: 10px; font-weight: 600; font-size: 14px; }
    .ln-bot { display: flex; justify-content: space-between; align-items: center; margin-top: 6px; font-size: 12px; color: var(--muted); }
    .step { display: inline-flex; align-items: center; gap: 4px; }
    .step button { width: 28px; height: 28px; border: 1.5px solid var(--line); background: #fff; border-radius: 8px; cursor: pointer; font-weight: 700; color: var(--ink); line-height: 1; display: inline-flex; align-items: center; justify-content: center; }
    .step button:hover { border-color: var(--indigo); color: var(--indigo); }
    .step b { min-width: 26px; text-align: center; color: var(--ink); font-size: 14px; }
    .step .del { border: 0; color: #c2362b; }
    .step .del:hover { background: #fde4e1; color: #a1271b; }
    .row-k { display: flex; justify-content: space-between; font-size: 14px; margin: 5px 0; color: var(--muted); }
    .row-k span:last-child { color: var(--ink); }
    .total { display: flex; justify-content: space-between; align-items: baseline; margin: 10px 0 4px; }
    .total span:first-child { font-weight: 800; font-size: 16px; }
    .total span:last-child { font-size: 28px; font-weight: 700; color: var(--indigo); letter-spacing: -.02em; }
    .pos label { font-size: 12px; font-weight: 600; color: var(--muted); margin-bottom: 4px; display: block; }
    .pos .fld { width: 100%; height: 42px; border: 1.5px solid var(--line); border-radius: 10px; padding: 0 12px; background: #f8f9ff; outline: none; font-size: 14px; }
    .pos .fld:focus { border-color: var(--indigo); background: #fff; box-shadow: 0 0 0 3px rgba(73,66,221,.14); }
    .chips { display: flex; flex-wrap: wrap; gap: 6px; margin: 8px 0; }
    .chips button { border: 1.5px solid var(--line); background: #fff; border-radius: 999px; padding: 5px 12px; font-size: 13px; font-weight: 600; cursor: pointer; color: var(--ink); }
    .chips button:hover { border-color: var(--indigo); color: var(--indigo); background: var(--tint); }
    .btn-simpan { width: 100%; height: 54px; border: 0; border-radius: 12px; background: var(--indigo); color: #fff; font-size: 16px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: background .15s; margin-top: 12px; }
    .btn-simpan:hover:not(:disabled) { background: var(--indigo-d); }
    .btn-simpan:disabled { background: #b9b6f0; cursor: not-allowed; }
    .btn-simpan:focus-visible, .chips button:focus-visible, .step button:focus-visible { outline: 3px solid rgba(73,66,221,.45); outline-offset: 2px; }
    .galat { background: #fdecea; color: #b42318; border-left: 4px solid #b42318; border-radius: 8px; padding: 9px 12px; font-size: 13px; margin-top: 10px; display: none; }
    .mbar { display: none; }
    @media (max-width: 991px) {
        .mbar { position: fixed; left: 12px; right: 12px; bottom: 12px; z-index: 1030; display: flex; justify-content: space-between; align-items: center; gap: 12px; background: #4942dd; color: #fff; border: 0; border-radius: 14px; padding: 12px 16px; box-shadow: 0 10px 24px rgba(35,31,120,.35); font-weight: 700; cursor: pointer; text-align: left; }
        .mbar[hidden] { display: none; }
        .mbar small { display: block; font-weight: 500; opacity: .85; font-size: 12px; }
        .mbar .mono { font-size: 18px; }
        .pos { padding-bottom: 70px; }
    }
    .empty-p { text-align: center; color: var(--muted); padding: 36px 12px; background: #fff; border: 1.5px dashed var(--line); border-radius: 14px; }
</style>
<div class="row pos">
    <!-- DAFTAR PRODUK -->
    <div class="col-lg-7 col-xl-8 mb-4">
        <div class="pos-search">
            <i class="ti ti-search"></i>
            <input type="text" id="cariProduk" placeholder="Cari produk..." autocomplete="off" aria-label="Cari produk">
            <kbd>/</kbd>
        </div>
        <div class="pos-meta"><span><?= count($produk) ?> produk</span><span><?= e($cabang['nama_cabang']) ?></span></div>

        <?php if (!$produk) { ?>
            <div class="empty-p">Belum ada produk di cabang ini. Hubungi owner untuk menambahkannya.</div>
        <?php } ?>

        <div class="pos-grid" id="daftarProduk">
            <?php foreach ($produk as $p) { ?>
                <button type="button" class="pos-item btn-tambah" data-id="<?= (int) $p['id'] ?>" data-nama="<?= e(strtolower($p['nama_produk'])) ?>" <?= $p['stok'] <= 0 ? 'disabled' : '' ?>>
                    <span class="qty">0</span>
                    <span>
                        <div class="nm"><?= e($p['nama_produk']) ?></div>
                        <div class="hg mono"><?= rupiah($p['harga_jual']) ?></div>
                    </span>
                    <span class="stk">
                        <?php if ($p['stok'] <= 0) { ?>
                            <b class="habis">Habis</b>
                        <?php } elseif ($p['stok'] <= 10) { ?>
                            <b class="tipis">Sisa <?= (int) $p['stok'] ?></b>
                        <?php } else { ?>
                            Stok <?= (int) $p['stok'] ?>
                        <?php } ?>
                    </span>
                </button>
            <?php } ?>
        </div>
        <div id="tidakAda" class="empty-p" style="display:none">Produk tidak ditemukan. Coba kata kunci lain.</div>
    </div>

    <!-- KERANJANG (bentuk struk) -->
    <div class="col-lg-5 col-xl-4 mb-4" id="panelKeranjang">
        <div class="slip-wrap pos-cart">
            <div class="slip">
                <div class="slip-top">
                    <h5><i class="ti ti-shopping-cart"></i> Keranjang</h5>
                    <button type="button" id="btnKosongkan" class="btn-kosong">Kosongkan</button>
                </div>
                <hr class="dash">
                <div id="isiKeranjang"><div class="kosong">Keranjang masih kosong.<br>Pilih produk di sebelah kiri.</div></div>
                <hr class="dash">

                <div class="row g-2 mb-2">
                    <div class="col-5">
                        <label for="diskonTipe">Potongan</label>
                        <select id="diskonTipe" class="fld">
                            <option value="rupiah">Rp</option>
                            <option value="persen">%</option>
                        </select>
                    </div>
                    <div class="col-7">
                        <label for="diskonNilai">Nilai potongan</label>
                        <input type="number" id="diskonNilai" class="fld mono" min="0" step="any" value="0">
                    </div>
                </div>
                <div class="row-k"><span>Subtotal</span><span class="mono" id="tSubtotal">Rp 0</span></div>
                <div class="row-k"><span>Potongan</span><span class="mono" id="tDiskon">- Rp 0</span></div>
                <div class="total"><span>Total</span><span class="mono" id="tTotal">Rp 0</span></div>
                <hr class="dash">

                <label for="uangTerima">Uang diterima (hanya untuk hitung kembalian)</label>
                <input type="number" id="uangTerima" class="fld mono" min="0" step="any" placeholder="0">
                <div class="chips quick-cash">
                    <button type="button" data-cash="pas">Uang pas</button>
                    <button type="button" data-cash="20000">20rb</button>
                    <button type="button" data-cash="50000">50rb</button>
                    <button type="button" data-cash="100000">100rb</button>
                </div>
                <div class="row-k"><span>Kembalian</span><strong class="mono" id="tKembali" style="color:var(--ink)">Rp 0</strong></div>

                <div id="pesanError" class="galat" role="alert"></div>
                <button type="button" id="btnSimpan" class="btn-simpan" disabled>
                    <i class="ti ti-check"></i> Simpan &amp; cetak struk
                </button>
            </div>
        </div>
    </div>
</div>
<button type="button" class="mbar" id="mbar" hidden><span>Lihat keranjang<small id="mbarJml">0 barang</small></span><span class="mono" id="mbarTotal">Rp 0</span></button>

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
            html += '<div class="line" data-id="' + id + '">'
                + '<div class="ln-top"><span>' + esc(p.nama) + '</span><span class="mono">' + rp(p.harga * q) + '</span></div>'
                + '<div class="ln-bot"><span class="mono">' + q + ' x ' + rp(p.harga) + '</span>'
                + '<span class="step"><button type="button" data-act="kurang" aria-label="Kurangi">&minus;</button>'
                + '<b class="mono">' + q + '</b>'
                + '<button type="button" data-act="tambah" aria-label="Tambah">+</button>'
                + '<button type="button" class="del" data-act="hapus" aria-label="Hapus"><i class="ti ti-trash"></i></button></span></div>'
                + '</div>';
        }
        $('isiKeranjang').innerHTML = ada ? html : '<div class="kosong">Keranjang masih kosong.<br>Pilih produk di sebelah kiri.</div>';
        var h = hitung();
        $('tSubtotal').textContent = rp(h.sub);
        $('tDiskon').textContent = '- ' + rp(h.diskon);
        $('tTotal').textContent = rp(h.total);
        $('btnSimpan').disabled = !ada;
        var jml = 0; for (id in cart) { jml += cart[id]; }
        $('mbar').hidden = !ada;
        $('mbarJml').textContent = jml + ' barang';
        $('mbarTotal').textContent = rp(h.total);
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
        if (teks && window.eposToast) { window.eposToast('danger', teks); }
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
        var id = b.closest('.line').getAttribute('data-id');
        var act = b.getAttribute('data-act');
        if (act === 'tambah') { tambah(id); return; }
        if (act === 'kurang') { cart[id] = cart[id] - 1; if (cart[id] < 1) { delete cart[id]; } }
        if (act === 'hapus') { delete cart[id]; }
        tampilError('');
        render();
    });

    $('mbar').addEventListener('click', function () { $('panelKeranjang').scrollIntoView({ behavior: 'smooth', block: 'start' }); });
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
