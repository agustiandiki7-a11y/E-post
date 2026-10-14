<?php
require_once __DIR__ . '/keamanan.php';
eposMulai();
// Halaman login tidak boleh disimpan cache browser (supaya tidak "nyangkut" setelah logout)
header('Cache-Control: no-store, no-cache, must-revalidate');
require_once __DIR__ . '/database/connection.php';

// Samakan penulisan role: 'manajer' dan 'manager' dianggap sama -> 'manajer'
function rolePakai($role)
{
    $r = strtolower(trim((string) $role));
    return $r === 'manager' ? 'manajer' : $r;
}

// Cari halaman tujuan sesuai role.
// Folder dicek benar-benar ada, jadi login tidak pernah diarahkan ke halaman 404.
// Nama folder yang dipakai sama dengan nama role: owner (untuk admin), kasir, manajer.
function halamanRole($role)
{
    $kandidat = [
        'admin'   => ['owner', 'admin'],
        'kasir'   => ['kasir'],
        'manajer' => ['manajer', 'manager'],
    ];
    $role = rolePakai($role);
    if (!isset($kandidat[$role])) {
        return null;
    }
    foreach ($kandidat[$role] as $folder) {
        if (is_file(__DIR__ . '/../' . $folder . '/index.php')) {
            return '../' . rawurlencode($folder) . '/index.php';
        }
    }
    return null;
}

// Sudah login: langsung ke halaman sesuai role.
// Role di sesi dirapikan dulu supaya cocok dengan penjaga halaman di tiap folder.
// Kalau halaman tujuan tidak ada, sesi dibuang supaya form login tetap bisa dipakai.
if (isset($_SESSION['user'])) {
    $_SESSION['user']['role'] = rolePakai($_SESSION['user']['role']);
    $tujuan = halamanRole($_SESSION['user']['role']);
    if ($tujuan !== null) {
        header('Location: ' . $tujuan);
        exit;
    }
    $_SESSION = [];
}

// Token CSRF untuk form login
if (empty($_SESSION['csrf_login'])) {
    $_SESSION['csrf_login'] = bin2hex(random_bytes(16));
}

$pesan = '';
if (isset($_SESSION['pesan_login'])) {
    $pesan = $_SESSION['pesan_login'];
    unset($_SESSION['pesan_login']);
}
$detail = '';
$dbBermasalah = false;
$emailIsi = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailIsi = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? (string) $_POST['password'] : '';
    $tokenOk = isset($_POST['csrf']) && is_string($_POST['csrf']) && hash_equals($_SESSION['csrf_login'], $_POST['csrf']);
    $emailIsi = mb_substr($emailIsi, 0, 150);
    $sisaKunci = eposThrottleSisa($emailIsi);

    if (!$tokenOk) {
        $pesan = 'Sesi form kedaluwarsa. Silakan coba masuk lagi.';
    } elseif ($sisaKunci > 0) {
        $pesan = 'Terlalu banyak percobaan. Coba lagi dalam ' . $sisaKunci . ' detik.';
    } else try {
        $db = new Database();
        $stmt = $db->conn->prepare("SELECT id, nama, email, password, role, branch_id FROM users WHERE email = ?");
        $stmt->bind_param('s', $emailIsi);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $user['role'] = rolePakai($user['role']);
            $tujuan = halamanRole($user['role']);

            if ($tujuan === null) {
                $pesan = 'Halaman untuk role "' . $user['role'] . '" tidak ditemukan. Pastikan folder owner, kasir, dan manajer ada.';
            } else {
                // Hash lama (misalnya SHA-512) diganti ke hash standar PHP secara otomatis
                if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
                    try {
                        $baru = password_hash($password, PASSWORD_DEFAULT);
                        $up = $db->conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                        $up->bind_param('si', $baru, $user['id']);
                        $up->execute();
                    } catch (mysqli_sql_exception $ex) {
                        // gagal memperbarui hash tidak boleh menggagalkan login
                    }
                }
                session_regenerate_id(true);
                eposThrottleReset($emailIsi);
                unset($user['password']);
                $_SESSION['user'] = $user;
                header('Location: ' . $tujuan);
                exit;
            }
        } else {
            $sisa = eposThrottleGagal($emailIsi);
            $pesan = $sisa > 0
                ? 'Terlalu banyak percobaan. Coba lagi dalam ' . $sisa . ' detik.'
                : 'Email atau password salah.';
        }
    } catch (mysqli_sql_exception $ex) {
        // database belum siap: tabel belum ada, MySQL belum jalan, atau nama database salah
        $dbBermasalah = true;
        $pesan = 'Database belum siap, jadi login belum bisa diproses.';
        $detail = '';  // detail teknis sengaja tidak ditampilkan ke pengguna
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk - Nexora POS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500;600&display=swap">
    <style>
        :root { --indigo: #4942dd; --indigo-d: #3a33c4; --ink: #14142b; --muted: #6a6c86; --line: #e4e6f2; --soft: #f5f6fc; --danger: #b42318; }
        * { box-sizing: border-box; }
        html, body { margin: 0; min-height: 100%; }
        body { font-family: Inter, "Segoe UI", system-ui, sans-serif; color: var(--ink); background: #fff; -webkit-font-smoothing: antialiased; }
        .serif { font-family: "Instrument Serif", Georgia, "Times New Roman", serif; font-weight: 400; }
        .mono { font-family: "IBM Plex Mono", ui-monospace, Consolas, monospace; font-variant-numeric: tabular-nums; }

        .page { min-height: 100vh; display: grid; grid-template-columns: minmax(380px, 44%) 1fr; }

        /* kolom form */
        .side { display: flex; flex-direction: column; padding: 36px clamp(28px, 6vw, 84px); }
        .brand { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 17px; letter-spacing: -.01em; }
        .brand img { width: 30px; height: 30px; }
        .formwrap { flex: 1; display: flex; flex-direction: column; justify-content: center; max-width: 392px; width: 100%; margin: 0; padding: 40px 0; }
        h1 { font-size: 46px; line-height: 1.04; margin: 0 0 12px; letter-spacing: -.01em; }
        .hint { margin: 0 0 30px; color: var(--muted); font-size: 15px; line-height: 1.55; }
        .note { border-radius: 10px; padding: 12px 14px; font-size: 14px; margin-bottom: 20px; line-height: 1.45; background: #fdf1ef; color: var(--danger); border: 1px solid #f6d3ce; }
        .note a { color: inherit; font-weight: 700; }
        .field { margin-bottom: 18px; position: relative; }
        label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 7px; }
        input[type=email], input[type=password], input[type=text] { width: 100%; height: 52px; border: 1px solid var(--line); border-radius: 12px; padding: 0 16px; font: 500 15px Inter, system-ui, sans-serif; color: var(--ink); background: #fff; outline: none; transition: border-color .15s, box-shadow .15s; box-shadow: 0 1px 2px rgba(20,20,43,.04); }
        input::placeholder { color: #a9abc2; }
        input:hover { border-color: #cfd2e8; }
        input:focus { border-color: var(--indigo); box-shadow: 0 0 0 4px rgba(73,66,221,.12); }
        .eye { position: absolute; right: 6px; bottom: 6px; width: 40px; height: 40px; border: 0; border-radius: 9px; background: transparent; color: var(--muted); cursor: pointer; display: inline-flex; align-items: center; justify-content: center; }
        .eye:hover { background: var(--soft); color: var(--indigo); }
        .eye:focus-visible, .btn:focus-visible { outline: 3px solid rgba(73,66,221,.4); outline-offset: 2px; }
        .btn { width: 100%; height: 54px; margin-top: 6px; border: 0; border-radius: 12px; background: linear-gradient(180deg, #5750ea, var(--indigo-d)); color: #fff; font: 600 16px Inter, system-ui, sans-serif; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; box-shadow: 0 8px 20px -6px rgba(73,66,221,.55), inset 0 1px 0 rgba(255,255,255,.22); transition: transform .1s, box-shadow .15s; }
        .btn:hover { box-shadow: 0 12px 26px -6px rgba(73,66,221,.6), inset 0 1px 0 rgba(255,255,255,.22); }
        .btn:active { transform: translateY(1px); }
        .btn:disabled { opacity: .7; cursor: wait; }
        .roles { margin-top: 28px; padding-top: 22px; border-top: 1px solid var(--line); display: flex; align-items: center; gap: 14px; color: var(--muted); font-size: 13px; line-height: 1.5; }
        .roles .av { display: flex; flex-shrink: 0; }
        .roles .av b { width: 30px; height: 30px; border-radius: 50%; border: 2px solid #fff; margin-left: -8px; display: inline-flex; align-items: center; justify-content: center; color: #fff; font-size: 11px; font-weight: 700; }
        .roles .av b:first-child { margin-left: 0; }
        .foot { font-size: 12px; color: #9a9cb4; }

        /* panel kanan */
        .showcase { position: relative; margin: 16px 16px 16px 0; border-radius: 28px; overflow: hidden; background: radial-gradient(900px 520px at 85% 0%, #e7e8ff 0%, rgba(231,232,255,0) 70%), linear-gradient(160deg, #f8f8fe 0%, #eceefb 100%); border: 1px solid #e8eaf7; padding: clamp(32px, 5vw, 72px); display: flex; flex-direction: column; }
        .showcase::before { content: ""; position: absolute; inset: 0; background-image: linear-gradient(rgba(73,66,221,.06) 1px, transparent 1px), linear-gradient(90deg, rgba(73,66,221,.06) 1px, transparent 1px); background-size: 44px 44px; -webkit-mask-image: linear-gradient(180deg, #000 0%, transparent 75%); mask-image: linear-gradient(180deg, #000 0%, transparent 75%); pointer-events: none; }
        .showcase > * { position: relative; }
        .kicker { font-size: 13px; color: var(--muted); display: inline-flex; align-items: center; gap: 8px; }
        .kicker i { width: 8px; height: 8px; border-radius: 50%; background: #22c55e; box-shadow: 0 0 0 4px rgba(34,197,94,.18); }
        .showcase h2 { font-size: clamp(40px, 4.6vw, 68px); line-height: 1; letter-spacing: -.015em; margin: 14px 0 0; }
        .showcase h2 em { color: var(--indigo); }
        .stack { position: relative; margin-top: auto; height: 400px; }
        .card { position: absolute; background: #fff; border-radius: 18px; border: 1px solid #e6e8f6; box-shadow: 0 24px 50px -22px rgba(40,36,140,.28), 0 2px 6px rgba(20,20,43,.04); padding: 20px 22px; }
        .c1 { left: 0; bottom: 120px; width: min(430px, 86%); animation: naik .8s .15s cubic-bezier(.2,.8,.2,1) both; }
        .c2 { right: 0; bottom: 0; width: min(330px, 70%); animation: naik .8s .3s cubic-bezier(.2,.8,.2,1) both; }
        .c3 { left: 18px; bottom: 0; padding: 12px 16px; border-radius: 14px; display: flex; align-items: center; gap: 12px; animation: naik .8s .45s cubic-bezier(.2,.8,.2,1) both; }
        @keyframes naik { from { opacity: 0; transform: translateY(22px); } to { opacity: 1; transform: none; } }
        .lbl { font-size: 12px; color: var(--muted); }
        .big { font-size: 30px; font-weight: 600; letter-spacing: -.02em; margin: 4px 0 2px; }
        .delta { font-size: 12px; font-weight: 600; color: #15803d; background: #e7f7ee; border-radius: 999px; padding: 2px 8px; margin-left: 8px; font-family: Inter, sans-serif; letter-spacing: 0; }
        .c1 svg { display: block; width: 100%; height: 86px; margin-top: 10px; }
        .brow { margin-top: 14px; }
        .brow div { display: flex; justify-content: space-between; font-size: 13px; font-weight: 500; margin-bottom: 6px; }
        .bar { height: 6px; border-radius: 999px; background: #eceefb; overflow: hidden; }
        .bar span { display: block; height: 100%; border-radius: 999px; background: var(--indigo); }
        .chip-ok { width: 32px; height: 32px; border-radius: 10px; background: #e7f7ee; color: #15803d; display: inline-flex; align-items: center; justify-content: center; }
        .c3 b { font-size: 13px; display: block; } .c3 small { color: var(--muted); font-size: 12px; }
        .sample { position: absolute; right: 28px; top: 24px; font-size: 12px; color: #8a8ca6; }

        .toast-box { position: fixed; top: 20px; right: 20px; z-index: 3000; background: #fff; border-left: 5px solid #16a34a; box-shadow: 0 10px 30px rgba(0,0,0,.18); border-radius: 10px; padding: 13px 16px; font-size: 14px; display: flex; gap: 10px; align-items: center; animation: tIn .25s ease-out; max-width: calc(100vw - 40px); }
        .toast-box.hide { opacity: 0; transform: translateX(20px); transition: .3s; }
        @keyframes tIn { from { opacity: 0; transform: translateX(24px); } to { opacity: 1; transform: none; } }
        @media (prefers-reduced-motion: reduce) { .card, .toast-box { animation: none; } .btn { transition: none; } }
        @media (max-width: 980px) { .page { grid-template-columns: 1fr; } .showcase { display: none; } h1 { font-size: 40px; } }
        @media (max-height: 760px) and (min-width: 981px) { .stack { height: 340px; } .c1 { bottom: 100px; } }
    </style>
</head>
<body>
<?php if (isset($_GET['logout'])) { ?>
    <div class="toast-box" id="toastLogout" role="status">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5L20 7"/></svg>
        <span>Kamu berhasil logout. Sampai jumpa lagi!</span>
    </div>
<?php } ?>
<div class="page">
    <section class="side">
        <div class="brand"><img src="../owner/template/src/assets/images/logo-icon.svg" alt="">Nexora POS</div>

        <main class="formwrap">
            <h1 class="serif">Selamat datang kembali</h1>
            <p class="hint">Masuk untuk mengelola penjualan, stok, dan laporan cabang kamu.</p>

            <?php if ($pesan) { ?>
                <div class="note" role="alert">
                    <?= htmlspecialchars($pesan) ?>
                    <?php if ($dbBermasalah) { ?>
                        <div style="margin-top:6px">Buka <a href="../install.php">install.php</a> untuk memperbaikinya otomatis.</div>
                    <?php } ?>
                </div>
            <?php } ?>

            <form method="post" id="formLogin" autocomplete="on">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf_login']) ?>">
                <div class="field">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" placeholder="nama@email.com" value="<?= htmlspecialchars($emailIsi) ?>" required autofocus>
                </div>
                <div class="field">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" placeholder="Masukkan password" required style="padding-right:54px">
                    <button type="button" class="eye" id="lihatPw" aria-label="Tampilkan password">
                        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
                <button type="submit" class="btn" id="btnMasuk">Masuk <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg></button>
            </form>

            <div class="roles">
                <span class="av" aria-hidden="true"><b style="background:#4942dd">O</b><b style="background:#2563eb">M</b><b style="background:#16a34a">K</b></span>
                <span>Satu halaman masuk untuk Owner, Manajer Cabang, dan Kasir.</span>
            </div>
        </main>

        <div class="foot">&copy; <?= date('Y') ?> Nexora POS</div>
    </section>

    <aside class="showcase" aria-hidden="true">
        <div class="kicker"><i></i> Sistem kasir multi-cabang</div>
        <h2 class="serif">Semua cabang,<br><em>satu layar.</em></h2>
        <span class="sample">contoh tampilan</span>

        <div class="stack">
            <div class="card c1">
                <div class="lbl">Penjualan hari ini</div>
                <div class="big mono">Rp 4.280.000 <span class="delta">+12%</span></div>
                <svg viewBox="0 0 400 86" preserveAspectRatio="none">
                    <defs><linearGradient id="g" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#4942dd" stop-opacity=".22"/><stop offset="1" stop-color="#4942dd" stop-opacity="0"/></linearGradient></defs>
                    <path d="M0 70 C40 62 60 44 100 48 S160 66 200 40 S270 18 310 28 S370 10 400 14 V86 H0Z" fill="url(#g)"/>
                    <path d="M0 70 C40 62 60 44 100 48 S160 66 200 40 S270 18 310 28 S370 10 400 14" fill="none" stroke="#4942dd" stroke-width="2.5" stroke-linecap="round"/>
                    <circle cx="400" cy="14" r="4.5" fill="#4942dd"/>
                </svg>
            </div>
            <div class="card c2">
                <div class="lbl">Penjualan per cabang</div>
                <div class="brow"><div><span>Semarang</span><span class="mono">3,8 jt</span></div><div class="bar"><span style="width:92%"></span></div></div>
                <div class="brow"><div><span>Ungaran</span><span class="mono">3,6 jt</span></div><div class="bar"><span style="width:86%"></span></div></div>
                <div class="brow"><div><span>Salatiga</span><span class="mono">1,6 jt</span></div><div class="bar"><span style="width:40%"></span></div></div>
            </div>
            <div class="card c3">
                <span class="chip-ok"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5L20 7"/></svg></span>
                <span><b>Void disetujui</b><small>INV-20261012-0007</small></span>
            </div>
        </div>
    </aside>
</div>
<script>
(function () {
    var tl = document.getElementById('toastLogout');
    if (tl) {
        setTimeout(function () { tl.classList.add('hide'); setTimeout(function () { tl.remove(); }, 350); }, 4000);
        if (window.history && history.replaceState) { history.replaceState(null, '', location.pathname); }  // refresh tidak memunculkan toast lagi
    }
    var pw = document.getElementById('password');
    document.getElementById('lihatPw').addEventListener('click', function () {
        pw.type = pw.type === 'password' ? 'text' : 'password';
    });
    document.getElementById('formLogin').addEventListener('submit', function () {
        var b = document.getElementById('btnMasuk');
        b.disabled = true; b.textContent = 'Memproses...';
    });
})();
</script>
</body>
</html>
