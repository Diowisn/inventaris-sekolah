<?php
include('../config/conn.php');
include('../config/function.php');

// ── Filter (opsional) ─────────────────────────────────────────────────────────
$filter_kategori = isset($_GET['kategori_id']) ? (int)$_GET['kategori_id'] : 0;
$filter_kondisi  = isset($_GET['kondisi'])     ? mysqli_real_escape_string($con, $_GET['kondisi']) : '';

$where = "WHERE 1=1";
if ($filter_kategori) $where .= " AND x.kategori_id = '$filter_kategori'";
if ($filter_kondisi)  $where .= " AND x.kondisi = '$filter_kondisi'";

// ── Query utama ───────────────────────────────────────────────────────────────
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

// ── Statistik ─────────────────────────────────────────────────────────────────
$totalJenis  = count($rows);
$totalStok   = array_sum(array_column($rows, 'stok'));
$totalBaik   = count(array_filter($rows, fn($r) => ($r['kondisi'] ?? 'Baik') === 'Baik'));
$totalRusak  = count(array_filter($rows, fn($r) => in_array($r['kondisi'] ?? '', ['Rusak Ringan', 'Rusak Berat'])));
$totalHilang = count(array_filter($rows, fn($r) => ($r['kondisi'] ?? '') === 'Hilang'));
$totalKritis = count(array_filter($rows, fn($r) => $r['stok'] <= 2));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Stok Barang</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            color: #000;
            padding: 20px;
        }

        /* Header institusi */
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

        /* Judul laporan */
        .judul-laporan {
            text-align: center;
            margin: 10px 0 4px;
        }
        .judul-laporan h2 {
            font-size: 13pt;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .judul-laporan p {
            font-size: 10pt;
            margin-top: 3px;
        }

        /* Statistik */
        .statistik {
            display: flex;
            gap: 6px;
            margin-bottom: 12px;
        }
        .stat-box {
            border: 1px solid #000;
            padding: 6px 10px;
            text-align: center;
            flex: 1;
        }
        .stat-box .angka { font-size: 15pt; font-weight: bold; }
        .stat-box .label { font-size: 8pt; text-transform: uppercase; }

        /* Tabel */
        table { border-collapse: collapse; width: 100%; font-size: 10pt; }
        th, td { border: 1px solid #000; padding: 5px 7px; }
        thead tr { background-color: #333; color: #fff; text-align: center; }
        tbody tr:nth-child(even) { background-color: #f5f5f5; }

        .row-kategori { background: #ddd !important; font-weight: bold; }

        .kondisi-baik         { color: #2e7d32; font-weight: bold; }
        .kondisi-rusak-ringan { color: #f57f17; font-weight: bold; }
        .kondisi-rusak-berat  { color: #c62828; font-weight: bold; }
        .kondisi-hilang       { color: #6a1b9a; font-weight: bold; }

        .stok-kritis { background-color: #fff3cd !important; }
        .stok-habis  { background-color: #f8d7da !important; }

        .keterangan { margin-top: 8px; font-size: 9pt; display: flex; gap: 16px; }
        .ket-item   { display: flex; align-items: center; gap: 4px; }
        .ket-box    { width: 14px; height: 14px; border: 1px solid #999; }

        .ttd       { margin-top: 30px; display: flex; justify-content: flex-end; }
        .ttd-box   { text-align: center; width: 200px; }
        .ttd-space { height: 60px; border-bottom: 1px solid #000; margin: 8px 0; }

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
        <h2>Laporan Stok Barang</h2>
        <p>Dicetak pada: <?= date('d F Y, H:i') ?> WIB</p>
        <?php if ($filter_kondisi): ?>
            <p>Filter Kondisi: <strong><?= htmlspecialchars($filter_kondisi) ?></strong></p>
        <?php endif; ?>
    </div>

    <br>

    <!-- Statistik Ringkasan -->
    <div class="statistik">
        <div class="stat-box">
            <div class="angka"><?= $totalJenis ?></div>
            <div class="label">Jenis Barang</div>
        </div>
        <div class="stat-box">
            <div class="angka"><?= $totalStok ?></div>
            <div class="label">Total Stok</div>
        </div>
        <div class="stat-box" style="color:#2e7d32;">
            <div class="angka"><?= $totalBaik ?></div>
            <div class="label">Kondisi Baik</div>
        </div>
        <div class="stat-box" style="color:#f57f17;">
            <div class="angka"><?= $totalRusak ?></div>
            <div class="label">Rusak</div>
        </div>
        <div class="stat-box" style="color:#6a1b9a;">
            <div class="angka"><?= $totalHilang ?></div>
            <div class="label">Hilang</div>
        </div>
        <div class="stat-box" style="color:#e65100;">
            <div class="angka"><?= $totalKritis ?></div>
            <div class="label">Stok Kritis</div>
        </div>
    </div>

    <!-- Tabel Stok -->
    <table>
        <thead>
            <tr>
                <th width="25">NO</th>
                <th width="90">KODE BARANG</th>
                <th>NAMA BARANG</th>
                <th width="80">MEREK</th>
                <th width="80">KATEGORI</th>
                <th width="90">LOKASI</th>
                <th width="90">KONDISI</th>
                <th width="45">STOK</th>
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
                    &nbsp;<?= htmlspecialchars($kategoriAktif ?: 'Tanpa Kategori') ?>
                </td>
            </tr>
        <?php endif;
            $kondisi      = $row['kondisi'] ?? 'Baik';
            $kondisiClass = match($kondisi) {
                'Baik'         => 'kondisi-baik',
                'Rusak Ringan' => 'kondisi-rusak-ringan',
                'Rusak Berat'  => 'kondisi-rusak-berat',
                'Hilang'       => 'kondisi-hilang',
                default        => ''
            };
            $rowClass = '';
            if ($row['stok'] == 0)     $rowClass = 'stok-habis';
            elseif ($row['stok'] <= 2) $rowClass = 'stok-kritis';
        ?>
            <tr class="<?= $rowClass ?>">
                <td align="center"><?= $n++ ?></td>
                <td><code><?= htmlspecialchars($row['kode_barang'] ?? '-') ?></code></td>
                <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                <td><?= htmlspecialchars($row['nama_merek'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['nama_kategori'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['lokasi'] ?? '-') ?></td>
                <td align="center" class="<?= $kondisiClass ?>"><?= htmlspecialchars($kondisi) ?></td>
                <td align="center" style="font-weight:bold;">
                    <?= $row['stok'] ?>
                    <?php if ($row['stok'] == 0): ?> ⚠
                    <?php elseif ($row['stok'] <= 2): ?> !
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>

        <tr style="background:#333; color:#fff; font-weight:bold;">
            <td colspan="7" align="right">TOTAL STOK KESELURUHAN :</td>
            <td align="center"><?= $totalStok ?></td>
        </tr>
        </tbody>
    </table>

    <!-- Keterangan warna -->
    <div class="keterangan">
        <div class="ket-item">
            <div class="ket-box" style="background:#fff3cd;"></div> Stok kritis (≤ 2)
        </div>
        <div class="ket-item">
            <div class="ket-box" style="background:#f8d7da;"></div> Stok habis (0)
        </div>
    </div>

    <!-- Tanda tangan -->
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