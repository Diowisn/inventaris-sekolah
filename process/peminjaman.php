<?php
session_start();
include('../config/conn.php');
include('../config/function.php');

// ── TAMBAH PEMINJAMAN BARU ────────────────────────────────────────────────────
if (isset($_POST['tambah'])) {
    $barang_id      = (int) $_POST['barang_id'];
    $jumlah         = (int) $_POST['jumlah'];
    $nama_peminjam  = mysqli_real_escape_string($con, trim($_POST['nama_peminjam']));
    $keperluan      = mysqli_real_escape_string($con, trim($_POST['keperluan']));
    $tanggal_pinjam = mysqli_real_escape_string($con, $_POST['tanggal_pinjam']);
    $tanggal_rencana= mysqli_real_escape_string($con, $_POST['tanggal_rencana']);
    $keterangan     = mysqli_real_escape_string($con, trim($_POST['keterangan'] ?? ''));
    $user_id        = $_SESSION['iduser'] ?? null;

    // Validasi tanggal rencana tidak sebelum tanggal pinjam
    if ($tanggal_rencana < $tanggal_pinjam) {
        $_SESSION['error'] = 'Tanggal rencana kembali tidak boleh sebelum tanggal pinjam';
        header('Location:../?peminjaman');
        exit;
    }

    // Validasi stok tersedia
    $cekStok = mysqli_fetch_assoc(
        mysqli_query($con, "SELECT stok, nama_barang FROM barang WHERE idbarang = '$barang_id'")
    );

    if (!$cekStok) {
        $_SESSION['error'] = 'Barang tidak ditemukan';
        header('Location:../?peminjaman');
        exit;
    }

    if ($jumlah <= 0) {
        $_SESSION['error'] = 'Jumlah harus lebih dari 0';
        header('Location:../?peminjaman');
        exit;
    }

    if ($jumlah > $cekStok['stok']) {
        $_SESSION['error'] = 'Stok tidak mencukupi. Stok tersedia: ' . $cekStok['stok'] . ' unit';
        header('Location:../?peminjaman');
        exit;
    }

    // Simpan peminjaman
    $insert = mysqli_query($con,
        "INSERT INTO peminjaman (barang_id, jumlah, nama_peminjam, keperluan, tanggal_pinjam, tanggal_rencana, keterangan, status, user_id)
         VALUES ('$barang_id','$jumlah','$nama_peminjam','$keperluan','$tanggal_pinjam','$tanggal_rencana','$keterangan','Dipinjam', " . ($user_id ? "'$user_id'" : "NULL") . ")"
    ) or die(mysqli_error($con));

    if ($insert) {
        $_SESSION['success'] = $cekStok['nama_barang'] . ' berhasil dicatat dipinjam oleh ' . $nama_peminjam;
    } else {
        $_SESSION['error'] = 'Gagal menyimpan data peminjaman';
    }

    header('Location:../?peminjaman');
    exit;
}

// ── KONFIRMASI PENGEMBALIAN ───────────────────────────────────────────────────
if (isset($_POST['kembali'])) {
    $id_pinjam          = (int) $_POST['id_pinjam'];
    $tanggal_kembali    = mysqli_real_escape_string($con, $_POST['tanggal_kembali']);
    $keterangan_kembali = mysqli_real_escape_string($con, trim($_POST['keterangan_kembali'] ?? ''));

    // Ambil data peminjaman
    $dataPinjam = mysqli_fetch_assoc(
        mysqli_query($con, "SELECT p.*, b.nama_barang FROM peminjaman p JOIN barang b ON b.idbarang = p.barang_id WHERE p.id_pinjam = '$id_pinjam'")
    );

    if (!$dataPinjam) {
        $_SESSION['error'] = 'Data peminjaman tidak ditemukan';
        header('Location:../?peminjaman');
        exit;
    }

    if ($dataPinjam['status'] === 'Dikembalikan') {
        $_SESSION['error'] = 'Barang ini sudah dikembalikan sebelumnya';
        header('Location:../?peminjaman');
        exit;
    }

    // Update status peminjaman
    $catatan = $keterangan_kembali
        ? $dataPinjam['keterangan'] . ' | Kembali: ' . $keterangan_kembali
        : $dataPinjam['keterangan'];
    $catatan = mysqli_real_escape_string($con, $catatan);

    $update = mysqli_query($con,
        "UPDATE peminjaman
         SET status='Dikembalikan', tanggal_kembali='$tanggal_kembali', keterangan='$catatan'
         WHERE id_pinjam='$id_pinjam'"
    ) or die(mysqli_error($con));

    if ($update) {
        $_SESSION['success'] = $dataPinjam['nama_barang'] . ' berhasil dicatat dikembalikan';
    } else {
        $_SESSION['error'] = 'Gagal mengupdate data pengembalian';
    }

    header('Location:../?peminjaman');
    exit;
}
?>