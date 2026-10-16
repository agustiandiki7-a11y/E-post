<?php
// Penjaga halaman Owner/Admin (fungsi bantu umum ada di login/bantu.php)
require_once __DIR__ . '/../../login/keamanan.php';
eposMulai();   // sesi aman, header keamanan, penanganan error

// Jangan simpan halaman di cache browser: setelah logout, tombol Back tidak menampilkan isi lama
header('Cache-Control: no-store, no-cache, must-revalidate');

if (!isset($_SESSION['user'])) {
    header('Location: ../login/index.php');
    exit;
}
if (rolePakai($_SESSION['user']['role']) !== 'admin') {
    tolakAkses('Admin / Owner');
}

// Semua POST wajib bertoken CSRF; token disisipkan otomatis ke setiap form
eposCsrfOtomatis();

function redirect($page, $tipe = '', $pesan = '')
{
    if ($pesan !== '') {
        toastFlash($tipe, $pesan);
    }
    $page = preg_replace('/[^a-z0-9_]/i', '', (string) $page);
    echo "<script>window.location.href='index.php?page=" . $page . "'</script>";
    exit;
}

