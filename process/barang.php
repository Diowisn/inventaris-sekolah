<?php
session_start();
include('../config/conn.php');
include('../config/function.php');

function bersihkan($con, $data) {
    return mysqli_real_escape_string($con, trim(htmlspecialchars($data)));
}

function generateKodeBarang($con) {
    $tahun  = date('Y');
    $prefix = "BRG-$tahun-";
    $result = mysqli_query($con,
        "SELECT kode_barang FROM barang WHERE kode_barang LIKE '$prefix%' ORDER BY kode_barang DESC LIMIT 1"
    );
    if ($result && mysqli_num_rows($result) > 0) {
        $row    = mysqli_fetch_assoc($result);
        $lastNum = (int) substr($row['kode_barang'], -4);
        $newNum  = $lastNum + 1;
    } else {
        $newNum = 1;
    }
    return $prefix . str_pad($newNum, 4, '0', STR_PAD_LEFT);
}

// ── TAMBAH ────────────────────────────────────────────────────────────────────
if (isset($_POST['tambah'])) {
    $nama_barang = bersihkan($con, $_POST['nama_barang']);
    $merek_id    = (int) $_POST['merek_id'];
    $kategori_id = (int) $_POST['kategori_id'];
    $keterangan  = bersihkan($con, $_POST['keterangan']);
    $kondisi     = bersihkan($con, $_POST['kondisi'] ?? 'Baik');
    $lokasi      = bersihkan($con, $_POST['lokasi'] ?? '');
    $jenis       = bersihkan($con, $_POST['jenis'] ?? 'Tidak Habis Pakai');
    $stok        = 0;
    $kode_barang = generateKodeBarang($con);

    $insert = mysqli_query($con,
        "INSERT INTO barang (kode_barang, merek_id, kategori_id, nama_barang, keterangan, stok, kondisi, lokasi, jenis)
         VALUES ('$kode_barang','$merek_id','$kategori_id','$nama_barang','$keterangan','$stok','$kondisi','$lokasi','$jenis')"
    ) or die(mysqli_error($con));

    if ($insert) {
        $_SESSION['success'] = "Berhasil menambahkan barang dengan kode <strong>$kode_barang</strong>";
    } else {
        $_SESSION['error'] = 'Gagal menambahkan data barang';
    }
    header('Location:../?barang');
    exit;
}

// ── UBAH ──────────────────────────────────────────────────────────────────────
if (isset($_POST['ubah'])) {
    $id          = (int) $_POST['idbarang'];
    $merek_id    = (int) $_POST['merek_id'];
    $kategori_id = (int) $_POST['kategori_id'];
    $nama_barang = bersihkan($con, $_POST['nama_barang']);
    $keterangan  = bersihkan($con, $_POST['keterangan']);
    $kondisi     = bersihkan($con, $_POST['kondisi'] ?? 'Baik');
    $lokasi      = bersihkan($con, $_POST['lokasi'] ?? '');
    $jenis       = bersihkan($con, $_POST['jenis'] ?? 'Tidak Habis Pakai');

    $update = mysqli_query($con,
        "UPDATE barang
         SET merek_id='$merek_id', kategori_id='$kategori_id',
             nama_barang='$nama_barang', keterangan='$keterangan',
             kondisi='$kondisi', lokasi='$lokasi', jenis='$jenis'
         WHERE idbarang='$id'"
    ) or die(mysqli_error($con));

    if ($update) {
        $_SESSION['success'] = 'Berhasil mengubah data barang';
    } else {
        $_SESSION['error'] = 'Gagal mengubah data barang';
    }
    header('Location:../?barang');
    exit;
}

// ── HAPUS ─────────────────────────────────────────────────────────────────────
if (isset($_GET['act']) && isset($_GET['id'])) {
    if (decrypt($_GET['act']) == 'delete') {
        $id  = (int) decrypt($_GET['id']);
        $cek = mysqli_fetch_assoc(mysqli_query($con, "SELECT stok FROM barang WHERE idbarang='$id'"));

        if ($cek && $cek['stok'] > 0) {
            $_SESSION['error'] = 'Barang tidak bisa dihapus karena masih ada stok';
            header('Location:../?barang');
            exit;
        }

        $delete = mysqli_query($con, "DELETE FROM barang WHERE idbarang='$id'") or die(mysqli_error($con));
        $_SESSION[$delete ? 'success' : 'error'] = $delete
            ? 'Data barang berhasil dihapus'
            : 'Data barang gagal dihapus';
    }
    header('Location:../?barang');
    exit;
}
?>