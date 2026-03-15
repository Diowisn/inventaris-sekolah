<?php
include('../config/conn.php');
include('../config/function.php');

$filter_kategori = isset($_GET['kategori_id']) ? (int)$_GET['kategori_id'] : 0;
$filter_kondisi  = isset($_GET['kondisi'])     ? mysqli_real_escape_string($con, $_GET['kondisi']) : '';

$where = "WHERE 1=1";
if ($filter_kategori) $where .= " AND x.kategori_id = '$filter_kategori'";
if ($filter_kondisi)  $where .= " AND x.kondisi = '$filter_kondisi'";

$query = mysqli_query($con,
    "SELECT x.*, x1.nama_merek, x2.nama_kategori
     FROM barang x
     LEFT JOIN merek x1 ON x1.idmerek = x.merek_id
     LEFT JOIN kategori x2 ON x2.idkategori = x.kategori_id
     $where
     ORDER BY x2.nama_kategori ASC, x.nama_barang ASC"
) or die(mysqli_error($con));

$rows = [];
while ($row = mysqli_fetch_assoc($query)) {
    $rows[] = $row;
}

$totalJenis  = count($rows);
$totalStok   = array_sum(array_column($rows, 'stok'));
$totalBaik   = count(array_filter($rows, fn($r) => ($r['kondisi'] ?? 'Baik') === 'Baik'));
$totalRusak  = count(array_filter($rows, fn($r) => in_array($r['kondisi'] ?? '', ['Rusak Ringan','Rusak Berat'])));
$totalHilang = count(array_filter($rows, fn($r) => ($r['kondisi'] ?? '') === 'Hilang'));
$totalKritis = count(array_filter($rows, fn($r) => $r['stok'] <= 2));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Stok Barang - <?= date('d-m-Y') ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            color: #000;
            background: #fff;
            padding: 30px 40px;
        }

        /* ── Header Institusi ── */
        .header {
            display: flex;
            align-items: center;
            padding-bottom: 10px;
            border-bottom: 2px solid #000;
            margin-bottom: 6px;
        }
        .header img {
            width: 65px;
            height: 65px;
            object-fit: contain;
            margin-right: 16px;
        }
        .header-teks { flex: 1; text-align: center; }
        .header-teks .institusi {
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header-teks .alamat {
            font-size: 9pt;
            margin-top: 2px;
            line-height: 1.6;
        }
        .garis-bawah-header {
            border-bottom: 1px solid #000;
            margin-bottom: 14px;
        }

        /* ── Judul Laporan ── */
        .judul {
            text-align: center;
            margin-bottom: 14px;
        }
        .judul h2 {
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .judul .sub {
            font-size: 10pt;
            margin-top: 2px;
        }

        /* ── Ringkasan ── */
        .ringkasan {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 10pt;
        }
        .ringkasan td {
            border: 1px solid #000;
            padding: 4px 10px;
            text-align: center;
        }
        .ringkasan .label {
            font-size: 8pt;
            text-transform: uppercase;
            display: block;
            color: #444;
        }
        .ringkasan .nilai {
            font-size: 13pt;
            font-weight: bold;
        }

        /* ── Tabel Data ── */
        table.data {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
        }
        table.data th {
            background-color: #000;
            color: #fff;
            padding: 5px 7px;
            text-align: center;
            font-weight: bold;
        }
        table.data td {
            border: 1px solid #000;
            padding: 4px 7px;
            vertical-align: middle;
        }
        table.data .row-kategori {
            background-color: #e0e0e0;
            font-weight: bold;
            font-size: 9.5pt;
            padding: 3px 7px;
        }
        table.data tbody tr:nth-child(even) td {
            background-color: #f9f9f9;
        }
        table.data .row-total td {
            background-color: #000;
            color: #fff;
            font-weight: bold;
        }

        /* ── Keterangan ── */
        .keterangan {
            margin-top: 8px;
            font-size: 9pt;
            color: #444;
        }
        .keterangan span {
            margin-right: 16px;
        }

        /* ── Tanda Tangan ── */
        .ttd {
            margin-top: 36px;
            display: flex;
            justify-content: flex-end;
        }
        .ttd-box { text-align: center; width: 190px; }
        .ttd-box .garis {
            margin: 50px auto 4px;
            border-bottom: 1px solid #000;
        }

        /* ── Tombol (hanya layar) ── */
        .aksi {
            margin-bottom: 20px;
            padding: 10px 0;
            border-bottom: 1px dashed #ccc;
        }
        .aksi button {
            padding: 7px 20px;
            font-size: 10pt;
            border: 1px solid #000;
            background: #fff;
            cursor: pointer;
            border-radius: 3px;
            margin-right: 8px;
        }
        .aksi button:hover { background: #f0f0f0; }
        .aksi .btn-print { background: #000; color: #fff; }
        .aksi .btn-print:hover { background: #333; }
        .aksi small { color: #666; font-size: 9pt; }

        /* ── Print ── */
        @media print {
            .aksi { display: none !important; }
            body { padding: 15px 20px; }

            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }

            table.data th {
                background-color: #000 !important;
                color: #fff !important;
            }
            table.data .row-kategori {
                background-color: #e0e0e0 !important;
            }
            table.data tbody tr:nth-child(even) td {
                background-color: #f9f9f9 !important;
            }
            table.data .row-total td {
                background-color: #000 !important;
                color: #fff !important;
            }
            .ringkasan td {
                border: 1px solid #000 !important;
            }

            tr { page-break-inside: avoid; }
            .row-kategori { page-break-after: avoid; }
        }
    </style>
</head>
<body>

    <!-- Tombol Aksi (hanya tampil di layar) -->
    <div class="aksi">
        <button class="btn-print" onclick="window.print()">Cetak / Simpan PDF</button>
        <button onclick="window.close()">Tutup</button>
        <small>Tips: Pilih "Save as PDF" di dialog cetak untuk menyimpan sebagai file PDF.</small>
    </div>

    <!-- Header Institusi -->
    <div class="header">
        <img src="../assets/img/icon.png" alt="Logo">
        <div class="header-teks">
            <div class="institusi">Pondok Modern Assalaam</div>
            <div class="alamat">
                Jl. Raya Secang Km. 05 Gandokan, Kranggan, Temanggung 56271<br>
                Telp: (0293) 4960541 &nbsp;|&nbsp; HP: 0813-2725-3337 / 0813-2782-5824
            </div>
        </div>
    </div>
    <div class="garis-bawah-header"></div>

    <!-- Judul -->
    <div class="judul">
        <h2>Laporan Stok Barang</h2>
        <div class="sub">
            Dicetak pada: <?= date('d F Y, H:i') ?> WIB
            <?php if ($filter_kondisi): ?>
                &nbsp;&mdash;&nbsp; Filter Kondisi: <strong><?= htmlspecialchars($filter_kondisi) ?></strong>
            <?php endif; ?>
        </div>
    </div>

    <!-- Ringkasan Statistik -->
    <table class="ringkasan">
        <tr>
            <td>
                <span class="label">Jenis Barang</span>
                <span class="nilai"><?= $totalJenis ?></span>
            </td>
            <td>
                <span class="label">Total Stok</span>
                <span class="nilai"><?= $totalStok ?></span>
            </td>
            <td>
                <span class="label">Kondisi Baik</span>
                <span class="nilai"><?= $totalBaik ?></span>
            </td>
            <td>
                <span class="label">Rusak</span>
                <span class="nilai"><?= $totalRusak ?></span>
            </td>
            <td>
                <span class="label">Hilang</span>
                <span class="nilai"><?= $totalHilang ?></span>
            </td>
            <td>
                <span class="label">Stok Kritis (≤2)</span>
                <span class="nilai"><?= $totalKritis ?></span>
            </td>
        </tr>
    </table>

    <!-- Tabel Data -->
    <table class="data">
        <thead>
            <tr>
                <th width="25">NO</th>
                <th width="95">KODE BARANG</th>
                <th>NAMA BARANG</th>
                <th width="80">MEREK</th>
                <th width="85">KATEGORI</th>
                <th width="85">LOKASI</th>
                <th width="90">KONDISI</th>
                <th width="40">STOK</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $n = 1;
        $kategoriAktif = '';
        foreach ($rows as $row):
            if ($row['nama_kategori'] !== $kategoriAktif):
                $kategoriAktif = $row['nama_kategori'];
        ?>
            <tr>
                <td colspan="8" class="row-kategori">
                    <?= htmlspecialchars($kategoriAktif ?: 'Tanpa Kategori') ?>
                </td>
            </tr>
        <?php endif;
            $kondisi  = $row['kondisi'] ?? 'Baik';
            $rowClass = '';
        ?>
            <tr <?= $rowClass ?>>
                <td align="center"><?= $n++ ?></td>
                <td><?= htmlspecialchars($row['kode_barang'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                <td><?= htmlspecialchars($row['nama_merek'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['nama_kategori'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['lokasi'] ?? '-') ?></td>
                <td align="center"><?= htmlspecialchars($kondisi) ?></td>
                <td align="center"><strong><?= $row['stok'] ?></strong></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tr class="row-total">
            <td colspan="7" align="right">Total Stok Keseluruhan</td>
            <td align="center"><?= $totalStok ?></td>
        </tr>
    </table>

    <!-- Keterangan -->
    <div class="keterangan">
        Keterangan:
        <span>*) Stok kritis = stok kurang dari atau sama dengan 2 unit</span>
    </div>

    <!-- Tanda Tangan -->
    <div class="ttd">
        <div class="ttd-box">
            <div>Temanggung, <?= date('d F Y') ?></div>
            <div>Penanggung Jawab,</div>
            <div class="garis"></div>
            <div>(__________________)</div>
        </div>
    </div>

</body>
</html>