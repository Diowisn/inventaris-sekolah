<?php
session_start();
include('../config/conn.php');
include('../config/function.php');

if (isset($_POST['tambah'])) {
    $barang_id  = (int) $_POST['barang_id'];
    $jumlah     = (int) $_POST['jumlah'];
    $penerima   = mysqli_real_escape_string($con, trim($_POST['penerima']));
    $keterangan = mysqli_real_escape_string($con, trim($_POST['keterangan']));
    $tanggal    = mysqli_real_escape_string($con, $_POST['tanggal']);

    $cekStok = mysqli_fetch_assoc(
        mysqli_query($con, "SELECT stok, nama_barang FROM barang WHERE idbarang = '$barang_id'")
    );

    if (!$cekStok) {
        $_SESSION['error'] = 'Barang tidak ditemukan';
        header('Location:../?barang_keluar');
        exit;
    }

    if ($jumlah > $cekStok['stok']) {
        $_SESSION['error'] = 'Gagal: Jumlah keluar (' . $jumlah . ') melebihi stok tersedia (' . $cekStok['stok'] . ' unit)';
        header('Location:../?barang_keluar');
        exit;
    }

    if ($jumlah <= 0) {
        $_SESSION['error'] = 'Gagal: Jumlah harus lebih dari 0';
        header('Location:../?barang_keluar');
        exit;
    }

    $insert = mysqli_query($con,
        "INSERT INTO barang_keluar (barang_id, jumlah, penerima, keterangan, tanggal)
         VALUES ('$barang_id', '$jumlah', '$penerima', '$keterangan', '$tanggal')"
    ) or die(mysqli_error($con));

    if ($insert) {
        $_SESSION['success'] = 'Berhasil mencatat barang keluar: ' . $cekStok['nama_barang'] . ' (' . $jumlah . ' unit)';
    } else {
        $_SESSION['error'] = 'Gagal menambahkan data barang keluar';
    }

    header('Location:../?barang_keluar');
    exit;
}
?>