<?php
// Pintu masuk Nexora POS (http://localhost/E-post/ atau http://e-post.test/).
// Tidak menampilkan isi apa pun: selalu diteruskan ke login/index.php.
//  - belum login            -> halaman login tampil
//  - sudah login (valid)    -> login/index.php meneruskan ke dashboard sesuai role
// Alamat dihitung dari lokasi file ini, jadi tetap benar kalau nama folder project berubah.
$dasar = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Location: ' . $dasar . '/login/index.php');
exit;
