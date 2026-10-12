<?php
session_start();

// kosongkan data sesi
$_SESSION = [];

// hapus juga cookie sesinya di browser, supaya login berikutnya mulai dari nol
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}

session_destroy();
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Location: index.php?logout=1');
exit;
