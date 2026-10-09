<?php
// Penjaga halaman Manajer Cabang + fungsi bantu kecil
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'manajer') {
    header('Location: ../login/index.php');
    exit;
}

// Cabang manajer diambil dari session, bukan dari input user
$BRANCH_ID = (int) $_SESSION['user']['branch_id'];

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
    header('Location: index.php?page=' . $page);
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
