<?php hakAkses(['admin', 'staff', 'gudang']); ?>

<?php
// ── Filter dari URL ───────────────────────────────────────────────────────────
$filter_kategori = isset($_GET['kategori_id']) ? (int)$_GET['kategori_id'] : 0;
$filter_kondisi  = isset($_GET['kondisi'])     ? mysqli_real_escape_string($con, $_GET['kondisi']) : '';
$filter_jenis    = isset($_GET['jenis'])       ? mysqli_real_escape_string($con, $_GET['jenis']) : '';
$filter_kritis   = isset($_GET['kritis']);     // flag khusus tampilkan stok kritis saja

$where = "WHERE 1=1";
if ($filter_kategori) $where .= " AND b.kategori_id = '$filter_kategori'";
if ($filter_kondisi)  $where .= " AND b.kondisi = '$filter_kondisi'";
if ($filter_jenis)    $where .= " AND b.jenis = '$filter_jenis'";
if ($filter_kritis)   $where .= " AND b.stok <= 2";

// ── Query ─────────────────────────────────────────────────────────────────────
$query = mysqli_query($con,
    "SELECT b.*, m.nama_merek, k.nama_kategori
     FROM barang b
     LEFT JOIN merek m ON m.idmerek = b.merek_id
     LEFT JOIN kategori k ON k.idkategori = b.kategori_id
     $where
     ORDER BY k.nama_kategori ASC, b.nama_barang ASC"
) or die(mysqli_error($con));

$dataBarang = [];
while ($row = mysqli_fetch_assoc($query)) {
    $dataBarang[] = $row;
}

// ── Statistik ─────────────────────────────────────────────────────────────────
$qStat = mysqli_query($con, "SELECT * FROM barang b LEFT JOIN kategori k ON k.idkategori=b.kategori_id");
$allBarang = [];
while ($r = mysqli_fetch_assoc($qStat)) $allBarang[] = $r;

$totalJenis  = count($allBarang);
$totalStok   = array_sum(array_column($allBarang, 'stok'));
$totalKritis = count(array_filter($allBarang, fn($b) => $b['stok'] <= 2));
$totalRusak  = count(array_filter($allBarang, fn($b) => in_array($b['kondisi'] ?? '', ['Rusak Ringan','Rusak Berat','Hilang'])));

// ── Daftar kategori untuk filter ─────────────────────────────────────────────
$qKategori = mysqli_query($con, "SELECT * FROM kategori ORDER BY nama_kategori");
?>

<!-- Begin Page Content -->
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            Laporan Stok Barang
            <?php if ($filter_kritis): ?>
                <span class="badge badge-warning ml-2">Stok Kritis</span>
            <?php elseif ($filter_kondisi): ?>
                <span class="badge badge-danger ml-2"><?= htmlspecialchars($filter_kondisi) ?></span>
            <?php endif; ?>
        </h1>
        <div>
            <a href="<?= base_url() ?>process/lap_stok_barang.php" target="_blank"
               class="btn btn-info btn-sm">
                <i class="fas fa-print"></i> Cetak
            </a>
        </div>
    </div>

    <!-- Kartu Statistik -->
    <div class="row mb-4">
        <!-- Total Jenis -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Jenis Barang</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalJenis ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-boxes fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Total Stok -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Stok</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalStok ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-warehouse fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Stok Kritis -->
        <div class="col-xl-3 col-md-6 mb-3">
            <a href="<?= base_url() ?>?lap_stok_barang&kritis" class="text-decoration-none">
                <div class="card border-left-warning shadow h-100 py-2 <?= $filter_kritis ? 'border-warning' : '' ?>">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Stok Kritis (≤ 2)
                                    <?= $filter_kritis ? '<span class="badge badge-warning">aktif</span>' : '' ?>
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalKritis ?></div>
                            </div>
                            <div class="col-auto"><i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i></div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <!-- Rusak/Hilang -->
        <div class="col-xl-3 col-md-6 mb-3">
            <a href="<?= base_url() ?>?lap_stok_barang&kondisi=Rusak+Berat" class="text-decoration-none">
                <div class="card border-left-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Rusak / Hilang</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalRusak ?></div>
                            </div>
                            <div class="col-auto"><i class="fas fa-tools fa-2x text-gray-300"></i></div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Filter -->
    <div class="card shadow mb-4">
        <div class="card-header py-2">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter mr-1"></i> Filter
            </h6>
        </div>
        <div class="card-body py-2">
            <form method="GET" action="">
                <input type="hidden" name="lap_stok_barang" value="">
                <div class="row">
                    <div class="col-md-3">
                        <select name="kategori_id" class="form-control form-control-sm">
                            <option value="">-- Semua Kategori --</option>
                            <?php
                            mysqli_data_seek($qKategori, 0);
                            while ($kat = mysqli_fetch_assoc($qKategori)):
                            ?>
                            <option value="<?= $kat['idkategori'] ?>"
                                <?= $filter_kategori == $kat['idkategori'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($kat['nama_kategori']) ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="kondisi" class="form-control form-control-sm">
                            <option value="">-- Semua Kondisi --</option>
                            <?php foreach (['Baik','Rusak Ringan','Rusak Berat','Hilang'] as $k): ?>
                            <option value="<?= $k ?>" <?= $filter_kondisi === $k ? 'selected' : '' ?>>
                                <?= $k ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="jenis" class="form-control form-control-sm">
                            <option value="">-- Semua Jenis --</option>
                            <option value="Habis Pakai"       <?= $filter_jenis === 'Habis Pakai'       ? 'selected' : '' ?>>Habis Pakai</option>
                            <option value="Tidak Habis Pakai" <?= $filter_jenis === 'Tidak Habis Pakai' ? 'selected' : '' ?>>Tidak Habis Pakai</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm mr-1">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="<?= base_url() ?>?lap_stok_barang" class="btn btn-secondary btn-sm">
                            <i class="fas fa-times"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Data Stok Barang</h6>
            <small class="text-muted">
                Menampilkan <strong><?= count($dataBarang) ?></strong> barang
                <?php if ($filter_kritis): ?> — <span class="text-warning">stok kritis</span>
                <?php elseif ($filter_kondisi): ?> — kondisi: <span class="text-danger"><?= htmlspecialchars($filter_kondisi) ?></span>
                <?php endif; ?>
            </small>
        </div>
    <!-- Keterangan warna -->
        <div class="card-body">
            <div class="small text-muted mb-4">
                <span class="badge badge-warning p-1">&nbsp;&nbsp;</span> Stok kritis (≤ 2) &nbsp;
                <span class="badge badge-danger p-1">&nbsp;&nbsp;</span> Stok habis / rusak berat / hilang
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm" id="dataTable" width="100%">
                    <thead class="thead-light">
                        <tr>
                            <th width="30">NO</th>
                            <th>KODE BARANG</th>
                            <th>NAMA BARANG</th>
                            <th>MEREK</th>
                            <th>KATEGORI</th>
                            <th>JENIS</th>
                            <th>LOKASI</th>
                            <th class="text-center">KONDISI</th>
                            <th class="text-center">STOK</th>
                            <th width="80" class="text-center">QR</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($dataBarang)): ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                <i class="fas fa-box-open fa-2x mb-2 d-block"></i>
                                Tidak ada data barang
                            </td>
                        </tr>
                    <?php else: ?>
                    <?php $n = 1; foreach ($dataBarang as $b):
                        $kondisi = $b['kondisi'] ?? 'Baik';
                        $jenis   = $b['jenis']   ?? 'Tidak Habis Pakai';
                        $badgeKondisi = match($kondisi) {
                            'Baik'         => 'success',
                            'Rusak Ringan' => 'warning',
                            'Rusak Berat'  => 'danger',
                            'Hilang'       => 'dark',
                            default        => 'secondary'
                        };
                        $rowClass = '';
                        if ($b['stok'] == 0)     $rowClass = 'table-danger';
                        elseif ($b['stok'] <= 2) $rowClass = 'table-warning';
                        elseif (in_array($kondisi, ['Rusak Berat','Hilang'])) $rowClass = 'table-danger';
                    ?>
                    <tr class="<?= $rowClass ?>">
                        <td><?= $n++ ?></td>
                        <td><code><?= htmlspecialchars($b['kode_barang'] ?? '-') ?></code></td>
                        <td>
                            <?= htmlspecialchars($b['nama_barang']) ?>
                            <?php if ($b['stok'] <= 2): ?>
                                <span class="badge badge-warning ml-1">⚠ Kritis</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($b['nama_merek'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($b['nama_kategori'] ?? '-') ?></td>
                        <td>
                            <span class="badge badge-<?= $jenis === 'Habis Pakai' ? 'warning' : 'info' ?>">
                                <?= htmlspecialchars($jenis) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($b['lokasi'] ?? '-') ?></td>
                        <td class="text-center">
                            <span class="badge badge-<?= $badgeKondisi ?>">
                                <?= htmlspecialchars($kondisi) ?>
                            </span>
                        </td>
                        <td class="text-center font-weight-bold"><?= $b['stok'] ?></td>
                        <td class="text-center">
                            <button type="button"
                                class="btn btn-outline-secondary btn-sm"
                                title="Lihat QR Code"
                                onclick="lihatQR(
                                    '<?= htmlspecialchars($b['kode_barang']) ?>',
                                    '<?= htmlspecialchars($b['nama_barang']) ?>',
                                    '<?= htmlspecialchars($b['nama_merek'] ?? '-') ?>',
                                    <?= $b['idbarang'] ?>
                                )">
                                <i class="fas fa-qrcode"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <!-- Total -->
                    <tr class="font-weight-bold">
                        <td colspan="8" class="text-right">Total Stok:</td>
                        <td class="text-center"><?= array_sum(array_column($dataBarang, 'stok')) ?></td>
                        <td></td>
                    </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- /.container-fluid -->

<!-- ── Modal QR Code ─────────────────────────────────────────────────────── -->
<div class="modal fade" id="modalQR" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title font-weight-bold" id="modalQRTitle">QR Code Barang</h6>
                <button class="close" type="button" data-dismiss="modal">
                    <span>×</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <!-- Gambar QR -->
                <div id="qrImageWrapper" class="mb-3" style="display: flex; justify-content: center;">
                    <div id="qrLoading" class="text-muted py-4">
                        <i class="fas fa-spinner fa-spin fa-2x"></i><br>
                        <small>Memuat QR...</small>
                    </div>
                    <img id="qrImage" src="" alt="QR Code"
                         style="width:180px; height:180px;"
                         onerror="document.getElementById('qrError')">
                    <div id="qrError" class="alert alert-danger small">
                        Gagal memuat QR. Pastikan phpqrcode sudah terpasang.
                    </div>
                </div>
                <!-- Info barang -->
                <div class="border-top pt-2">
                    <div class="font-weight-bold" id="qrKodeBarang" style="font-size:0.85rem;"></div>
                    <div id="qrNamaBarang" style="font-size:0.8rem;"></div>
                    <div class="text-muted" id="qrMerekBarang" style="font-size:0.75rem;"></div>
                </div>
            </div>
            <div class="modal-footer py-2 justify-content-between">
                <a id="qrPrintLink" href="#" target="_blank"
                   class="btn btn-primary btn-sm">
                    <i class="fas fa-print"></i> Print
                </a>
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/blueimp-md5/2.19.0/js/md5.min.js"></script>
<script>
var BASE_URL = '<?= base_url() ?>';

function lihatQR(kode, nama, merek, id) {
    // Isi info teks
    document.getElementById('modalQRTitle').textContent  = 'QR Code — ' + kode;
    document.getElementById('qrKodeBarang').textContent  = kode;
    document.getElementById('qrNamaBarang').textContent  = nama;
    document.getElementById('qrMerekBarang').textContent = merek;

    // Set link print
    document.getElementById('qrPrintLink').href = BASE_URL + '?qrcode&id=' + id;

    // Reset tampilan gambar
    const img     = document.getElementById('qrImage');
    const loading = document.getElementById('qrLoading');
    const errEl   = document.getElementById('qrError');
    img.style.display     = 'none';
    errEl.style.display   = 'none';
    loading.style.display = 'block';

    // Load gambar QR langsung dari cache folder
    img.onload = function () {
        loading.style.display = 'none';
        img.style.display     = 'block';
    };
    // File QR disimpan dengan nama md5(kode_barang).png di cache folder
    img.src = BASE_URL + 'assets/vendor/phpqrcode/qrcode_cache/' + md5(kode) + '.png';

    $('#modalQR').modal('show');
}
</script>