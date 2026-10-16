<?php require_once __DIR__ . '/../../login/shared.php'; ?>
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

            <!-- Transaksi baru (jalan pintas) -->
            <li>
                <a class="btn btn-primary btn-sm" href="index.php?page=baru"><i class="ti ti-shopping-cart"></i> <span class="d-none d-sm-inline">Transaksi Baru</span></a>
            </li>

            <!-- Profil -->
            <li class="ms-3 dropdown">
                <a href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <?= eposAvatar($_SESSION['user']['nama'], $_SESSION['user']['role'], 36) ?>
                </a>
                <div class="dropdown-menu dropdown-menu-end p-0" style="min-width: 220px;">
                    <div class="d-flex gap-3 align-items-center border-dashed border-bottom px-3 py-3">
                        <?= eposAvatar($_SESSION['user']['nama'], $_SESSION['user']['role'], 44) ?>
                        <div>
                            <h4 class="mb-0 small"><?= e($_SESSION['user']['nama']) ?></h4>
                            <p class="mb-0 small"><?= e($_SESSION['user']['email']) ?></p>
<span class="badge bg-primary-subtle text-primary mt-1"><?= e(eposRoleLabel($_SESSION['user']['role'])) ?></span>
                        </div>
                    </div>
                    <div class="p-3 d-flex flex-column gap-1 small lh-lg">
                        <a href="index.php?page=dashboard"><span>Dashboard</span></a>
                        <a href="index.php?page=profil"><span>Profil &amp; Password</span></a>
                        <a href="../login/logout.php" class="text-danger"><span>Logout</span></a>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</nav>
