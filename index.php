<?php
// Halaman awal Nexora POS (landing page publik) - memakai template InApp yang sama dengan dashboard.
// Dibuka lewat http://e-post.test/ (Laragon: folder "E-post" otomatis jadi alamat e-post.test).
// Kalau user sudah login, tombol utama langsung membuka dashboard sesuai role.
session_start();
header('Cache-Control: no-store, no-cache, must-revalidate');
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');

// Semua tombol lewat login/index.php: kalau sudah login, halaman itu otomatis
// meneruskan ke dashboard sesuai role (satu tempat saja yang tahu nama folder).
$tujuan = 'login/index.php';
$sudahLogin = isset($_SESSION['user']);
$labelTombol = $sudahLogin ? 'Buka Dashboard' : 'Login';

$tpl = 'owner/template/src/assets';

// Ikon garis (inline SVG) supaya selalu tampil walau internet mati
function ic($nama, $ukuran = 22)
{
    static $p = [
        'store'   => 'M3 9l1-5h16l1 5M4 9v11h16V9M9 20v-6h6v6',
        'cart'    => 'M6 6h15l-1.5 9h-12zM6 6L5 3H2M9 20.5h.01M18 20.5h.01',
        'tag'     => 'M20.6 13.4l-7.2 7.2a2 2 0 0 1-2.8 0L3 13V3h10l7.6 7.6a2 2 0 0 1 0 2.8zM7.5 7.5h.01',
        'printer' => 'M6 9V3h12v6M6 18H4a1 1 0 0 1-1-1v-6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v6a1 1 0 0 1-1 1h-2M6 14h12v7H6z',
        'box'     => 'M21 8l-9-5-9 5v8l9 5 9-5zM3 8l9 5 9-5M12 13v8',
        'users'   => 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8M22 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8',
        'chart'   => 'M3 3v18h18M7 15v3M12 9v9M17 5v13',
        'receipt' => 'M4 2v20l3-2 2 2 3-2 3 2 2-2 3 2V2l-3 2-2-2-3 2-3-2-2 2zM8 8h8M8 12h8',
        'shield'  => 'M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10zM9 12l2 2 4-4',
        'check'   => 'M5 12l5 5L20 7',
        'arrow'   => 'M5 12h14M13 6l6 6-6 6',
        'lock'    => 'M5 11h14v10H5zM8 11V7a4 4 0 0 1 8 0v4',
        'undo'    => 'M9 14L4 9l5-5M4 9h10a6 6 0 0 1 0 12h-3',
    ];
    $d = isset($p[$nama]) ? $p[$nama] : $p['check'];
    return '<svg width="' . (int) $ukuran . '" height="' . (int) $ukuran . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="' . $d . '"/></svg>';
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <title>Nexora POS - Sistem Penjualan &amp; Kasir Multi-Cabang</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Nexora POS: sistem kasir multi-cabang dengan transaksi real-time, stok per cabang, diskon, struk, kontrol void, dan laporan terpusat.">
    <meta name="theme-color" content="#4942DD">
    <link rel="icon" type="image/svg+xml" href="<?= $tpl ?>/images/logo-icon.svg">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= $tpl ?>/images/favicon_io/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= $tpl ?>/images/favicon_io/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= $tpl ?>/images/favicon_io/favicon-16x16.png">
    <link rel="stylesheet" crossorigin href="<?= $tpl ?>/css/main.css">
    <style>
        html { scroll-behavior: smooth; }
        body { overflow-x: hidden; }
        section[id] { scroll-margin-top: 72px; }

        .nav-blur { background: rgba(255,255,255,.88); backdrop-filter: saturate(1.4) blur(10px); }
        .nav-link-x { color: #525252; text-decoration: none; padding: 6px 4px; }
        .nav-link-x:hover { color: var(--bs-primary); }

        /* ---------- HERO ---------- */
        .hero { position: relative; padding: 72px 0 72px; background:
            radial-gradient(900px 420px at 85% -10%, rgba(73,66,221,.14), transparent 60%),
            radial-gradient(600px 300px at 0% 100%, rgba(48,44,77,.06), transparent 60%); }
        .hero h1 { letter-spacing: -.02em; line-height: 1.1; }
        .trust { display: flex; flex-wrap: wrap; gap: 8px 22px; margin-top: 28px; color: #525252; font-size: .9rem; }
        .trust span { display: inline-flex; align-items: center; gap: 7px; }
        .trust svg { color: #16a34a; }

        /* mockup dashboard di hero */
        .mock { position: relative; border-radius: 16px; background: #fff; border: 1px solid #e5e5e5; box-shadow: 0 30px 60px rgba(23,23,23,.14); overflow: hidden; }
        .mock-bar { display: flex; align-items: center; gap: 6px; padding: 10px 14px; background: #fafafa; border-bottom: 1px solid #eee; }
        .mock-bar i { width: 10px; height: 10px; border-radius: 50%; background: #d4d4d4; display: inline-block; }
        .mock-bar small { margin-left: 10px; color: #a3a3a3; }
        .mock-body { display: grid; grid-template-columns: 64px 1fr; min-height: 300px; }
        .mock-side { background: #fff; border-right: 1px solid #eee; padding: 14px 0; display: flex; flex-direction: column; align-items: center; gap: 12px; }
        .mock-side .lg { width: 28px; height: 28px; border-radius: 8px; background: var(--bs-primary); }
        .mock-side .it { width: 26px; height: 6px; border-radius: 4px; background: #e5e5e5; }
        .mock-side .it.on { background: var(--bs-primary); opacity: .6; }
        .mock-main { padding: 16px; background: #fafafa; }
        .mock-kpi { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 12px; }
        .mock-kpi div { background: #fff; border: 1px solid #eee; border-radius: 10px; padding: 10px 12px; }
        .mock-kpi small { display: block; color: #a3a3a3; font-size: .68rem; }
        .mock-kpi b { font-size: .98rem; }
        .mock-chart { background: #fff; border: 1px solid #eee; border-radius: 10px; padding: 12px; display: flex; align-items: flex-end; gap: 9px; height: 120px; }
        .mock-chart span { flex: 1; border-radius: 5px 5px 0 0; background: var(--bs-primary); opacity: .25; }
        .mock-chart span.hi { opacity: 1; }
        .mock-note { position: absolute; right: -14px; bottom: -44px; }
        .receipt { width: 210px; background: #fff; border-radius: 6px; padding: 14px 14px 16px; box-shadow: 0 18px 40px rgba(23,23,23,.2); transform: rotate(3deg); font: 11.5px/1.5 Consolas, "Courier New", monospace; color: #171717; border: 1px solid #eee; }
        .receipt .c { text-align: center; font-weight: 700; letter-spacing: 1.5px; }
        .receipt .r { display: flex; justify-content: space-between; }
        .receipt hr { border: 0; border-top: 1px dashed #a3a3a3; margin: 7px 0; opacity: 1; }
        .receipt .t { color: var(--bs-primary); font-weight: 700; }
        @media (max-width: 991px) { .mock-note { position: static; margin-top: 16px; display: flex; justify-content: center; } }

        /* ---------- UMUM ---------- */
        .kicker { display: inline-block; font-size: .78rem; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; color: var(--bs-primary); margin-bottom: 8px; }
        .ico { width: 48px; height: 48px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; background: var(--bs-primary-bg-subtle, #dddcf9); color: var(--bs-primary); margin-bottom: 14px; }
        .ico.solid { background: var(--bs-primary); color: #fff; }
        .lift { transition: transform .2s, box-shadow .2s, border-color .2s; }
        .lift:hover { transform: translateY(-4px); box-shadow: 0 16px 34px rgba(23,23,23,.1); border-color: rgba(73,66,221,.45); }

        .strip { border-top: 1px solid #eee; border-bottom: 1px solid #eee; background: #fff; }
        .strip .it { display: flex; align-items: center; gap: 12px; padding: 18px 8px; }
        .strip .it b { display: block; font-size: .95rem; }
        .strip .it small { color: #737373; }
        .strip .it .ico { margin: 0; width: 40px; height: 40px; }

        /* alur void */
        .flow { position: relative; display: grid; gap: 14px; }
        .flow .st { display: flex; gap: 14px; align-items: flex-start; background: #fff; border: 1px solid #e5e5e5; border-radius: 14px; padding: 16px 18px; }
        .flow .no { flex: 0 0 34px; height: 34px; border-radius: 50%; background: var(--bs-primary); color: #fff; font-weight: 700; display: grid; place-items: center; }
        .flow h6 { margin: 0 0 2px; }
        .flow p { margin: 0; color: #737373; font-size: .9rem; }

        .dark-sec { background: #302C4D; color: #fff; }
        .dark-sec .step-no { width: 42px; height: 42px; border-radius: 50%; background: var(--bs-primary); color: #fff; font-weight: 700; display: grid; place-items: center; margin-bottom: 16px; }
        .dark-sec p { color: rgba(255,255,255,.65); }

        details.faq { background: #fff; border: 1px solid #e5e5e5; border-radius: 12px; padding: 0; margin-bottom: 10px; }
        details.faq summary { cursor: pointer; padding: 16px 20px; font-weight: 600; list-style: none; display: flex; justify-content: space-between; align-items: center; gap: 12px; }
        details.faq summary::-webkit-details-marker { display: none; }
        details.faq summary::after { content: "+"; font-size: 1.4rem; line-height: 1; color: var(--bs-primary); transition: transform .2s; }
        details.faq[open] summary::after { transform: rotate(45deg); }
        details.faq p { padding: 0 20px 18px; margin: 0; color: #525252; }

        .cta { background: linear-gradient(135deg, #4942dd 0%, #1d15b9 100%); color: #fff; border-radius: 20px; position: relative; overflow: hidden; }
        .cta::before { content: ""; position: absolute; width: 320px; height: 320px; border-radius: 50%; background: rgba(255,255,255,.1); right: -80px; top: -120px; }
        .cta > * { position: relative; }

        footer.site { background: #171717; color: #a3a3a3; }
        footer.site h6 { color: #fff; }
        footer.site a { color: #a3a3a3; text-decoration: none; }
        footer.site a:hover { color: #fff; }

        .reveal { opacity: 0; transform: translateY(18px); transition: opacity .6s ease, transform .6s ease; }
        .reveal.in { opacity: 1; transform: none; }
        @media (prefers-reduced-motion: reduce) { .reveal { opacity: 1; transform: none; transition: none; } html { scroll-behavior: auto; } }
    </style>
    <noscript><style>.reveal { opacity: 1 !important; transform: none !important; }</style></noscript>
</head>

<body>

    <!-- NAVBAR -->
    <nav class="navbar nav-blur border-bottom sticky-top">
        <div class="container py-1">
            <a href="#atas" class="navbar-brand d-inline-flex align-items-center">
                <img src="<?= $tpl ?>/images/logo-icon.svg" alt="" width="30" height="30">
                <span class="ms-2 fw-bold fs-5">Nexora POS</span>
            </a>
            <div class="d-flex align-items-center gap-3 gap-md-4">
                <a href="#fitur" class="nav-link-x d-none d-md-inline">Fitur</a>
                <a href="#peran" class="nav-link-x d-none d-md-inline">Peran</a>
                <a href="#void" class="nav-link-x d-none d-md-inline">Kontrol Void</a>
                <a href="#faq" class="nav-link-x d-none d-md-inline">FAQ</a>
                <a href="<?= $tujuan ?>" class="btn btn-primary btn-sm px-3"><?= $labelTombol ?></a>
            </div>
        </div>
    </nav>

    <!-- HERO -->
    <header id="atas" class="hero">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 mb-3">Sistem kasir multi-cabang</span>
                    <h1 class="display-5 fw-bold mb-3">Kasir cepat, laporan <span class="text-primary">terpusat</span> untuk semua cabang.</h1>
                    <p class="lead text-secondary mb-4">Nexora POS membantu bisnis dengan banyak cabang mencatat penjualan, mengatur harga dan stok, mencetak struk, dan memantau semua cabang dari satu dashboard.</p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="<?= $tujuan ?>" class="btn btn-primary btn-lg"><?= $labelTombol ?> <?= ic('arrow', 18) ?></a>
                        <a href="#fitur" class="btn btn-outline-secondary btn-lg">Lihat Fitur</a>
                    </div>
                    <div class="trust">
                        <span><?= ic('check', 16) ?> Stok per cabang</span>
                        <span><?= ic('check', 16) ?> Struk otomatis</span>
                        <span><?= ic('check', 16) ?> Void dengan persetujuan</span>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="position-relative" aria-hidden="true">
                        <div class="mock">
                            <div class="mock-bar"><i></i><i></i><i></i><small>Dashboard Owner &middot; contoh tampilan</small></div>
                            <div class="mock-body">
                                <div class="mock-side"><div class="lg"></div><div class="it on"></div><div class="it"></div><div class="it"></div><div class="it"></div></div>
                                <div class="mock-main">
                                    <div class="mock-kpi">
                                        <div><small>Penjualan</small><b>Rp 4,2 jt</b></div>
                                        <div><small>Transaksi</small><b>86</b></div>
                                        <div><small>Cabang</small><b>3</b></div>
                                    </div>
                                    <div class="mock-chart">
                                        <span style="height:38%"></span><span style="height:55%"></span><span style="height:42%"></span>
                                        <span style="height:70%"></span><span style="height:60%"></span><span class="hi" style="height:92%"></span><span style="height:66%"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mock-note">
                            <div class="receipt">
                                <div class="c">NEXORA POS</div>
                                <div class="r"><span>Cabang Ungaran</span></div>
                                <hr>
                                <div class="r"><span>Indomie Goreng x4</span><span>14.000</span></div>
                                <div class="r"><span>Aqua 600ml x2</span><span>8.000</span></div>
                                <div class="r"><span>Roti Tawar x1</span><span>16.000</span></div>
                                <hr>
                                <div class="r"><span>Diskon</span><span>-3.000</span></div>
                                <div class="r t"><span>TOTAL</span><span>35.000</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- JALUR CEPAT -->
    <section class="strip">
        <div class="container">
            <div class="row">
                <div class="col-6 col-lg-3"><div class="it"><span class="ico"><?= ic('store', 20) ?></span><div><b>Multi cabang</b><small>Satu sistem, banyak toko</small></div></div></div>
                <div class="col-6 col-lg-3"><div class="it"><span class="ico"><?= ic('cart', 20) ?></span><div><b>Real-time</b><small>Stok langsung berkurang</small></div></div></div>
                <div class="col-6 col-lg-3"><div class="it"><span class="ico"><?= ic('shield', 20) ?></span><div><b>Hak akses</b><small>Owner, Manajer, Kasir</small></div></div></div>
                <div class="col-6 col-lg-3"><div class="it"><span class="ico"><?= ic('chart', 20) ?></span><div><b>Laporan</b><small>Per cabang dan periode</small></div></div></div>
            </div>
        </div>
    </section>

    <!-- FITUR -->
    <section id="fitur" class="py-6 py-md-10">
        <div class="container">
            <div class="text-center mx-auto mb-5 reveal" style="max-width: 640px;">
                <span class="kicker">Fitur utama</span>
                <h2 class="fw-bold mb-2">Semua yang dibutuhkan kasir dan pemilik usaha</h2>
                <p class="text-secondary mb-0">Dari transaksi harian sampai laporan seluruh cabang, dalam satu aplikasi.</p>
            </div>
            <div class="row g-4">
                <?php
                $fitur = [
                    ['store',   'Multi Cabang',          'Produk, harga, stok, dan petugas dikelola per cabang dalam satu sistem.'],
                    ['cart',    'Transaksi Real-time',   'Penjualan tersimpan langsung dan stok berkurang otomatis.'],
                    ['tag',     'Potongan Harga',        'Kasir memberi diskon nominal atau persen, total langsung terhitung.'],
                    ['printer', 'Cetak Struk',           'Struk pelanggan siap cetak dengan nomor invoice otomatis.'],
                    ['box',     'Produk, Harga & Stok',  'Kelola katalog, harga jual, dan stok terpisah di setiap cabang.'],
                    ['users',   'Pengguna & Hak Akses',  'Owner membuat akun Manajer dan Kasir, lalu menentukan cabangnya.'],
                    ['chart',   'Laporan & Analitik',    'Penjualan per cabang dan per periode, produk terlaris, dan diskon.'],
                    ['receipt', 'Riwayat Transaksi',     'Semua transaksi tercatat dan bisa dicari berdasarkan tanggal dan invoice.'],
                ];
                foreach ($fitur as $i => $f) { ?>
                    <div class="col-md-6 col-xl-3 reveal" style="transition-delay: <?= ($i % 4) * 70 ?>ms">
                        <div class="card h-100 lift"><div class="card-body p-4">
                            <span class="ico"><?= ic($f[0]) ?></span>
                            <h5 class="mb-1"><?= htmlspecialchars($f[1]) ?></h5>
                            <p class="text-secondary mb-0 small"><?= htmlspecialchars($f[2]) ?></p>
                        </div></div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </section>

    <!-- PERAN -->
    <section id="peran" class="py-6 py-md-10 bg-body-tertiary">
        <div class="container">
            <div class="text-center mx-auto mb-5 reveal" style="max-width: 640px;">
                <span class="kicker">Hak akses</span>
                <h2 class="fw-bold mb-2">Satu sistem, tiga peran</h2>
                <p class="text-secondary mb-0">Setiap pengguna hanya melihat menu dan data yang dibutuhkan sesuai tugasnya.</p>
            </div>
            <div class="row g-4">
                <?php
                $peran = [
                    ['store',  'Admin / Owner',   'Pemilik usaha',   ['Kelola master cabang', 'Kelola produk dan harga jual', 'Atur akun kasir dan manajer', 'Laporan semua cabang']],
                    ['cart',   'Kasir',           'Petugas penjualan', ['Transaksi penjualan real-time', 'Terapkan potongan / diskon', 'Stok berkurang otomatis', 'Cetak struk pelanggan']],
                    ['shield', 'Manajer Cabang',  'Pengawas cabang', ['Pantau penutupan kasir (shift)', 'Setujui atau tolak void', 'Cek penjualan cabangnya', 'Data terbatas pada cabang sendiri']],
                ];
                foreach ($peran as $i => $r) { ?>
                    <div class="col-lg-4 reveal" style="transition-delay: <?= $i * 90 ?>ms">
                        <div class="card h-100 lift"><div class="card-body p-4">
                            <span class="ico solid"><?= ic($r[0]) ?></span>
                            <h4 class="mb-0"><?= htmlspecialchars($r[1]) ?></h4>
                            <p class="text-secondary small mb-3"><?= htmlspecialchars($r[2]) ?></p>
                            <ul class="list-unstyled mb-0">
                                <?php foreach ($r[3] as $li) { ?>
                                    <li class="py-2 border-top d-flex align-items-center gap-2"><span class="text-primary"><?= ic('check', 18) ?></span><?= htmlspecialchars($li) ?></li>
                                <?php } ?>
                            </ul>
                        </div></div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </section>

    <!-- KONTROL VOID -->
    <section id="void" class="py-6 py-md-10">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-5 reveal">
                    <span class="kicker">Kontrol pembatalan</span>
                    <h2 class="fw-bold mb-3">Void transaksi tidak asal hapus</h2>
                    <p class="text-secondary mb-4">Transaksi yang sudah tersimpan tidak dihapus diam-diam. Kasir hanya mengajukan, manajer cabang yang memutuskan, dan stok dikembalikan secara konsisten.</p>
                    <a href="<?= $tujuan ?>" class="btn btn-primary"><?= $labelTombol ?> <?= ic('arrow', 16) ?></a>
                </div>
                <div class="col-lg-7 reveal" style="transition-delay: 90ms">
                    <div class="flow">
                        <div class="st"><span class="no">1</span><div><h6>Kasir mengajukan void</h6><p>Dari riwayat transaksi, dengan konfirmasi sebelum dikirim.</p></div></div>
                        <div class="st"><span class="no">2</span><div><h6>Manajer cabang memeriksa</h6><p>Menyetujui atau menolak pengajuan untuk cabangnya sendiri.</p></div></div>
                        <div class="st"><span class="no">3</span><div><h6>Stok dan laporan ikut sesuai</h6><p>Jika disetujui, stok kembali dan transaksi tidak dihitung di laporan.</p></div></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ALUR KERJA -->
    <section id="alur" class="py-6 py-md-10 dark-sec">
        <div class="container">
            <div class="text-center mx-auto mb-5" style="max-width: 600px;">
                <h2 class="fw-bold text-white mb-2">Alur kerja sederhana</h2>
                <p class="mb-0">Dari pengaturan awal sampai laporan, semuanya terhubung.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-xl-3"><div class="step-no">1</div><h5 class="text-white">Siapkan data</h5><p class="small mb-0">Owner membuat cabang, produk, harga, dan akun petugas.</p></div>
                <div class="col-md-6 col-xl-3"><div class="step-no">2</div><h5 class="text-white">Jual di kasir</h5><p class="small mb-0">Kasir memilih produk, memberi diskon, lalu menyimpan transaksi.</p></div>
                <div class="col-md-6 col-xl-3"><div class="step-no">3</div><h5 class="text-white">Awasi cabang</h5><p class="small mb-0">Manajer memeriksa shift kasir dan memutuskan pengajuan void.</p></div>
                <div class="col-md-6 col-xl-3"><div class="step-no">4</div><h5 class="text-white">Baca laporan</h5><p class="small mb-0">Owner melihat total penjualan per cabang dan per periode.</p></div>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section id="faq" class="py-6 py-md-10">
        <div class="container" style="max-width: 800px;">
            <div class="text-center mb-5 reveal">
                <span class="kicker">FAQ</span>
                <h2 class="fw-bold mb-2">Pertanyaan umum</h2>
            </div>
            <div class="reveal">
                <details class="faq"><summary>Siapa yang bisa membuat akun?</summary><p>Hanya Owner. Owner membuat akun Manajer Cabang dan Kasir, lalu menentukan cabang tempat mereka bertugas.</p></details>
                <details class="faq"><summary>Apakah kasir bisa melihat data cabang lain?</summary><p>Tidak. Kasir dan manajer hanya mengakses data cabangnya sendiri. Pembatasan ini diperiksa di server, bukan hanya menyembunyikan menu.</p></details>
                <details class="faq"><summary>Bagaimana jika transaksi salah?</summary><p>Kasir mengajukan void dari riwayat transaksi. Setelah manajer cabang menyetujui, stok dikembalikan dan transaksi tidak dihitung di laporan.</p></details>
                <details class="faq"><summary>Apakah struk bisa dicetak?</summary><p>Ya. Setiap transaksi punya struk dengan nomor invoice otomatis yang bisa dicetak langsung dari browser.</p></details>
            </div>
        </div>
    </section>

    <!-- AJAKAN MASUK -->
    <section class="pb-6 pb-md-10">
        <div class="container">
            <div class="cta text-center py-6 px-4">
                <h2 class="fw-bold text-white mb-2">Siap mulai berjualan?</h2>
                <p class="mb-4" style="color: rgba(255,255,255,.85)">Masuk dengan akun yang diberikan oleh owner perusahaan.</p>
                <a href="<?= $tujuan ?>" class="btn btn-light btn-lg text-primary fw-semibold"><?= $labelTombol ?> <?= ic('arrow', 18) ?></a>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="site pt-6 pb-4">
        <div class="container">
            <div class="row g-4 pb-4">
                <div class="col-lg-5">
                    <div class="d-flex align-items-center mb-2">
                        <img src="<?= $tpl ?>/images/logo-icon.svg" alt="" width="28" height="28">
                        <span class="ms-2 fw-bold fs-5 text-white">Nexora POS</span>
                    </div>
                    <p class="small mb-0" style="max-width: 360px">Sistem penjualan dan kasir multi-cabang untuk mencatat transaksi, memantau stok, dan membaca laporan dari satu tempat.</p>
                </div>
                <div class="col-6 col-lg-3 offset-lg-1">
                    <h6 class="mb-3">Halaman</h6>
                    <ul class="list-unstyled small mb-0 d-grid gap-2">
                        <li><a href="#fitur">Fitur</a></li>
                        <li><a href="#peran">Peran</a></li>
                        <li><a href="#void">Kontrol Void</a></li>
                        <li><a href="#faq">FAQ</a></li>
                    </ul>
                </div>
                <div class="col-6 col-lg-3">
                    <h6 class="mb-3">Akses</h6>
                    <ul class="list-unstyled small mb-0 d-grid gap-2">
                        <li><a href="<?= $tujuan ?>"><?= $labelTombol ?></a></li>
                        <li class="d-flex align-items-center gap-2"><?= ic('lock', 14) ?> Area internal dilindungi login</li>
                    </ul>
                </div>
            </div>
            <div class="border-top border-secondary border-opacity-25 pt-3 small d-flex flex-column flex-md-row justify-content-between gap-1">
                <span>Copyright &copy; <?= date('Y') ?> Nexora POS</span>
                <span>Template InApp by CodesCandy / ThemeWagon</span>
            </div>
        </div>
    </footer>

    <script>
        // animasi muncul saat di-scroll (tanpa library)
        (function () {
            var els = document.querySelectorAll('.reveal');
            if (!('IntersectionObserver' in window)) { for (var i = 0; i < els.length; i++) { els[i].classList.add('in'); } return; }
            var io = new IntersectionObserver(function (items) {
                items.forEach(function (it) { if (it.isIntersecting) { it.target.classList.add('in'); io.unobserve(it.target); } });
            }, { threshold: 0.12 });
            els.forEach(function (el) { io.observe(el); });
        })();
    </script>
</body>

</html>
