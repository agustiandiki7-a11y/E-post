<?php
// ============================================================
// Pengaturan keamanan bersama (dipakai login, owner, manajer, kasir)
//  - sesi aman (cookie HttpOnly, SameSite, Secure bila HTTPS, mode ketat, batas tidak aktif)
//  - header keamanan HTTP
//  - error tidak ditampilkan ke pengguna (detail masuk ke log PHP)
//  - perlindungan CSRF otomatis untuk form owner
//  - pembatasan percobaan login (per IP, tidak bisa dilewati dengan hapus cookie)
// ============================================================

// Ubah ke true HANYA saat development kalau ingin melihat pesan error PHP di layar.
if (!defined('EPOS_DEBUG')) {
    define('EPOS_DEBUG', false);
}
// Sesi berakhir otomatis kalau tidak ada aktivitas selama ini (detik)
if (!defined('EPOS_IDLE')) {
    define('EPOS_IDLE', 7200);
}

function eposHalamanError()
{
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=utf-8');
    }
    echo '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Terjadi kesalahan</title></head>'
        . '<body style="font-family:Segoe UI,Arial,sans-serif;background:#f5f5f5;display:grid;place-items:center;min-height:100vh;margin:0">'
        . '<div style="background:#fff;border-radius:12px;padding:32px;max-width:420px;text-align:center;box-shadow:0 2px 10px rgba(0,0,0,.08)">'
        . '<h2 style="margin:0 0 8px">Terjadi kesalahan</h2>'
        . '<p style="color:#525252;margin:0 0 18px">Permintaanmu belum bisa diproses. Coba lagi, dan hubungi admin kalau masalah berlanjut.</p>'
        . '<a href="javascript:history.back()" style="color:#4942dd">Kembali</a></div></body></html>';
}

function eposMulai()
{
    // --- penanganan error ---
    ini_set('display_errors', EPOS_DEBUG ? '1' : '0');
    ini_set('log_errors', '1');
    if (!EPOS_DEBUG) {
        set_exception_handler(function ($ex) {
            error_log('[Nexora POS] ' . get_class($ex) . ': ' . $ex->getMessage() . ' di ' . $ex->getFile() . ':' . $ex->getLine());
            eposHalamanError();
            exit;
        });
    }
    date_default_timezone_set('Asia/Jakarta');

    // --- sesi ---
    if (session_status() === PHP_SESSION_NONE) {
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax', 'secure' => $https]);
        session_start();
    }
    // batas tidak aktif
    if (isset($_SESSION['user'])) {
        if (isset($_SESSION['_aktif']) && (time() - (int) $_SESSION['_aktif']) > EPOS_IDLE) {
            unset($_SESSION['user'], $_SESSION['csrf']);
            $_SESSION['pesan_login'] = 'Sesi berakhir karena lama tidak aktif. Silakan login lagi.';
        } else {
            $_SESSION['_aktif'] = time();
        }
    }

    // --- header keamanan ---
    if (!headers_sent()) {
        header('X-Frame-Options: DENY');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: same-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com data:; img-src 'self' data: blob:; object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'");
        header_remove('X-Powered-By');
    }
}

// ---------------- CSRF ----------------
function eposCsrfToken()
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function eposCsrfOk($t)
{
    return is_string($t) && isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $t);
}

// Untuk halaman yang form-nya ditulis tanpa token (owner):
//  1) setiap POST wajib membawa token yang benar, kalau tidak -> 403
//  2) token otomatis disisipkan ke setiap <form method="post">
function eposCsrfOtomatis()
{
    $token = eposCsrfToken();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!eposCsrfOk(isset($_POST['csrf']) ? $_POST['csrf'] : '')) {
            http_response_code(403);
            echo '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Sesi kedaluwarsa</title></head>'
                . '<body style="font-family:Segoe UI,Arial,sans-serif;background:#f5f5f5;display:grid;place-items:center;min-height:100vh;margin:0">'
                . '<div style="background:#fff;border-radius:12px;padding:32px;max-width:420px;text-align:center">'
                . '<h2 style="margin:0 0 8px">Sesi form kedaluwarsa</h2>'
                . '<p style="color:#525252">Permintaan ditolak demi keamanan. Muat ulang halaman lalu coba lagi.</p>'
                . '<a href="javascript:history.back()" style="color:#4942dd">Kembali</a></div></body></html>';
            exit;
        }
    }
    ob_start(function ($html) use ($token) {
        return preg_replace(
            '~(<form\b[^>]*\bmethod\s*=\s*["\']?post["\']?[^>]*>)~i',
            '$1<input type="hidden" name="csrf" value="' . $token . '">',
            $html
        );
    });
}

// ---------------- Pembatasan login ----------------
function eposIp()
{
    return isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : '0';
}

function eposThrottleFile($kunci)
{
    return sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'nexora_lg_' . hash('sha256', $kunci) . '.json';
}

function eposThrottleBaca($kunci)
{
    $f = eposThrottleFile($kunci);
    $d = is_file($f) ? json_decode((string) @file_get_contents($f), true) : null;
    return is_array($d) ? $d : ['n' => 0, 'mulai' => 0, 'kunci' => 0];
}

function eposThrottleTulis($kunci, $d)
{
    @file_put_contents(eposThrottleFile($kunci), json_encode($d), LOCK_EX);
}

// sisa detik terkunci (0 = boleh mencoba)
function eposThrottleSisa($email)
{
    $sisa = 0;
    foreach ([eposIp() . '|' . strtolower($email), eposIp()] as $k) {
        $d = eposThrottleBaca($k);
        $sisa = max($sisa, (int) $d['kunci'] - time());
    }
    return max(0, $sisa);
}

// catat satu kegagalan; kembalikan sisa detik kunci (0 = belum terkunci)
function eposThrottleGagal($email)
{
    $aturan = [
        [eposIp() . '|' . strtolower($email), 5, 60],   // 5 salah untuk email yang sama -> kunci 60 dtk
        [eposIp(), 20, 300],                            // 20 salah dari IP yang sama -> kunci 5 menit
    ];
    $sisa = 0;
    foreach ($aturan as $a) {
        $d = eposThrottleBaca($a[0]);
        if (time() - (int) $d['mulai'] > 900) {          // jendela 15 menit
            $d = ['n' => 0, 'mulai' => time(), 'kunci' => 0];
        }
        $d['n']++;
        if ($d['n'] >= $a[1]) {
            $d['kunci'] = time() + $a[2];
            $d['n'] = 0;
            $d['mulai'] = time();
        }
        eposThrottleTulis($a[0], $d);
        $sisa = max($sisa, (int) $d['kunci'] - time());
    }
    return max(0, $sisa);
}

function eposThrottleReset($email)
{
    @unlink(eposThrottleFile(eposIp() . '|' . strtolower($email)));
}
require_once __DIR__ . '/toast.php';
require_once __DIR__ . '/bantu.php';

require_once __DIR__ . '/fitur.php';
