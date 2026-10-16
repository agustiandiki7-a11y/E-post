<?php
// Kotak konfirmasi logout (dipakai owner, manajer, kasir). Dipasang dari partials/script.php.
// Tombol "Ya, keluar" mengirim POST + token CSRF ke login/logout.php.
$__nama = isset($_SESSION['user']['nama']) ? $_SESSION['user']['nama'] : '';
$__role = isset($_SESSION['user']['role']) && function_exists('eposRoleLabel') ? eposRoleLabel($_SESSION['user']['role']) : '';
$__token = function_exists('eposCsrfToken') ? eposCsrfToken() : '';
?>
<style>
    #logoutBackdrop { position: fixed; inset: 0; background: rgba(20,20,43,.55); backdrop-filter: blur(3px); display: none; align-items: center; justify-content: center; z-index: 2000; padding: 16px; }
    #logoutBackdrop.show { display: flex; }
    #logoutBox { background: #fff; border-radius: 20px; width: 100%; max-width: 380px; padding: 28px 26px 22px; box-shadow: 0 30px 60px -20px rgba(20,20,43,.45); text-align: center; animation: lgIn .18s ease-out; font-family: Inter, "Segoe UI", system-ui, sans-serif; }
    @keyframes lgIn { from { opacity: 0; transform: translateY(10px) scale(.97); } to { opacity: 1; transform: none; } }
    @media (prefers-reduced-motion: reduce) { #logoutBox { animation: none; } }
    #logoutBox .lg-ic { width: 56px; height: 56px; border-radius: 50%; background: #ecebff; color: #4942dd; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 14px; }
    #logoutBox h3 { margin: 0 0 6px; font-size: 19px; font-weight: 700; color: #14142b; }
    #logoutBox p { margin: 0 0 6px; color: #6a6c86; font-size: 14px; line-height: 1.5; }
    #logoutBox .lg-who { display: inline-flex; align-items: center; gap: 8px; background: #f3f4fc; border-radius: 999px; padding: 5px 12px; font-size: 13px; font-weight: 600; color: #14142b; margin: 4px 0 18px; }
    #logoutBox .lg-who small { color: #6a6c86; font-weight: 500; }
    #logoutBox .lg-act { display: flex; gap: 10px; margin: 0; }
    #logoutBox .lg-act button { flex: 1; height: 46px; border-radius: 10px; font: 600 15px inherit; cursor: pointer; border: 1px solid #dcdff0; background: #fff; color: #14142b; }
    #logoutBox .lg-act button:hover { background: #f3f4fc; }
    #logoutBox .lg-act button.ya { background: #4942dd; border-color: #4942dd; color: #fff; }
    #logoutBox .lg-act button.ya:hover { background: #3a33c4; }
    #logoutBox button:focus-visible { outline: 3px solid rgba(73,66,221,.45); outline-offset: 2px; }
</style>
<div id="logoutBackdrop" role="dialog" aria-modal="true" aria-labelledby="logoutJudul">
    <div id="logoutBox">
        <div class="lg-ic">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 8V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2-2v-2"/><path d="M9 12h12l-3-3m0 6 3-3"/></svg>
        </div>
        <h3 id="logoutJudul">Keluar dari akun?</h3>
        <p>Pastikan pekerjaanmu sudah tersimpan.</p>
        <?php if ($__nama !== '') { ?>
            <div class="lg-who"><?= htmlspecialchars($__nama, ENT_QUOTES, 'UTF-8') ?><?php if ($__role !== '') { ?> <small><?= htmlspecialchars($__role, ENT_QUOTES, 'UTF-8') ?></small><?php } ?></div>
        <?php } ?>
        <form method="post" action="../login/logout.php" class="lg-act">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($__token, ENT_QUOTES, 'UTF-8') ?>">
            <button type="button" id="logoutBatal">Batal</button>
            <button type="submit" class="ya" id="logoutYa">Ya, keluar</button>
        </form>
    </div>
</div>
<script>
(function () {
    var bd = document.getElementById('logoutBackdrop');
    var terakhir = null;
    function buka(ev) { if (ev) { ev.preventDefault(); } terakhir = document.activeElement; bd.classList.add('show'); document.getElementById('logoutBatal').focus(); }
    function tutup() { bd.classList.remove('show'); if (terakhir && terakhir.focus) { terakhir.focus(); } }
    document.addEventListener('click', function (ev) {
        var a = ev.target.closest('a[href$="logout.php"]');
        if (a) { buka(ev); }
    });
    document.getElementById('logoutBatal').addEventListener('click', tutup);
    bd.addEventListener('click', function (ev) { if (ev.target === bd) { tutup(); } });
    document.addEventListener('keydown', function (ev) {
        if (!bd.classList.contains('show')) { return; }
        if (ev.key === 'Escape') { tutup(); }
        if (ev.key === 'Tab') {   // fokus tetap di dalam kotak
            var f = bd.querySelectorAll('button'), a = f[0], z = f[f.length - 1];
            if (ev.shiftKey && document.activeElement === a) { ev.preventDefault(); z.focus(); }
            else if (!ev.shiftKey && document.activeElement === z) { ev.preventDefault(); a.focus(); }
        }
    });
})();
</script>
