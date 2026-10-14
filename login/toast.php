<?php
// Notifikasi (toast) seragam untuk seluruh aplikasi.
//   toast('success', 'Pesan')       -> tampil di halaman ini
//   toastFlash('success', 'Pesan')  -> tampil di halaman berikutnya (setelah redirect), sekali saja
//   toastRender()                   -> dipanggil di partials/script.php; mencetak semua toast
// Tipe: success, danger, warning, info. Pesan selalu di-escape lewat json_encode.
function toast($tipe, $pesan)
{
    if ($pesan === '' || $pesan === null) {
        return;
    }
    $GLOBALS['_epos_toast'][] = [(string) $tipe, (string) $pesan];
}

function toastFlash($tipe, $pesan)
{
    $_SESSION['toast'][] = [(string) $tipe, (string) $pesan];
}

function toastRender()
{
    $daftar = isset($GLOBALS['_epos_toast']) ? $GLOBALS['_epos_toast'] : [];
    if (!empty($_SESSION['toast']) && is_array($_SESSION['toast'])) {
        $daftar = array_merge($_SESSION['toast'], $daftar);
    }
    unset($_SESSION['toast']);
    // kompatibel dengan format lama $_SESSION['flash'] = [tipe, pesan]
    if (!empty($_SESSION['flash']) && is_array($_SESSION['flash'])) {
        $daftar[] = [(string) $_SESSION['flash'][0], (string) $_SESSION['flash'][1]];
    }
    unset($_SESSION['flash']);
    $GLOBALS['_epos_toast'] = [];
    $json = json_encode($daftar, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
    ?>
<style>
#eposToasts { position: fixed; top: 20px; right: 20px; z-index: 3000; display: flex; flex-direction: column; gap: 10px; max-width: calc(100vw - 40px); width: 360px; pointer-events: none; }
.etoast { pointer-events: auto; background: #fff; border-left: 5px solid #16a34a; box-shadow: 0 10px 30px rgba(0,0,0,.18); border-radius: 10px; padding: 12px 14px; font: 14px/1.4 "Segoe UI", Arial, sans-serif; color: #111; display: flex; gap: 10px; align-items: flex-start; animation: etIn .25s ease-out; }
.etoast.danger { border-color: #dc2626; } .etoast.warning { border-color: #2117e9; } .etoast.info { border-color: #2563eb; }
.etoast b { display: block; margin-bottom: 1px; }
.etoast .x { margin-left: auto; border: 0; background: none; font-size: 18px; line-height: 1; cursor: pointer; color: #777; }
.etoast.hide { opacity: 0; transform: translateX(20px); transition: .3s; }
@keyframes etIn { from { opacity: 0; transform: translateX(20px); } to { opacity: 1; transform: none; } }
</style>
<div id="eposToasts" aria-live="polite"></div>
<script>
(function () {
    var judul = {success: 'Berhasil', danger: 'Gagal', warning: 'Perhatian', info: 'Info'};
    window.eposToast = function (tipe, pesan) {
        var box = document.getElementById('eposToasts');
        if (!box || !pesan) { return; }
        tipe = judul[tipe] ? tipe : 'info';
        var el = document.createElement('div');
        el.className = 'etoast ' + tipe;
        el.setAttribute('role', tipe === 'danger' ? 'alert' : 'status');
        var t = document.createElement('div');
        var b = document.createElement('b'); b.textContent = judul[tipe];
        var s = document.createElement('span'); s.textContent = pesan;
        t.appendChild(b); t.appendChild(s);
        var x = document.createElement('button'); x.type = 'button'; x.className = 'x'; x.textContent = '×'; x.setAttribute('aria-label', 'Tutup');
        el.appendChild(t); el.appendChild(x);
        function tutup() { el.classList.add('hide'); setTimeout(function () { el.remove(); }, 300); }
        x.onclick = tutup;
        box.appendChild(el);
        setTimeout(tutup, tipe === 'danger' ? 7000 : 4500);
    };
    var data = <?= $json ?>;
    for (var i = 0; i < data.length; i++) { window.eposToast(data[i][0], data[i][1]); }
})();
</script>
<?php
}
