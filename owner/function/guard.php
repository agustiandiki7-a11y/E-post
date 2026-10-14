<?php
// Penjaga halaman Owner/Admin + fungsi bantu kecil
require_once __DIR__ . '/../../login/keamanan.php';
eposMulai();   // sesi aman, header keamanan, penanganan error

// Jangan simpan halaman di cache browser: setelah logout, tombol Back tidak menampilkan isi lama
header('Cache-Control: no-store, no-cache, must-revalidate');

// Samakan penulisan role: 'manager' dianggap 'manajer'
function rolePakai($role)
{
    $r = strtolower(trim((string) $role));
    return $r === 'manager' ? 'manajer' : $r;
}

// Halaman "akses ditolak". Sengaja BUKAN redirect ke login, supaya tidak bisa terjadi
// putaran login -> dashboard -> login yang tidak berujung.
function tolakAkses($peran)
{
    http_response_code(403);
    echo '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">'
        . '<title>Akses ditolak</title></head>'
        . '<body style="font-family:Segoe UI,Arial,sans-serif;background:#f5f5f5;display:grid;place-items:center;min-height:100vh;margin:0">'
        . '<div style="background:#fff;border:1px solid #e5e5e5;border-radius:12px;padding:32px;max-width:420px;text-align:center">'
        . '<h2 style="margin:0 0 8px">Akses ditolak</h2>'
        . '<p style="color:#525252">Halaman ini khusus ' . htmlspecialchars($peran) . '. Akunmu tidak punya izin untuk membukanya.</p>'
        . '<a href="../login/index.php" style="display:inline-block;background:#4942dd;color:#fff;padding:10px 18px;border-radius:8px;text-decoration:none;margin-right:6px">Ke halaman saya</a>'
        . '<a href="../login/logout.php" style="display:inline-block;border:1px solid #d4d4d4;color:#171717;padding:10px 18px;border-radius:8px;text-decoration:none">Logout</a>'
        . '</div></body></html>';
    exit;
}

if (!isset($_SESSION['user'])) {
    header('Location: ../login/index.php');
    exit;
}
if (rolePakai($_SESSION['user']['role']) !== 'admin') {
    tolakAkses('Admin / Owner');
}

// Semua POST wajib bertoken CSRF; token disisipkan otomatis ke setiap form
eposCsrfOtomatis();

function e($v)
{
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}

function rupiah($n)
{
    return 'Rp ' . number_format((float) $n, 0, ',', '.');
}

function redirect($page, $tipe = '', $pesan = '')
{
    if ($pesan !== '') {
        toastFlash($tipe, $pesan);
    }
    $page = preg_replace('/[^a-z0-9_]/i', '', (string) $page);
    echo "<script>window.location.href='index.php?page=" . $page . "'</script>";
    exit;
}

function badgeStatus($s)
{
    $map = [
        'selesai'       => ['bg-success', 'Selesai'],
        'menunggu_void' => ['bg-warning text-dark', 'Menunggu Void'],
        'void'          => ['bg-danger', 'Void'],
    ];
    $b = isset($map[$s]) ? $map[$s] : ['bg-secondary', $s];
    return '<span class="badge ' . $b[0] . '">' . $b[1] . '</span>';
}

function tglValid($t, $default)
{
    $d = DateTime::createFromFormat('Y-m-d', (string) $t);
    return ($d && $d->format('Y-m-d') === $t) ? $t : $default;
}
