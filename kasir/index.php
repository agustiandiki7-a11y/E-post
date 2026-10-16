<?php
require_once 'function/guard.php';
require_once 'function/kasir.php';

// Kasir wajib terikat ke satu cabang
if ($BRANCH_ID < 1) {
    echo 'Akun kasir ini belum terhubung ke cabang. Hubungi owner.';
    exit;
}

$ksr = new Kasir($BRANCH_ID, $USER_ID);
$cabang = $ksr->cabang();
if (!$cabang) {
    echo 'Cabang untuk akun ini tidak ditemukan. Hubungi owner.';
    exit;
}

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
if (!in_array($page, ['dashboard', 'baru', 'riwayat', 'produk', 'struk', 'shift', 'profil'], true)) {
    $page = 'dashboard';
}

// ---- Simpan transaksi baru (dipanggil lewat JavaScript dari halaman Transaksi Baru, balasannya JSON)
if ($page === 'baru' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    if (!eposCsrfOk(isset($_POST['csrf']) ? $_POST['csrf'] : '')) {
        echo json_encode(['ok' => false, 'msg' => 'Sesi kedaluwarsa. Muat ulang halaman lalu coba lagi.']);
        exit;
    }
    $items = (isset($_POST['item']) && is_array($_POST['item'])) ? $_POST['item'] : [];
    $tipe = (isset($_POST['diskon_tipe']) && $_POST['diskon_tipe'] === 'persen') ? 'persen' : 'rupiah';
    $nilai = isset($_POST['diskon_nilai']) ? $_POST['diskon_nilai'] : 0;
    $id = $ksr->simpan($items, $tipe, $nilai);
    if ($id) { toastFlash('success', 'Transaksi berhasil disimpan.'); }
    echo json_encode($id ? ['ok' => true, 'id' => $id] : ['ok' => false, 'msg' => $ksr->error]);
    exit;
}

// ---- Ajukan void (kasir hanya mengajukan, manajer yang menyetujui)
if ($page === 'riwayat' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!eposCsrfOk(isset($_POST['csrf']) ? $_POST['csrf'] : '')) {
        $_SESSION['flash'] = ['danger', 'Sesi kedaluwarsa. Coba lagi.'];
    } else {
        $ok = $ksr->ajukanVoid(isset($_POST['id']) ? (int) $_POST['id'] : 0);
        $_SESSION['flash'] = $ok
            ? ['success', 'Pengajuan void dikirim ke manajer cabang untuk disetujui.']
            : ['danger', $ksr->error];
    }
    redirect('riwayat');
}

// ---- Profil: simpan nama / ganti password
if ($page === 'profil') {
    profilProses();
}

// ---- Buka / tutup shift
$dbShift = eposDb();
if ($page === 'shift' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!eposCsrfOk(isset($_POST['csrf']) ? $_POST['csrf'] : '')) {
        toastFlash('danger', 'Sesi kedaluwarsa. Muat ulang halaman lalu coba lagi.');
    } else {
        $galat = '';
        if ((isset($_POST['aksi']) ? $_POST['aksi'] : '') === 'buka') {
            if (shiftBuka($dbShift, $BRANCH_ID, $USER_ID, isset($_POST['kas']) ? $_POST['kas'] : '', $galat)) {
                toastFlash('success', 'Shift dibuka. Selamat bekerja!');
            } else {
                toastFlash('danger', $galat);
            }
        } else {
            if (shiftTutup($dbShift, $BRANCH_ID, $USER_ID, isset($_POST['kas']) ? $_POST['kas'] : '', isset($_POST['catatan']) ? $_POST['catatan'] : '', $galat)) {
                toastFlash('success', 'Shift ditutup. Rekap sudah tersimpan untuk manajer.');
            } else {
                toastFlash('danger', $galat);
            }
        }
    }
    redirect('shift');
}
$shiftAktif = shiftAktif($dbShift, $USER_ID);

// ---- Struk: halaman mandiri tanpa sidebar supaya bersih saat dicetak
if ($page === 'struk') {
    include 'pages/transaksi/struk.php';
    exit;
}

$judul = [
    'dashboard' => ['Dashboard Kasir', 'Ringkasan penjualanmu hari ini'],
    'baru'      => ['Transaksi Baru', 'Pilih produk, atur potongan, lalu simpan'],
    'riwayat'   => ['Riwayat Transaksi', 'Transaksi yang kamu catat'],
    'produk'    => ['Produk & Stok', 'Harga jual dan stok di ' . $cabang['nama_cabang']],
    'shift'     => ['Shift Kasir', 'Buka dan tutup shift, hitung kas'],
    'profil'    => ['Profil Saya', 'Ubah nama dan ganti password akunmu'],
];
?>
<!DOCTYPE html>
<html lang="id">

<!-- head -->
<?php include 'partials/head.php' ?>

<body>
    <div id="overlay" class="overlay"></div>

    <!-- TOPBAR -->
    <?php include 'components/topbar.php' ?>

    <!-- SIDEBAR -->
    <?php include 'components/sidebar.php' ?>

    <!-- MAIN CONTENT -->
    <main id="content" class="content py-10">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                        <div>
                            <h1 class="fs-3 mb-1"><?= e($judul[$page][0]) ?></h1>
                            <p class="mb-0"><?= e($judul[$page][1]) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <?php
            switch ($page) {
                case 'dashboard':
                    include 'pages/dashboard.php';
                    break;
                case 'baru':
                    include 'pages/transaksi/baru.php';
                    break;
                case 'riwayat':
                    include 'pages/transaksi/riwayat.php';
                    break;
                case 'produk':
                    include 'pages/produk/produk.php';
                    break;
                case 'shift':
                    include 'pages/shift.php';
                    break;
                case 'profil':
                    include '../login/halaman/profil.php';
                    break;
            }
            ?>

            <?php include 'components/footer.php' ?>
        </div>
    </main>

    <!-- Javascript -->
    <?php include 'partials/script.php' ?>
</body>

</html>
