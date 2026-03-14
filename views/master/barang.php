<?php hakAkses(['admin']); ?>
<script>
function submit(x) {
    if (x == 'add') {
        $('[name="nama_barang"]').val("");
        $('[name="merek_id"]').val("").trigger('change');
        $('[name="kategori_id"]').val("").trigger('change');
        $('[name="keterangan"]').val("");
        $('[name="kondisi"]').val("").trigger('change');
        $('[name="lokasi"]').val("");
        $('[name="jenis"]').val("").trigger('change');
        $('#barangModal .modal-title').html('Tambah Barang');
        $('[name="ubah"]').hide();
        $('[name="tambah"]').show();
    } else {
        $('#barangModal .modal-title').html('Edit Barang');
        $('[name="tambah"]').hide();
        $('[name="ubah"]').show();

        $.ajax({
            type: "POST",
            data: { id: x },
            url: '<?= base_url(); ?>process/view_barang.php',
            dataType: 'json',
            success: function(data) {
                $('[name="idbarang"]').val(data.idbarang);
                $('[name="merek_id"]').val(data.merek_id).trigger('change');
                $('[name="kategori_id"]').val(data.kategori_id).trigger('change');
                $('[name="nama_barang"]').val(data.nama_barang);
                $('[name="keterangan"]').val(data.keterangan);
                $('[name="kondisi"]').val(data.kondisi).trigger('change');
                $('[name="lokasi"]').val(data.lokasi);
                $('[name="jenis"]').val(data.jenis).trigger('change');
            }
        });
    }
}
</script>

<!-- Begin Page Content -->
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Barang</h1>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <a href="#" class="btn btn-primary btn-icon-split btn-sm"
                data-toggle="modal" data-target="#barangModal" onclick="submit('add')">
                <span class="icon text-white-50"><i class="fas fa-plus"></i></span>
                <span class="text">Tambah</span>
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th width="20">NO</th>
                            <th>KODE BARANG</th>
                            <th>NAMA BARANG</th>
                            <th>MEREK</th>
                            <th>KATEGORI</th>
                            <th>JENIS</th>
                            <th>KETERANGAN</th>
                            <th class="text-center">STOK</th>
                            <th>KONDISI</th>
                            <th>LOKASI</th>
                            <th width="80">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $n = 1;
                        $query = mysqli_query($con,
                            "SELECT x.*, x1.nama_merek, x2.nama_kategori
                             FROM barang x
                             LEFT JOIN merek x1 ON x1.idmerek = x.merek_id
                             LEFT JOIN kategori x2 ON x2.idkategori = x.kategori_id
                             ORDER BY x.idbarang DESC"
                        ) or die(mysqli_error($con));
                        while ($row = mysqli_fetch_array($query)):
                            $jenis = $row['jenis'] ?? 'Tidak Habis Pakai';
                            $jenisBadge = $jenis === 'Habis Pakai' ? 'warning' : 'info';
                        ?>
                        <tr>
                            <td class="text-center"><?= $n++; ?></td>
                            <td><code><?= htmlspecialchars($row['kode_barang']); ?></code></td>
                            <td><?= htmlspecialchars($row['nama_barang']); ?></td>
                            <td><?= htmlspecialchars($row['nama_merek'] ?? '-'); ?></td>
                            <td><?= htmlspecialchars($row['nama_kategori'] ?? '-'); ?></td>
                            <td>
                                <span class="badge badge-<?= $jenisBadge ?>">
                                    <?= htmlspecialchars($jenis) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($row['keterangan']); ?></td>
                            <td class="text-center"><?= $row['stok']; ?></td>
                            <td><?= htmlspecialchars($row['kondisi']); ?></td>
                            <td><?= htmlspecialchars($row['lokasi']); ?></td>
                            <td>
                                <a href="#barangModal" data-toggle="modal"
                                    onclick="submit(<?= $row['idbarang']; ?>)"
                                    class="btn btn-sm btn-circle btn-info">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="<?= base_url(); ?>/process/barang.php?act=<?= encrypt('delete'); ?>&id=<?= encrypt($row['idbarang']); ?>"
                                    class="btn btn-sm btn-circle btn-danger btn-hapus">
                                    <i class="fas fa-trash"></i>
                                </a>
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

<!-- Modal Tambah/Edit Barang -->
<div class="modal fade" id="barangModal" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="<?= base_url(); ?>process/barang.php" method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel"></h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">

                        <!-- Nama Barang -->
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="nama_barang">Nama Barang <span class="text-danger">*</span></label>
                                <input type="hidden" name="idbarang" class="form-control">
                                <input type="text" class="form-control" id="nama_barang" name="nama_barang" required>
                            </div>
                        </div>

                        <!-- Merek -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="merek_id">Merek Barang <span class="text-danger">*</span></label>
                                <select name="merek_id" id="merek_id" class="form-control select2"
                                    style="width:100%;" required>
                                    <option value="">-- Pilih Merek --</option>
                                    <?= list_merek(); ?>
                                </select>
                            </div>
                        </div>

                        <!-- Kategori -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="kategori_id">Kategori Barang <span class="text-danger">*</span></label>
                                <select name="kategori_id" id="kategori_id" class="form-control select2"
                                    style="width:100%;" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    <?= list_kategori(); ?>
                                </select>
                            </div>
                        </div>

                        <!-- Jenis (BARU) -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="jenis">Jenis Barang <span class="text-danger">*</span></label>
                                <select name="jenis" id="jenis" class="form-control select2"
                                    style="width:100%;" required>
                                    <option value="">-- Pilih Jenis --</option>
                                    <option value="Habis Pakai">Habis Pakai</option>
                                    <option value="Tidak Habis Pakai">Tidak Habis Pakai</option>
                                </select>
                                <small class="text-muted">
                                    <i class="fas fa-info-circle"></i>
                                    Habis Pakai = ATK, tinta, dll |
                                    Tidak Habis Pakai = elektronik, perabot, dll
                                </small>
                            </div>
                        </div>

                        <!-- Kondisi -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="kondisi">Kondisi <span class="text-danger">*</span></label>
                                <select name="kondisi" id="kondisi" class="form-control select2"
                                    style="width:100%;" required>
                                    <option value="">-- Pilih Kondisi --</option>
                                    <option value="Baik">Baik</option>
                                    <option value="Rusak Ringan">Rusak Ringan</option>
                                    <option value="Rusak Berat">Rusak Berat</option>
                                    <option value="Hilang">Hilang</option>
                                </select>
                            </div>
                        </div>

                        <!-- Lokasi -->
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="lokasi">Lokasi <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="lokasi" name="lokasi"
                                    placeholder="Contoh: Lab Komputer, Gudang, Ruang Guru..." required>
                            </div>
                        </div>

                        <!-- Keterangan -->
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="keterangan">Keterangan</label>
                                <textarea name="keterangan" id="keterangan" cols="30" rows="3"
                                    class="form-control"></textarea>
                            </div>
                        </div>

                    </div>
                    <hr class="sidebar-divider">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button class="btn btn-primary float-right" type="submit" name="tambah">
                        <i class="fas fa-save"></i> Tambah
                    </button>
                    <button class="btn btn-primary float-right" type="submit" name="ubah">
                        <i class="fas fa-save"></i> Ubah
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>