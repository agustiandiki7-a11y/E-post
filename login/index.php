<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
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

$pesan = '';
$detail = '';
$dbBermasalah = false;
$emailIsi = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailIsi = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? (string) $_POST['password'] : '';

    try {
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
                unset($user['password']);
                $_SESSION['user'] = $user;
                header('Location: ' . $tujuan);
                exit;
            }
        } else {
            $pesan = 'Email atau password salah.';
        }
    } catch (mysqli_sql_exception $ex) {
        // database belum siap: tabel belum ada, MySQL belum jalan, atau nama database salah
        $dbBermasalah = true;
        $pesan = 'Database belum siap, jadi login belum bisa diproses.';
        $detail = $ex->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - E-POS</title>
    <link rel="stylesheet" href="../owner/template/src/assets/css/main.css">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #fff; }
        .lg { display: flex; min-height: 100vh; }
        .lg-brand { flex: 1 1 52%; background: linear-gradient(145deg, #e66239 0%, #c2410c 100%); color: #fff; padding: 56px; display: flex; flex-direction: column; justify-content: space-between; position: relative; overflow: hidden; }
        .lg-brand::before, .lg-brand::after { content: ""; position: absolute; border-radius: 50%; background: rgba(255,255,255,.08); }
        .lg-brand::before { width: 420px; height: 420px; right: -140px; top: -120px; }
        .lg-brand::after { width: 300px; height: 300px; left: -100px; bottom: -110px; }
        .lg-brand > * { position: relative; z-index: 1; }
        .lg-logo { display: flex; align-items: center; gap: 10px; font-size: 22px; font-weight: 700; letter-spacing: .5px; }
        .lg-logo span.box { width: 40px; height: 40px; border-radius: 10px; background: rgba(255,255,255,.2); display: inline-flex; align-items: center; justify-content: center; }
        .lg-brand h2 { font-size: 34px; line-height: 1.2; margin: 0 0 14px; color: #fff; }
        .lg-brand p.sub { margin: 0 0 28px; opacity: .9; max-width: 440px; }
        .lg-fitur { list-style: none; padding: 0; margin: 0; display: grid; gap: 12px; }
        .lg-fitur li { display: flex; align-items: center; gap: 12px; }
        .lg-fitur .dot { width: 28px; height: 28px; border-radius: 8px; background: rgba(255,255,255,.18); display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .lg-foot { font-size: 13px; opacity: .8; }
        .lg-form { flex: 1 1 48%; display: flex; align-items: center; justify-content: center; padding: 32px 24px; background: #fafafa; }
        .lg-card { width: 100%; max-width: 400px; }
        .lg-card h1 { font-size: 26px; margin: 0 0 4px; }
        .lg-card .hint { color: #737373; margin: 0 0 24px; }
        .lg-field { margin-bottom: 16px; }
        .lg-field label { display: block; font-size: 14px; font-weight: 500; margin-bottom: 6px; }
        .lg-input { position: relative; }
        .lg-input svg.ic { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: #a3a3a3; pointer-events: none; }
        .lg-input input { width: 100%; height: 46px; border: 1px solid #d4d4d4; border-radius: 10px; padding: 0 44px 0 42px; font-size: 15px; background: #fff; outline: none; transition: .15s; }
        .lg-input input:focus { border-color: #e66239; box-shadow: 0 0 0 3px rgba(230,98,57,.15); }
        .lg-eye { position: absolute; right: 6px; top: 50%; transform: translateY(-50%); border: 0; background: transparent; width: 36px; height: 36px; color: #737373; cursor: pointer; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; }
        .lg-eye:hover { background: #f0f0f0; }
        .lg-btn { width: 100%; height: 48px; border: 0; border-radius: 10px; background: #e66239; color: #fff; font-size: 16px; font-weight: 600; cursor: pointer; transition: .15s; }
        .lg-btn:hover { background: #d4552d; }
        .lg-btn:disabled { opacity: .7; cursor: wait; }
        .lg-note { border-radius: 10px; padding: 11px 14px; font-size: 14px; margin-bottom: 16px; }
        .lg-note.err { background: #fee2e2; color: #991b1b; }
        .lg-note.ok { background: #dcfce7; color: #166534; }
        .lg-note a { color: inherit; font-weight: 600; }
        .lg-note small { display: block; opacity: .8; margin-top: 6px; word-break: break-word; }
        .lg-demo { margin-top: 26px; padding-top: 18px; border-top: 1px dashed #d4d4d4; }
        .lg-demo .t { font-size: 12px; color: #737373; margin-bottom: 8px; }
        .lg-chip { display: inline-flex; align-items: center; gap: 6px; border: 1px solid #e5e5e5; background: #fff; border-radius: 999px; padding: 6px 12px; font-size: 13px; cursor: pointer; margin: 0 6px 6px 0; }
        .lg-chip:hover { border-color: #e66239; color: #e66239; }
        @media (max-width: 900px) { .lg-brand { display: none; } .lg-form { background: #fff; } }
    </style>
</head>
<body>
<div class="lg">
    <div class="lg-brand">
        <div class="lg-logo">
            <span class="box"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l1-5h16l1 5"/><path d="M4 9v11h16V9"/><path d="M9 20v-6h6v6"/></svg></span>
            E-POS
        </div>
        <div>
            <h2>Kasir multi-cabang,<br>rapi dan real-time.</h2>
            <p class="sub">Catat penjualan, pantau stok, dan kendalikan laporan semua cabang dari satu tempat.</p>
            <ul class="lg-fitur">
                <li><span class="dot"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5L20 7"/></svg></span> Transaksi cepat dengan cetak struk</li>
                <li><span class="dot"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5L20 7"/></svg></span> Void transaksi dengan persetujuan manajer</li>
                <li><span class="dot"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5L20 7"/></svg></span> Laporan penjualan per cabang untuk owner</li>
            </ul>
        </div>
        <div class="lg-foot">&copy; <?= date('Y') ?> E-POS Multi-Cabang</div>
    </div>

    <div class="lg-form">
        <div class="lg-card">
            <h1>Selamat datang</h1>
            <p class="hint">Masuk dengan akun Owner, Manajer, atau Kasir.</p>

            <?php if (isset($_GET['logout']) && !$pesan) { ?>
                <div class="lg-note ok">Kamu sudah logout. Sampai jumpa lagi!</div>
            <?php } ?>
            <?php if ($pesan) { ?>
                <div class="lg-note err">
                    <?= htmlspecialchars($pesan) ?>
                    <?php if ($dbBermasalah) { ?>
                        <div style="margin-top:6px">Buka <a href="../install.php">install.php</a> untuk memperbaikinya otomatis.</div>
                        <small><?= htmlspecialchars($detail) ?></small>
                    <?php } ?>
                </div>
            <?php } ?>

            <form method="post" id="formLogin" autocomplete="on">
                <div class="lg-field">
                    <label for="email">Email</label>
                    <div class="lg-input">
                        <svg class="ic" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg>
                        <input type="email" name="email" id="email" placeholder="nama@email.com" value="<?= htmlspecialchars($emailIsi) ?>" required autofocus>
                    </div>
                </div>
                <div class="lg-field">
                    <label for="password">Password</label>
                    <div class="lg-input">
                        <svg class="ic" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
                        <input type="password" name="password" id="password" placeholder="Masukkan password" required>
                        <button type="button" class="lg-eye" id="lihatPw" aria-label="Tampilkan password">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>
                <button type="submit" class="lg-btn" id="btnMasuk">Masuk</button>
            </form>

            <div class="lg-demo">
                <div class="t">Akun contoh (klik untuk mengisi otomatis)</div>
                <button type="button" class="lg-chip" data-e="admin@epos.test" data-p="admin123">Owner</button>
                <button type="button" class="lg-chip" data-e="manajer1@epos.test" data-p="manajer123">Manajer</button>
                <button type="button" class="lg-chip" data-e="kasir1@epos.test" data-p="kasir123">Kasir</button>
            </div>
        </div>
    </div>
</div>
<script>
(function () {
    var pw = document.getElementById('password');
    document.getElementById('lihatPw').addEventListener('click', function () {
        pw.type = pw.type === 'password' ? 'text' : 'password';
    });
    var chips = document.querySelectorAll('.lg-chip');
    for (var i = 0; i < chips.length; i++) {
        chips[i].addEventListener('click', function () {
            document.getElementById('email').value = this.getAttribute('data-e');
            pw.value = this.getAttribute('data-p');
            pw.focus();
        });
    }
    document.getElementById('formLogin').addEventListener('submit', function () {
        var b = document.getElementById('btnMasuk');
        b.disabled = true; b.textContent = 'Memproses...';
    });
})();
</script>
</body>
</html>
