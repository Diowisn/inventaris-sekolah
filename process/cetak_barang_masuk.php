<?php
include('../config/conn.php');
include('../config/function.php');

$query = mysqli_query($con,
    "SELECT x.*, x1.nama_barang, x2.nama_merek, x3.nama_kategori
     FROM barang_masuk x
     JOIN barang x1 ON x1.idbarang = x.barang_id
     LEFT JOIN merek x2 ON x2.idmerek = x1.merek_id
     LEFT JOIN kategori x3 ON x3.idkategori = x1.kategori_id
     ORDER BY x.idbarang_masuk DESC"
) or die(mysqli_error($con));

$totalJumlah = 0;
$totalHarga  = 0;
$rows = [];
while ($row = mysqli_fetch_assoc($query)) {
    $totalJumlah += $row['jumlah'];
    $totalHarga  += $row['harga'] ?? 0;
    $rows[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Barang Masuk</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            padding: 20px;
            color: #000;
        }

        .header-institusi {
            display: flex;
            align-items: center;
            border-bottom: 3px double #000;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .header-logo {
            width: 70px;
            height: 70px;
            margin-right: 12px;
            flex-shrink: 0;
        }
        .header-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .header-teks {
            flex: 1;
            text-align: center;
        }
        .header-teks .nama-institusi {
            font-size: 15pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header-teks .alamat {
            font-size: 9pt;
            margin-top: 3px;
            line-height: 1.5;
        }
        .header-teks .telp {
            font-size: 9pt;
        }

        .judul-laporan {
            text-align: center;
            margin: 10px 0 12px;
        }
        .judul-laporan h2 {
            font-size: 13pt;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            font-size: 10pt;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px 7px;
        }
        thead tr {
            background-color: #333;
            color: #fff;
            text-align: center;
        }
        tbody tr:nth-child(even) {
            background-color: #f5f5f5;
        }
        tfoot tr {
            font-weight: bold;
            background-color: #ddd;
        }

        .ttd {
            margin-top: 30px;
            display: flex;
            justify-content: flex-end;
        }
        .ttd-box {
            text-align: center;
            width: 200px;
        }
        .ttd-space {
            height: 55px;
            border-bottom: 1px solid #000;
            margin: 6px 0;
        }

        @media print {
            body { padding: 10px; }
        }
    </style>
</head>
<body>

    <!-- Header Institusi -->
    <div class="header-institusi">
        <div class="header-logo">
            <img src="../assets/img/icon.png" alt="Logo">
        </div>
        <div class="header-teks">
            <div class="nama-institusi">Pondok Modern Assalaam</div>
            <div class="alamat">
                Jl. Raya Secang Km. 05 Gandokan, Kranggan, Temanggung 56271
            </div>
            <div class="telp">
                Telp: (0293) 4960541 &nbsp;|&nbsp;
                HP: 0813-2725-3337 &nbsp;/&nbsp; 0813-2782-5824
            </div>
        </div>
    </div>

    <!-- Judul Laporan -->
    <div class="judul-laporan">
        <h2>Laporan Barang Masuk</h2>
    </div>

    <!-- Tabel -->
    <table>
        <thead>
            <tr>
                <th width="25">NO</th>
                <th width="80">TANGGAL</th>
                <th>NAMA BARANG</th>
                <th width="80">MEREK</th>
                <th width="80">KATEGORI</th>
                <th>KETERANGAN</th>
                <th width="45">JML</th>
                <th width="110">TOTAL HARGA</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($rows)): ?>
            <tr>
                <td colspan="8" align="center" style="padding:16px; color:#666;">
                    Tidak ada data barang masuk
                </td>
            </tr>
        <?php else: ?>
        <?php $n = 1; foreach ($rows as $row): ?>
            <tr>
                <td align="center"><?= $n++ ?></td>
                <td align="center"><?= date('d-m-Y', strtotime($row['tanggal'])) ?></td>
                <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                <td><?= htmlspecialchars($row['nama_merek'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['nama_kategori'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['keterangan']) ?></td>
                <td align="center"><?= $row['jumlah'] ?></td>
                <td align="right">
                    <?= ($row['harga'] ?? 0) > 0
                        ? 'Rp ' . number_format($row['harga'], 0, ',', '.')
                        : '-' ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" align="right">Total:</td>
                <td align="center"><?= $totalJumlah ?></td>
                <td align="right">
                    <?= $totalHarga > 0
                        ? 'Rp ' . number_format($totalHarga, 0, ',', '.')
                        : '-' ?>
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- Tanda Tangan -->
    <div class="ttd">
        <div class="ttd-box">
            <div>Temanggung, <?= date('d F Y') ?></div>
            <div>Penanggung Jawab,</div>
            <div class="ttd-space"></div>
            <div>(__________________)</div>
        </div>
    </div>

</body>
</html>

<script>window.print();</script>