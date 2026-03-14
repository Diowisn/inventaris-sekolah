<?php 
hakAkses(['admin', 'staff', 'gudang']);

mysqli_query($con,
    "UPDATE peminjaman SET status='Terlambat'
     WHERE status='Dipinjam' AND tanggal_rencana < CURDATE()"
);

// Statistik ringkasan
$totalDipinjam   = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM peminjaman WHERE status='Dipinjam'"))['total'];
$totalTerlambat  = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM peminjaman WHERE status='Terlambat'"))['total'];
$totalKembali    = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM peminjaman WHERE status='Dikembalikan'"))['total'];
?>

<!-- Begin Page Content -->
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Peminjaman Barang</h1>
        <a href="#" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalTambahPinjam">
            <i class="fas fa-plus"></i> Catat Peminjaman
        </a>
    </div>

    <!-- Kartu Statistik -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Sedang Dipinjam</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalDipinjam ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-hand-holding fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Terlambat Kembali</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalTerlambat ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-exclamation-circle fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Sudah Dikembalikan</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalKembali ?></div>
                        </div>
                        <div class="col-auto"><i class="fas fa-check-circle fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($totalTerlambat > 0): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        <strong><?= $totalTerlambat ?> peminjaman</strong> sudah melewati tanggal rencana kembali!
    </div>
    <?php endif; ?>

    <!-- Tabel Peminjaman -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Peminjaman</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th width="20">NO</th>
                            <th>BARANG</th>
                            <th>PEMINJAM</th>
                            <th>KEPERLUAN</th>
                            <th width="50" class="text-center">JML</th>
                            <th>TGL PINJAM</th>
                            <th>RENCANA KEMBALI</th>
                            <th>TGL KEMBALI</th>
                            <th class="text-center">STATUS</th>
                            <th width="100" class="text-center">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $n = 1;
                        $query = mysqli_query($con,
                            "SELECT p.*, b.nama_barang, b.kode_barang
                             FROM peminjaman p
                             JOIN barang b ON b.idbarang = p.barang_id
                             ORDER BY 
                                FIELD(p.status, 'Terlambat', 'Dipinjam', 'Dikembalikan'),
                                p.tanggal_rencana ASC,
                                p.id_pinjam DESC"
                        ) or die(mysqli_error($con));
                        while ($row = mysqli_fetch_assoc($query)):
                            $badgeStatus = match($row['status']) {
                                'Dipinjam'     => 'warning',
                                'Terlambat'    => 'danger',
                                'Dikembalikan' => 'success',
                                default        => 'secondary'
                            };
                        ?>
                        <tr>
                            <td><?= $n++ ?></td>
                            <td>
                                <span class="font-weight-bold"><?= htmlspecialchars($row['nama_barang']) ?></span><br>
                                <small class="text-muted"><code><?= htmlspecialchars($row['kode_barang']) ?></code></small>
                            </td>
                            <td><?= htmlspecialchars($row['nama_peminjam']) ?></td>
                            <td><?= htmlspecialchars($row['keperluan']) ?></td>
                            <td class="text-center"><?= $row['jumlah'] ?></td>
                            <td><?= date('d-m-Y', strtotime($row['tanggal_pinjam'])) ?></td>
                            <td>
                                <?= date('d-m-Y', strtotime($row['tanggal_rencana'])) ?>
                                <?php if ($row['status'] === 'Terlambat'):
                                    $selisih = (new DateTime())->diff(new DateTime($row['tanggal_rencana']))->days;
                                ?>
                                    <br><small class="text-danger"><i class="fas fa-clock"></i> <?= $selisih ?> hari terlambat</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= $row['tanggal_kembali']
                                    ? date('d-m-Y', strtotime($row['tanggal_kembali']))
                                    : '<span class="text-muted">-</span>' ?>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-<?= $badgeStatus ?>">
                                    <?= $row['status'] ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <?php if ($row['status'] !== 'Dikembalikan'): ?>
                                <button type="button"
                                    class="btn btn-success btn-sm"
                                    onclick="konfirmasiKembali(<?= $row['id_pinjam'] ?>, '<?= htmlspecialchars($row['nama_barang']) ?>', '<?= htmlspecialchars($row['nama_peminjam']) ?>')"
                                    title="Tandai Dikembalikan">
                                    <i class="fas fa-undo"></i> Kembali
                                </button>
                                <?php else: ?>
                                <span class="text-muted small">Selesai</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
<!-- /.container-fluid -->

<!-- ================================================================
     MODAL TAMBAH PEMINJAMAN
     ================================================================ -->
<div class="modal fade" id="modalTambahPinjam" tabindex="-1" role="dialog"
    aria-labelledby="labelModalPinjam" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="<?= base_url() ?>process/peminjaman.php" method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="labelModalPinjam">Catat Peminjaman Barang</h5>
                    <button class="close" type="button" data-dismiss="modal">
                        <span>×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">

                        <!-- Nama Barang -->
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Nama Barang <span class="text-danger">*</span></label>
                                <select name="barang_id" id="barang_id_pinjam" class="form-control select2"
                                    style="width:100%;" required>
                                    <option value="">-- Pilih Barang --</option>
                                    <?= list_barang(); ?>
                                </select>
                                <div id="infoStokPinjam" class="mt-1" style="display:none;">
                                    <small class="text-info">
                                        <i class="fas fa-info-circle"></i>
                                        Stok tersedia: <strong id="angkaStokPinjam">0</strong> unit
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Nama Peminjam -->
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Nama Peminjam <span class="text-danger">*</span></label>
                                <input type="text" name="nama_peminjam" class="form-control"
                                    placeholder="Nama lengkap peminjam" required>
                            </div>
                        </div>

                        <!-- Jumlah -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Jumlah <span class="text-danger">*</span></label>
                                <input type="number" name="jumlah" id="jumlahPinjam" class="form-control"
                                    min="1" value="1" required>
                                <div id="errorJumlahPinjam" class="text-danger small" style="display:none;">
                                    Jumlah melebihi stok!
                                </div>
                            </div>
                        </div>

                        <!-- Keperluan -->
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Keperluan <span class="text-danger">*</span></label>
                                <input type="text" name="keperluan" class="form-control"
                                    placeholder="Contoh: Untuk rapat kurikulum, Acara pensi..." required>
                            </div>
                        </div>

                        <!-- Tanggal Pinjam -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tanggal Pinjam <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_pinjam" class="form-control"
                                    value="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>

                        <!-- Rencana Kembali -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Rencana Tanggal Kembali <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_rencana" class="form-control"
                                    value="<?= date('Y-m-d', strtotime('+7 days')) ?>" required>
                                <small class="text-muted">Default 7 hari ke depan</small>
                            </div>
                        </div>

                        <!-- Keterangan -->
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Keterangan</label>
                                <textarea name="keterangan" class="form-control" rows="2"
                                    placeholder="Catatan tambahan (opsional)"></textarea>
                            </div>
                        </div>

                    </div>
                    <hr class="sidebar-divider">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button class="btn btn-primary float-right" type="submit" name="tambah">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Kembali -->
<div class="modal fade" id="modalKonfirmasiKembali" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="<?= base_url() ?>process/peminjaman.php" method="post">
                <input type="hidden" name="id_pinjam" id="inputIdPinjam">
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi Pengembalian</h5>
                    <button class="close" type="button" data-dismiss="modal"><span>×</span></button>
                </div>
                <div class="modal-body">
                    <p id="textKonfirmasiKembali"></p>
                    <div class="form-group">
                        <label>Tanggal Dikembalikan <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_kembali" class="form-control"
                            value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Keterangan</label>
                        <input type="text" name="keterangan_kembali" class="form-control"
                            placeholder="Kondisi barang saat dikembalikan...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" name="kembali" class="btn btn-success">
                        <i class="fas fa-undo"></i> Konfirmasi Dikembalikan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
var BASE_URL = '<?= base_url() ?>';

// Tampilkan stok saat barang dipilih
$('#barang_id_pinjam').on('change', function () {
    const id = $(this).val();
    if (!id) {
        document.getElementById('infoStokPinjam').style.display = 'none';
        return;
    }
    $.ajax({
        url: BASE_URL + 'process/cari_barang.php',
        type: 'GET',
        data: { id: id },
        dataType: 'json',
        success: function (data) {
            if (data.status === 'found') {
                document.getElementById('angkaStokPinjam').textContent = data.stok;
                document.getElementById('infoStokPinjam').style.display = 'block';
            }
        }
    });
});

// Validasi jumlah tidak melebihi stok
document.getElementById('jumlahPinjam').addEventListener('input', function () {
    const stok   = parseInt(document.getElementById('angkaStokPinjam').textContent) || 0;
    const jumlah = parseInt(this.value) || 0;
    const errEl  = document.getElementById('errorJumlahPinjam');
    if (stok > 0 && jumlah > stok) {
        errEl.style.display = 'block';
        this.classList.add('is-invalid');
    } else {
        errEl.style.display = 'none';
        this.classList.remove('is-invalid');
    }
});

// Buka modal konfirmasi kembali
function konfirmasiKembali(id, namaBarang, namaPeminjam) {
    document.getElementById('inputIdPinjam').value = id;
    document.getElementById('textKonfirmasiKembali').innerHTML =
        'Konfirmasi pengembalian <strong>' + namaBarang + '</strong> dari <strong>' + namaPeminjam + '</strong>?';
    $('#modalKonfirmasiKembali').modal('show');
}
</script>