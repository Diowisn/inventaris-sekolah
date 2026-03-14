<?php

session_start();
include('../config/conn.php');
include('../config/function.php');

if (isset($_POST['id'])) {
    $id    = (int) $_POST['id'];
    $query = mysqli_query($con, "SELECT * FROM barang WHERE idbarang = '$id' LIMIT 1");
    $data  = mysqli_fetch_assoc($query);

    echo json_encode([
        'idbarang'    => $data['idbarang'],
        'kode_barang' => $data['kode_barang'],
        'nama_barang' => $data['nama_barang'],
        'merek_id'    => $data['merek_id'],
        'kategori_id' => $data['kategori_id'],
        'keterangan'  => $data['keterangan'],
        'kondisi'     => $data['kondisi'] ?? 'Baik',
        'lokasi'      => $data['lokasi'] ?? '',
        'jenis'       => $data['jenis'] ?? 'Tidak Habis Pakai',
    ]);
}
?>