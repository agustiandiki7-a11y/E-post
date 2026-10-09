<nav id="topbar" class="navbar bg-white border-bottom fixed-top topbar px-3">
    <button id="toggleBtn" class="d-none d-lg-inline-flex btn btn-light btn-icon btn-sm ">
        <i class="ti ti-layout-sidebar-left-expand"></i>
    </button>

    <!-- MOBILE -->
    <button id="mobileBtn" class="btn btn-light btn-icon btn-sm d-lg-none me-2">
        <i class="ti ti-layout-sidebar-left-expand"></i>
    </button>
    <div>
        <ul class="list-unstyled d-flex align-items-center mb-0 gap-1">
            <!-- Nama cabang -->
            <li class="me-3 d-none d-md-block text-end lh-sm">
                <div class="small fw-semibold"><?= e($cabang['nama_cabang']) ?></div>
                <div class="small text-secondary"><?= e($cabang['telepon']) ?></div>
            </li>

            <!-- Notifikasi: pengajuan void -->
            <li>
                <a class="position-relative btn-icon btn-sm btn-light btn rounded-circle" data-bs-toggle="dropdown"
                    aria-expanded="false" href="#" role="button">
                    <i class="ti ti-bell"></i>
                    <?php if ($voidPending > 0) { ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger mt-2 ms-n2">
                            <?= (int) $voidPending ?>
                            <span class="visually-hidden">pengajuan void</span>
                        </span>
                    <?php } ?>
                </a>
                <div class="dropdown-menu dropdown-menu-end p-0" style="min-width: 280px;">
                    <ul class="list-unstyled p-0 m-0">
                        <?php if ($voidPending > 0) { ?>
                            <li class="p-3 border-bottom">
                                <p class="mb-0 small fw-semibold">Pengajuan void menunggu</p>
                                <p class="mb-0 small"><?= (int) $voidPending ?> transaksi perlu kamu setujui atau tolak.</p>
                            </li>
                            <li class="px-4 py-3 text-center">
                                <a href="index.php?page=void" class="text-primary">Tinjau sekarang</a>
                            </li>
                        <?php } else { ?>
                            <li class="px-4 py-3 text-center small text-secondary">Tidak ada notifikasi baru.</li>
                        <?php } ?>
                    </ul>
                </div>
            </li>

            <!-- Profil -->
            <li class="ms-3 dropdown">
                <a href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="template/src/assets/images/avatar/avatar-1.jpg" alt="" class="avatar avatar-sm rounded-circle" />
                </a>
                <div class="dropdown-menu dropdown-menu-end p-0" style="min-width: 220px;">
                    <div class="d-flex gap-3 align-items-center border-dashed border-bottom px-3 py-3">
                        <img src="template/src/assets/images/avatar/avatar-1.jpg" alt="" class="avatar avatar-md rounded-circle" />
                        <div>
                            <h4 class="mb-0 small"><?= e($_SESSION['user']['nama']) ?></h4>
                            <p class="mb-0 small"><?= e($_SESSION['user']['email']) ?> &middot; Manajer</p>
                        </div>
                    </div>
                    <div class="p-3 d-flex flex-column gap-1 small lh-lg">
                        <a href="index.php?page=dashboard"><span>Dashboard</span></a>
                        <a href="../login/logout.php" class="text-danger"><span>Logout</span></a>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</nav>
