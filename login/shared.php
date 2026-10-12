<?php
// Pembantu tampilan yang dipakai bersama oleh owner, manajer, dan kasir.

// Nama peran untuk ditampilkan
function eposRoleLabel($role)
{
    $map = ['admin' => 'Owner', 'manajer' => 'Manajer', 'manager' => 'Manajer', 'kasir' => 'Kasir'];
    $r = strtolower(trim((string) $role));
    return isset($map[$r]) ? $map[$r] : ucfirst($r);
}

// Avatar bulat berisi inisial nama (warna mengikuti peran), supaya profil sesuai siapa yang login
function eposAvatar($nama, $role, $ukuran = 36)
{
    $warna = ['admin' => '#e66239', 'manajer' => '#2563eb', 'manager' => '#2563eb', 'kasir' => '#16a34a'];
    $r = strtolower(trim((string) $role));
    $bg = isset($warna[$r]) ? $warna[$r] : '#64748b';

    $kata = preg_split('/\s+/', trim((string) $nama), -1, PREG_SPLIT_NO_EMPTY);
    $ini = '';
    foreach (array_slice($kata, 0, 2) as $k) {
        $ini .= function_exists('mb_substr') ? mb_strtoupper(mb_substr($k, 0, 1, 'UTF-8'), 'UTF-8') : strtoupper(substr($k, 0, 1));
    }
    if ($ini === '') {
        $ini = '?';
    }
    $fs = (int) round($ukuran * 0.4);
    return '<span style="display:inline-flex;align-items:center;justify-content:center;width:' . (int) $ukuran . 'px;height:' . (int) $ukuran
        . 'px;border-radius:50%;background:' . $bg . ';color:#fff;font-weight:600;font-size:' . $fs . 'px;line-height:1;flex-shrink:0">'
        . htmlspecialchars($ini, ENT_QUOTES, 'UTF-8') . '</span>';
}
