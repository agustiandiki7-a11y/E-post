<?php
require_once 'function/guard.php';

// Aksi yang harus jalan sebelum ada output HTML: simpan profil, unduh CSV
$halaman = isset($_GET['page']) ? $_GET['page'] : '';
if ($halaman === 'profil') {
    profilProses();
}
if (isset($_GET['unduh'])) {
    if ($halaman === 'pembayaran') {
        require_once 'function/pembayaran.php';
        $dari = tglValid(isset($_GET['dari']) ? $_GET['dari'] : '', date('Y-m-01'));
        $sampai = tglValid(isset($_GET['sampai']) ? $_GET['sampai'] : '', date('Y-m-d'));
        $baris = [];
        foreach ((new Pembayaran())->perCabangHari($dari, $sampai) as $r) {
            $baris[] = [$r['tgl'], $r['nama_cabang'], (int) $r['jml_transaksi'], (float) $r['total_diskon'], (float) $r['total_penjualan']];
        }
        csvKirim('laporan-penjualan-' . $dari . '_' . $sampai . '.csv', ['Tanggal', 'Cabang', 'Transaksi', 'Diskon', 'Penjualan'], $baris);
    }
    if ($halaman === 'penyewaan') {
        require_once 'function/penyewaan.php';
        $dari = tglValid(isset($_GET['dari']) ? $_GET['dari'] : '', date('Y-m-01'));
        $sampai = tglValid(isset($_GET['sampai']) ? $_GET['sampai'] : '', date('Y-m-d'));
        $baris = [];
        foreach ((new Penyewaan())->semua(isset($_GET['branch_id']) ? (int) $_GET['branch_id'] : 0, $dari, $sampai, isset($_GET['cari']) ? trim($_GET['cari']) : '') as $r) {
            $baris[] = [$r['invoice_number'], $r['tanggal'], $r['nama_cabang'], $r['nama_kasir'], (float) $r['diskon'], (float) $r['total_bayar'], $r['status']];
        }
        csvKirim('transaksi-' . $dari . '_' . $sampai . '.csv', ['Invoice', 'Tanggal', 'Cabang', 'Kasir', 'Diskon', 'Total', 'Status'], $baris);
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<!-- head -->
<?php include 'partials/head.php' ?>

<body>
    <div id="overlay" class="overlay"></div>
    <!-- TOPBAR -->
    <?php include 'components/topbar.php' ?>

    

    <!-- SIDEBAR -->
    <?php include 'components/sidebar.php' ?>

    <!-- MAIN CONTENT: halaman dipilih lewat ?page=... -->
    <?php
    $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
    switch ($page) {
        case 'dashboard':
            include 'pages/dashboard.php';
            break;
        // kategori (= master cabang)
        case 'kategori':
            include 'pages/kategori/kategori.php';
            break;
        case 'tambahKategori':
            include 'pages/kategori/tambah.php';
            break;
        case 'updateKategori':
            include 'pages/kategori/update.php';
            break;
        // KENDARAAN
        case 'kendaraan':
            include 'pages/kendaraan/kendaraan.php';
            break;
        // pembayaran
        case 'pembayaran':
            include 'pages/pembayaran/pembayaran.php';
            break;
        // penyewaan
        case 'penyewaan':
            include 'pages/penyewaan/penyewa.php';
            break;
        case 'profil':
            include 'pages/profil/profil.php';
            break;
        case 'restock':
            include 'pages/stok/restock.php';
            break;
        case 'riwayatStok':
            include 'pages/stok/riwayat.php';
            break;
        // KENDARAAN (produk) - tambah & ubah
        case 'tambahKendaraan':
            include 'pages/kendaraan/tambah.php';
            break;
        case 'updateKendaraan':
            include 'pages/kendaraan/update.php';
            break;
        // PENYEWAAN (transaksi) - detail
        case 'detailTransaksi':
            include 'pages/penyewaan/detail.php';
            break;
        // PETUGAS (user)
        case 'petugas':
            include 'pages/petugas/petugas.php';
            break;
        case 'tambahPetugas':
            include 'pages/petugas/tambah.php';
            break;
        case 'updatePetugas':
            include 'pages/petugas/update.php';
            break;
        // halaman yang tidak dikenal -> kembali ke dashboard
        default:
            include 'pages/dashboard.php';
    }
    ?>
    <!-- MAIN CONTENT -->

    <!-- Bootstrap JS -->
    <?php include 'partials/script.php' ?>



</body>

</html>