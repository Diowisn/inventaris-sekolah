-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 15, 2026 at 10:12 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `inventaris`
--

-- --------------------------------------------------------

--
-- Table structure for table `barang`
--

CREATE TABLE `barang` (
  `idbarang` int NOT NULL,
  `kode_barang` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `merek_id` int NOT NULL,
  `kategori_id` int NOT NULL,
  `nama_barang` varchar(128) COLLATE utf8mb4_general_ci NOT NULL,
  `keterangan` varchar(256) COLLATE utf8mb4_general_ci NOT NULL,
  `stok` int NOT NULL,
  `kondisi` enum('Baik','Rusak Ringan','Rusak Berat','Hilang') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Baik',
  `jenis` enum('Habis Pakai','Tidak Habis Pakai') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Tidak Habis Pakai',
  `lokasi` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barang`
--

INSERT INTO `barang` (`idbarang`, `kode_barang`, `merek_id`, `kategori_id`, `nama_barang`, `keterangan`, `stok`, `kondisi`, `jenis`, `lokasi`) VALUES
(1, 'BRG-0001', 2, 1, 'Printer', 'Printer Canon Baru', 2, 'Baik', 'Tidak Habis Pakai', ''),
(3, 'BRG-0003', 1, 1, 'Printer', 'Printer Epson', 2, 'Baik', 'Tidak Habis Pakai', ''),
(4, 'BRG-0004', 2, 2, 'Spidol', ' ', 10, 'Baik', 'Tidak Habis Pakai', ''),
(5, 'BRG-0005', 7, 1, 'Printer', 'Printer Epson L3210', 1, 'Baik', 'Tidak Habis Pakai', ''),
(6, 'BRG-0006', 7, 1, 'Scanner', 'Epson L3110', 1, 'Baik', 'Tidak Habis Pakai', ''),
(7, 'BRG-0007', 16, 7, 'L3110', ' ', 6, 'Baik', 'Tidak Habis Pakai', ''),
(8, 'BRG-0008', 17, 7, 'EOS 1500D Kit (EF S18-55 IS II)', ' ', 2, 'Baik', 'Tidak Habis Pakai', ''),
(9, 'BRG-0009', 18, 7, 'Mark II', ' ', 0, 'Baik', 'Tidak Habis Pakai', ''),
(10, 'BRG-0010', 19, 8, 'Permanent Marker', ' ', 11, 'Baik', 'Habis Pakai', ''),
(11, 'BRG-0011', 19, 8, 'White Board Marker', ' ', 22, 'Baik', 'Habis Pakai', ''),
(12, 'BRG-0012', 20, 8, 'F4', ' ', 7, 'Baik', 'Habis Pakai', ''),
(13, 'BRG-2026-0001', 21, 8, 'Buku Tulis Latin', 'Menulis huruf latin', 23, 'Hilang', 'Habis Pakai', ''),
(14, 'BRG-2026-0002', 22, 8, 'Pensil', 'Pensil mahal pada masanya', 5, 'Rusak Ringan', 'Habis Pakai', '');

-- --------------------------------------------------------

--
-- Table structure for table `barang_keluar`
--

CREATE TABLE `barang_keluar` (
  `idbarang_keluar` int NOT NULL,
  `barang_id` int NOT NULL,
  `jumlah` int NOT NULL,
  `penerima` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `keterangan` varchar(256) COLLATE utf8mb4_general_ci NOT NULL,
  `tanggal` date NOT NULL,
  `user_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barang_keluar`
--

INSERT INTO `barang_keluar` (`idbarang_keluar`, `barang_id`, `jumlah`, `penerima`, `keterangan`, `tanggal`, `user_id`) VALUES
(1, 1, 1, '', 'Rusak', '2020-11-26', NULL),
(2, 5, 2, '', 'Peminjaman Printer - Budi', '2024-05-25', NULL),
(3, 5, 1, '', 'Epson L3210 - Yahya', '2025-01-02', NULL),
(4, 10, 1, '', 'Bu Saadah', '2025-01-07', NULL),
(5, 11, 1, '', 'Bu Saadah', '2024-11-07', NULL),
(6, 11, 1, '', 'Bu Puji', '2024-11-07', NULL),
(7, 8, 1, '', 'Dipinjam - Kurikulum', '2024-11-30', NULL),
(8, 7, 1, '', 'Dipinjam - Kurikulum', '2024-11-30', NULL),
(9, 9, 1, '', 'Dipinjam - Humas', '2024-11-29', NULL),
(10, 12, 1, '', 'Diminta - Kantor Guru MA', '2025-01-14', NULL),
(11, 12, 2, '', 'Diminta - Kantor Guru MTs', '2025-01-14', NULL),
(12, 13, 12, '', 'Buku diambil', '2026-03-12', NULL),
(13, 13, 30, '', 'buku keluar kedua', '2026-03-12', NULL),
(14, 13, 5, 'Lab', 'rapat', '2026-03-12', NULL),
(15, 8, 1, 'b', 'b', '2026-03-13', NULL);

--
-- Triggers `barang_keluar`
--
DELIMITER $$
CREATE TRIGGER `kurang_stok` AFTER INSERT ON `barang_keluar` FOR EACH ROW BEGIN
	UPDATE barang SET stok = stok - new.jumlah WHERE idbarang = new.barang_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `barang_masuk`
--

CREATE TABLE `barang_masuk` (
  `idbarang_masuk` int NOT NULL,
  `barang_id` int NOT NULL,
  `jumlah` int NOT NULL,
  `harga` bigint NOT NULL DEFAULT '0',
  `keterangan` varchar(256) COLLATE utf8mb4_general_ci NOT NULL,
  `tanggal` date NOT NULL,
  `user_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barang_masuk`
--

INSERT INTO `barang_masuk` (`idbarang_masuk`, `barang_id`, `jumlah`, `harga`, `keterangan`, `tanggal`, `user_id`) VALUES
(1, 4, 10, 0, 'Beli Baru', '2020-11-24', NULL),
(2, 1, 3, 0, 'Beli baru', '2020-11-23', NULL),
(3, 3, 2, 0, 'Beli baru', '2020-11-24', NULL),
(4, 5, 2, 0, 'Pengembalian Printer - Budi', '2024-05-25', NULL),
(5, 6, 1, 0, 'Beli Baru - Ayyas', '2025-01-01', NULL),
(6, 5, 2, 0, 'Printer Epson L3210 - Ayyas', '2025-01-01', NULL),
(7, 10, 12, 0, 'Beli Baru - Ayyas', '2024-11-07', NULL),
(8, 11, 24, 0, 'Beli Baru - Ayyas', '2024-11-07', NULL),
(9, 8, 1, 0, 'Beli Second - Humas', '2024-11-29', NULL),
(10, 7, 1, 0, 'Beli Second - Humas', '2024-11-29', NULL),
(11, 9, 1, 0, 'Beli Second - Humas', '2024-11-29', NULL),
(12, 8, 1, 0, 'Dikembalikan - Kurikulum', '2024-12-01', NULL),
(13, 7, 1, 0, 'Dikembalikan - Kurikulum', '2024-12-01', NULL),
(14, 12, 10, 0, 'Beli - Dua Warna', '2025-01-14', NULL),
(15, 13, 50, 0, 'Buku masuk', '2026-03-12', NULL),
(16, 14, 5, 0, 'masukkan pensil', '2026-03-12', NULL),
(17, 13, 20, 100000, 'pembelian buku baru', '2026-03-12', NULL),
(18, 7, 5, 2500000, 'Epson baru', '2026-03-13', NULL),
(19, 8, 2, 24000000, 'Camera baru', '2026-03-13', NULL);

--
-- Triggers `barang_masuk`
--
DELIMITER $$
CREATE TRIGGER `tambah_stok` AFTER INSERT ON `barang_masuk` FOR EACH ROW BEGIN
	UPDATE barang SET stok = stok + new.jumlah WHERE idbarang = new.barang_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `idkategori` int NOT NULL,
  `nama_kategori` varchar(128) COLLATE utf8mb4_general_ci NOT NULL,
  `keterangan` varchar(256) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`idkategori`, `nama_kategori`, `keterangan`) VALUES
(7, 'Elektronik', ' '),
(8, 'ATK', ' ');

-- --------------------------------------------------------

--
-- Table structure for table `merek`
--

CREATE TABLE `merek` (
  `idmerek` int NOT NULL,
  `nama_merek` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `keterangan` varchar(256) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `merek`
--

INSERT INTO `merek` (`idmerek`, `nama_merek`, `keterangan`) VALUES
(16, 'Epson', ' '),
(17, 'Cannon', ' '),
(18, 'Sony', ' '),
(19, 'Snowman', ' '),
(20, 'SiDU', ' '),
(21, 'Sinaar Dunia', 'Buku isi 38 halaman'),
(22, 'Fabercastle', 'Pensil sejuta umat');

-- --------------------------------------------------------

--
-- Table structure for table `peminjaman`
--

CREATE TABLE `peminjaman` (
  `id_pinjam` int NOT NULL,
  `barang_id` int NOT NULL,
  `jumlah` int NOT NULL DEFAULT '1',
  `nama_peminjam` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `keperluan` varchar(256) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `tanggal_pinjam` date NOT NULL,
  `tanggal_rencana` date NOT NULL,
  `tanggal_kembali` date DEFAULT NULL,
  `status` enum('Dipinjam','Dikembalikan','Terlambat') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Dipinjam',
  `user_id` int DEFAULT NULL,
  `keterangan` varchar(256) COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `peminjaman`
--

INSERT INTO `peminjaman` (`id_pinjam`, `barang_id`, `jumlah`, `nama_peminjam`, `keperluan`, `tanggal_pinjam`, `tanggal_rencana`, `tanggal_kembali`, `status`, `user_id`, `keterangan`) VALUES
(1, 7, 2, 'Ayyasy', 'Rapat', '2026-03-13', '2026-03-14', NULL, 'Terlambat', 3, ''),
(2, 8, 1, 's', 's', '2026-03-13', '2026-03-20', '2026-03-13', 'Dikembalikan', 3, ' | Kembali: baik');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_users` int NOT NULL,
  `nama` varchar(50) NOT NULL,
  `no_hp` varchar(15) NOT NULL,
  `username` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `level` enum('admin','staff','viewer','gudang') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_users`, `nama`, `no_hp`, `username`, `password`, `level`) VALUES
(3, 'Admin', '085747909632', 'admin', '$2y$10$E33mbIeZc665JZiGOIwCMunuLcI.YnlIzMvGoqgPWflEykvFGFTAK', 'admin'),
(13, 'Staf', '0857868723341', 'staf', '$2y$10$hZC5TmAsXW0e14mgswfEJ.rruGEeIkxlZTFLAB9gFKm5ICUCfXVva', 'gudang');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `barang`
--
ALTER TABLE `barang`
  ADD PRIMARY KEY (`idbarang`),
  ADD UNIQUE KEY `unique_kode_barang` (`kode_barang`);

--
-- Indexes for table `barang_keluar`
--
ALTER TABLE `barang_keluar`
  ADD PRIMARY KEY (`idbarang_keluar`);

--
-- Indexes for table `barang_masuk`
--
ALTER TABLE `barang_masuk`
  ADD PRIMARY KEY (`idbarang_masuk`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`idkategori`);

--
-- Indexes for table `merek`
--
ALTER TABLE `merek`
  ADD PRIMARY KEY (`idmerek`);

--
-- Indexes for table `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD PRIMARY KEY (`id_pinjam`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_users`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `barang`
--
ALTER TABLE `barang`
  MODIFY `idbarang` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `barang_keluar`
--
ALTER TABLE `barang_keluar`
  MODIFY `idbarang_keluar` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `barang_masuk`
--
ALTER TABLE `barang_masuk`
  MODIFY `idbarang_masuk` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `idkategori` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `merek`
--
ALTER TABLE `merek`
  MODIFY `idmerek` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `peminjaman`
--
ALTER TABLE `peminjaman`
  MODIFY `id_pinjam` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_users` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
