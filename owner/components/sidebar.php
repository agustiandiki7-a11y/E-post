  <?php $menu = isset($_GET['page']) ? $_GET['page'] : 'dashboard'; ?>
  <aside id="sidebar" class="sidebar">
      <div class="logo-area">
          <a href="index.php?page=dashboard" class="d-inline-flex"><img src="template/src/assets/images/logo-icon.svg" alt="" width="24">
              <span class="logo-text ms-2 fw-bold fs-5">E-POS</span>
          </a>
      </div>
      <ul class="nav flex-column">
          <li class="px-4 py-2"><small class="nav-text">Main</small></li>
          <li><a class="nav-link <?= $menu == 'dashboard' ? 'active' : '' ?>" href="index.php?page=dashboard"><i class="ti ti-home"></i><span
                      class="nav-text">Dashboard</span></a></li>
          <li class="px-4 pt-3 pb-2"><small class="nav-text">Master Data</small></li>
          <li><a class="nav-link <?= in_array($menu, ['kategori', 'tambahKategori', 'updateKategori']) ? 'active' : '' ?>" href="index.php?page=kategori"><i class="ti ti-building-store"></i><span
                      class="nav-text">Cabang</span></a></li>
          <li><a class="nav-link <?= in_array($menu, ['kendaraan', 'tambahKendaraan', 'updateKendaraan']) ? 'active' : '' ?>" href="index.php?page=kendaraan"><i class="ti ti-box-seam"></i>
                  <span class="nav-text">Produk &amp; Harga</span></a></li>
          <li><a class="nav-link <?= in_array($menu, ['petugas', 'tambahPetugas', 'updatePetugas']) ? 'active' : '' ?>" href="index.php?page=petugas"><i class="ti ti-users"></i><span class="nav-text">User</span></a></li>
          <li class="px-4 pt-3 pb-2"><small class="nav-text">Penjualan</small></li>
          <li><a class="nav-link <?= in_array($menu, ['penyewaan', 'detailTransaksi']) ? 'active' : '' ?>" href="index.php?page=penyewaan"><i class="ti ti-receipt"></i><span class="nav-text">Transaksi</span></a>
          </li>
          <li><a class="nav-link <?= $menu == 'pembayaran' ? 'active' : '' ?>" href="index.php?page=pembayaran"><i class="ti ti-file-text"></i><span class="nav-text">Laporan Agregat</span></a>
          </li>

          <li class="px-4 pt-4 pb-2"><small class="nav-text">Akun</small></li>
          <li><a class="nav-link" href="../login/logout.php"><i class="ti ti-logout"></i><span class="nav-text">Logout</span></a>
          </li>
      </ul>

  </aside>
