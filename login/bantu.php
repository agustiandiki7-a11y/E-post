<?php
// Pembantu bersama: status stok, paginasi (10 per halaman), dan foto produk.

// ---------- STOK ----------
const EPOS_STOK_TIPIS = 10;

// 'habis' (0), 'menipis' (1-10), 'tersedia' (>10)
function stokStatus($stok)
{
    $stok = (int) $stok;
    if ($stok <= 0) {
        return 'habis';
    }
    return $stok <= EPOS_STOK_TIPIS ? 'menipis' : 'tersedia';
}

// Lencana stok: Habis (merah) / Menipis (kuning) / kosong kalau aman
function stokBadge($stok)
{
    $s = stokStatus($stok);
    if ($s === 'habis') {
        return '<span class="badge bg-danger ms-1">Habis</span>';
    }
    if ($s === 'menipis') {
        return '<span class="badge bg-warning text-dark ms-1">Menipis</span>';
    }
    return '';
}

// Nilai filter stok dari URL: semua / tersedia / menipis / habis
function stokFilter()
{
    $f = isset($_GET['stok']) ? (string) $_GET['stok'] : 'semua';
    return in_array($f, ['tersedia', 'menipis', 'habis'], true) ? $f : 'semua';
}

function stokCocok($stok, $filter)
{
    return $filter === 'semua' || stokStatus($stok) === $filter;
}

// Kotak pilihan filter stok untuk form pencarian
function stokSelect($filter)
{
    $opsi = ['semua' => 'Semua stok', 'tersedia' => 'Stok aman', 'menipis' => 'Menipis (1-10)', 'habis' => 'Habis'];
    $h = '<select name="stok" class="form-select" style="max-width: 190px;" aria-label="Filter stok">';
    foreach ($opsi as $v => $t) {
        $h .= '<option value="' . $v . '"' . ($filter === $v ? ' selected' : '') . '>' . $t . '</option>';
    }
    return $h . '</select>';
}

// ---------- PAGINASI ----------
const EPOS_PER_HAL = 10;

function halIni($kunci = 'hal')
{
    $h = isset($_GET[$kunci]) ? (int) $_GET[$kunci] : 1;
    return max(1, $h);
}

// Ambil satu halaman dari array hasil query. $hal dikoreksi kalau melewati halaman terakhir.
function halPotong($baris, &$hal, $per = EPOS_PER_HAL)
{
    $maks = max(1, (int) ceil(count($baris) / $per));
    $hal = min(max(1, (int) $hal), $maks);
    return array_slice($baris, ($hal - 1) * $per, $per);
}

// Nomor urut baris pertama di halaman ini (supaya kolom "No" berlanjut: 11, 12, ...)
function halMulai($hal, $per = EPOS_PER_HAL)
{
    return ($hal - 1) * $per + 1;
}

function halUrl($hal, $kunci = 'hal')
{
    $q = $_GET;
    unset($q[$kunci]);
    if ($hal > 1) {
        $q[$kunci] = $hal;
    }
    return '?' . http_build_query($q);
}

// Cetak ringkasan "Menampilkan 1-10 dari 101" + menu halaman (hanya jika lebih dari satu halaman)
function halNav($total, $hal, $per = EPOS_PER_HAL, $kunci = 'hal')
{
    $total = (int) $total;
    if ($total === 0) {
        return;
    }
    $maks = max(1, (int) ceil($total / $per));
    $dari = ($hal - 1) * $per + 1;
    $sampai = min($total, $hal * $per);
    echo '<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 px-3 py-3 border-top">';
    echo '<small class="text-secondary">Menampilkan ' . $dari . '-' . $sampai . ' dari ' . $total . '</small>';
    if ($maks > 1) {
        $nomor = [1, $maks];
        for ($i = $hal - 1; $i <= $hal + 1; $i++) {
            if ($i > 1 && $i < $maks) {
                $nomor[] = $i;
            }
        }
        $nomor = array_unique($nomor);
        sort($nomor);
        echo '<nav aria-label="Halaman"><ul class="pagination pagination-sm mb-0">';
        echo '<li class="page-item' . ($hal <= 1 ? ' disabled' : '') . '"><a class="page-link" href="' . htmlspecialchars(halUrl($hal - 1, $kunci)) . '">Sebelumnya</a></li>';
        $prev = 0;
        foreach ($nomor as $n) {
            if ($prev && $n - $prev > 1) {
                echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
            }
            echo '<li class="page-item' . ($n === $hal ? ' active' : '') . '"><a class="page-link" href="' . htmlspecialchars(halUrl($n, $kunci)) . '">' . $n . '</a></li>';
            $prev = $n;
        }
        echo '<li class="page-item' . ($hal >= $maks ? ' disabled' : '') . '"><a class="page-link" href="' . htmlspecialchars(halUrl($hal + 1, $kunci)) . '">Berikutnya</a></li>';
        echo '</ul></nav>';
    }
    echo '</div>';
}

// ---------- FOTO PRODUK ----------
// Foto disimpan sebagai path relatif dari folder utama (contoh: assets/produk/aqua.svg atau uploads/produk/ab12.jpg).
// Dari folder owner/manajer/kasir, alamatnya diawali "../".
function fotoAda($foto)
{
    if (!is_string($foto) || $foto === '' || !preg_match('#^(assets|uploads)/produk/[A-Za-z0-9._-]+$#', $foto)) {
        return false;
    }
    return is_file(__DIR__ . '/../' . $foto);
}

// Tag <img> foto produk; kalau belum ada foto, tampil kotak berisi huruf pertama nama produk
function fotoProduk($foto, $nama, $ukuran = 44, $kelas = '')
{
    $u = (int) $ukuran;
    if (fotoAda($foto)) {
        return '<img src="../' . htmlspecialchars($foto, ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($nama, ENT_QUOTES, 'UTF-8') . '" width="' . $u . '" height="' . $u . '" loading="lazy" class="foto-produk ' . $kelas . '" style="width:' . $u . 'px;height:' . $u . 'px">';
    }
    $ini = function_exists('mb_substr') ? mb_strtoupper(mb_substr(trim((string) $nama), 0, 1, 'UTF-8'), 'UTF-8') : strtoupper(substr(trim((string) $nama), 0, 1));
    return '<span class="foto-produk foto-kosong ' . $kelas . '" style="width:' . $u . 'px;height:' . $u . 'px;font-size:' . round($u * .42) . 'px" aria-hidden="true">' . htmlspecialchars($ini ?: '?', ENT_QUOTES, 'UTF-8') . '</span>';
}

// Gaya foto (dicetak sekali per halaman lewat fotoGaya())
function fotoGaya()
{
    static $sudah = false;
    if ($sudah) {
        return '';
    }
    $sudah = true;
    return '<style>.foto-produk{border-radius:10px;object-fit:cover;flex:none;background:#f1f2fb;display:inline-block;vertical-align:middle}'
        . '.foto-kosong{display:inline-flex;align-items:center;justify-content:center;font-weight:700;color:#4942dd;background:#ecebff}</style>';
}

// Simpan foto unggahan dengan aman. Mengembalikan path relatif, '' bila tidak ada unggahan,
// atau false (isi pesan di $galat) kalau ditolak. SVG sengaja tidak diizinkan (bisa memuat skrip).
function fotoSimpan($field, &$galat)
{
    $galat = '';
    if (!isset($_FILES[$field]) || !is_array($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return '';
    }
    $f = $_FILES[$field];
    if ($f['error'] !== UPLOAD_ERR_OK) {
        $galat = ($f['error'] === UPLOAD_ERR_INI_SIZE || $f['error'] === UPLOAD_ERR_FORM_SIZE)
            ? 'Foto terlalu besar. Maksimal 2 MB.' : 'Foto gagal diunggah. Coba lagi.';
        return false;
    }
    if ($f['size'] > 2 * 1024 * 1024) {
        $galat = 'Foto terlalu besar. Maksimal 2 MB.';
        return false;
    }
    if (!is_uploaded_file($f['tmp_name'])) {
        $galat = 'Foto tidak valid.';
        return false;
    }
    $info = @getimagesize($f['tmp_name']);
    $tipe = $info ? $info[2] : 0;
    $ext = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
    if (!isset($ext[$tipe]) || $info[0] > 6000 || $info[1] > 6000) {
        $galat = 'Foto harus berformat JPG, PNG, atau WEBP.';
        return false;
    }
    $dir = __DIR__ . '/../uploads/produk';
    if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
        $galat = 'Folder penyimpanan foto tidak bisa dibuat.';
        return false;
    }
    $nama = bin2hex(random_bytes(10)) . '.' . $ext[$tipe];
    if (!@move_uploaded_file($f['tmp_name'], $dir . '/' . $nama)) {
        $galat = 'Foto gagal disimpan.';
        return false;
    }
    return 'uploads/produk/' . $nama;
}

// Hapus file foto unggahan lama (foto bawaan di assets/ tidak pernah dihapus)
function fotoHapus($foto)
{
    if (is_string($foto) && preg_match('#^uploads/produk/[A-Za-z0-9._-]+$#', $foto)) {
        $p = __DIR__ . '/../' . $foto;
        if (is_file($p)) {
            @unlink($p);
        }
    }
}

// ---------- PEMBANTU UMUM (dipakai guard owner/manajer/kasir) ----------
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

function e($v)
{
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}

function rupiah($n)
{
    return 'Rp ' . number_format((float) $n, 0, ',', '.');
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
