<?php

hakAkses(['admin']);

$rootPath   = dirname(__DIR__); 
$qrLibPath  = $rootPath . '/assets/vendor/phpqrcode/qrlib.php';
$cacheDir   = $rootPath . '/assets/vendor/phpqrcode/qrcode_cache/';

$qrAvailable = file_exists($qrLibPath);
if ($qrAvailable) {
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }
    include($qrLibPath);
}

$id_single       = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$filter_kategori = isset($_GET['kategori_id']) ? (int)$_GET['kategori_id'] : 0;

$where = "WHERE 1=1";
if ($id_single)       $where .= " AND b.idbarang = '$id_single'";
if ($filter_kategori) $where .= " AND b.kategori_id = '$filter_kategori'";

$query = mysqli_query($con,
    "SELECT b.*, m.nama_merek, k.nama_kategori
     FROM barang b
     LEFT JOIN merek m ON m.idmerek = b.merek_id
     LEFT JOIN kategori k ON k.idkategori = b.kategori_id
     $where
     ORDER BY b.kode_barang ASC"
) or die(mysqli_error($con));

$barangs = [];
while ($row = mysqli_fetch_assoc($query)) {
    $barangs[] = $row;
}

$qKategori = mysqli_query($con, "SELECT * FROM kategori ORDER BY nama_kategori");
?>

<!-- Begin Page Content -->
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-qrcode mr-2"></i>
            <?= $id_single ? 'QR Code Barang' : 'QR Code Semua Barang' ?>
        </h1>
        <div class="no-print">
            <?php if ($id_single): ?>
                <a href="<?= base_url() ?>?qrcode" class="btn btn-secondary btn-sm mr-2">
                    <i class="fas fa-list"></i> Semua Barang
                </a>
            <?php endif; ?>
            <button onclick="window.print()" class="btn btn-primary btn-sm">
                <i class="fas fa-print"></i> Print QR Code
            </button>
        </div>
    </div>

    <?php if (!$qrAvailable): ?>
    <div class="alert alert-danger no-print">
        <h5><i class="fas fa-exclamation-triangle"></i> Library phpqrcode tidak ditemukan!</h5>
        Path yang dicari: <code><?= htmlspecialchars($qrLibPath) ?></code><br><br>
        Pastikan struktur foldernya seperti ini:
        <pre class="mt-2 p-2 bg-light">inventaris-sekolah/
└── vendor/
    └── phpqrcode/
        ├── qrlib.php        ← harus ada
        ├── qrconst.php
        ├── qrimage.php
        └── qrcode_cache/    ← folder ini harus bisa ditulis</pre>
    </div>
    <?php else: ?>

    <!-- Filter Kategori -->
    <div class="card shadow mb-3 no-print">
        <div class="card-body py-2">
            <form method="GET" action="" class="form-inline">
                <input type="hidden" name="qrcode" value="">
                <label class="mr-2 font-weight-bold">Filter:</label>
                <select name="kategori_id" class="form-control form-control-sm mr-2">
                    <option value="">Semua Kategori</option>
                    <?php while ($kat = mysqli_fetch_assoc($qKategori)): ?>
                    <option value="<?= $kat['idkategori'] ?>"
                        <?= $filter_kategori == $kat['idkategori'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($kat['nama_kategori']) ?>
                    </option>
                    <?php endwhile; ?>
                </select>
                <button type="submit" class="btn btn-primary btn-sm mr-3">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <small class="text-muted">
                    Total: <strong><?= count($barangs) ?></strong> barang
                </small>
            </form>
        </div>
    </div>

    <!-- Grid QR Code -->
    <?php if (empty($barangs)): ?>
        <div class="alert alert-info text-center">
            <i class="fas fa-box-open fa-2x mb-2"></i><br>
            Tidak ada data barang.
        </div>
    <?php else: ?>
    <div class="row" id="qr-print-area">
        <?php foreach ($barangs as $barang):
            $qrData  = $barang['kode_barang'];
            $tmpFile = $cacheDir . md5($qrData) . '.png';

            // Generate QR jika belum ada di cache
            if (!file_exists($tmpFile)) {
                QRcode::png($qrData, $tmpFile, QR_ECLEVEL_M, 6, 2);
            }
            $imgData = file_exists($tmpFile) ? base64_encode(file_get_contents($tmpFile)) : null;
        ?>
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-4 qr-card-wrapper">
            <div class="card shadow text-center qr-card p-2">

                <!-- Gambar QR -->
                <div class="d-flex justify-content-center mb-1">
                    <?php if ($imgData): ?>
                        <img src="data:image/png;base64,<?= $imgData ?>"
                             alt="QR <?= htmlspecialchars($qrData) ?>"
                             style="width:130px; height:130px;">
                    <?php else: ?>
                        <div style="width:130px;height:130px;background:#f8d7da;display:flex;align-items:center;justify-content:center;border-radius:4px;">
                            <small class="text-danger text-center px-1">Gagal generate<br>cek cache folder</small>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Info Barang -->
                <div style="font-size:0.7rem; line-height:1.5;">
                    <div class="font-weight-bold text-dark">
                        <?= htmlspecialchars($barang['kode_barang']) ?>
                    </div>
                    <div class="text-truncate" title="<?= htmlspecialchars($barang['nama_barang']) ?>">
                        <?= htmlspecialchars($barang['nama_barang']) ?>
                    </div>
                    <div class="text-muted">
                        <?= htmlspecialchars($barang['nama_merek'] ?? '-') ?> |
                        <?= htmlspecialchars($barang['nama_kategori'] ?? '-') ?>
                    </div>
                </div>

                <!-- Tombol print satu -->
                <?php if (!$id_single): ?>
                <div class="mt-2 no-print">
                    <a href="<?= base_url() ?>?qrcode&id=<?= $barang['idbarang'] ?>"
                       class="btn btn-outline-secondary btn-sm btn-block" style="font-size:0.65rem;">
                        <i class="fas fa-print"></i> Print Satu
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <?php endif; // end $qrAvailable ?>

</div>
<!-- /.container-fluid -->

<style>
.qr-card {
    border: 1px solid #dee2e6;
    border-radius: 6px;
    break-inside: avoid;
}

@media print {
    #accordionSidebar, .topbar, .no-print,
    .btn, nav, footer { display: none !important; }

    body, .container-fluid {
        background: white !important;
        padding: 0 !important;
    }

    .qr-card-wrapper {
        width: 25% !important;
        float: left !important;
        padding: 3px !important;
    }

    .qr-card {
        border: 1px solid #000 !important;
        box-shadow: none !important;
        padding: 6px !important;
        page-break-inside: avoid;
    }

    <?php if ($id_single): ?>
    .qr-card-wrapper {
        width: 40% !important;
        margin: 0 auto !important;
        float: none !important;
    }
    .qr-card img { width: 180px !important; height: 180px !important; }
    <?php endif; ?>
}
</style>