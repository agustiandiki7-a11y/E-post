<?php
require_once 'function/guard.php';
require_once 'function/manajer.php';

// Manajer wajib terikat ke satu cabang
if ($BRANCH_ID < 1) {
    echo 'Akun manajer ini belum terhubung ke cabang. Hubungi owner.';
    exit;
}

$mgr = new Manajer($BRANCH_ID);
$cabang = $mgr->cabang();
$voidPending = $mgr->jumlahMenungguVoid();

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Profil, restock, dan unduh CSV diproses sebelum ada output HTML
if ($page === 'profil') {
    profilProses();
}
if ($page === 'restock' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    restockProses(eposDb(), $BRANCH_ID);
    redirect('stok');
}
if (isset($_GET['unduh'])) {
    if ($page === 'penjualan') {
        $dari = tglValid(isset($_GET['dari']) ? $_GET['dari'] : '', date('Y-m-01'));
        $sampai = tglValid(isset($_GET['sampai']) ? $_GET['sampai'] : '', date('Y-m-d'));
        $baris = [];
        foreach ($mgr->penjualan($dari, $sampai, isset($_GET['kasir_id']) ? (int) $_GET['kasir_id'] : 0, isset($_GET['cari']) ? trim($_GET['cari']) : '') as $r) {
            $baris[] = [$r['invoice_number'], $r['tanggal'], $r['nama_kasir'], (float) $r['diskon'], (float) $r['total_bayar'], $r['status']];
        }
        csvKirim('penjualan-' . $dari . '_' . $sampai . '.csv', ['Invoice', 'Tanggal', 'Kasir', 'Diskon', 'Total', 'Status'], $baris);
    }
    if ($page === 'shift') {
        $tgl = tglValid(isset($_GET['tanggal']) ? $_GET['tanggal'] : '', $mgr->tanggalTerakhir());
        $baris = [];
        foreach (shiftCabang(eposDb(), $BRANCH_ID, $tgl) as $r) {
            $baris[] = [$r['nama_kasir'], $r['buka'], $r['tutup'] ? $r['tutup'] : 'Berjalan', (float) $r['kas_awal'], $r['kas_akhir'] === null ? '' : (float) $r['kas_akhir'], $r['penjualan'] === null ? '' : (float) $r['penjualan'], $r['selisih'] === null ? '' : (float) $r['selisih'], (string) $r['catatan']];
        }
        csvKirim('shift-' . $tgl . '.csv', ['Kasir', 'Buka', 'Tutup', 'Kas awal', 'Kas akhir', 'Penjualan', 'Selisih', 'Catatan'], $baris);
    }
}

// Aksi void diproses sebelum ada output HTML (supaya bisa redirect)
if ($page === 'void' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $aksi = isset($_POST['aksi']) ? $_POST['aksi'] : '';
    if (!eposCsrfOk(isset($_POST['csrf']) ? $_POST['csrf'] : '')) {
        $_SESSION['flash'] = ['danger', 'Sesi kedaluwarsa. Muat ulang halaman lalu coba lagi.'];
        redirect('void');
    }
    $ok = ($aksi === 'setujui') ? $mgr->setujuiVoid($id) : (($aksi === 'tolak') ? $mgr->tolakVoid($id) : false);
    $_SESSION['flash'] = $ok
        ? ['success', $aksi === 'setujui' ? 'Void disetujui dan stok dikembalikan.' : 'Pengajuan void ditolak.']
        : ['danger', $mgr->error ?: 'Aksi tidak dikenali.'];
    redirect('void');
}

$judul = [
    'dashboard' => ['Dashboard Cabang', 'Ringkasan penjualan ' . $cabang['nama_cabang']],
    'penjualan' => ['Penjualan Cabang', 'Daftar transaksi cabang ini'],
    'detail'    => ['Detail Transaksi', 'Rincian barang yang dibeli'],
    'shift'     => ['Penutupan Shift Kasir', 'Rekap kasir per hari'],
    'void'      => ['Persetujuan Void', 'Setujui atau tolak pengajuan void dari kasir'],
    'stok'      => ['Stok Produk', 'Pantau stok dan tambah barang masuk di ' . $cabang['nama_cabang']],
    'restock'   => ['Restock Produk', 'Tambah stok barang yang masuk'],
    'riwayatStok' => ['Riwayat Stok', 'Catatan penambahan dan pengurangan stok cabang ini'],
    'profil'    => ['Profil Saya', 'Ubah nama dan ganti password akunmu'],
];
if (!isset($judul[$page])) {
    $page = 'dashboard';
}
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
                case 'penjualan':
                    include 'pages/penjualan.php';
                    break;
                case 'detail':
                    include 'pages/detail.php';
                    break;
                case 'shift':
                    include 'pages/shift.php';
                    break;
                case 'void':
                    include 'pages/void.php';
                    break;
                case 'stok':
                    include 'pages/stok.php';
                    break;
                case 'restock':
                    include 'pages/restock.php';
                    break;
                case 'riwayatStok':
                    include 'pages/riwayat_stok.php';
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
