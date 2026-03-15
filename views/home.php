<?php

hakAkses(['admin', 'staff', 'gudang']);
$now = date('Y-m-d');

// ── Statistik ─────────────────────────────────────────────────────────────────
$totalBarang   = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM barang"))['total'];
$totalStok     = mysqli_fetch_assoc(mysqli_query($con, "SELECT SUM(stok) as total FROM barang"))['total'] ?? 0;
$stokKritis    = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM barang WHERE stok <= 2"))['total'];
$barangRusak   = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM barang WHERE kondisi IN ('Rusak Ringan','Rusak Berat','Hilang')"))['total'];
$masukHariIni  = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM barang_masuk WHERE tanggal = '$now'"))['total'];
$keluarHariIni = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM barang_keluar WHERE tanggal = '$now'"))['total'];
$dipinjam      = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM peminjaman WHERE status IN ('Dipinjam','Terlambat')"))['total'];
$terlambat     = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as total FROM peminjaman WHERE status='Terlambat'"))['total'];
?>

<div class="container-fluid">

    <!-- Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
        <small class="text-muted"><?= date('l, d F Y') ?></small>
    </div>

    <!-- ── Alert ──────────────────────────────────────────────────────────── -->
    <?php if ($terlambat > 0): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-clock mr-2"></i>
        <strong><?= $terlambat ?> peminjaman</strong> sudah melewati tanggal rencana kembali!
        <a href="<?= base_url() ?>?peminjaman" class="alert-link ml-2">Lihat detail →</a>
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
    <?php endif; ?>

    <?php if ($stokKritis > 0): ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        <strong>Perhatian!</strong> Ada <strong><?= $stokKritis ?></strong> barang dengan stok kritis (≤ 2 unit).
        <a href="<?= base_url() ?>?lap_stok&kritis" class="alert-link ml-2">Lihat detail →</a>
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
    <?php endif; ?>

    <?php if ($barangRusak > 0): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-tools mr-2"></i>
        <strong><?= $barangRusak ?></strong> barang dalam kondisi rusak atau hilang.
        <a href="<?= base_url() ?>?lap_stok&kondisi=Rusak+Berat" class="alert-link ml-2">Lihat detail →</a>
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
    <?php endif; ?>

    <!-- ── Kartu Statistik ────────────────────────────────────────────────── -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body py-2">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Jenis Barang</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalBarang ?></div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body py-2">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Stok</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalStok ?></div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <a href="<?= base_url() ?>?lap_stok&kritis" class="text-decoration-none">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body py-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Stok Kritis</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stokKritis ?></div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <a href="<?= base_url() ?>?lap_stok&kondisi=Rusak+Berat" class="text-decoration-none">
                <div class="card border-left-danger shadow h-100 py-2">
                    <div class="card-body py-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Rusak/Hilang</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $barangRusak ?></div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body py-2">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Masuk Hari Ini</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $masukHariIni ?></div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <a href="<?= base_url() ?>?peminjaman" class="text-decoration-none">
                <div class="card border-left-secondary shadow h-100 py-2">
                    <div class="card-body py-2">
                        <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                            Sedang Dipinjam
                            <?php if ($terlambat > 0): ?>
                                <span class="badge badge-danger ml-1"><?= $terlambat ?> terlambat</span>
                            <?php endif; ?>
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $dipinjam ?></div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- ── Tabel Barang Masuk & Keluar Hari Ini ───────────────────────────── -->
    <div class="row">

        <!-- Barang Masuk -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-arrow-down mr-1"></i> Barang Masuk Hari Ini
                    </h6>
                    <a href="<?= base_url() ?>process/cetak_barang_masuk_today.php" target="_blank"
                       class="btn btn-info btn-sm">
                        <i class="fas fa-print"></i> Cetak
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>BARANG</th>
                                    <th>KETERANGAN</th>
                                    <th class="text-center">JML</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $qMasuk = mysqli_query($con,
                                    "SELECT x.*, x1.nama_barang, x2.nama_merek
                                     FROM barang_masuk x
                                     JOIN barang x1 ON x1.idbarang = x.barang_id
                                     LEFT JOIN merek x2 ON x2.idmerek = x1.merek_id
                                     WHERE x.tanggal = '$now'
                                     ORDER BY x.idbarang_masuk DESC"
                                ) or die(mysqli_error($con));
                                if (mysqli_num_rows($qMasuk) == 0): ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">
                                        <i class="fas fa-inbox mr-1"></i> Belum ada transaksi hari ini
                                    </td>
                                </tr>
                                <?php else: while ($row = mysqli_fetch_array($qMasuk)): ?>
                                <tr>
                                    <td>
                                        <span class="font-weight-bold"><?= htmlspecialchars($row['nama_barang']) ?></span><br>
                                        <small class="text-muted"><?= htmlspecialchars($row['nama_merek'] ?? '-') ?></small>
                                    </td>
                                    <td><small><?= htmlspecialchars($row['keterangan']) ?></small></td>
                                    <td class="text-center font-weight-bold text-success">+<?= $row['jumlah'] ?></td>
                                </tr>
                                <?php endwhile; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Barang Keluar -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-danger">
                        <i class="fas fa-arrow-up mr-1"></i> Barang Keluar Hari Ini
                    </h6>
                    <a href="<?= base_url() ?>process/cetak_barang_keluar_today.php" target="_blank"
                       class="btn btn-info btn-sm">
                        <i class="fas fa-print"></i> Cetak
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>BARANG</th>
                                    <th>PENERIMA</th>
                                    <th class="text-center">JML</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $qKeluar = mysqli_query($con,
                                    "SELECT x.*, x1.nama_barang, x2.nama_merek
                                     FROM barang_keluar x
                                     JOIN barang x1 ON x1.idbarang = x.barang_id
                                     LEFT JOIN merek x2 ON x2.idmerek = x1.merek_id
                                     WHERE x.tanggal = '$now'
                                     ORDER BY x.idbarang_keluar DESC"
                                ) or die(mysqli_error($con));
                                if (mysqli_num_rows($qKeluar) == 0): ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">
                                        <i class="fas fa-inbox mr-1"></i> Belum ada transaksi hari ini
                                    </td>
                                </tr>
                                <?php else: while ($row = mysqli_fetch_array($qKeluar)): ?>
                                <tr>
                                    <td>
                                        <span class="font-weight-bold"><?= htmlspecialchars($row['nama_barang']) ?></span><br>
                                        <small class="text-muted"><?= htmlspecialchars($row['nama_merek'] ?? '-') ?></small>
                                    </td>
                                    <td><small><?= htmlspecialchars($row['penerima'] ?? $row['keterangan']) ?></small></td>
                                    <td class="text-center font-weight-bold text-danger">-<?= $row['jumlah'] ?></td>
                                </tr>
                                <?php endwhile; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Peminjaman Aktif (Terlambat di atas) ───────────────────────────── -->
    <?php if ($dipinjam > 0):
        $qPinjam = mysqli_query($con,
            "SELECT p.*, b.nama_barang, b.kode_barang
             FROM peminjaman p
             JOIN barang b ON b.idbarang = p.barang_id
             WHERE p.status IN ('Dipinjam','Terlambat')
             ORDER BY FIELD(p.status,'Terlambat','Dipinjam'), p.tanggal_rencana ASC
             LIMIT 5"
        );
    ?>
    <div class="card shadow mb-4 border-left-<?= $terlambat > 0 ? 'danger' : 'warning' ?>">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-<?= $terlambat > 0 ? 'danger' : 'warning' ?>">
                <i class="fas fa-hand-holding mr-1"></i> Peminjaman Aktif
                <?php if ($terlambat > 0): ?>
                    <span class="badge badge-danger ml-1"><?= $terlambat ?> terlambat</span>
                <?php endif; ?>
            </h6>
            <a href="<?= base_url() ?>?peminjaman" class="btn btn-sm btn-outline-secondary">
                Lihat Semua
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>BARANG</th>
                            <th>PEMINJAM</th>
                            <th>RENCANA KEMBALI</th>
                            <th class="text-center">STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($qPinjam)):
                            $badge = $row['status'] === 'Terlambat' ? 'danger' : 'warning';
                        ?>
                        <tr>
                            <td>
                                <span class="font-weight-bold"><?= htmlspecialchars($row['nama_barang']) ?></span><br>
                                <small class="text-muted"><code><?= htmlspecialchars($row['kode_barang']) ?></code></small>
                            </td>
                            <td><?= htmlspecialchars($row['nama_peminjam']) ?></td>
                            <td>
                                <?= date('d-m-Y', strtotime($row['tanggal_rencana'])) ?>
                                <?php if ($row['status'] === 'Terlambat'): ?>
                                    <br><small class="text-danger">
                                        <i class="fas fa-clock"></i>
                                        <?= (new DateTime())->diff(new DateTime($row['tanggal_rencana']))->days ?> hari terlambat
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-<?= $badge ?>"><?= $row['status'] ?></span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if ($dipinjam > 5): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-2">
                                <small>... dan <?= $dipinjam - 5 ?> peminjaman lainnya</small>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── Stok Kritis ────────────────────────────────────────────────────── -->
    <?php if ($stokKritis > 0):
        $qKritis = mysqli_query($con,
            "SELECT b.kode_barang, b.nama_barang, b.stok, b.lokasi, k.nama_kategori
             FROM barang b
             LEFT JOIN kategori k ON k.idkategori = b.kategori_id
             WHERE b.stok <= 2
             ORDER BY b.stok ASC
             LIMIT 10"
        );
    ?>
    <div class="card shadow mb-4 border-left-warning">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-warning">
                <i class="fas fa-exclamation-triangle mr-1"></i> Barang Stok Kritis
            </h6>
            <?php if ($_SESSION['level'] == 'admin'): ?>
            <a href="<?= base_url() ?>?lap_stok&kritis" class="btn btn-sm btn-outline-warning">
                Lihat Semua
            </a>
            <?php endif; ?>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>KODE</th>
                            <th>NAMA BARANG</th>
                            <th>KATEGORI</th>
                            <th>LOKASI</th>
                            <th class="text-center">STOK</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($qKritis)): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($row['kode_barang']) ?></code></td>
                            <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                            <td><?= htmlspecialchars($row['nama_kategori'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($row['lokasi']) ?: '-' ?></td>
                            <td class="text-center">
                                <span class="badge badge-<?= $row['stok'] == 0 ? 'danger' : 'warning' ?>">
                                    <?= $row['stok'] ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>