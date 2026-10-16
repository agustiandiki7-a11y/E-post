<?php
// Penjaga halaman Manajer Cabang (fungsi bantu umum ada di login/bantu.php)
require_once __DIR__ . '/../../login/keamanan.php';
eposMulai();   // sesi aman, header keamanan, penanganan error

// Jangan simpan halaman di cache browser: setelah logout, tombol Back tidak menampilkan isi lama
header('Cache-Control: no-store, no-cache, must-revalidate');

if (!isset($_SESSION['user'])) {
    header('Location: ../login/index.php');
    exit;
}
if (rolePakai($_SESSION['user']['role']) !== 'manajer') {
    tolakAkses('Manajer Cabang');
}

// Cabang manajer diambil dari session, bukan dari input user
$BRANCH_ID = (int) $_SESSION['user']['branch_id'];

eposCsrfToken();   // pastikan token anti-CSRF sesi ada (dipakai form lewat eposCsrfOk)

function redirect($page)
{
    header('Location: index.php?page=' . $page);
    exit;
}
