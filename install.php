<?php
// ============================================================
// INSTALLER / PERBAIKAN DATABASE Nexora POS
// Buka sekali lewat browser: http://localhost/E-post/install.php
//
// Yang dikerjakan:
//  1. Membuat database 'e-pos' kalau belum ada
//  2. Memeriksa 5 tabel (users, branches, products, transactions, transaction_details)
//  3. Membuat akun contoh yang belum ada (akun yang sudah punya password valid tidak diubah)
//  4. Memeriksa folder aplikasi (login, owner, manajer, kasir)
//
// Aman dijalankan berulang kali: tabel yang sudah benar TIDAK dihapus.
// Tabel hanya dibangun ulang kalau kosong total, atau kalau kamu klik tombolnya.
// Setelah login jalan, file ini boleh dihapus.
// ============================================================
$host = 'localhost';
$user = 'root';
$pass = '';
$dbName = 'e-pos';

// Hanya boleh dibuka dari komputer sendiri
$ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
if (!in_array($ip, ['127.0.0.1', '::1'], true)) {
    http_response_code(403);
    exit('Installer ini hanya bisa dibuka dari komputer sendiri (localhost).');
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$log = [];            // setiap isi: [tipe, teks]  (tipe: ok / warn / err)
$hasil = [];          // hasil uji tiap akun contoh
$butuhKonfirmasi = false;

function catat($tipe, $teks)
{
    global $log;
    $log[] = [$tipe, $teks];
}

// Mengembalikan [daftar masalah, jumlah tabel yang sudah ada]
function cekSkema($c)
{
    $harus = [
        'branches'            => ['id', 'nama_cabang', 'alamat', 'telepon'],
        'users'               => ['id', 'nama', 'email', 'password', 'role', 'branch_id'],
        'products'            => ['id', 'branch_id', 'nama_produk', 'harga_jual', 'stok'],
        'transactions'        => ['id', 'branch_id', 'cashier_id', 'invoice_number', 'total_bayar', 'diskon', 'tanggal', 'status'],
        'transaction_details' => ['id', 'transaction_id', 'product_id', 'jumlah', 'harga_satuan', 'subtotal'],
    ];
    $masalah = [];
    $adaTabel = 0;

    foreach ($harus as $tabel => $kolomWajib) {
        $q = $c->prepare(
            "SELECT COLUMN_NAME AS kolom, COLUMN_TYPE AS tipe
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?"
        );
        $q->bind_param('s', $tabel);
        $q->execute();
        $ada = [];
        foreach ($q->get_result() as $r) {
            $ada[$r['kolom']] = $r['tipe'];
        }

        if (!$ada) {
            $masalah[] = "Tabel $tabel belum ada.";
            continue;
        }
        $adaTabel++;

        foreach ($kolomWajib as $k) {
            if (!isset($ada[$k])) {
                $masalah[] = "Tabel $tabel kekurangan kolom $k.";
            }
        }

        // kolom role harus bisa menampung admin / kasir / manajer
        if ($tabel === 'users' && isset($ada['role']) && stripos($ada['role'], 'enum(') === 0) {
            foreach (['admin', 'kasir', 'manajer'] as $nilai) {
                if (stripos($ada['role'], "'" . $nilai . "'") === false) {
                    $masalah[] = "Kolom users.role belum punya nilai '$nilai'.";
                }
            }
        }
    }
    return [$masalah, $adaTabel];
}

function bangunUlang($c, $file)
{
    if (!is_file($file)) {
        throw new Exception('File database.sql tidak ditemukan di folder yang sama dengan install.php.');
    }
    $sql = file_get_contents($file);
    $c->multi_query($sql);
    do {
        $r = $c->store_result();
        if ($r) {
            $r->free();
        }
    } while ($c->more_results() && $c->next_result());
}

// Pastikan kolom password cukup panjang untuk menyimpan hash (hash yang terpotong = login selalu gagal)
function perbaikiKolomPassword($c)
{
    $q = $c->query(
        "SELECT DATA_TYPE AS jenis, CHARACTER_MAXIMUM_LENGTH AS panjang
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'password'"
    );
    $d = $q->fetch_assoc();
    if ($d && ($d['panjang'] === null ? false : (int) $d['panjang'] < 255)) {
        $c->query("ALTER TABLE users MODIFY password VARCHAR(255) NOT NULL");
        catat('warn', 'Kolom users.password terlalu pendek (' . (int) $d['panjang'] . ' karakter), hash password akan terpotong. Sudah diperlebar jadi 255.');
    }
}

function siapkanAkun($c)
{
    global $hasil;

    // dua cabang contoh (dilewati kalau id 1 dan 2 sudah dipakai)
    $c->query(
        "INSERT IGNORE INTO branches (id, nama_cabang, alamat, telepon) VALUES
         (1, 'Cabang Ungaran', 'Jl. Diponegoro No. 10, Ungaran', '024-6921001'),
         (2, 'Cabang Semarang', 'Jl. Pandanaran No. 25, Semarang', '024-8311002'),
         (3, 'Cabang Salatiga', 'Jl. Jend. Sudirman No. 8, Salatiga', '0298-321003')"
    );

    $akun = [
        ['Diki Agustian', 'agustiandiki7@gmail.com', 'Owner@Nexora26', 'admin', null],
        ['Rina Wulandari', 'rina@nexora.test', 'Rina@Kasir26', 'kasir', 1],
        ['Dedi Prasetyo', 'dedi@nexora.test', 'Dedi@Kasir26', 'kasir', 2],
        ['Piter', 'piter@gmail.com', 'Piter@Ungaran26', 'manajer', 1],
        ['Lestari Ayu', 'lestari@nexora.test', 'Lestari@Smg26', 'manajer', 2],
        ['Sari Handayani', 'sari@nexora.test', 'Sari@Kasir26', 'kasir', 3],
        ['Bayu Nugroho', 'bayu@nexora.test', 'Bayu@Salatiga26', 'manajer', 3],
        ['Tono Wijaya', 'tono@nexora.test', 'Tono@Kasir26', 'kasir', 1],
    ];


    foreach ($akun as $a) {
        $nama = $a[0];
        $email = $a[1];
        $pw = $a[2];
        $role = $a[3];
        $branch = $a[4];
        $hash = password_hash($pw, PASSWORD_DEFAULT);

        $q = $c->prepare("SELECT id FROM users WHERE email = ?");
        $q->bind_param('s', $email);
        $q->execute();
        $row = $q->get_result()->fetch_assoc();

        if ($row) {
            // Akun yang sudah punya hash valid TIDAK disentuh: password yang sudah kamu ganti tidak boleh di-reset
            $cek = $c->prepare("SELECT password FROM users WHERE id = ?");
            $cek->bind_param('i', $row['id']);
            $cek->execute();
            $lama = $cek->get_result()->fetch_assoc();
            if (preg_match('/^\$2[aby]\$/', (string) $lama['password'])) {
                $hasil[] = [$email, '(tidak diubah)', $role, 'dipertahankan', true];
                continue;
            }
            $id = (int) $row['id'];
            $u = $c->prepare("UPDATE users SET nama = ?, password = ?, role = ?, branch_id = ? WHERE id = ?");
            $u->bind_param('sssii', $nama, $hash, $role, $branch, $id);
            $u->execute();
            $aksi = 'diperbarui';
        } else {
            $u = $c->prepare("INSERT INTO users (nama, email, password, role, branch_id) VALUES (?, ?, ?, ?, ?)");
            $u->bind_param('ssssi', $nama, $email, $hash, $role, $branch);
            $u->execute();
            $aksi = 'dibuat';
        }

        // Uji: password yang tersimpan benar-benar cocok
        $v = $c->prepare("SELECT password, role FROM users WHERE email = ?");
        $v->bind_param('s', $email);
        $v->execute();
        $d = $v->get_result()->fetch_assoc();
        $hasil[] = [$email, $pw, $d['role'], $aksi, password_verify($pw, $d['password'])];
    }
}

try {
    $c = new mysqli($host, $user, $pass);
    $c->set_charset('utf8mb4');
    catat('ok', 'Terhubung ke MySQL.');

    $c->query("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    $c->select_db($dbName);
    catat('ok', "Database $dbName siap.");

    list($masalah, $adaTabel) = cekSkema($c);
    $bangun = isset($_POST['rebuild']);

    // Kosong total (tidak ada yang bisa hilang) atau diminta lewat tombol: bangun ulang
    if ($masalah && ($adaTabel === 0 || $bangun)) {
        bangunUlang($c, __DIR__ . '/database.sql');
        $c->select_db($dbName);
        catat('ok', 'Tabel dibuat dari database.sql.');
        list($masalah, $adaTabel) = cekSkema($c);
    }

    if ($masalah) {
        $butuhKonfirmasi = true;
        foreach ($masalah as $m) {
            catat('warn', $m);
        }
        catat('warn', 'Struktur tabel belum sesuai dengan yang dipakai aplikasi. Klik tombol merah di bawah untuk membangun ulang (tabel Nexora POS yang lama akan dihapus).');
    } else {
        catat('ok', 'Struktur 5 tabel sudah benar.');
        perbaikiKolomPassword($c);
        siapkanAkun($c);
        catat('ok', 'Akun contoh siap dan password sudah diuji.');
    }
} catch (Exception $ex) {
    catat('err', 'Gagal: ' . $ex->getMessage());
    catat('warn', 'Pastikan MySQL di Laragon sudah Start. Kalau MySQL-mu memakai password, isi $pass di bagian atas install.php dan di owner/database/connection.php.');
}

// Cek letak folder aplikasi
$root = __DIR__;
foreach (['login', 'owner', 'manajer', 'kasir'] as $f) {
    if (is_file($root . '/' . $f . '/index.php')) {
        catat('ok', "Folder $f ditemukan.");
    } elseif ($f === 'manajer' && (is_dir($root . '/manager') || is_dir($root . '/maanejer caabang'))) {
        catat('warn', "Folder manajer belum bernama 'manajer'. Ganti nama folder 'manager' atau 'maanejer caabang' menjadi 'manajer'.");
    } else {
        catat('warn', "Folder $f/index.php tidak ditemukan.");
    }
}

$semuaCocok = $hasil && !in_array(false, array_column($hasil, 4), true);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Installer Nexora POS</title>
    <style>
        body { font-family: "Segoe UI", Arial, sans-serif; background: #f5f5f5; color: #171717; margin: 0; padding: 24px; }
        .box { max-width: 780px; margin: 0 auto; background: #fff; border: 1px solid #e5e5e5; border-radius: 12px; padding: 28px; }
        h1 { margin: 0 0 4px; font-size: 1.5rem; }
        h2 { font-size: 1.05rem; margin: 24px 0 8px; }
        ul { padding-left: 20px; margin: 0; }
        li { margin: 5px 0; }
        .ok { color: #15803d; } .warn { color: #1912ab; } .err { color: #b91c1c; }
        table { border-collapse: collapse; width: 100%; margin: 8px 0; }
        td, th { border: 1px solid #e5e5e5; padding: 8px 10px; text-align: left; font-size: .92rem; }
        th { background: #fafafa; }
        .btn { display: inline-block; background: #4942dd; color: #fff; border: 0; padding: 11px 22px; border-radius: 8px; font-size: 1rem; text-decoration: none; cursor: pointer; }
        .btn-red { background: #b91c1c; }
        small, .muted { color: #737373; }
        code { background: #f5f5f5; padding: 1px 5px; border-radius: 4px; }
    </style>
</head>
<body>
<div class="box">
    <h1>Installer Nexora POS</h1>
    <div class="muted">Lokasi proyek: <code><?= htmlspecialchars(__DIR__) ?></code></div>

    <h2>Hasil pemeriksaan</h2>
    <ul>
        <?php foreach ($log as $l) { ?>
            <li class="<?= $l[0] ?>"><?= $l[0] === 'ok' ? '&#10003;' : ($l[0] === 'warn' ? '&#9888;' : '&#10007;') ?> <?= htmlspecialchars($l[1]) ?></li>
        <?php } ?>
    </ul>

    <?php if ($hasil) { ?>
        <h2>Akun untuk login</h2>
        <table>
            <tr><th>Email</th><th>Password</th><th>Role</th><th>Status</th><th>Uji password</th></tr>
            <?php foreach ($hasil as $h) { ?>
                <tr>
                    <td><?= htmlspecialchars($h[0]) ?></td>
                    <td><code><?= htmlspecialchars($h[1]) ?></code></td>
                    <td><?= htmlspecialchars($h[2]) ?></td>
                    <td><?= htmlspecialchars($h[3]) ?></td>
                    <td class="<?= $h[4] ? 'ok' : 'err' ?>"><?= $h[4] ? '&#10003; cocok' : '&#10007; tidak cocok' ?></td>
                </tr>
            <?php } ?>
        </table>
    <?php } ?>

    <?php if ($butuhKonfirmasi) { ?>
        <h2>Perlu dibangun ulang</h2>
        <form method="post" onsubmit="return confirm('Semua tabel Nexora POS yang lama akan dihapus dan dibuat ulang. Lanjutkan?')">
            <button type="submit" name="rebuild" value="1" class="btn btn-red">Bangun ulang database</button>
        </form>
    <?php } elseif ($semuaCocok) { ?>
        <p><a href="login/index.php" class="btn">Buka halaman login</a></p>
        <p class="muted"><small>Kalau login sudah jalan, hapus file <code>install.php</code> ini.</small></p>
    <?php } ?>
</div>
</body>
</html>
