<?php

header('Content-Type: application/json');
session_start();
include('../config/conn.php');
include('../config/function.php');

if (!isset($_SESSION['iduser'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$kode = isset($_GET['kode']) ? trim($_GET['kode']) : '';

if (empty($kode)) {
    echo json_encode(['status' => 'error', 'message' => 'Kode kosong']);
    exit;
}

$kode_esc = mysqli_real_escape_string($con, $kode);

$query = mysqli_query($con,
    "SELECT b.idbarang, b.kode_barang, b.nama_barang, b.stok,
            COALESCE(b.kondisi, 'Baik') as kondisi,
            COALESCE(b.lokasi, '') as lokasi,
            COALESCE(m.nama_merek, '-') as nama_merek,
            COALESCE(k.nama_kategori, '-') as nama_kategori
     FROM barang b
     LEFT JOIN merek m ON m.idmerek = b.merek_id
     LEFT JOIN kategori k ON k.idkategori = b.kategori_id
     WHERE b.kode_barang = '$kode_esc'
     LIMIT 1"
);

if ($query && $row = mysqli_fetch_assoc($query)) {
    echo json_encode([
        'status'        => 'found',
        'idbarang'      => $row['idbarang'],
        'kode_barang'   => $row['kode_barang'],
        'nama_barang'   => $row['nama_barang'],
        'stok'          => $row['stok'],
        'kondisi'       => $row['kondisi'],
        'lokasi'        => $row['lokasi'],
        'nama_merek'    => $row['nama_merek'],
        'nama_kategori' => $row['nama_kategori'],
    ]);
} else {
    echo json_encode(['status' => 'not_found']);
}
?>