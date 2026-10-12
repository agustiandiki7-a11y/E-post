<aside id="sidebar" class="sidebar">
    <div class="logo-area">
        <a href="index.php?page=dashboard" class="d-inline-flex"><img src="template/src/assets/images/logo-icon.svg" alt="" width="24">
            <span class="logo-text ms-2 fw-bold fs-5">E-POS</span>
        </a>
    </div>
    <ul class="nav flex-column">
        <li class="px-4 py-2"><small class="nav-text">Cabang</small></li>
        <li><a class="nav-link <?= $page == 'dashboard' ? 'active' : '' ?>" href="index.php?page=dashboard"><i class="ti ti-home"></i><span
                    class="nav-text">Dashboard</span></a></li>
        <li><a class="nav-link <?= in_array($page, ['penjualan', 'detail']) ? 'active' : '' ?>" href="index.php?page=penjualan"><i class="ti ti-receipt"></i><span
                    class="nav-text">Penjualan</span></a></li>

        <li class="px-4 pt-3 pb-2"><small class="nav-text">Pengawasan</small></li>
        <li><a class="nav-link <?= $page == 'shift' ? 'active' : '' ?>" href="index.php?page=shift"><i class="ti ti-calendar"></i><span
                    class="nav-text">Shift Kasir</span></a></li>
        <li><a class="nav-link <?= $page == 'void' ? 'active' : '' ?>" href="index.php?page=void"><i class="ti ti-ban"></i><span
                    class="nav-text">Persetujuan Void</span>
                <?php if ($voidPending > 0) { ?><span class="badge bg-danger ms-auto nav-text"><?= (int) $voidPending ?></span><?php } ?></a></li>

        <li class="px-4 pt-4 pb-2"><small class="nav-text">Akun</small></li>
        <li><a class="nav-link" href="../login/logout.php"><i class="ti ti-logout"></i><span class="nav-text">Logout</span></a></li>
    </ul>
</aside>
