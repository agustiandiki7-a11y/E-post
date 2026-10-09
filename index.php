<?php
// Halaman awal E-POS (landing page).
// Kalau user sudah login, tombol utama langsung membuka dashboard sesuai role.
session_start();

$tujuan = 'login/index.php';
$labelTombol = 'Masuk ke Sistem';
if (isset($_SESSION['user'])) {
    $role = $_SESSION['user']['role'];
    if ($role === 'admin') {
        $tujuan = 'owner/index.php';
    } elseif ($role === 'kasir') {
        $tujuan = 'kasir/index.php';
    } else {
        $tujuan = 'maanejer%20caabang/index.php';
    }
    $labelTombol = 'Buka Dashboard';
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>E-POS - Point of Sales Kasir Multi-Cabang</title>
    <meta name="description" content="Sistem kasir multi-cabang: transaksi real-time, stok per cabang, diskon, struk, dan laporan terpusat.">
    <style>
        :root {
            --ink: #14221f;
            --muted: #5b6b67;
            --bg: #f6f4ee;
            --card: #ffffff;
            --line: #e3dfd3;
            --brand: #0f6b57;
            --brand-dark: #0a4a3c;
            --accent: #f2a541;
            --radius: 16px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: "Segoe UI", system-ui, -apple-system, Roboto, "Helvetica Neue", Arial, sans-serif;
            color: var(--ink);
            background: var(--bg);
            line-height: 1.6;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .wrap {
            width: min(1120px, 100% - 40px);
            margin-inline: auto;
        }

        /* NAV */
        .nav {
            position: sticky;
            top: 0;
            z-index: 10;
            background: rgba(246, 244, 238, .9);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid var(--line);
        }

        .nav .wrap {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 64px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 800;
            font-size: 1.25rem;
            letter-spacing: .5px;
        }

        .logo-mark {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: var(--brand);
            display: grid;
            place-items: center;
        }

        .logo-mark svg {
            width: 20px;
            height: 20px;
            stroke: #fff;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 28px;
            font-size: .95rem;
            color: var(--muted);
        }

        .nav-links a:hover {
            color: var(--brand);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 22px;
            border-radius: 999px;
            font-weight: 600;
            font-size: .95rem;
            border: 2px solid transparent;
            transition: transform .15s, background .15s, box-shadow .15s;
            cursor: pointer;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .btn-primary {
            background: var(--brand);
            color: #fff;
            box-shadow: 0 6px 16px rgba(15, 107, 87, .28);
        }

        .btn-primary:hover {
            background: var(--brand-dark);
        }

        .btn-ghost {
            border-color: var(--line);
            background: #fff;
        }

        .btn-ghost:hover {
            border-color: var(--brand);
            color: var(--brand);
        }

        .btn-sm {
            padding: 9px 18px;
            font-size: .88rem;
        }

        /* HERO */
        .hero {
            padding: 72px 0 56px;
        }

        .hero .wrap {
            display: grid;
            grid-template-columns: 1.1fr .9fr;
            gap: 48px;
            align-items: center;
        }

        .tag {
            display: inline-block;
            background: #fff;
            border: 1px solid var(--line);
            padding: 6px 14px;
            border-radius: 999px;
            font-size: .82rem;
            color: var(--brand);
            font-weight: 600;
            margin-bottom: 20px;
        }

        h1 {
            font-size: clamp(2.2rem, 4.6vw, 3.6rem);
            line-height: 1.1;
            letter-spacing: -1px;
            margin-bottom: 20px;
        }

        h1 span {
            color: var(--brand);
        }

        .lead {
            font-size: 1.1rem;
            color: var(--muted);
            max-width: 520px;
            margin-bottom: 32px;
        }

        .cta {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        /* kartu struk di hero */
        .receipt-stage {
            position: relative;
            display: grid;
            place-items: center;
            padding: 20px;
        }

        .receipt-stage::before {
            content: "";
            position: absolute;
            inset: 6% 8%;
            background: var(--accent);
            opacity: .22;
            border-radius: 40% 60% 55% 45% / 50% 45% 55% 50%;
        }

        .receipt {
            position: relative;
            width: 300px;
            background: #fff;
            padding: 26px 24px 30px;
            border-radius: 6px;
            box-shadow: 0 24px 50px rgba(20, 34, 31, .16);
            transform: rotate(2.5deg);
            font-family: "Consolas", "Courier New", monospace;
            font-size: .82rem;
        }

        .receipt::after {
            content: "";
            position: absolute;
            left: 0;
            right: 0;
            bottom: -10px;
            height: 10px;
            background: linear-gradient(-45deg, transparent 7px, #fff 0), linear-gradient(45deg, transparent 7px, #fff 0);
            background-size: 14px 14px;
            background-position: left bottom;
        }

        .receipt h4 {
            text-align: center;
            font-size: 1rem;
            letter-spacing: 2px;
        }

        .receipt .sub {
            text-align: center;
            color: var(--muted);
            margin-bottom: 14px;
        }

        .receipt .row {
            display: flex;
            justify-content: space-between;
            padding: 3px 0;
        }

        .receipt hr {
            border: none;
            border-top: 1px dashed #b9b4a4;
            margin: 10px 0;
        }

        .receipt .total {
            font-weight: 700;
            font-size: .95rem;
        }

        .receipt .thanks {
            text-align: center;
            margin-top: 14px;
            color: var(--muted);
        }

        /* STATS */
        .stats {
            padding: 8px 0 24px;
        }

        .stats .wrap {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
        }

        .stat {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            padding: 22px;
            text-align: center;
        }

        .stat b {
            display: block;
            font-size: 1.9rem;
            color: var(--brand);
            line-height: 1.2;
        }

        .stat span {
            color: var(--muted);
            font-size: .9rem;
        }

        /* SECTION */
        section {
            padding: 64px 0;
        }

        .head {
            text-align: center;
            max-width: 600px;
            margin: 0 auto 44px;
        }

        .head h2 {
            font-size: clamp(1.7rem, 3vw, 2.3rem);
            letter-spacing: -.5px;
            margin-bottom: 10px;
        }

        .head p {
            color: var(--muted);
        }

        /* ROLE */
        .roles {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .role {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            padding: 30px 26px;
            transition: transform .2s, box-shadow .2s;
        }

        .role:hover {
            transform: translateY(-5px);
            box-shadow: 0 16px 34px rgba(20, 34, 31, .1);
        }

        .ico {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            margin-bottom: 18px;
            background: #e4f1ed;
        }

        .role:nth-child(2) .ico {
            background: #fdeccf;
        }

        .role:nth-child(3) .ico {
            background: #e6e9f7;
        }

        .ico svg {
            width: 26px;
            height: 26px;
            stroke: var(--brand-dark);
        }

        .role:nth-child(2) .ico svg {
            stroke: #9a5b00;
        }

        .role:nth-child(3) .ico svg {
            stroke: #3b4aa5;
        }

        .role h3 {
            font-size: 1.2rem;
            margin-bottom: 4px;
        }

        .role .who {
            color: var(--muted);
            font-size: .88rem;
            margin-bottom: 16px;
        }

        .role ul {
            list-style: none;
        }

        .role li {
            padding: 7px 0 7px 26px;
            position: relative;
            font-size: .95rem;
            border-top: 1px solid #f0ede3;
        }

        .role li::before {
            content: "";
            position: absolute;
            left: 2px;
            top: 14px;
            width: 12px;
            height: 7px;
            border-left: 2.5px solid var(--brand);
            border-bottom: 2.5px solid var(--brand);
            transform: rotate(-45deg);
        }

        /* ALUR */
        .flow-bg {
            background: var(--brand-dark);
            color: #fff;
        }

        .flow-bg .head p {
            color: #b7d3cb;
        }

        .steps {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            counter-reset: s;
        }

        .step {
            position: relative;
            padding: 26px 22px;
            border: 1px solid rgba(255, 255, 255, .18);
            border-radius: var(--radius);
            background: rgba(255, 255, 255, .05);
        }

        .step::before {
            counter-increment: s;
            content: counter(s);
            display: grid;
            place-items: center;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--accent);
            color: var(--ink);
            font-weight: 800;
            margin-bottom: 14px;
        }

        .step h4 {
            margin-bottom: 6px;
            font-size: 1.05rem;
        }

        .step p {
            color: #b7d3cb;
            font-size: .92rem;
        }

        /* CTA */
        .final {
            text-align: center;
        }

        .final .box {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 24px;
            padding: 54px 24px;
        }

        .final h2 {
            font-size: clamp(1.6rem, 3vw, 2.2rem);
            margin-bottom: 10px;
        }

        .final p {
            color: var(--muted);
            margin-bottom: 26px;
        }

        footer {
            padding: 28px 0 40px;
            color: var(--muted);
            font-size: .88rem;
            text-align: center;
            border-top: 1px solid var(--line);
        }

        @media (max-width: 900px) {
            .hero .wrap {
                grid-template-columns: 1fr;
            }

            .receipt-stage {
                order: -1;
            }

            .roles {
                grid-template-columns: 1fr;
            }

            .steps {
                grid-template-columns: 1fr 1fr;
            }

            .stats .wrap {
                grid-template-columns: 1fr 1fr;
            }

            .nav-links a:not(.btn) {
                display: none;
            }
        }

        @media (max-width: 520px) {
            .steps {
                grid-template-columns: 1fr;
            }

            .hero {
                padding-top: 40px;
            }
        }
    </style>
</head>

<body>

    <!-- NAVBAR -->
    <header class="nav">
        <div class="wrap">
            <a href="#" class="logo">
                <span class="logo-mark">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z" />
                        <path d="M3 6h18" />
                        <path d="M16 10a4 4 0 0 1-8 0" />
                    </svg>
                </span>
                E-POS
            </a>
            <nav class="nav-links">
                <a href="#peran">Peran</a>
                <a href="#alur">Alur Kerja</a>
                <a href="<?= $tujuan ?>" class="btn btn-primary btn-sm"><?= $labelTombol ?></a>
            </nav>
        </div>
    </header>

    <!-- HERO -->
    <section class="hero">
        <div class="wrap">
            <div>
                <span class="tag">Point of Sales Multi-Cabang</span>
                <h1>Kasir cepat, laporan <span>terpusat</span> untuk semua cabang.</h1>
                <p class="lead">Catat penjualan secara real-time, atur harga dan stok tiap cabang, berikan potongan, cetak struk, dan pantau seluruh cabang dari satu dashboard.</p>
                <div class="cta">
                    <a href="<?= $tujuan ?>" class="btn btn-primary"><?= $labelTombol ?></a>
                    <a href="#peran" class="btn btn-ghost">Lihat Fitur</a>
                </div>
            </div>
            <div class="receipt-stage" aria-hidden="true">
                <div class="receipt">
                    <h4>E-POS</h4>
                    <div class="sub">Cabang Ungaran</div>
                    <div class="row"><span>INV-20261008-0001</span></div>
                    <hr>
                    <div class="row"><span>Indomie Goreng x4</span><span>14.000</span></div>
                    <div class="row"><span>Aqua 600ml x2</span><span>8.000</span></div>
                    <div class="row"><span>Roti Tawar x1</span><span>16.000</span></div>
                    <hr>
                    <div class="row"><span>Subtotal</span><span>38.000</span></div>
                    <div class="row"><span>Diskon</span><span>-3.000</span></div>
                    <div class="row total"><span>TOTAL</span><span>35.000</span></div>
                    <div class="thanks">Terima kasih!</div>
                </div>
            </div>
        </div>
    </section>

    <!-- STATS -->
    <div class="stats">
        <div class="wrap">
            <div class="stat"><b>3</b><span>Peran pengguna</span></div>
            <div class="stat"><b>Multi</b><span>Cabang dalam satu sistem</span></div>
            <div class="stat"><b>Real-time</b><span>Transaksi &amp; stok</span></div>
            <div class="stat"><b>Struk</b><span>Siap cetak otomatis</span></div>
        </div>
    </div>

    <!-- PERAN -->
    <section id="peran">
        <div class="wrap">
            <div class="head">
                <h2>Satu sistem, tiga peran</h2>
                <p>Setiap pengguna hanya melihat menu yang dibutuhkan sesuai tugasnya.</p>
            </div>
            <div class="roles">
                <article class="role">
                    <div class="ico"><svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 21h18" />
                            <path d="M5 21V7l7-4 7 4v14" />
                            <path d="M9 21v-6h6v6" />
                        </svg></div>
                    <h3>Admin / Owner</h3>
                    <p class="who">Pemilik usaha</p>
                    <ul>
                        <li>Kelola master cabang</li>
                        <li>Kelola produk dan harga jual</li>
                        <li>Atur akun kasir dan manajer</li>
                        <li>Laporan agregat semua cabang</li>
                    </ul>
                </article>
                <article class="role">
                    <div class="ico"><svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="9" cy="21" r="1" />
                            <circle cx="20" cy="21" r="1" />
                            <path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6" />
                        </svg></div>
                    <h3>Kasir</h3>
                    <p class="who">Petugas penjualan</p>
                    <ul>
                        <li>Transaksi penjualan real-time</li>
                        <li>Terapkan potongan / diskon</li>
                        <li>Stok berkurang otomatis</li>
                        <li>Cetak struk pelanggan</li>
                    </ul>
                </article>
                <article class="role">
                    <div class="ico"><svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                            <path d="m9 12 2 2 4-4" />
                        </svg></div>
                    <h3>Manajer Cabang</h3>
                    <p class="who">Pengawas cabang</p>
                    <ul>
                        <li>Pantau penutupan kasir (shift)</li>
                        <li>Setujui void transaksi</li>
                        <li>Cek penjualan cabangnya</li>
                        <li>Data terbatas pada cabang sendiri</li>
                    </ul>
                </article>
            </div>
        </div>
    </section>

    <!-- ALUR -->
    <section id="alur" class="flow-bg">
        <div class="wrap">
            <div class="head">
                <h2>Alur kerja sederhana</h2>
                <p>Dari pengaturan awal sampai laporan, semuanya terhubung.</p>
            </div>
            <div class="steps">
                <div class="step">
                    <h4>Siapkan data</h4>
                    <p>Owner membuat cabang, produk, harga, dan akun pengguna.</p>
                </div>
                <div class="step">
                    <h4>Jual di kasir</h4>
                    <p>Kasir memilih produk, memberi diskon, lalu menyimpan transaksi.</p>
                </div>
                <div class="step">
                    <h4>Awasi cabang</h4>
                    <p>Manajer memeriksa shift kasir dan menyetujui void bila perlu.</p>
                </div>
                <div class="step">
                    <h4>Baca laporan</h4>
                    <p>Owner melihat total penjualan per cabang dan per periode.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="final">
        <div class="wrap">
            <div class="box">
                <h2>Siap mulai berjualan?</h2>
                <p>Masuk dengan akun yang diberikan oleh owner.</p>
                <a href="<?= $tujuan ?>" class="btn btn-primary"><?= $labelTombol ?></a>
            </div>
        </div>
    </section>

    <footer>
        <div class="wrap">&copy; <?= date('Y') ?> E-POS &middot; Point of Sales Kasir Multi-Cabang</div>
    </footer>

</body>

</html>