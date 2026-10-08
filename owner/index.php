<?php require_once 'function/guard.php'; ?>
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

    <!-- MAIN CONTENT -->
    <!-- untuk menamnpilkkan hanya di bagian content saja -->
    <?php
    $page = isset($_GET['page']) ? $_GET['page'] : "dashboard";
    switch ($page) {
        // untuk meberi nama halaman yg akan di buka
        // punya Dashboard
        case 'dashboard':
            // mau di isi dengan pages apa
            include 'pages/dashboard.php';
            // biar berenti agar gamasuk ke halaman sebelahnya
            // otomatis pindah halaman selanjutnya
            break;
        // untuk mengarahkan halam awal yg akan di buka
        case 'pages/dashboard.php':
            include 'pages/dashboard.php';
            break;
        default:
            //   kategori
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
        case 'peminjaman':
            include 'pages/peminjam/peminjam.php';
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
    }
    ?>
    <!-- MAIN CONTENT -->

    <!-- Bootstrap JS -->
    <?php include 'partials/script.php' ?>



</body>

</html>