<?php

session_start();
include('../config/conn.php');
include('../config/function.php');
include('../config/header.php');

// Cek akses
hakAkses(['admin', 'staff', 'gudang']);

// Ambil data barang
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$single = ($id > 0);

if ($single) {
    // QR satu barang
    $query = mysqli_query($con, "SELECT b.*, m.nama_merek, k.nama_kategori 
                                  FROM barang b 
                                  JOIN merek m ON m.idmerek = b.merek_id 
                                  JOIN kategori k ON k.idkategori = b.kategori_id 
                                  WHERE b.idbarang = '$id'") or die(mysqli_error($con));
    $barangs = [];
    if ($row = mysqli_fetch_assoc($query)) {
        $barangs[] = $row;
    }
} else {
    // Semua barang
    $query = mysqli_query($con, "SELECT b.*, m.nama_merek, k.nama_kategori 
                                  FROM barang b 
                                  JOIN merek m ON m.idmerek = b.merek_id 
                                  JOIN kategori k ON k.idkategori = b.kategori_id 
                                  ORDER BY b.kode_barang ASC") or die(mysqli_error($con));
    $barangs = [];
    while ($row = mysqli_fetch_assoc($query)) {
        $barangs[] = $row;
    }
}

// Cek apakah library phpqrcode tersedia
$qrLibPath = __DIR__ . '/../vendor/phpqrcode/qrlib.php';
$qrAvailable = file_exists($qrLibPath);
if ($qrAvailable) {
    include($qrLibPath);
}
?>

<!-- Konten Halaman -->
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <?= $single ? 'QR Code Barang' : 'QR Code Semua Barang' ?>
        </h1>
        <div>
            <?php if ($single): ?>
                <a href="?qrcode" class="btn btn-secondary btn-sm mr-2">
                    <i class="fas fa-list"></i> Semua Barang
                </a>
            <?php endif; ?>
            <button onclick="window.print()" class="btn btn-primary btn-sm">
                <i class="fas fa-print"></i> Print QR Code
            </button>
        </div>
    </div>

    <?php if (!$qrAvailable): ?>
    <!-- Panduan install library jika belum ada -->
    <div class="alert alert-warning">
        <strong><i class="fas fa-exclamation-triangle"></i> Library phpqrcode belum terpasang.</strong><br>
        Ikuti langkah berikut:
        <ol class="mt-2">
            <li>Download dari <a href="https://sourceforge.net/projects/phpqrcode/files/" target="_blank">sourceforge.net/projects/phpqrcode</a></li>
            <li>Ekstrak, copy file <code>qrlib.php</code> dan folder <code>qrcode_cache</code></li>
            <li>Taruh di <code>vendor/phpqrcode/qrlib.php</code></li>
            <li>Buat folder <code>vendor/phpqrcode/qrcode_cache/</code> dan beri permission write</li>
        </ol>
        <hr>
        <strong>Alternatif via Composer:</strong><br>
        <code>composer require chillerlan/php-qrcode</code>
    </div>
    <?php endif; ?>

    <!-- Grid QR Code Cards -->
    <div class="row" id="qr-print-area">
        <?php foreach ($barangs as $barang): ?>
        <div class="col-md-3 col-sm-4 col-6 mb-4 qr-card-wrapper">
            <div class="card shadow text-center qr-card p-3">
                <div class="qr-image-wrapper mb-2">
                    <?php if ($qrAvailable): ?>
                        <?php
                        // Generate QR code sebagai base64 image
                        $qrData = $barang['kode_barang'];
                        $cacheDir = __DIR__ . '/../vendor/phpqrcode/qrcode_cache/';
                        if (!is_dir($cacheDir)) mkdir($cacheDir, 0755, true);
                        
                        $tmpFile = $cacheDir . md5($qrData) . '.png';
                        QRcode::png($qrData, $tmpFile, QR_ECLEVEL_M, 6, 2);
                        $imgData = base64_encode(file_get_contents($tmpFile));
                        ?>
                        <img src="data:image/png;base64,<?= $imgData ?>" 
                             alt="QR <?= htmlspecialchars($barang['kode_barang']) ?>"
                             style="width:140px;height:140px;">
                    <?php else: ?>
                        <!-- Fallback: tampilkan via Google Charts API (butuh internet) -->
                        <img src="https://chart.googleapis.com/chart?chs=140x140&cht=qr&chl=<?= urlencode($barang['kode_barang']) ?>&choe=UTF-8"
                             alt="QR <?= htmlspecialchars($barang['kode_barang']) ?>"
                             style="width:140px;height:140px;"
                             onerror="this.src='data:image/svg+xml,<svg xmlns=\'http://www.w3.org/2000/svg\'><rect width=\'140\' height=\'140\' fill=\'%23eee\'/><text x=\'10\' y=\'70\' font-size=\'12\'>QR Offline</text></svg>'">
                    <?php endif; ?>
                </div>
                
                <div class="qr-info">
                    <div class="font-weight-bold text-dark" style="font-size:0.75rem;">
                        <?= htmlspecialchars($barang['kode_barang']) ?>
                    </div>
                    <div class="text-gray-700" style="font-size:0.7rem; line-height:1.3;">
                        <?= htmlspecialchars($barang['nama_barang']) ?><br>
                        <small class="text-muted">
                            <?= htmlspecialchars($barang['nama_merek']) ?> | 
                            <?= htmlspecialchars($barang['nama_kategori']) ?>
                        </small>
                    </div>
                </div>

                <?php if (!$single): ?>
                <div class="mt-2 no-print">
                    <a href="?qrcode&id=<?= $barang['idbarang'] ?>" 
                       class="btn btn-outline-info btn-sm btn-block" style="font-size:0.7rem;">
                        <i class="fas fa-qrcode"></i> Print Satu
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>

        <?php if (empty($barangs)): ?>
        <div class="col-12">
            <div class="alert alert-info text-center">
                <i class="fas fa-box-open fa-2x mb-2"></i><br>
                Belum ada data barang.
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- CSS khusus print -->
<style>
.qr-card {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    break-inside: avoid;
}
.qr-image-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
}

@media print {
    /* Sembunyikan elemen yang tidak perlu */
    .sidebar, .topbar, .no-print, 
    .btn, nav, footer, 
    #accordionSidebar { display: none !important; }

    body { background: white !important; }
    .container-fluid { padding: 0 !important; }
    
    .qr-card-wrapper {
        width: 25% !important;
        float: left;
    }
    .qr-card {
        margin: 4px !important;
        padding: 8px !important;
        border: 1px solid #000 !important;
        page-break-inside: avoid;
    }
    .h3 { font-size: 14pt !important; }
}
</style>

<?php include('../config/footer.php'); ?>