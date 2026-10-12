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

// Aksi void diproses sebelum ada output HTML (supaya bisa redirect)
if ($page === 'void' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) $_POST['id'];
    $aksi = isset($_POST['aksi']) ? $_POST['aksi'] : '';
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
            }
            ?>

            <?php include 'components/footer.php' ?>
        </div>
    </main>

    <!-- Javascript -->
    <?php include 'partials/script.php' ?>
</body>

</html>
