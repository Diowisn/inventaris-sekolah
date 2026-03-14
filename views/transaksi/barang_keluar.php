<?php 

hakAkses(['admin', 'staff', 'gudang']);

$mode        = $mode ?? 'keluar';
$isKeluar    = ($mode === 'keluar');
$labelAksi   = $isKeluar ? 'Barang Keluar' : 'Barang Masuk';
$warnaTombol = $isKeluar ? 'danger' : 'success';
$ikonAksi    = $isKeluar ? 'fa-arrow-up' : 'fa-arrow-down';
$modalTarget = $isKeluar ? '#barang_keluar' : '#barang_masuk';

?>
<!-- Begin Page Content -->
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Barang Keluar</h1>
    </div>

    <!-- Card Scan QR -->
    <div class="card shadow mb-3 border-left-<?= $warnaTombol ?>">
        <div class="card-header py-2 d-flex align-items-center justify-content-between">
            <span>
                <i class="fas fa-qrcode mr-1"></i> Scan QR / Barcode Barang
                <span class="badge badge-<?= $warnaTombol ?> ml-2">
                    <i class="fas <?= $ikonAksi ?>"></i> <?= $labelAksi ?>
                </span>
            </span>
            <button type="button" class="btn btn-sm btn-info" id="btnToggleScan">
                <i class="fas fa-camera"></i> Buka Kamera
            </button>
        </div>
        <div class="card-body" id="scanArea" style="display:none;">
            <div class="row">
                <div class="col-md-6">
                    <div id="qr-reader" style="width:100%; max-width:400px;"></div>
                    <div class="mt-2">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i>
                            Arahkan kamera ke QR Code barang. Form akan otomatis terisi.
                        </small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Atau ketik kode barang:</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="inputKodeManual"
                                placeholder="Contoh: BRG-0001">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-primary"
                                    onclick="cariBarangByKode($('#inputKodeManual').val())">
                                    <i class="fas fa-search"></i> Cari
                                </button>
                            </div>
                        </div>
                        <small class="text-muted">Tekan Enter atau klik Cari</small>
                    </div>

                    <div id="previewBarang" class="alert alert-success" style="display:none;">
                        <strong><i class="fas fa-check-circle"></i> Barang Ditemukan:</strong><br>
                        <span id="previewNama" class="font-weight-bold"></span><br>
                        <small class="text-muted">
                            Stok saat ini: <strong id="previewStok"></strong> |
                            Kondisi: <span id="previewKondisi"></span>
                        </small>
                        <hr class="my-2">
                        <button type="button"
                            class="btn btn-<?= $warnaTombol ?> btn-sm btn-block"
                            onclick="$('<?= $modalTarget ?>').modal('show')">
                            <i class="fas <?= $ikonAksi ?>"></i>
                            Lanjut Input <?= $labelAksi ?>
                        </button>
                    </div>

                    <div id="previewError" class="alert alert-danger" style="display:none;">
                        <i class="fas fa-times-circle"></i> Barang tidak ditemukan. Pastikan kode benar.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Barang Keluar -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <a href="#" class="btn btn-primary btn-icon-split btn-sm"
                data-toggle="modal" data-target="#barang_keluar">
                <span class="icon text-white-50"><i class="fas fa-plus"></i></span>
                <span class="text">Tambah</span>
            </a>
            <a href="<?= base_url(); ?>process/cetak_barang_keluar.php" target="_blank"
                class="btn btn-info btn-icon-split btn-sm float-right">
                <span class="icon text-white-50"><i class="fas fa-print"></i></span>
                <span class="text">Cetak</span>
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th width="20">NO</th>
                            <th>TANGGAL</th>
                            <th>NAMA BARANG</th>
                            <th>MEREK</th>
                            <th>KATEGORI</th>
                            <th>PENERIMA / KEPERLUAN</th>
                            <th>KETERANGAN</th>
                            <th>JUMLAH</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $n = 1;
                        $query = mysqli_query($con,
                            "SELECT x.*, x1.nama_barang, x2.nama_merek, x3.nama_kategori
                             FROM barang_keluar x
                             JOIN barang x1 ON x1.idbarang = x.barang_id
                             LEFT JOIN merek x2 ON x2.idmerek = x1.merek_id
                             LEFT JOIN kategori x3 ON x3.idkategori = x1.kategori_id
                             ORDER BY x.idbarang_keluar DESC"
                        ) or die(mysqli_error($con));
                        while ($row = mysqli_fetch_array($query)):
                        ?>
                        <tr>
                            <td><?= $n++; ?></td>
                            <td><?= date('d-m-Y', strtotime($row['tanggal'])); ?></td>
                            <td><?= htmlspecialchars($row['nama_barang']); ?></td>
                            <td><?= htmlspecialchars($row['nama_merek'] ?? '-'); ?></td>
                            <td><?= htmlspecialchars($row['nama_kategori'] ?? '-'); ?></td>
                            <td><?= htmlspecialchars($row['penerima'] ?? '-'); ?></td>
                            <td><?= htmlspecialchars($row['keterangan']); ?></td>
                            <td align="center"><?= $row['jumlah']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
<!-- /.container-fluid -->

<!-- Modal Tambah Barang Keluar -->
<div class="modal fade" id="barang_keluar" tabindex="-1" role="dialog"
    aria-labelledby="labelModalKeluar" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="<?= base_url(); ?>process/barang_keluar.php" method="post"
                  id="formBarangKeluar">
                <div class="modal-header">
                    <h5 class="modal-title" id="labelModalKeluar">Tambah Barang Keluar</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">

                        <!-- Tanggal -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="tanggal">Tanggal <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="tanggal" name="tanggal"
                                    value="<?= date('Y-m-d'); ?>" required>
                            </div>
                        </div>

                        <!-- Nama Barang -->
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="barang_id">Nama Barang <span class="text-danger">*</span></label>
                                <select name="barang_id" id="barang_id" class="form-control select2"
                                    style="width:100%;" required>
                                    <option value="">-- Pilih Barang --</option>
                                    <?= list_barang(); ?>
                                </select>
                            </div>
                        </div>

                        <!-- Info stok barang terpilih -->
                        <div class="col-md-12">
                            <div id="infoStokBarang" class="alert alert-info py-2" style="display:none;">
                                <i class="fas fa-info-circle"></i>
                                Stok tersedia: <strong id="infoStokAngka">0</strong> unit
                            </div>
                        </div>

                        <!-- Jumlah -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="jumlah">Jumlah <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="jumlah" name="jumlah"
                                    min="1" placeholder="0" required>
                                <div id="errorJumlah" class="text-danger small" style="display:none;">
                                    <i class="fas fa-exclamation-circle"></i>
                                    Jumlah melebihi stok yang tersedia!
                                </div>
                            </div>
                        </div>

                        <!-- Penerima -->
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="penerima">Penerima / Keperluan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="penerima" name="penerima"
                                    placeholder="Contoh: Bu Saadah, Lab Komputer, Kurikulum..."
                                    required>
                                <small class="text-muted">Nama orang atau bagian yang menerima barang</small>
                            </div>
                        </div>

                        <!-- Keterangan -->
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="keterangan">Keterangan</label>
                                <textarea name="keterangan" id="keterangan" cols="30" rows="3"
                                    class="form-control"
                                    placeholder="Contoh: Dipinjam untuk acara rapat, Rusak tidak bisa dipakai..."></textarea>
                            </div>
                        </div>

                    </div>
                    <hr class="sidebar-divider">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button class="btn btn-danger float-right" type="submit" name="tambah" id="btnSubmit">
                        <i class="fas fa-save"></i> Tambah
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    var BASE_URL = '<?= base_url(); ?>';
</script>
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
(function () {
    'use strict';

    let html5QrCode = null;
    let isScanning  = false;
    let stokTersedia = 0;

    // ── Toggle kamera ─────────────────────────────────────────────────────────
    document.getElementById('btnToggleScan').addEventListener('click', function () {
        const scanArea  = document.getElementById('scanArea');
        const isVisible = scanArea.style.display !== 'none';
        if (isVisible) {
            tutupKamera();
        } else {
            scanArea.style.display = 'block';
            this.innerHTML = '<i class="fas fa-stop"></i> Tutup Kamera';
            bukaKamera();
        }
    });

    function bukaKamera() {
        if (html5QrCode) html5QrCode.clear();
        html5QrCode = new Html5Qrcode("qr-reader");
        html5QrCode.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: { width: 200, height: 200 } },
            function onScanSuccess(decodedText) {
                cariBarangByKode(decodedText);
                tutupKamera();
            },
            function onScanFailure() {}
        ).catch(function (err) {
            alert('Kamera tidak bisa dibuka.\n' + err);
        });
        isScanning = true;
    }

    function tutupKamera() {
        if (html5QrCode && isScanning) {
            html5QrCode.stop().catch(function () {});
            isScanning = false;
        }
        const btn = document.getElementById('btnToggleScan');
        if (btn) btn.innerHTML = '<i class="fas fa-camera"></i> Buka Kamera';
        const scanArea = document.getElementById('scanArea');
        if (scanArea) scanArea.style.display = 'none';
    }

    // ── Enter key input manual ────────────────────────────────────────────────
    document.getElementById('inputKodeManual').addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            cariBarangByKode(this.value);
        }
    });

    // ── Cari barang by kode ───────────────────────────────────────────────────
    window.cariBarangByKode = function (kode) {
        kode = kode.trim();
        if (!kode) return;
        document.getElementById('previewBarang').style.display = 'none';
        document.getElementById('previewError').style.display  = 'none';

        $.ajax({
            url: BASE_URL + 'process/cari_barang.php',
            type: 'GET',
            data: { kode: kode },
            dataType: 'json',
            success: function (data) {
                if (data.status === 'found') {
                    $('#barang_id').val(data.idbarang).trigger('change');
                    stokTersedia = parseInt(data.stok);
                    document.getElementById('previewNama').textContent    = data.nama_barang + ' (' + data.kode_barang + ')';
                    document.getElementById('previewStok').textContent    = data.stok;
                    document.getElementById('previewKondisi').textContent = data.kondisi;
                    document.getElementById('previewBarang').style.display = 'block';
                    tampilkanInfoStok(data.stok);
                } else {
                    document.getElementById('previewError').style.display = 'block';
                }
            },
            error: function () {
                document.getElementById('previewError').style.display = 'block';
            }
        });
    };

    // ── Tampilkan info stok saat barang dipilih dari dropdown ─────────────────
    $('#barang_id').on('change', function () {
        const barangId = $(this).val();
        if (!barangId) {
            document.getElementById('infoStokBarang').style.display = 'none';
            stokTersedia = 0;
            return;
        }
        $.ajax({
            url: BASE_URL + 'process/cari_barang.php',
            type: 'GET',
            data: { id: barangId },
            dataType: 'json',
            success: function (data) {
                if (data.status === 'found') {
                    stokTersedia = parseInt(data.stok);
                    tampilkanInfoStok(data.stok);
                }
            }
        });
    });

    function tampilkanInfoStok(stok) {
        const infoEl  = document.getElementById('infoStokBarang');
        const angkaEl = document.getElementById('infoStokAngka');
        angkaEl.textContent = stok;
        infoEl.style.display = 'block';
        // Warna berbeda jika stok kritis
        infoEl.className = stok <= 2
            ? 'alert alert-warning py-2'
            : 'alert alert-info py-2';
    }

    // ── Validasi jumlah tidak melebihi stok ───────────────────────────────────
    document.getElementById('jumlah').addEventListener('input', function () {
        const jumlah   = parseInt(this.value) || 0;
        const errorEl  = document.getElementById('errorJumlah');
        const btnEl    = document.getElementById('btnSubmit');

        if (stokTersedia > 0 && jumlah > stokTersedia) {
            errorEl.style.display = 'block';
            btnEl.disabled = true;
            this.classList.add('is-invalid');
        } else {
            errorEl.style.display = 'none';
            btnEl.disabled = false;
            this.classList.remove('is-invalid');
        }
    });

    // ── Reset form saat modal ditutup ─────────────────────────────────────────
    $('#barang_keluar').on('hidden.bs.modal', function () {
        document.getElementById('jumlah').value    = '';
        document.getElementById('penerima').value  = '';
        document.getElementById('keterangan').value = '';
        document.getElementById('infoStokBarang').style.display = 'none';
        document.getElementById('errorJumlah').style.display    = 'none';
        document.getElementById('btnSubmit').disabled = false;
        document.getElementById('jumlah').classList.remove('is-invalid');
        stokTersedia = 0;
    });

    window.addEventListener('beforeunload', tutupKamera);
})();
</script>