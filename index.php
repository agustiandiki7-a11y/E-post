<?php
// Halaman awal E-POS (landing page) - memakai template InApp yang sama dengan dashboard.
// Kalau user sudah login, tombol utama langsung membuka dashboard sesuai role.
session_start();

// Semua tombol lewat login/index.php: kalau sudah login, halaman itu otomatis
// meneruskan ke dashboard sesuai role (satu tempat saja yang tahu nama folder).
$tujuan = 'login/index.php';
$labelTombol = isset($_SESSION['user']) ? 'Buka Dashboard' : 'Masuk ke Sistem';

$tpl = 'owner/template/src/assets';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <title>E-POS - Point of Sales Kasir Multi-Cabang</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Sistem kasir multi-cabang: transaksi real-time, stok per cabang, diskon, struk, dan laporan terpusat.">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= $tpl ?>/images/favicon_io/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= $tpl ?>/images/favicon_io/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= $tpl ?>/images/favicon_io/favicon-16x16.png">
    <link rel="stylesheet" crossorigin href="<?= $tpl ?>/css/main.css">
    <!-- Font ikon Tabler: main.css menunjuk ke /inapp/assets/... yang tidak ada, jadi dimuat dari CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.35.0/dist/tabler-icons.min.css">
    <style>
        html { scroll-behavior: smooth; }

        /* struk contoh di hero */
        .receipt-stage { position: relative; display: grid; place-items: center; padding: 24px 0; }
        .receipt-stage::before {
            content: ""; position: absolute; inset: 4% 10%;
            background: var(--bs-primary); opacity: .12; border-radius: 42% 58% 55% 45% / 50% 45% 55% 50%;
        }
        .receipt {
            position: relative; width: 300px; background: #fff; padding: 26px 24px 30px; border-radius: 6px;
            box-shadow: 0 24px 50px rgba(23, 23, 23, .15); transform: rotate(2.5deg);
            font-family: Consolas, "Courier New", monospace; font-size: .82rem; color: #171717;
        }
        .receipt::after {
            content: ""; position: absolute; left: 0; right: 0; bottom: -10px; height: 10px;
            background: linear-gradient(-45deg, transparent 7px, #fff 0), linear-gradient(45deg, transparent 7px, #fff 0);
            background-size: 14px 14px; background-position: left bottom;
        }
        .receipt h4 { text-align: center; font-size: 1rem; letter-spacing: 2px; margin-bottom: 0; font-family: inherit; }
        .receipt .sub { text-align: center; color: #737373; margin-bottom: 14px; }
        .receipt .r { display: flex; justify-content: space-between; padding: 3px 0; }
        .receipt hr { border: none; border-top: 1px dashed #a3a3a3; margin: 10px 0; opacity: 1; }
        .receipt .total { font-weight: 700; font-size: .95rem; color: var(--bs-primary); }
        .receipt .thanks { text-align: center; margin-top: 14px; color: #737373; }

        .step-no { width: 40px; height: 40px; border-radius: 50%; background: var(--bs-primary); color: #fff; font-weight: 700; display: grid; place-items: center; margin-bottom: 16px; }
        .card-hover { transition: transform .2s, box-shadow .2s; }
        .card-hover:hover { transform: translateY(-4px); box-shadow: 0 14px 30px rgba(23, 23, 23, .1); }
    </style>
</head>

<body>

    <!-- NAVBAR -->
    <nav class="navbar bg-white border-bottom sticky-top">
        <div class="container py-1">
            <a href="#" class="navbar-brand d-inline-flex align-items-center">
                <img src="<?= $tpl ?>/images/logo-icon.svg" alt="" width="26">
                <span class="ms-2 fw-bold fs-5">E-POS</span>
            </a>
            <div class="d-flex align-items-center gap-4">
                <a href="#fitur" class="text-secondary d-none d-md-inline">Fitur</a>
                <a href="#peran" class="text-secondary d-none d-md-inline">Peran</a>
                <a href="#alur" class="text-secondary d-none d-md-inline">Alur Kerja</a>
                <a href="<?= $tujuan ?>" class="btn btn-primary btn-sm px-3"><?= $labelTombol ?></a>
            </div>
        </div>
    </nav>

    <!-- HERO -->
    <section class="py-6 py-md-10">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 mb-3">Point of Sales Multi-Cabang</span>
                    <h1 class="display-5 fw-bold mb-3">Kasir cepat, laporan <span class="text-primary">terpusat</span> untuk semua cabang.</h1>
                    <p class="lead text-secondary mb-4">Catat penjualan secara real-time, atur harga dan stok tiap cabang, berikan potongan, cetak struk, dan pantau seluruh cabang dari satu dashboard.</p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="<?= $tujuan ?>" class="btn btn-primary btn-lg"><?= $labelTombol ?> <i class="ti ti-arrow-right ms-1"></i></a>
                        <a href="#peran" class="btn btn-outline-secondary btn-lg">Lihat Peran</a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="receipt-stage" aria-hidden="true">
                        <div class="receipt">
                            <h4>E-POS</h4>
                            <div class="sub">Cabang Ungaran</div>
                            <div class="r"><span>INV-20261008-0001</span></div>
                            <hr>
                            <div class="r"><span>Indomie Goreng x4</span><span>14.000</span></div>
                            <div class="r"><span>Aqua 600ml x2</span><span>8.000</span></div>
                            <div class="r"><span>Roti Tawar x1</span><span>16.000</span></div>
                            <hr>
                            <div class="r"><span>Subtotal</span><span>38.000</span></div>
                            <div class="r"><span>Diskon</span><span>-3.000</span></div>
                            <div class="r total"><span>TOTAL</span><span>35.000</span></div>
                            <div class="thanks">Terima kasih!</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FITUR -->
    <section id="fitur" class="pb-6 pb-md-10">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-6 col-xl-3">
                    <div class="card h-100 card-hover"><div class="card-body p-4">
                        <div class="icon-shape icon-lg rounded-3 bg-primary-subtle text-primary mb-3"><i class="ti ti-building-store fs-3"></i></div>
                        <h5 class="mb-1">Multi Cabang</h5>
                        <p class="text-secondary mb-0 small">Produk, harga, stok, dan petugas dikelola per cabang dalam satu sistem.</p>
                    </div></div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card h-100 card-hover"><div class="card-body p-4">
                        <div class="icon-shape icon-lg rounded-3 bg-primary-subtle text-primary mb-3"><i class="ti ti-shopping-cart fs-3"></i></div>
                        <h5 class="mb-1">Transaksi Real-time</h5>
                        <p class="text-secondary mb-0 small">Penjualan tersimpan langsung dan stok berkurang otomatis.</p>
                    </div></div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card h-100 card-hover"><div class="card-body p-4">
                        <div class="icon-shape icon-lg rounded-3 bg-primary-subtle text-primary mb-3"><i class="ti ti-discount-2 fs-3"></i></div>
                        <h5 class="mb-1">Potongan Harga</h5>
                        <p class="text-secondary mb-0 small">Kasir bisa memberi diskon per transaksi, total langsung terhitung.</p>
                    </div></div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card h-100 card-hover"><div class="card-body p-4">
                        <div class="icon-shape icon-lg rounded-3 bg-primary-subtle text-primary mb-3"><i class="ti ti-printer fs-3"></i></div>
                        <h5 class="mb-1">Cetak Struk</h5>
                        <p class="text-secondary mb-0 small">Struk pelanggan siap cetak dengan nomor invoice otomatis.</p>
                    </div></div>
                </div>
            </div>
        </div>
    </section>

    <!-- PERAN -->
    <section id="peran" class="py-6 py-md-10 bg-body-tertiary">
        <div class="container">
            <div class="text-center mx-auto mb-5" style="max-width: 600px;">
                <h2 class="fw-bold mb-2">Satu sistem, tiga peran</h2>
                <p class="text-secondary mb-0">Setiap pengguna hanya melihat menu yang dibutuhkan sesuai tugasnya.</p>
            </div>
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="card h-100 card-hover"><div class="card-body p-4">
                        <div class="icon-shape icon-lg rounded-3 bg-primary text-white mb-3"><i class="ti ti-home fs-3"></i></div>
                        <h4 class="mb-0">Admin / Owner</h4>
                        <p class="text-secondary small mb-3">Pemilik usaha</p>
                        <ul class="list-unstyled mb-0">
                            <li class="py-2 border-top"><i class="ti ti-check text-primary me-2"></i>Kelola master cabang</li>
                            <li class="py-2 border-top"><i class="ti ti-check text-primary me-2"></i>Kelola produk dan harga jual</li>
                            <li class="py-2 border-top"><i class="ti ti-check text-primary me-2"></i>Atur akun kasir dan manajer</li>
                            <li class="py-2 border-top"><i class="ti ti-check text-primary me-2"></i>Laporan agregat semua cabang</li>
                        </ul>
                    </div></div>
                </div>
                <div class="col-lg-4">
                    <div class="card h-100 card-hover"><div class="card-body p-4">
                        <div class="icon-shape icon-lg rounded-3 bg-primary text-white mb-3"><i class="ti ti-shopping-cart fs-3"></i></div>
                        <h4 class="mb-0">Kasir</h4>
                        <p class="text-secondary small mb-3">Petugas penjualan</p>
                        <ul class="list-unstyled mb-0">
                            <li class="py-2 border-top"><i class="ti ti-check text-primary me-2"></i>Transaksi penjualan real-time</li>
                            <li class="py-2 border-top"><i class="ti ti-check text-primary me-2"></i>Terapkan potongan / diskon</li>
                            <li class="py-2 border-top"><i class="ti ti-check text-primary me-2"></i>Stok berkurang otomatis</li>
                            <li class="py-2 border-top"><i class="ti ti-check text-primary me-2"></i>Cetak struk pelanggan</li>
                        </ul>
                    </div></div>
                </div>
                <div class="col-lg-4">
                    <div class="card h-100 card-hover"><div class="card-body p-4">
                        <div class="icon-shape icon-lg rounded-3 bg-primary text-white mb-3"><i class="ti ti-shield-check fs-3"></i></div>
                        <h4 class="mb-0">Manajer Cabang</h4>
                        <p class="text-secondary small mb-3">Pengawas cabang</p>
                        <ul class="list-unstyled mb-0">
                            <li class="py-2 border-top"><i class="ti ti-check text-primary me-2"></i>Pantau penutupan kasir (shift)</li>
                            <li class="py-2 border-top"><i class="ti ti-check text-primary me-2"></i>Setujui void transaksi</li>
                            <li class="py-2 border-top"><i class="ti ti-check text-primary me-2"></i>Cek penjualan cabangnya</li>
                            <li class="py-2 border-top"><i class="ti ti-check text-primary me-2"></i>Data terbatas pada cabang sendiri</li>
                        </ul>
                    </div></div>
                </div>
            </div>
        </div>
    </section>

    <!-- ALUR KERJA -->
    <section id="alur" class="py-6 py-md-10 bg-dark text-white">
        <div class="container">
            <div class="text-center mx-auto mb-5" style="max-width: 600px;">
                <h2 class="fw-bold text-white mb-2">Alur kerja sederhana</h2>
                <p class="text-white-50 mb-0">Dari pengaturan awal sampai laporan, semuanya terhubung.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-xl-3">
                    <div class="step-no">1</div>
                    <h5 class="text-white">Siapkan data</h5>
                    <p class="text-white-50 small mb-0">Owner membuat cabang, produk, harga, dan akun petugas.</p>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="step-no">2</div>
                    <h5 class="text-white">Jual di kasir</h5>
                    <p class="text-white-50 small mb-0">Kasir memilih produk, memberi diskon, lalu menyimpan transaksi.</p>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="step-no">3</div>
                    <h5 class="text-white">Awasi cabang</h5>
                    <p class="text-white-50 small mb-0">Manajer memeriksa shift kasir dan menyetujui void bila perlu.</p>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="step-no">4</div>
                    <h5 class="text-white">Baca laporan</h5>
                    <p class="text-white-50 small mb-0">Owner melihat total penjualan per cabang dan per periode.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- AJAKAN MASUK -->
    <section class="py-6 py-md-10">
        <div class="container">
            <div class="card border-0 bg-primary-subtle">
                <div class="card-body text-center py-6 px-4">
                    <h2 class="fw-bold mb-2">Siap mulai berjualan?</h2>
                    <p class="text-secondary mb-4">Masuk dengan akun yang diberikan oleh owner.</p>
                    <a href="<?= $tujuan ?>" class="btn btn-primary btn-lg"><?= $labelTombol ?> <i class="ti ti-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="border-top py-4">
        <div class="container text-center text-secondary small">
            Copyright © <?= date('Y') ?> E-POS Multi-Cabang &bull; Template InApp by CodesCandy / ThemeWagon
        </div>
    </footer>

</body>

</html>
