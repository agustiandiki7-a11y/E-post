<?php
// Logout aman: hanya lewat POST dengan token CSRF (supaya situs lain tidak bisa memaksa logout).
// Membuka alamat ini langsung (GET) menampilkan halaman konfirmasi kecil.
require_once __DIR__ . '/keamanan.php';
eposMulai();
header('Cache-Control: no-store, no-cache, must-revalidate');

if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && eposCsrfOk(isset($_POST['csrf']) ? $_POST['csrf'] : '')) {
    // kosongkan data sesi
    $_SESSION = [];
    // hapus juga cookie sesinya di browser, supaya login berikutnya mulai dari nol
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
    header('Location: index.php?logout=1');
    exit;
}

$nama = htmlspecialchars((string) $_SESSION['user']['nama'], ENT_QUOTES, 'UTF-8');
$token = htmlspecialchars(eposCsrfToken(), ENT_QUOTES, 'UTF-8');
$balik = 'javascript:history.back()';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Keluar - Nexora POS</title>
    <style>
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; background: #f5f6fc; font-family: Inter, "Segoe UI", system-ui, sans-serif; color: #14142b; padding: 16px; }
        .k { background: #fff; border-radius: 18px; padding: 32px 28px; max-width: 380px; width: 100%; text-align: center; box-shadow: 0 20px 50px -20px rgba(40,36,140,.3); }
        h1 { font-size: 20px; margin: 0 0 8px; } p { color: #6a6c86; margin: 0 0 22px; font-size: 14px; line-height: 1.5; }
        .a { display: flex; gap: 10px; } .a > * { flex: 1; height: 46px; border-radius: 10px; font: 600 15px inherit; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; }
        .a a { border: 1px solid #dcdff0; color: #14142b; background: #fff; } .a button { border: 0; background: #4942dd; color: #fff; }
    </style>
</head>
<body>
    <div class="k">
        <h1>Keluar dari akun?</h1>
        <p>Kamu masuk sebagai <strong><?= $nama ?></strong>. Pastikan pekerjaanmu sudah tersimpan.</p>
        <form method="post" class="a">
            <input type="hidden" name="csrf" value="<?= $token ?>">
            <a href="<?= $balik ?>">Batal</a>
            <button type="submit">Ya, keluar</button>
        </form>
    </div>
</body>
</html>
