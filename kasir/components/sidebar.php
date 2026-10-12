<aside id="sidebar" class="sidebar">
    <div class="logo-area">
        <a href="index.php?page=dashboard" class="d-inline-flex"><img src="template/src/assets/images/logo-icon.svg" alt="" width="24">
            <span class="logo-text ms-2 fw-bold fs-5">E-POS</span>
        </a>
    </div>
    <ul class="nav flex-column">
        <li class="px-4 py-2"><small class="nav-text">Kasir</small></li>
        <li><a class="nav-link <?= $page == 'dashboard' ? 'active' : '' ?>" href="index.php?page=dashboard"><i class="ti ti-home"></i><span
                    class="nav-text">Dashboard</span></a></li>
        <li><a class="nav-link <?= $page == 'baru' ? 'active' : '' ?>" href="index.php?page=baru"><i class="ti ti-shopping-cart"></i><span
                    class="nav-text">Transaksi Baru</span></a></li>
        <li><a class="nav-link <?= $page == 'riwayat' ? 'active' : '' ?>" href="index.php?page=riwayat"><i class="ti ti-receipt"></i><span
                    class="nav-text">Riwayat Transaksi</span></a></li>
        <li><a class="nav-link <?= $page == 'produk' ? 'active' : '' ?>" href="index.php?page=produk"><i class="ti ti-box-seam"></i><span
                    class="nav-text">Produk &amp; Stok</span></a></li>

        <li class="px-4 pt-4 pb-2"><small class="nav-text">Akun</small></li>
        <li><a class="nav-link" href="../login/logout.php"><i class="ti ti-logout"></i><span class="nav-text">Logout</span></a></li>
    </ul>
</aside>
