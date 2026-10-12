<?php
// Kotak konfirmasi logout (dipakai owner, manajer, kasir). Dipasang dari partials/script.php.
$__nama = isset($_SESSION['user']['nama']) ? $_SESSION['user']['nama'] : '';
?>
<style>
    #logoutBackdrop { position: fixed; inset: 0; background: rgba(15,23,42,.5); display: none; align-items: center; justify-content: center; z-index: 2000; padding: 16px; }
    #logoutBackdrop.show { display: flex; }
    #logoutBox { background: #fff; border-radius: 14px; width: 100%; max-width: 380px; padding: 26px 24px 20px; box-shadow: 0 20px 50px rgba(0,0,0,.25); text-align: center; animation: lgIn .15s ease-out; }
    @keyframes lgIn { from { opacity: 0; transform: translateY(8px) scale(.97); } to { opacity: 1; transform: none; } }
    #logoutBox .lg-ic { width: 54px; height: 54px; border-radius: 50%; background: #fee2e2; color: #dc2626; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 12px; }
    #logoutBox h3 { margin: 0 0 6px; font-size: 18px; }
    #logoutBox p { margin: 0 0 20px; color: #64748b; font-size: 14px; }
    #logoutBox .lg-act { display: flex; gap: 10px; }
    #logoutBox .lg-act button, #logoutBox .lg-act a { flex: 1; padding: 10px 14px; border-radius: 8px; font-size: 14px; cursor: pointer; text-decoration: none; text-align: center; border: 1px solid #d4d4d4; background: #fff; color: #111; }
    #logoutBox .lg-act a { background: #dc2626; border-color: #dc2626; color: #fff; }
</style>
<div id="logoutBackdrop" role="dialog" aria-modal="true" aria-labelledby="logoutJudul">
    <div id="logoutBox">
        <div class="lg-ic">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 8V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2-2v-2"/><path d="M9 12h12l-3-3m0 6 3-3"/></svg>
        </div>
        <h3 id="logoutJudul">Keluar dari akun?</h3>
        <p>
            <?php if ($__nama !== '') { ?>Kamu login sebagai <strong><?= htmlspecialchars($__nama, ENT_QUOTES, 'UTF-8') ?></strong>. <?php } ?>
            Pastikan pekerjaanmu sudah tersimpan.
        </p>
        <div class="lg-act">
            <button type="button" id="logoutBatal">Batal</button>
            <a href="../login/logout.php" id="logoutYa">Ya, Logout</a>
        </div>
    </div>
</div>
<script>
(function () {
    var bd = document.getElementById('logoutBackdrop');
    function buka(ev) { if (ev) { ev.preventDefault(); } bd.classList.add('show'); document.getElementById('logoutYa').focus(); }
    function tutup() { bd.classList.remove('show'); }
    document.addEventListener('click', function (ev) {
        var a = ev.target.closest('a[href$="logout.php"]');
        if (a && a.id !== 'logoutYa') { buka(ev); }
    });
    document.getElementById('logoutBatal').addEventListener('click', tutup);
    bd.addEventListener('click', function (ev) { if (ev.target === bd) { tutup(); } });
    document.addEventListener('keydown', function (ev) { if (ev.key === 'Escape') { tutup(); } });
})();
</script>
