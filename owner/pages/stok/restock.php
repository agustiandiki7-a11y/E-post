<?php
$db = eposDb(); $SCOPE = 0; $KEMBALI = 'kendaraan';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    restockProses($db, $SCOPE);
    redirect('kendaraan');
}
?>
<main id="content" class="content py-10">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                    <div>
                        <h1 class="fs-3 mb-1">Restock Produk</h1>
                        <p class="mb-0">Tambah stok barang yang masuk</p>
                    </div>

                </div>
            </div>
        </div>
        <?php include __DIR__ . '/../../../login/halaman/restock.php'; ?>
        <?php include 'components/footer.php'; ?>
    </div>
</main>
