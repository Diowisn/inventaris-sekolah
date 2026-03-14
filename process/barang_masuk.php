<?php
session_start();
include('../config/conn.php');
include('../config/function.php');

if (isset($_POST['tambah'])) {
    $barang_id  = (int) $_POST['barang_id'];
    $jumlah     = (int) $_POST['jumlah'];
    $harga      = (int) $_POST['harga'];
    $keterangan = mysqli_real_escape_string($con, trim($_POST['keterangan']));
    $tanggal    = mysqli_real_escape_string($con, $_POST['tanggal']);

    $insert = mysqli_query($con,
        "INSERT INTO barang_masuk (barang_id, jumlah, harga, keterangan, tanggal)
         VALUES ('$barang_id', '$jumlah', '$harga', '$keterangan', '$tanggal')"
    ) or die(mysqli_error($con));

    if ($insert) {
        $_SESSION['success'] = 'Berhasil menambahkan data barang masuk';
    } else {
        $_SESSION['error'] = 'Gagal menambahkan data barang masuk';
    }
    header('Location:../?barang_masuk');
    exit;
}
?>