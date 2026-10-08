<?php
// Penjaga halaman Owner/Admin + fungsi bantu kecil
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../login/index.php');
    exit;
}

function e($v)
{
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}

function rupiah($n)
{
    return 'Rp ' . number_format((float) $n, 0, ',', '.');
}

function redirect($page)
{
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
