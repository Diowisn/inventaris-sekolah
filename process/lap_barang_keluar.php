<?php
include('../config/conn.php');
include('../config/function.php');

$tgl_awal  = mysqli_real_escape_string($con, $_POST['tanggal_awal']);
$tgl_akhir = mysqli_real_escape_string($con, $_POST['tanggal_akhir']);

$query = mysqli_query($con,
    "SELECT x.*, x1.nama_barang, x2.nama_merek, x3.nama_kategori
     FROM barang_keluar x JOIN barang x1 ON x1.idbarang=x.barang_id
     LEFT JOIN merek x2 ON x2.idmerek=x1.merek_id
     LEFT JOIN kategori x3 ON x3.idkategori=x1.kategori_id
     WHERE x.tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir'
     ORDER BY x.idbarang_keluar DESC"
) or die(mysqli_error($con));

$totalJumlah = 0; $rows = [];
while ($row = mysqli_fetch_assoc($query)) {
    $totalJumlah += $row['jumlah'];
    $rows[] = $row;
}
?>
<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8">
<title>Laporan Barang Keluar</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:Arial,sans-serif; font-size:11pt; color:#000; background:#fff; padding:30px 40px; }
.header { display:flex; align-items:center; padding-bottom:10px; border-bottom:2px solid #000; margin-bottom:6px; }
.header img { width:65px; height:65px; object-fit:contain; margin-right:16px; }
.header-teks { flex:1; text-align:center; }
.header-teks .institusi { font-size:14pt; font-weight:bold; text-transform:uppercase; }
.header-teks .alamat { font-size:9pt; margin-top:2px; line-height:1.6; }
.garis-bawah { border-bottom:1px solid #000; margin-bottom:14px; }
.judul { text-align:center; margin-bottom:14px; }
.judul h2 { font-size:12pt; font-weight:bold; text-transform:uppercase; letter-spacing:1px; }
.judul .sub { font-size:10pt; margin-top:2px; }
table.data { width:100%; border-collapse:collapse; font-size:10pt; }
table.data th { background:#000; color:#fff; padding:5px 7px; text-align:center; }
table.data td { border:1px solid #000; padding:4px 7px; }
table.data tbody tr:nth-child(even) td { background:#f9f9f9; }
table.data .row-total td { background:#000; color:#fff; font-weight:bold; }
.ttd { margin-top:36px; display:flex; justify-content:flex-end; }
.ttd-box { text-align:center; width:190px; }
.ttd-box .garis { margin:50px auto 4px; border-bottom:1px solid #000; }
.aksi { margin-bottom:20px; padding:10px 0; border-bottom:1px dashed #ccc; }
.aksi button { padding:7px 20px; font-size:10pt; border:1px solid #000; background:#fff; cursor:pointer; border-radius:3px; margin-right:8px; }
.aksi .btn-print { background:#000; color:#fff; }
.aksi small { color:#666; font-size:9pt; }
@media print {
    .aksi { display:none !important; }
    body { padding:15px 20px; }
    * { -webkit-print-color-adjust:exact !important; print-color-adjust:exact !important; color-adjust:exact !important; }
    table.data th { background:#000 !important; color:#fff !important; }
    table.data tbody tr:nth-child(even) td { background:#f9f9f9 !important; }
    table.data .row-total td { background:#000 !important; color:#fff !important; }
    tr { page-break-inside:avoid; }
}
</style></head><body>
<div class="aksi">
    <button class="btn-print" onclick="window.print()">Cetak / Simpan PDF</button>
    <button onclick="window.close()">Tutup</button>
    <small>Tips: Pilih "Save as PDF" di dialog cetak untuk menyimpan sebagai file PDF.</small>
</div>
<div class="header">
    <img src="../assets/img/icon.png" alt="Logo">
    <div class="header-teks">
        <div class="institusi">Pondok Modern Assalaam</div>
        <div class="alamat">Jl. Raya Secang Km. 05 Gandokan, Kranggan, Temanggung 56271<br>Telp: (0293) 4960541 &nbsp;|&nbsp; HP: 0813-2725-3337 / 0813-2782-5824</div>
    </div>
</div>
<div class="garis-bawah"></div>
<div class="judul">
    <h2>Laporan Barang Keluar</h2>
    <div class="sub">
        Periode: <?= date('d F Y', strtotime($tgl_awal)) ?> &mdash; <?= date('d F Y', strtotime($tgl_akhir)) ?>
    </div>
</div>
<table class="data">
    <thead><tr>
        <th width="25">NO</th><th width="80">TANGGAL</th><th>NAMA BARANG</th>
        <th width="75">MEREK</th><th width="75">KATEGORI</th>
        <th>PENERIMA / KEPERLUAN</th><th>KETERANGAN</th><th width="40">JML</th>
    </tr></thead>
    <tbody>
    <?php if (empty($rows)): ?>
        <tr><td colspan="8" align="center" style="padding:12px;">Tidak ada data pada periode ini</td></tr>
    <?php else: $n=1; foreach ($rows as $row): ?>
        <tr>
            <td align="center"><?= $n++ ?></td>
            <td align="center"><?= date('d-m-Y',strtotime($row['tanggal'])) ?></td>
            <td><?= htmlspecialchars($row['nama_barang']) ?></td>
            <td><?= htmlspecialchars($row['nama_merek']??'-') ?></td>
            <td><?= htmlspecialchars($row['nama_kategori']??'-') ?></td>
            <td><?= htmlspecialchars($row['penerima']??'-') ?></td>
            <td><?= htmlspecialchars($row['keterangan']) ?></td>
            <td align="center"><?= $row['jumlah'] ?></td>
        </tr>
    <?php endforeach; endif; ?>
    </tbody>
    <tr class="row-total">
        <td colspan="7" align="right">Total Barang Keluar</td>
        <td align="center"><?= $totalJumlah ?></td>
    </tr>
</table>
<div class="ttd"><div class="ttd-box">
    <div>Temanggung, <?= date('d F Y') ?></div>
    <div>Penanggung Jawab,</div>
    <div class="garis"></div>
    <div>(__________________)</div>
</div></div>
</body></html>