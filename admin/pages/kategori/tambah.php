  <main id="content" class="content py-10">
      <div class="container-fluid">
          <div class="row">
              <div class="col-12">
                  <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                      <div class="">
                          <h1 class="fs-3 mb-1">Add Inventory</h1>
                          <p class="mb-0">Manage your inventory items</p>
                      </div>
                      <div>
                          <a href="index.php?page=kategori" class="btn btn-primary">Go to Inventory List</a>
                      </div>
                  </div>
              </div>
          </div>
          <div class="row">
              <div class="col-12">
                  <div class="card">
                      <div class="card-body p-4">
                          <?php

                            require_once 'function/kategori.php';
                            if (isset($_POST['kategori'])) {
                                $kategori = new Kategori();

                                $varkategori = $_POST['kategori'];

                                $result = $kategori->tambah(
                                    $varkategori,
                                );

                                if ($result) {
                                    echo "<script>window.location.href='index.php?page=kategori'</script>";
                                } else {
                                    echo "Data kendaraan gagal ditambahkan.";
                                }
                            }
                            ?>
                          <form method="post" action="" id="addProductForm">
                              <input type="hidden" class="form-control" name="id" id="id" placeholder="Enter product name" required>
                              <div class="col-md-12 mb-3">
                                  <label for="kategori" class="form-label">Nama Kategori</label>
                                  <input type="text" class="form-control" name="kategori" id="kategori" placeholder="Enter product name" required>
                              </div>
                              <div class="d-flex gap-2">
                                  <button type="submit" class="btn btn-primary">Add Product</button>
                              </div>

                          </form>
                      </div>
                  </div>


              </div>

          </div>

          <div class="row">
              <div class="col-12">
                  <footer class="text-center py-2 mt-6 text-secondary ">
                      <p class="mb-0">Copyright © 2026 InApp Inventory Dashboard. Developed by <a href="https://codescandy.com/" target="_blank" class="text-primary">CodesCandy</a> • Distributed by <a href="https://themewagon.com/" target="_blank" class="text-primary">ThemeWagon</a> </p>
                  </footer>
              </div>

          </div>

      </div>
  </main>