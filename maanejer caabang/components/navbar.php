<div class="container-fluid nav-bar sticky-top px-0 px-lg-4 py-2 py-lg-0">
    <div class="container">
        <nav class="navbar navbar-expand-lg navbar-light">
            <a href="index.php?page=dashboard" class="navbar-brand p-0">
                <h1 class="display-6 text-primary"><i class="fas fa-cash-register me-3"></i>E-POS</h1>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
                <span class="fa fa-bars"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarCollapse">
                <div class="navbar-nav mx-auto py-0">
                    <a href="index.php?page=dashboard" class="nav-item nav-link <?= $page == 'dashboard' ? 'active' : '' ?>">Dashboard</a>
                    <a href="index.php?page=penjualan" class="nav-item nav-link <?= in_array($page, ['penjualan', 'detail']) ? 'active' : '' ?>">Penjualan</a>
                    <a href="index.php?page=shift" class="nav-item nav-link <?= $page == 'shift' ? 'active' : '' ?>">Shift Kasir</a>
                    <a href="index.php?page=void" class="nav-item nav-link <?= $page == 'void' ? 'active' : '' ?>">Void
                        <?php if ($voidPending > 0) { ?><span class="badge bg-primary rounded-pill ms-1"><?= (int) $voidPending ?></span><?php } ?>
                    </a>
                </div>
                <a href="../login/logout.php" class="btn btn-primary rounded-pill py-2 px-4">Logout</a>
            </div>
        </nav>
    </div>
</div>
