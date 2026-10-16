<?php
// Fitur bersama Nexora POS: profil & password, restock + riwayat stok, shift kasir, ekspor CSV.
// Semua fungsi menerima koneksi mysqli ($conn) dan memakai prepared statement.

function eposDb()
{
    if (!class_exists('Database')) {
        require_once 'database/connection.php';
    }
    $d = new Database();
    return $d->conn;
}

function eposUid()
{
    return isset($_SESSION['user']['id']) ? (int) $_SESSION['user']['id'] : 0;
}

// =====================================================================
//  PROFIL & PASSWORD
// =====================================================================

// Dipanggil di index.php setiap role SEBELUM ada output (supaya bisa redirect + ganti id sesi).
function profilProses()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }
    $balik = 'index.php?page=profil';
    if (!eposCsrfOk(isset($_POST['csrf']) ? $_POST['csrf'] : '')) {
        toastFlash('danger', 'Sesi kedaluwarsa. Muat ulang halaman lalu coba lagi.');
        header('Location: ' . $balik);
        exit;
    }
    $conn = eposDb();
    $uid = eposUid();
    $aksi = isset($_POST['aksi']) ? (string) $_POST['aksi'] : '';
    $galat = '';

    if ($aksi === 'nama') {
        if (profilSimpanNama($conn, $uid, isset($_POST['nama']) ? $_POST['nama'] : '', $galat)) {
            toastFlash('success', 'Nama berhasil diperbarui.');
        } else {
            toastFlash('danger', $galat);
        }
    } elseif ($aksi === 'password') {
        $ok = passwordGanti(
            $conn,
            $uid,
            isset($_POST['lama']) ? (string) $_POST['lama'] : '',
            isset($_POST['baru']) ? (string) $_POST['baru'] : '',
            isset($_POST['ulang']) ? (string) $_POST['ulang'] : '',
            $galat
        );
        if ($ok) {
            toastFlash('success', 'Password berhasil diganti. Gunakan password baru saat login berikutnya.');
        } else {
            toastFlash('danger', $galat);
        }
    }
    header('Location: ' . $balik);
    exit;
}

function profilSimpanNama($conn, $uid, $nama, &$galat)
{
    $nama = trim(preg_replace('/\s+/', ' ', (string) $nama));
    if (mb_strlen($nama) < 2 || mb_strlen($nama) > 100) {
        $galat = 'Nama harus 2 sampai 100 karakter.';
        return false;
    }
    if (preg_match('/[\x00-\x1F\x7F<>]/', $nama)) {
        $galat = 'Nama mengandung karakter yang tidak diizinkan.';
        return false;
    }
    $stmt = $conn->prepare("UPDATE users SET nama = ? WHERE id = ?");
    $stmt->bind_param('si', $nama, $uid);
    $stmt->execute();
    $_SESSION['user']['nama'] = $nama;
    return true;
}

function passwordGanti($conn, $uid, $lama, $baru, $ulang, &$galat)
{
    // batasi tebakan password lama: 5 kali salah -> tunggu 60 detik
    if (!empty($_SESSION['pw_kunci']) && $_SESSION['pw_kunci'] > time()) {
        $galat = 'Terlalu banyak percobaan. Coba lagi dalam ' . ($_SESSION['pw_kunci'] - time()) . ' detik.';
        return false;
    }
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if (!$row || !password_verify($lama, (string) $row['password'])) {
        $_SESSION['pw_gagal'] = (isset($_SESSION['pw_gagal']) ? (int) $_SESSION['pw_gagal'] : 0) + 1;
        if ($_SESSION['pw_gagal'] >= 5) {
            $_SESSION['pw_kunci'] = time() + 60;
            $_SESSION['pw_gagal'] = 0;
        }
        $galat = 'Password lama salah.';
        return false;
    }
    if (strlen($baru) < 8) {
        $galat = 'Password baru minimal 8 karakter.';
        return false;
    }
    if (strlen($baru) > 72) {
        $galat = 'Password baru maksimal 72 karakter.';
        return false;
    }
    if (!preg_match('/[A-Za-z]/', $baru) || !preg_match('/\d/', $baru)) {
        $galat = 'Password baru harus mengandung huruf dan angka.';
        return false;
    }
    if ($baru === $lama) {
        $galat = 'Password baru tidak boleh sama dengan password lama.';
        return false;
    }
    if ($baru !== $ulang) {
        $galat = 'Ulangi password baru tidak sama.';
        return false;
    }
    $hash = password_hash($baru, PASSWORD_DEFAULT);
    $up = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $up->bind_param('si', $hash, $uid);
    $up->execute();
    unset($_SESSION['pw_gagal'], $_SESSION['pw_kunci']);
    session_regenerate_id(true);
    return true;
}

// =====================================================================
//  STOK: RESTOCK + RIWAYAT
// =====================================================================

function stokTipeLabel($tipe)
{
    $map = [
        'restock'   => ['bg-success', 'Restock'],
        'penjualan' => ['bg-secondary', 'Penjualan'],
        'void'      => ['bg-info text-dark', 'Void (kembali)'],
        'koreksi'   => ['bg-warning text-dark', 'Koreksi'],
        'awal'      => ['bg-primary', 'Stok awal'],
    ];
    $b = isset($map[$tipe]) ? $map[$tipe] : ['bg-secondary', $tipe];
    return '<span class="badge ' . $b[0] . '">' . htmlspecialchars($b[1], ENT_QUOTES, 'UTF-8') . '</span>';
}

// Catat satu perubahan stok. Dipanggil DI DALAM transaksi database pemanggil.
function stokLogTulis($conn, $pid, $branch, $tipe, $jumlah, $sebelum, $sesudah, $ket = '')
{
    $uid = eposUid();
    $uidDb = $uid > 0 ? $uid : null;
    $ket = mb_substr((string) $ket, 0, 255);
    $stmt = $conn->prepare(
        "INSERT INTO stock_logs (product_id, branch_id, user_id, tipe, jumlah, stok_sebelum, stok_sesudah, keterangan, dibuat)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $waktu = date('Y-m-d H:i:s');
    $stmt->bind_param('iiisiiiss', $pid, $branch, $uidDb, $tipe, $jumlah, $sebelum, $sesudah, $ket, $waktu);
    $stmt->execute();
}

// Produk yang boleh disentuh: $scope = id cabang (manajer) atau 0 (owner, semua cabang)
function stokProduk($conn, $id, $scope)
{
    $stmt = $conn->prepare(
        "SELECT p.*, b.nama_cabang FROM products p JOIN branches b ON b.id = p.branch_id
         WHERE p.id = ? AND (? = 0 OR p.branch_id = ?)"
    );
    $stmt->bind_param('iii', $id, $scope, $scope);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function stokRestock($conn, $pid, $scope, $jumlah, $ket, &$galat)
{
    $jumlah = (int) $jumlah;
    if ($jumlah < 1 || $jumlah > 100000) {
        $galat = 'Jumlah restock harus antara 1 dan 100.000.';
        return false;
    }
    $ket = trim((string) $ket);
    if (mb_strlen($ket) > 200) {
        $galat = 'Keterangan maksimal 200 karakter.';
        return false;
    }
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare(
            "SELECT id, branch_id, stok FROM products WHERE id = ? AND (? = 0 OR branch_id = ?) FOR UPDATE"
        );
        $stmt->bind_param('iii', $pid, $scope, $scope);
        $stmt->execute();
        $p = $stmt->get_result()->fetch_assoc();
        if (!$p) {
            $conn->rollback();
            $galat = 'Produk tidak ditemukan.';
            return false;
        }
        $sebelum = (int) $p['stok'];
        $sesudah = $sebelum + $jumlah;
        if ($sesudah > 1000000) {
            $conn->rollback();
            $galat = 'Stok melebihi batas 1.000.000.';
            return false;
        }
        $up = $conn->prepare("UPDATE products SET stok = ? WHERE id = ?");
        $up->bind_param('ii', $sesudah, $pid);
        $up->execute();
        stokLogTulis($conn, (int) $p['id'], (int) $p['branch_id'], 'restock', $jumlah, $sebelum, $sesudah, $ket);
        $conn->commit();
        return true;
    } catch (mysqli_sql_exception $ex) {
        $conn->rollback();
        $galat = 'Restock gagal disimpan. Coba lagi.';
        return false;
    }
}

// Proses form restock (POST). Mengembalikan true/false; pesan lewat toast sesi.
function restockProses($conn, $scope)
{
    if (!eposCsrfOk(isset($_POST['csrf']) ? $_POST['csrf'] : '')) {
        toastFlash('danger', 'Sesi kedaluwarsa. Muat ulang halaman lalu coba lagi.');
        return false;
    }
    $galat = '';
    $pid = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
    $ok = stokRestock($conn, $pid, $scope, isset($_POST['jumlah']) ? $_POST['jumlah'] : 0, isset($_POST['keterangan']) ? $_POST['keterangan'] : '', $galat);
    if ($ok) {
        toastFlash('success', 'Restock berhasil. Stok produk sudah bertambah.');
    } else {
        toastFlash('danger', $galat);
    }
    return $ok;
}

function stokTipeValid($t)
{
    return in_array($t, ['restock', 'penjualan', 'void', 'koreksi', 'awal'], true) ? $t : '';
}

function stokLogDaftar($conn, $scope, $tipe, $cari, $dari, $sampai)
{
    $like = '%' . $cari . '%';
    $stmt = $conn->prepare(
        "SELECT l.*, p.nama_produk, b.nama_cabang, u.nama AS nama_user
         FROM stock_logs l
         JOIN products p ON p.id = l.product_id
         JOIN branches b ON b.id = l.branch_id
         LEFT JOIN users u ON u.id = l.user_id
         WHERE (? = 0 OR l.branch_id = ?)
           AND (? = '' OR l.tipe = ?)
           AND p.nama_produk LIKE ?
           AND DATE(l.dibuat) BETWEEN ? AND ?
         ORDER BY l.dibuat DESC, l.id DESC"
    );
    $stmt->bind_param('iisssss', $scope, $scope, $tipe, $tipe, $like, $dari, $sampai);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// =====================================================================
//  SHIFT KASIR
// =====================================================================

function shiftAktif($conn, $uid)
{
    $stmt = $conn->prepare("SELECT * FROM shifts WHERE cashier_id = ? AND tutup IS NULL ORDER BY id DESC LIMIT 1");
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Total penjualan kasir selama shift (void tidak dihitung)
function shiftPenjualan($conn, $branch, $uid, $buka, $tutup = null)
{
    $akhir = $tutup ? $tutup : date('Y-m-d H:i:s');
    $stmt = $conn->prepare(
        "SELECT COALESCE(SUM(total_bayar), 0) AS total, COUNT(*) AS n
         FROM transactions
         WHERE branch_id = ? AND cashier_id = ? AND status <> 'void' AND tanggal >= ? AND tanggal <= ?"
    );
    $stmt->bind_param('iiss', $branch, $uid, $buka, $akhir);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function uangValid($v, $maks = 999999999)
{
    $v = str_replace(['.', ' '], '', trim((string) $v));
    $v = str_replace(',', '.', $v);
    if ($v === '' || !preg_match('/^\d+(\.\d{1,2})?$/', $v)) {
        return false;
    }
    $n = (float) $v;
    return ($n >= 0 && $n <= $maks) ? $n : false;
}

function shiftBuka($conn, $branch, $uid, $kasAwal, &$galat)
{
    $kas = uangValid($kasAwal);
    if ($kas === false) {
        $galat = 'Kas awal harus berupa angka (0 atau lebih).';
        return false;
    }
    if (shiftAktif($conn, $uid)) {
        $galat = 'Shift kamu masih terbuka. Tutup dulu sebelum membuka yang baru.';
        return false;
    }
    $waktu = date('Y-m-d H:i:s');
    $stmt = $conn->prepare("INSERT INTO shifts (branch_id, cashier_id, buka, kas_awal) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('iisd', $branch, $uid, $waktu, $kas);
    $stmt->execute();
    return true;
}

function shiftTutup($conn, $branch, $uid, $kasAkhir, $catatan, &$galat)
{
    $kas = uangValid($kasAkhir);
    if ($kas === false) {
        $galat = 'Kas akhir harus berupa angka (0 atau lebih).';
        return false;
    }
    $catatan = trim((string) $catatan);
    if (mb_strlen($catatan) > 200) {
        $galat = 'Catatan maksimal 200 karakter.';
        return false;
    }
    $s = shiftAktif($conn, $uid);
    if (!$s || (int) $s['branch_id'] !== (int) $branch) {
        $galat = 'Tidak ada shift yang sedang terbuka.';
        return false;
    }
    $tutup = date('Y-m-d H:i:s');
    $jual = shiftPenjualan($conn, $branch, $uid, $s['buka'], $tutup);
    $penjualan = (float) $jual['total'];
    $selisih = $kas - ((float) $s['kas_awal'] + $penjualan);
    $stmt = $conn->prepare(
        "UPDATE shifts SET tutup = ?, kas_akhir = ?, penjualan = ?, selisih = ?, catatan = ?
         WHERE id = ? AND cashier_id = ? AND tutup IS NULL"
    );
    $sid = (int) $s['id'];
    $stmt->bind_param('sdddsii', $tutup, $kas, $penjualan, $selisih, $catatan, $sid, $uid);
    $stmt->execute();
    if ($stmt->affected_rows < 1) {
        $galat = 'Shift sudah ditutup.';
        return false;
    }
    return true;
}

function shiftRiwayatKasir($conn, $uid)
{
    $stmt = $conn->prepare("SELECT * FROM shifts WHERE cashier_id = ? ORDER BY buka DESC, id DESC");
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Untuk manajer: shift di cabangnya yang dibuka pada tanggal itu, atau masih terbuka
function shiftCabang($conn, $branch, $tanggal)
{
    $stmt = $conn->prepare(
        "SELECT s.*, u.nama AS nama_kasir
         FROM shifts s JOIN users u ON u.id = s.cashier_id
         WHERE s.branch_id = ? AND (DATE(s.buka) = ? OR (s.tutup IS NULL AND DATE(s.buka) <= ?))
         ORDER BY s.buka DESC, s.id DESC"
    );
    $stmt->bind_param('iss', $branch, $tanggal, $tanggal);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function selisihBadge($v)
{
    if ($v === null) {
        return '<span class="text-secondary">-</span>';
    }
    $n = round((float) $v);
    if ($n == 0) {
        return '<span class="badge bg-success">Pas</span>';
    }
    $teks = 'Rp ' . number_format(abs($n), 0, ',', '.');
    return '<span class="badge ' . ($n > 0 ? 'bg-info text-dark' : 'bg-danger') . '">' . ($n > 0 ? 'Lebih ' : 'Kurang ') . $teks . '</span>';
}

// =====================================================================
//  EKSPOR CSV (bisa dibuka di Excel)
// =====================================================================

// Cegah "CSV injection": sel teks yang diawali = + - @ dianggap rumus oleh Excel
function csvSel($v)
{
    if (is_int($v) || is_float($v)) {
        return $v;
    }
    $v = (string) $v;
    if ($v !== '' && strpos("=+-@\t\r", $v[0]) !== false && !is_numeric($v)) {
        return "'" . $v;
    }
    return $v;
}

function csvKirim($namaFile, array $judul, array $baris)
{
    $namaFile = preg_replace('/[^A-Za-z0-9_.-]/', '', $namaFile);
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $namaFile . '"');
    header('Cache-Control: no-store');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");   // BOM supaya Excel membaca UTF-8
    fputcsv($out, $judul, ';');
    foreach ($baris as $b) {
        fputcsv($out, array_map('csvSel', $b), ';');
    }
    fclose($out);
    exit;
}
