<?php
session_start();
include ('../config/conn.php');
include ('../config/function.php');

// ── Fungsi bantu: sanitasi input ──────────────────────────────────────────────
function bersihkan($con, $data) {
    return mysqli_real_escape_string($con, trim(htmlspecialchars($data)));
}

// ── Fungsi generate kode barang unik ─────────────────────────────────────────
function generateKodeBarang($con) {
    // Format: BRG-YYYY-XXXX  contoh: BRG-2025-0013
    $tahun = date('Y');
    $prefix = "BRG-$tahun-";

    $result = mysqli_query($con, "SELECT kode_barang FROM barang WHERE kode_barang LIKE '$prefix%' ORDER BY kode_barang DESC LIMIT 1");
    if ($result && mysqli_num_rows($result) > 0) {
        $row   = mysqli_fetch_assoc($result);
        $lastNum = (int) substr($row['kode_barang'], -4);
        $newNum  = $lastNum + 1;
    } else {
        $newNum = 1;
    }
    return $prefix . str_pad($newNum, 4, '0', STR_PAD_LEFT);
}

// ── TAMBAH BARANG ─────────────────────────────────────────────────────────────
if (isset($_POST['tambah'])) {
    $nama_barang  = bersihkan($con, $_POST['nama_barang']);
    $merek_id     = (int) $_POST['merek_id'];
    $kategori_id  = (int) $_POST['kategori_id'];
    $keterangan   = bersihkan($con, $_POST['keterangan']);
    $kondisi      = bersihkan($con, $_POST['kondisi'] ?? 'Baik');
    $lokasi       = bersihkan($con, $_POST['lokasi'] ?? '');
    $stok         = 0;

    // Auto-generate kode barang
    $kode_barang  = generateKodeBarang($con);

    $insert = mysqli_query($con,
        "INSERT INTO barang (kode_barang, merek_id, kategori_id, nama_barang, keterangan, stok, kondisi, lokasi)
         VALUES ('$kode_barang','$merek_id','$kategori_id','$nama_barang','$keterangan','$stok','$kondisi','$lokasi')"
    ) or die(mysqli_error($con));

    if ($insert) {
        $_SESSION['success'] = "Berhasil menambahkan barang dengan kode <strong>$kode_barang</strong>";
    } else {
        $_SESSION['error'] = 'Gagal menambahkan data barang';
    }
    header('Location:../?barang');
    exit;
}

// ── UBAH BARANG ───────────────────────────────────────────────────────────────
if (isset($_POST['ubah'])) {
    $id           = (int) $_POST['idbarang'];
    $merek_id     = (int) $_POST['merek_id'];
    $kategori_id  = (int) $_POST['kategori_id'];
    $nama_barang  = bersihkan($con, $_POST['nama_barang']);
    $keterangan   = bersihkan($con, $_POST['keterangan']);
    $kondisi      = bersihkan($con, $_POST['kondisi'] ?? 'Baik');
    $lokasi       = bersihkan($con, $_POST['lokasi'] ?? '');

    // kode_barang TIDAK diubah saat edit (karena sudah di-print di QR)
    $update = mysqli_query($con,
        "UPDATE barang
         SET merek_id='$merek_id', kategori_id='$kategori_id',
             nama_barang='$nama_barang', keterangan='$keterangan',
             kondisi='$kondisi', lokasi='$lokasi'
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

// ── HAPUS BARANG ──────────────────────────────────────────────────────────────
if (isset($_GET['act']) && isset($_GET['id'])) {
    $act = decrypt($_GET['act']);
    if ($act == 'delete') {
        $id = (int) decrypt($_GET['id']);

        // Cek apakah barang masih punya stok / transaksi aktif
        $cek = mysqli_fetch_assoc(mysqli_query($con, "SELECT stok FROM barang WHERE idbarang='$id'"));
        if ($cek && $cek['stok'] > 0) {
            $_SESSION['error'] = 'Barang tidak bisa dihapus karena masih ada stok';
            header('Location:../?barang');
            exit;
        }

        $delete = mysqli_query($con, "DELETE FROM barang WHERE idbarang='$id'") or die(mysqli_error($con));
        if ($delete) {
            $_SESSION['success'] = 'Data barang berhasil dihapus';
        } else {
            $_SESSION['error'] = 'Data barang gagal dihapus';
        }
    }
    header('Location:../?barang');
    exit;
}
?>