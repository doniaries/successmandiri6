-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 03, 2024 at 06:33 PM
-- Server version: 8.0.30
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `success-mandiri6`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('356a192b7913b04c54574d18c28d46e6395428ab', 'i:1;', 1733222418),
('356a192b7913b04c54574d18c28d46e6395428ab:timer', 'i:1733222418;', 1733222418),
('perusahaan-stats', 'a:3:{i:0;O:41:\"Filament\\Widgets\\StatsOverviewWidget\\Stat\":17:{s:9:\"\0*\0except\";a:0:{}s:13:\"componentName\";N;s:10:\"attributes\";N;s:8:\"\0*\0chart\";N;s:13:\"\0*\0chartColor\";N;s:8:\"\0*\0color\";s:7:\"success\";s:7:\"\0*\0icon\";N;s:14:\"\0*\0description\";s:43:\"Terakhir tambah: Rp 50.000.000 (03/12/2024)\";s:18:\"\0*\0descriptionIcon\";s:20:\"heroicon-m-banknotes\";s:26:\"\0*\0descriptionIconPosition\";N;s:19:\"\0*\0descriptionColor\";N;s:18:\"\0*\0extraAttributes\";a:0:{}s:24:\"\0*\0shouldOpenUrlInNewTab\";b:0;s:6:\"\0*\0url\";N;s:5:\"\0*\0id\";N;s:8:\"\0*\0label\";s:16:\"Saldo Perusahaan\";s:8:\"\0*\0value\";s:14:\"Rp 299.652.770\";}i:1;O:41:\"Filament\\Widgets\\StatsOverviewWidget\\Stat\":17:{s:9:\"\0*\0except\";a:0:{}s:13:\"componentName\";N;s:10:\"attributes\";N;s:8:\"\0*\0chart\";N;s:13:\"\0*\0chartColor\";N;s:8:\"\0*\0color\";s:4:\"info\";s:7:\"\0*\0icon\";N;s:14:\"\0*\0description\";s:14:\"Kasir: Kasir 1\";s:18:\"\0*\0descriptionIcon\";s:21:\"heroicon-m-user-group\";s:26:\"\0*\0descriptionIconPosition\";N;s:19:\"\0*\0descriptionColor\";N;s:18:\"\0*\0extraAttributes\";a:0:{}s:24:\"\0*\0shouldOpenUrlInNewTab\";b:0;s:6:\"\0*\0url\";N;s:5:\"\0*\0id\";N;s:8:\"\0*\0label\";s:18:\"CV SUCCESS MANDIRI\";s:8:\"\0*\0value\";s:6:\"Yondra\";}i:2;O:41:\"Filament\\Widgets\\StatsOverviewWidget\\Stat\":17:{s:9:\"\0*\0except\";a:0:{}s:13:\"componentName\";N;s:10:\"attributes\";N;s:8:\"\0*\0chart\";N;s:13:\"\0*\0chartColor\";N;s:8:\"\0*\0color\";s:7:\"success\";s:7:\"\0*\0icon\";N;s:14:\"\0*\0description\";s:33:\"Total Pengeluaran: Rp 152.120.480\";s:18:\"\0*\0descriptionIcon\";s:21:\"heroicon-m-arrow-path\";s:26:\"\0*\0descriptionIconPosition\";N;s:19:\"\0*\0descriptionColor\";N;s:18:\"\0*\0extraAttributes\";a:0:{}s:24:\"\0*\0shouldOpenUrlInNewTab\";b:0;s:6:\"\0*\0url\";N;s:5:\"\0*\0id\";N;s:8:\"\0*\0label\";s:19:\"Ringkasan Transaksi\";s:8:\"\0*\0value\";s:9:\"506599500\";}}', 1733224186),
('transaksi-stats', 'a:3:{i:0;O:41:\"Filament\\Widgets\\StatsOverviewWidget\\Stat\":17:{s:9:\"\0*\0except\";a:0:{}s:13:\"componentName\";N;s:10:\"attributes\";N;s:8:\"\0*\0chart\";N;s:13:\"\0*\0chartColor\";N;s:8:\"\0*\0color\";s:6:\"danger\";s:7:\"\0*\0icon\";N;s:14:\"\0*\0description\";s:36:\"Total sisa saldo setelah pengeluaran\";s:18:\"\0*\0descriptionIcon\";s:20:\"heroicon-m-banknotes\";s:26:\"\0*\0descriptionIconPosition\";N;s:19:\"\0*\0descriptionColor\";N;s:18:\"\0*\0extraAttributes\";a:0:{}s:24:\"\0*\0shouldOpenUrlInNewTab\";b:0;s:6:\"\0*\0url\";N;s:5:\"\0*\0id\";N;s:8:\"\0*\0label\";s:10:\"Sisa Saldo\";s:8:\"\0*\0value\";s:15:\"Rp -153.181.480\";}i:1;O:41:\"Filament\\Widgets\\StatsOverviewWidget\\Stat\":17:{s:9:\"\0*\0except\";a:0:{}s:13:\"componentName\";N;s:10:\"attributes\";N;s:8:\"\0*\0chart\";N;s:13:\"\0*\0chartColor\";N;s:8:\"\0*\0color\";s:7:\"success\";s:7:\"\0*\0icon\";N;s:14:\"\0*\0description\";s:63:\"Pembayaran Non-Tunai: Rp 141.589.500\nBayar Hutang: Rp 2.000.000\";s:18:\"\0*\0descriptionIcon\";s:28:\"heroicon-m-arrow-trending-up\";s:26:\"\0*\0descriptionIconPosition\";N;s:19:\"\0*\0descriptionColor\";N;s:18:\"\0*\0extraAttributes\";a:0:{}s:24:\"\0*\0shouldOpenUrlInNewTab\";b:0;s:6:\"\0*\0url\";N;s:5:\"\0*\0id\";N;s:8:\"\0*\0label\";s:22:\"Total Saldo/Uang Masuk\";s:8:\"\0*\0value\";s:14:\"Rp 143.589.500\";}i:2;O:41:\"Filament\\Widgets\\StatsOverviewWidget\\Stat\":17:{s:9:\"\0*\0except\";a:0:{}s:13:\"componentName\";N;s:10:\"attributes\";N;s:8:\"\0*\0chart\";N;s:13:\"\0*\0chartColor\";N;s:8:\"\0*\0color\";s:6:\"danger\";s:7:\"\0*\0icon\";N;s:14:\"\0*\0description\";s:53:\"Total DO: Rp 296.694.980\nTotal Operasional: Rp 76.000\";s:18:\"\0*\0descriptionIcon\";s:30:\"heroicon-m-arrow-trending-down\";s:26:\"\0*\0descriptionIconPosition\";N;s:19:\"\0*\0descriptionColor\";N;s:18:\"\0*\0extraAttributes\";a:0:{}s:24:\"\0*\0shouldOpenUrlInNewTab\";b:0;s:6:\"\0*\0url\";N;s:5:\"\0*\0id\";N;s:8:\"\0*\0label\";s:23:\"Pengeluaran/Uang Keluar\";s:8:\"\0*\0value\";s:14:\"Rp 296.770.980\";}}', 1733225387);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kendaraan`
--

CREATE TABLE `kendaraan` (
  `id` bigint UNSIGNED NOT NULL,
  `no_polisi` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `supir_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `laporan_keuangan`
--

CREATE TABLE `laporan_keuangan` (
  `id` bigint UNSIGNED NOT NULL,
  `tanggal` datetime NOT NULL,
  `jenis_transaksi` enum('Pemasukan','Pengeluaran') COLLATE utf8mb4_unicode_ci NOT NULL,
  `kategori` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Kategori transaksi (DO/Operasional)',
  `sub_kategori` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Sub kategori seperti upah_bongkar, biaya_lain, dll',
  `nominal` decimal(15,0) NOT NULL,
  `sumber_transaksi` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'DO/Operasional',
  `referensi_id` bigint UNSIGNED NOT NULL COMMENT 'ID dari tabel sumber (transaksi_do/operasional)',
  `nomor_referensi` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nomor DO jika dari transaksi DO',
  `pihak_terkait` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nama penjual/user terkait',
  `tipe_pihak` enum('penjual','pekerja','user','supir') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `cara_pembayaran` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tunai/Transfer/cair di luar',
  `keterangan` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `laporan_keuangan`
--

INSERT INTO `laporan_keuangan` (`id`, `tanggal`, `jenis_transaksi`, `kategori`, `sub_kategori`, `nominal`, `sumber_transaksi`, `referensi_id`, `nomor_referensi`, `pihak_terkait`, `tipe_pihak`, `cara_pembayaran`, `keterangan`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '2024-12-03 14:47:10', 'Pengeluaran', 'DO', 'Pembayaran DO', 13809510, 'DO', 6, 'DO-20241203-0001', 'FURQON', 'penjual', 'Tunai', 'Pembayaran DO #DO-20241203-0001', '2024-12-03 07:47:39', '2024-12-03 07:47:39', NULL),
(2, '2024-12-03 15:20:48', 'Pengeluaran', 'DO', 'Pembayaran DO', 8813970, 'DO', 7, 'DO-20241203-0002', 'EPI', 'penjual', 'Tunai', 'Pembayaran DO #DO-20241203-0002', '2024-12-03 08:21:56', '2024-12-03 09:34:52', '2024-12-03 09:34:52'),
(3, '2024-12-03 15:22:10', 'Pengeluaran', 'DO', 'Pembayaran DO', 1780020, 'DO', 8, 'DO-20241203-0003', 'ANDES', 'penjual', 'Tunai', 'Pembayaran DO #DO-20241203-0003', '2024-12-03 08:22:49', '2024-12-03 09:39:05', '2024-12-03 09:39:05'),
(4, '2024-12-03 15:20:48', 'Pemasukan', 'DO', 'Biaya Lain', 200000, 'DO', 7, 'DO-20241203-0002', 'EPI', 'penjual', 'Tunai', 'Pemasukan tunai DO #DO-20241203-0002', '2024-12-03 09:34:52', '2024-12-03 09:34:52', NULL),
(5, '2024-12-03 15:20:48', 'Pengeluaran', 'DO', 'Pembayaran DO', 8613970, 'DO', 7, 'DO-20241203-0002', 'EPI', 'penjual', 'Tunai', 'Pembayaran DO #DO-20241203-0002', '2024-12-03 09:34:52', '2024-12-03 09:34:52', NULL),
(6, '2024-12-03 16:36:08', 'Pemasukan', 'DO', 'Pemasukan Tunai', 116000, 'DO', 9, 'DO-20241203-0004', 'LOPON', 'penjual', 'Tunai', 'Pemasukan tunai DO #DO-20241203-0004', '2024-12-03 09:38:17', '2024-12-03 09:38:17', NULL),
(7, '2024-12-03 16:36:08', 'Pemasukan', 'DO', 'Pembayaran DO', 29152720, 'DO', 9, 'DO-20241203-0004', 'LOPON', 'penjual', 'cair di luar', 'Pembayaran DO via cair di luar', '2024-12-03 09:38:17', '2024-12-03 09:38:17', NULL),
(8, '2024-12-03 15:22:10', 'Pengeluaran', 'DO', 'Pembayaran DO', 1774440, 'DO', 8, 'DO-20241203-0003', 'ANDES', 'penjual', 'Tunai', 'Pembayaran DO #DO-20241203-0003', '2024-12-03 09:39:05', '2024-12-03 09:39:05', NULL),
(9, '2024-12-03 16:41:45', 'Pemasukan', 'DO', 'Biaya Lain', 50000, 'DO', 10, 'DO-20241203-0005', 'HERMAN', 'penjual', 'Tunai', 'Pemasukan tunai DO #DO-20241203-0005', '2024-12-03 09:42:47', '2024-12-03 09:42:47', NULL),
(10, '2024-12-03 16:41:45', 'Pengeluaran', 'DO', 'Pembayaran DO', 2939200, 'DO', 10, 'DO-20241203-0005', 'HERMAN', 'penjual', 'Tunai', 'Pembayaran DO #DO-20241203-0005', '2024-12-03 09:42:47', '2024-12-03 09:42:47', NULL),
(11, '2024-12-03 16:43:24', 'Pemasukan', 'DO', 'Biaya Lain', 100000, 'DO', 11, 'DO-20241203-0006', 'SIIT', 'penjual', 'Tunai', 'Pemasukan tunai DO #DO-20241203-0006', '2024-12-03 09:45:46', '2024-12-03 09:45:46', NULL),
(12, '2024-12-03 16:43:24', 'Pengeluaran', 'DO', 'Pembayaran DO', 6714740, 'DO', 11, 'DO-20241203-0006', 'SIIT', 'penjual', 'Tunai', 'Pembayaran DO #DO-20241203-0006', '2024-12-03 09:45:46', '2024-12-03 09:45:46', NULL),
(13, '2024-12-03 16:54:23', 'Pemasukan', 'DO', 'Pemasukan Tunai', 1400000, 'DO', 12, 'DO-20241203-0007', 'ETI SUSANA', 'penjual', 'Tunai', 'Pemasukan tunai DO #DO-20241203-0007', '2024-12-03 09:56:18', '2024-12-03 09:56:18', NULL),
(14, '2024-12-03 16:54:23', 'Pemasukan', 'DO', 'Pembayaran DO', 23709280, 'DO', 12, 'DO-20241203-0007', 'ETI SUSANA', 'penjual', 'Transfer', 'Pembayaran DO via Transfer', '2024-12-03 09:56:18', '2024-12-03 09:56:18', NULL),
(15, '2024-12-03 17:06:38', 'Pengeluaran', 'DO', 'Pembayaran DO', 3666540, 'DO', 13, 'DO-20241203-0008', 'SISKA', 'penjual', 'Tunai', 'Pembayaran DO #DO-20241203-0008', '2024-12-03 10:07:40', '2024-12-03 10:07:40', NULL),
(16, '2024-12-03 17:08:00', 'Pengeluaran', 'DO', 'Pembayaran DO', 4712760, 'DO', 14, 'DO-20241203-0009', 'JEKI', 'penjual', 'Tunai', 'Pembayaran DO #DO-20241203-0009', '2024-12-03 10:08:58', '2024-12-03 10:13:36', '2024-12-03 10:13:36'),
(17, '2024-12-03 17:09:07', 'Pemasukan', 'DO', 'Biaya Lain', 15000, 'DO', 15, 'DO-20241203-0010', 'AGUS', 'penjual', 'Tunai', 'Pemasukan tunai DO #DO-20241203-0010', '2024-12-03 10:10:22', '2024-12-03 10:10:22', NULL),
(18, '2024-12-03 17:09:07', 'Pengeluaran', 'DO', 'Pembayaran DO', 4386120, 'DO', 15, 'DO-20241203-0010', 'AGUS', 'penjual', 'Tunai', 'Pembayaran DO #DO-20241203-0010', '2024-12-03 10:10:22', '2024-12-03 10:10:22', NULL),
(19, '2024-12-03 17:08:00', 'Pemasukan', 'DO', 'Biaya Lain', 20000, 'DO', 14, 'DO-20241203-0009', 'JEKI', 'penjual', 'Tunai', 'Pemasukan tunai DO #DO-20241203-0009', '2024-12-03 10:13:36', '2024-12-03 10:13:36', NULL),
(20, '2024-12-03 17:08:00', 'Pengeluaran', 'DO', 'Pembayaran DO', 4692760, 'DO', 14, 'DO-20241203-0009', 'JEKI', 'penjual', 'Tunai', 'Pembayaran DO #DO-20241203-0009', '2024-12-03 10:13:36', '2024-12-03 10:13:36', NULL),
(21, '2024-12-03 17:14:51', 'Pengeluaran', 'DO', 'Pembayaran DO', 3234060, 'DO', 16, 'DO-20241203-0011', 'JOKO', 'penjual', 'Tunai', 'Pembayaran DO #DO-20241203-0011', '2024-12-03 10:16:10', '2024-12-03 10:16:10', NULL),
(22, '2024-12-03 17:17:12', 'Pemasukan', 'DO', 'Pemasukan Tunai', 124000, 'DO', 17, 'DO-20241203-0012', 'LOPON', 'penjual', 'Tunai', 'Pemasukan tunai DO #DO-20241203-0012', '2024-12-03 10:18:21', '2024-12-03 10:18:21', NULL),
(23, '2024-12-03 17:17:12', 'Pemasukan', 'DO', 'Pembayaran DO', 31068620, 'DO', 17, 'DO-20241203-0012', 'LOPON', 'penjual', 'cair di luar', 'Pembayaran DO via cair di luar', '2024-12-03 10:18:21', '2024-12-03 10:18:21', NULL),
(24, '2024-12-03 17:21:12', 'Pemasukan', 'DO', 'Biaya Lain', 300000, 'DO', 18, 'DO-20241203-0013', 'SUKARMIN', 'penjual', 'Tunai', 'Pemasukan tunai DO #DO-20241203-0013', '2024-12-03 10:22:45', '2024-12-03 10:22:45', NULL),
(25, '2024-12-03 17:21:12', 'Pemasukan', 'DO', 'Bayar Hutang', 1000000, 'DO', 18, 'DO-20241203-0013', 'SUKARMIN', 'penjual', 'Tunai', 'Pemasukan tunai DO #DO-20241203-0013', '2024-12-03 10:22:45', '2024-12-03 10:22:45', NULL),
(26, '2024-12-03 17:21:12', 'Pengeluaran', 'DO', 'Pembayaran DO', 24609180, 'DO', 18, 'DO-20241203-0013', 'SUKARMIN', 'penjual', 'Tunai', 'Pembayaran DO #DO-20241203-0013', '2024-12-03 10:22:45', '2024-12-03 10:22:45', NULL),
(27, '2024-12-03 17:22:45', 'Pemasukan', 'DO', 'Pemasukan Tunai', 350000, 'DO', 19, 'DO-20241203-0014', 'DITEG', 'penjual', 'Tunai', 'Pemasukan tunai DO #DO-20241203-0014', '2024-12-03 10:24:21', '2024-12-03 10:24:21', NULL),
(28, '2024-12-03 17:22:45', 'Pemasukan', 'DO', 'Pembayaran DO', 31383220, 'DO', 19, 'DO-20241203-0014', 'DITEG', 'penjual', 'Transfer', 'Pembayaran DO via Transfer', '2024-12-03 10:24:21', '2024-12-03 10:24:21', NULL),
(29, '2024-12-03 00:00:00', 'Pemasukan', 'Saldo', 'Tambah Saldo', 50000000, 'Perusahaan', 1, 'TBS-20241203-173926', 'Yondra', 'user', 'Transfer', NULL, '2024-12-03 10:39:26', '2024-12-03 10:39:26', NULL),
(30, '2024-12-03 17:41:55', 'Pemasukan', 'DO', 'Biaya Lain', 650000, 'DO', 20, 'DO-20241203-0015', 'KELOMPOK', 'penjual', 'Tunai', 'Pemasukan tunai DO #DO-20241203-0015', '2024-12-03 10:42:50', '2024-12-03 10:42:50', NULL),
(31, '2024-12-03 17:41:55', 'Pengeluaran', 'DO', 'Pembayaran DO', 26870130, 'DO', 20, 'DO-20241203-0015', 'KELOMPOK', 'penjual', 'Tunai', 'Pembayaran DO #DO-20241203-0015', '2024-12-03 10:42:50', '2024-12-03 10:42:50', NULL),
(32, '2024-12-03 17:42:50', 'Pemasukan', 'DO', 'Biaya Lain', 650000, 'DO', 21, 'DO-20241203-0016', 'KELOMPOK', 'penjual', 'Tunai', 'Pemasukan tunai DO #DO-20241203-0016', '2024-12-03 10:43:20', '2024-12-03 10:43:20', NULL),
(33, '2024-12-03 17:42:50', 'Pengeluaran', 'DO', 'Pembayaran DO', 25574990, 'DO', 21, 'DO-20241203-0016', 'KELOMPOK', 'penjual', 'Tunai', 'Pembayaran DO #DO-20241203-0016', '2024-12-03 10:43:20', '2024-12-03 10:43:20', NULL),
(34, '2024-12-03 00:00:00', 'Pemasukan', 'Saldo', 'Tambah Saldo', 50000000, 'Perusahaan', 1, 'TBS-20241203-174444', 'Yondra', 'user', 'Tunai', NULL, '2024-12-03 10:44:44', '2024-12-03 10:44:44', NULL),
(35, '2024-12-03 17:43:20', 'Pengeluaran', 'DO', 'Pembayaran DO', 19765240, 'DO', 22, 'DO-20241203-0017', 'HERMAN', 'penjual', 'Tunai', 'Pembayaran DO #DO-20241203-0017', '2024-12-03 10:44:59', '2024-12-03 10:44:59', NULL),
(36, '2024-12-03 17:51:25', 'Pengeluaran', 'DO', 'Pembayaran DO', 5469600, 'DO', 23, 'DO-20241203-0018', 'UCOK', 'penjual', 'Tunai', 'Pembayaran DO #DO-20241203-0018', '2024-12-03 10:52:03', '2024-12-03 10:52:03', NULL),
(37, '2024-12-03 17:52:03', 'Pemasukan', 'DO', 'Pemasukan Tunai', 80000, 'DO', 24, 'DO-20241203-0019', 'ARI WAHYU', 'penjual', 'Tunai', 'Pemasukan tunai DO #DO-20241203-0019', '2024-12-03 10:53:10', '2024-12-03 10:53:10', NULL),
(38, '2024-12-03 17:52:03', 'Pemasukan', 'DO', 'Pembayaran DO', 24205660, 'DO', 24, 'DO-20241203-0019', 'ARI WAHYU', 'penjual', 'Transfer', 'Pembayaran DO via Transfer', '2024-12-03 10:53:10', '2024-12-03 10:53:10', NULL),
(39, '2024-12-03 18:01:52', 'Pemasukan', 'Operasional', 'Tambah Saldo', 250000000, 'Operasional', 1, 'OP-00001', 'Wendy', 'user', 'Tunai', 'sdfsdfsfsfdf', '2024-12-03 11:02:53', '2024-12-03 11:02:53', NULL),
(40, '2024-12-03 18:03:05', 'Pemasukan', 'Operasional', 'Tambah Saldo', 12025000, 'Operasional', 2, 'OP-00002', 'Wendy', 'user', 'Tunai', 'siska minta transfer', '2024-12-03 11:03:55', '2024-12-03 11:03:55', NULL),
(41, '2024-12-03 18:14:42', 'Pengeluaran', 'Operasional', 'Pijak Gas', 76000, 'Operasional', 5, 'OP-00005', NULL, 'supir', 'Tunai', '-', '2024-12-03 11:16:19', '2024-12-03 11:17:26', '2024-12-03 11:17:26'),
(42, '2024-12-03 18:20:34', 'Pengeluaran', 'Operasional', 'Pijak Gas', 76000, 'Operasional', 6, 'OP-00006', NULL, 'supir', 'Tunai', '-', '2024-12-03 11:23:42', '2024-12-03 11:23:42', NULL),
(43, '2024-12-03 18:30:19', 'Pengeluaran', 'Operasional', 'Lain-lain', 50000, 'Operasional', 7, 'OP-00007', 'Kasir 1', 'user', 'Tunai', 'belanja', '2024-12-03 11:30:55', '2024-12-03 11:30:55', NULL),
(44, '2024-12-03 18:31:05', 'Pengeluaran', 'Operasional', 'Pijak Gas', 78000, 'Operasional', 8, 'OP-00008', NULL, 'supir', 'Tunai', '-', '2024-12-03 11:31:43', '2024-12-03 11:31:43', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2024_01_01_000000_create_cache_table', 1),
(2, '2024_01_01_000000_create_jobs_table', 1),
(3, '2024_01_01_000001_create_perusahaan_table', 1),
(4, '2024_01_01_000002_create_users_table', 1),
(5, '2024_01_01_000003_create_penjuals_table', 1),
(6, '2024_01_01_000003_create_supir_table', 1),
(7, '2024_01_01_000004_create_pekerjas_table', 1),
(8, '2024_01_01_000005_create_transaksi_do_table', 1),
(9, '2024_01_01_000006_create_operasional_table', 1),
(10, '2024_01_01_000007_create_laporan_keuangan_table', 1),
(11, '2024_11_18_182348_create_riwayat_pembayaran_hutangs_table', 1),
(12, '2024_12_03_102232_create_kendaraan_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `operasional`
--

CREATE TABLE `operasional` (
  `id` bigint UNSIGNED NOT NULL,
  `tanggal` datetime NOT NULL,
  `operasional` enum('pemasukan','pengeluaran') COLLATE utf8mb4_unicode_ci NOT NULL,
  `kategori` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipe_nama` enum('penjual','user','pekerja','supir') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `penjual_id` bigint UNSIGNED DEFAULT NULL,
  `pekerja_id` bigint UNSIGNED DEFAULT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `supir_id` bigint UNSIGNED DEFAULT NULL,
  `nominal` decimal(15,0) DEFAULT NULL,
  `keterangan` text COLLATE utf8mb4_unicode_ci,
  `file_bukti` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `operasional`
--

INSERT INTO `operasional` (`id`, `tanggal`, `operasional`, `kategori`, `tipe_nama`, `penjual_id`, `pekerja_id`, `user_id`, `supir_id`, `nominal`, `keterangan`, `file_bukti`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '2024-12-03 18:01:52', 'pemasukan', 'tambah_saldo', 'user', NULL, NULL, 3, NULL, 250000000, 'sdfsdfsfsfdf', NULL, '2024-12-03 11:02:53', '2024-12-03 11:02:53', NULL),
(2, '2024-12-03 18:03:05', 'pemasukan', 'tambah_saldo', 'user', NULL, NULL, 3, NULL, 12025000, 'siska minta transfer', NULL, '2024-12-03 11:03:55', '2024-12-03 11:03:55', NULL),
(3, '2024-12-03 18:08:58', 'pengeluaran', 'pijakan_gas', 'supir', NULL, NULL, NULL, 19, NULL, 'pijak gas', NULL, '2024-12-03 11:13:06', '2024-12-03 11:16:35', '2024-12-03 11:16:35'),
(4, '2024-12-03 18:14:42', 'pengeluaran', 'pijakan_gas', 'supir', NULL, NULL, NULL, 19, 76000, NULL, NULL, '2024-12-03 11:15:16', '2024-12-03 11:17:18', '2024-12-03 11:17:18'),
(5, '2024-12-03 18:14:42', 'pengeluaran', 'pijakan_gas', 'supir', NULL, NULL, NULL, 19, 76000, NULL, NULL, '2024-12-03 11:16:19', '2024-12-03 11:17:26', '2024-12-03 11:17:26'),
(6, '2024-12-03 18:20:34', 'pengeluaran', 'pijakan_gas', 'supir', NULL, NULL, NULL, 19, 76000, NULL, NULL, '2024-12-03 11:23:42', '2024-12-03 11:23:42', NULL),
(7, '2024-12-03 18:30:19', 'pengeluaran', 'lain_lain', 'user', NULL, NULL, 4, NULL, 50000, 'belanja', NULL, '2024-12-03 11:30:55', '2024-12-03 11:30:55', NULL),
(8, '2024-12-03 18:31:05', 'pengeluaran', 'pijakan_gas', 'supir', NULL, NULL, NULL, 8, 78000, NULL, NULL, '2024-12-03 11:31:43', '2024-12-03 11:31:43', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pekerja`
--

CREATE TABLE `pekerja` (
  `id` bigint UNSIGNED NOT NULL,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alamat` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telepon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pendapatan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `hutang` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `riwayat_bayar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `penjuals`
--

CREATE TABLE `penjuals` (
  `id` bigint UNSIGNED NOT NULL,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alamat` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telepon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hutang` decimal(15,0) DEFAULT NULL,
  `riwayat_bayar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `penjuals`
--

INSERT INTO `penjuals` (`id`, `nama`, `alamat`, `telepon`, `hutang`, `riwayat_bayar`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'FURQON', NULL, NULL, 0, NULL, '2024-12-03 04:36:58', '2024-12-03 04:36:58', NULL),
(2, 'EPI', NULL, NULL, 0, NULL, '2024-12-03 04:36:58', '2024-12-03 04:36:58', NULL),
(3, 'ANDES', NULL, NULL, 0, NULL, '2024-12-03 04:36:58', '2024-12-03 04:36:58', NULL),
(4, 'LOPON', NULL, NULL, 0, NULL, '2024-12-03 04:36:58', '2024-12-03 04:36:58', NULL),
(5, 'HERMAN', NULL, NULL, 0, NULL, '2024-12-03 04:36:58', '2024-12-03 04:36:58', NULL),
(6, 'SIIT', NULL, NULL, 0, NULL, '2024-12-03 04:36:58', '2024-12-03 04:38:50', NULL),
(7, 'ETI SUSANA', NULL, NULL, 0, NULL, '2024-12-03 04:36:58', '2024-12-03 09:56:18', NULL),
(8, 'SISKA', NULL, NULL, 0, NULL, '2024-12-03 04:36:58', '2024-12-03 04:40:31', NULL),
(9, 'JEKI', NULL, NULL, 0, NULL, '2024-12-03 04:36:58', '2024-12-03 04:36:58', NULL),
(10, 'AGUS', NULL, NULL, 0, NULL, '2024-12-03 04:36:58', '2024-12-03 04:36:58', NULL),
(11, 'JOKO', NULL, NULL, 0, NULL, '2024-12-03 04:36:58', '2024-12-03 04:36:58', NULL),
(12, 'SUKARMIN', NULL, NULL, 0, NULL, '2024-12-03 04:36:58', '2024-12-03 10:22:45', NULL),
(13, 'DITEG', NULL, NULL, 0, NULL, '2024-12-03 04:36:58', '2024-12-03 04:36:58', NULL),
(14, 'KELOMPOK', NULL, NULL, 0, NULL, '2024-12-03 04:36:58', '2024-12-03 04:36:58', NULL),
(15, 'UCOK', NULL, NULL, 0, NULL, '2024-12-03 04:36:58', '2024-12-03 04:36:58', NULL),
(16, 'ARI WAHYU', NULL, NULL, 0, NULL, '2024-12-03 04:36:58', '2024-12-03 04:36:58', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `perusahaans`
--

CREATE TABLE `perusahaans` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `saldo` decimal(15,0) NOT NULL DEFAULT '0',
  `alamat` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telepon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pimpinan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Pimpinan Perusahaan',
  `npwp` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Logo Perusahaan',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Status aktif perusahaan',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `perusahaans`
--

INSERT INTO `perusahaans` (`id`, `name`, `saldo`, `alamat`, `telepon`, `email`, `pimpinan`, `npwp`, `logo`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'CV SUCCESS MANDIRI', 299524770, 'Dusun Sungai Moran Nagari Kamang', '+62 823-8921-9670', NULL, 'Yondra', '12.345.678.9-123.000', NULL, 1, '2024-12-03 04:36:58', '2024-12-03 11:31:43', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `riwayat_pembayaran_hutangs`
--

CREATE TABLE `riwayat_pembayaran_hutangs` (
  `id` bigint UNSIGNED NOT NULL,
  `tanggal` timestamp NOT NULL,
  `nominal` decimal(15,0) NOT NULL,
  `tipe` enum('penjual','pekerja') COLLATE utf8mb4_unicode_ci NOT NULL,
  `penjual_id` bigint UNSIGNED DEFAULT NULL,
  `pekerja_id` bigint UNSIGNED DEFAULT NULL,
  `operasional_id` bigint UNSIGNED NOT NULL,
  `keterangan` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('SuM2Pt292mPf63IPZ9tgAlYQa0wCxIao44bh064f', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:133.0) Gecko/20100101 Firefox/133.0', 'YTo3OntzOjY6Il90b2tlbiI7czo0MDoiZFY4Y3duQ0RiNUgyU3lBNXpNZ3RneWpGU09YemZQdmU0aksyalRzbCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9vcGVyYXNpb25hbHMvY3JlYXRlIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2MDoiJDJ5JDEyJEc2UURhSFBRRXBTWlFwTzQ3S1hNck95TVlWV29DckFjVlBGbENGVjVxTTVzSWtKWk8zSXJPIjtzOjY6InRhYmxlcyI7YTo0OntzOjIxOiJMaXN0VHJhbnNha3NpRG9zX3NvcnQiO2E6Mjp7czo2OiJjb2x1bW4iO047czo5OiJkaXJlY3Rpb24iO047fXM6MTc6Ikxpc3RQZW5qdWFsc19zb3J0IjthOjI6e3M6NjoiY29sdW1uIjtOO3M6OToiZGlyZWN0aW9uIjtOO31zOjIxOiJMaXN0UGVuanVhbHNfcGVyX3BhZ2UiO3M6MzoiYWxsIjtzOjE5OiJMaXN0U3VwaXJzX3Blcl9wYWdlIjtzOjM6ImFsbCI7fXM6ODoiZmlsYW1lbnQiO2E6MDp7fX0=', 1733225522);

-- --------------------------------------------------------

--
-- Table structure for table `supir`
--

CREATE TABLE `supir` (
  `id` bigint UNSIGNED NOT NULL,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alamat` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telepon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hutang` decimal(15,0) DEFAULT NULL,
  `riwayat_bayar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `supir`
--

INSERT INTO `supir` (`id`, `nama`, `alamat`, `telepon`, `hutang`, `riwayat_bayar`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'FURQON', NULL, NULL, NULL, NULL, '2024-12-03 06:48:32', '2024-12-03 06:49:40', '2024-12-03 06:49:40'),
(2, 'FURQON', NULL, NULL, NULL, NULL, '2024-12-03 06:50:02', '2024-12-03 06:50:02', NULL),
(3, 'EPI', NULL, NULL, NULL, NULL, '2024-12-03 08:21:36', '2024-12-03 08:21:36', NULL),
(4, 'ANDES', NULL, NULL, NULL, NULL, '2024-12-03 08:22:32', '2024-12-03 08:22:32', NULL),
(5, 'ICAN', NULL, NULL, NULL, NULL, '2024-12-03 08:23:39', '2024-12-03 08:23:39', NULL),
(6, 'HERMAN', NULL, NULL, NULL, NULL, '2024-12-03 09:42:06', '2024-12-03 09:42:06', NULL),
(7, 'SIIT', NULL, NULL, NULL, NULL, '2024-12-03 09:44:31', '2024-12-03 09:44:31', NULL),
(8, 'NARO', NULL, NULL, NULL, NULL, '2024-12-03 09:47:05', '2024-12-03 09:47:05', NULL),
(9, 'agus', NULL, NULL, NULL, NULL, '2024-12-03 10:07:15', '2024-12-03 10:07:15', NULL),
(10, 'JEKI', NULL, NULL, NULL, NULL, '2024-12-03 10:08:20', '2024-12-03 10:08:20', NULL),
(11, 'JOKO', NULL, NULL, NULL, NULL, '2024-12-03 10:15:41', '2024-12-03 10:15:41', NULL),
(12, 'WILCO', NULL, NULL, NULL, NULL, '2024-12-03 10:17:28', '2024-12-03 10:17:28', NULL),
(13, 'KOMBET', NULL, NULL, NULL, NULL, '2024-12-03 10:18:58', '2024-12-03 10:18:58', NULL),
(14, 'DODY', NULL, NULL, NULL, NULL, '2024-12-03 10:23:33', '2024-12-03 10:23:33', NULL),
(15, 'KELOMPOK', NULL, NULL, NULL, NULL, '2024-12-03 10:25:13', '2024-12-03 10:25:13', NULL),
(16, 'AGUNG', NULL, NULL, NULL, NULL, '2024-12-03 10:43:48', '2024-12-03 10:43:48', NULL),
(17, 'UCOK', '', '', NULL, NULL, '2024-12-03 10:51:33', '2024-12-03 10:51:33', NULL),
(18, 'ARI WAHYU', '', '', NULL, NULL, '2024-12-03 10:52:22', '2024-12-03 10:52:22', NULL),
(19, 'EKO', NULL, NULL, NULL, NULL, '2024-12-03 10:52:36', '2024-12-03 10:52:36', NULL),
(20, 'EKO', NULL, NULL, NULL, NULL, '2024-12-03 11:18:06', '2024-12-03 11:21:50', '2024-12-03 11:21:50');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi_do`
--

CREATE TABLE `transaksi_do` (
  `id` bigint UNSIGNED NOT NULL,
  `nomor` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal` datetime NOT NULL,
  `penjual_id` bigint UNSIGNED NOT NULL,
  `supir_id` bigint UNSIGNED NOT NULL,
  `kendaraan_id` bigint UNSIGNED DEFAULT NULL,
  `nomor_polisi` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tonase` decimal(10,2) NOT NULL,
  `harga_satuan` decimal(15,0) NOT NULL,
  `sub_total` decimal(15,0) NOT NULL,
  `upah_bongkar` decimal(15,0) NOT NULL,
  `biaya_lain` decimal(15,0) NOT NULL DEFAULT '0',
  `keterangan_biaya_lain` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hutang_awal` decimal(15,0) NOT NULL,
  `pembayaran_hutang` decimal(12,0) NOT NULL,
  `sisa_hutang_penjual` decimal(12,0) NOT NULL,
  `sisa_bayar` decimal(15,0) NOT NULL,
  `file_do` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cara_bayar` enum('Tunai','Transfer','Cair di Luar','Belum Bayar') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Tunai',
  `catatan` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transaksi_do`
--

INSERT INTO `transaksi_do` (`id`, `nomor`, `tanggal`, `penjual_id`, `supir_id`, `kendaraan_id`, `nomor_polisi`, `tonase`, `harga_satuan`, `sub_total`, `upah_bongkar`, `biaya_lain`, `keterangan_biaya_lain`, `hutang_awal`, `pembayaran_hutang`, `sisa_hutang_penjual`, `sisa_bayar`, `file_do`, `cara_bayar`, `catatan`, `created_at`, `updated_at`, `deleted_at`) VALUES
(6, 'DO-20241203-0001', '2024-12-03 14:47:10', 1, 2, NULL, NULL, 4329.00, 3190, 13809510, 0, 0, NULL, 0, 0, 0, 13809510, NULL, 'Tunai', NULL, '2024-12-03 07:47:39', '2024-12-03 07:47:39', NULL),
(7, 'DO-20241203-0002', '2024-12-03 15:20:48', 2, 3, NULL, NULL, 2763.00, 3190, 8813970, 0, 200000, NULL, 0, 0, 0, 8613970, NULL, 'Tunai', NULL, '2024-12-03 08:21:56', '2024-12-03 09:34:52', NULL),
(8, 'DO-20241203-0003', '2024-12-03 15:22:10', 3, 4, NULL, NULL, 558.00, 3180, 1774440, 0, 0, NULL, 0, 0, 0, 1774440, NULL, 'Tunai', NULL, '2024-12-03 08:22:49', '2024-12-03 09:39:05', NULL),
(9, 'DO-20241203-0004', '2024-12-03 16:36:08', 4, 5, NULL, NULL, 9204.00, 3180, 29268720, 0, 116000, NULL, 0, 0, 0, 29152720, NULL, 'Cair di Luar', NULL, '2024-12-03 09:38:17', '2024-12-03 09:38:17', NULL),
(10, 'DO-20241203-0005', '2024-12-03 16:41:45', 5, 6, NULL, NULL, 940.00, 3180, 2989200, 0, 50000, NULL, 0, 0, 0, 2939200, NULL, 'Tunai', NULL, '2024-12-03 09:42:47', '2024-12-03 09:42:47', NULL),
(11, 'DO-20241203-0006', '2024-12-03 16:43:24', 6, 7, NULL, NULL, 2143.00, 3180, 6814740, 0, 100000, NULL, 0, 0, 0, 6714740, NULL, 'Tunai', NULL, '2024-12-03 09:45:46', '2024-12-03 09:45:46', NULL),
(12, 'DO-20241203-0007', '2024-12-03 16:54:23', 7, 8, NULL, NULL, 7896.00, 3180, 25109280, 0, 400000, NULL, 1000000, 1000000, 0, 23709280, NULL, 'Transfer', NULL, '2024-12-03 09:56:18', '2024-12-03 09:56:18', NULL),
(13, 'DO-20241203-0008', '2024-12-03 17:06:38', 8, 9, NULL, NULL, 1153.00, 3180, 3666540, 0, 0, NULL, 0, 0, 0, 3666540, NULL, 'Tunai', NULL, '2024-12-03 10:07:40', '2024-12-03 10:07:40', NULL),
(14, 'DO-20241203-0009', '2024-12-03 17:08:00', 9, 10, NULL, NULL, 1482.00, 3180, 4712760, 0, 20000, NULL, 0, 0, 0, 4692760, NULL, 'Tunai', NULL, '2024-12-03 10:08:58', '2024-12-03 10:13:36', NULL),
(15, 'DO-20241203-0010', '2024-12-03 17:09:07', 10, 9, NULL, NULL, 1384.00, 3180, 4401120, 0, 15000, NULL, 0, 0, 0, 4386120, NULL, 'Tunai', NULL, '2024-12-03 10:10:22', '2024-12-03 10:10:22', NULL),
(16, 'DO-20241203-0011', '2024-12-03 17:14:51', 11, 11, NULL, NULL, 1017.00, 3180, 3234060, 0, 0, NULL, 0, 0, 0, 3234060, NULL, 'Tunai', NULL, '2024-12-03 10:16:10', '2024-12-03 10:16:10', NULL),
(17, 'DO-20241203-0012', '2024-12-03 17:17:12', 4, 12, NULL, NULL, 9809.00, 3180, 31192620, 0, 124000, NULL, 0, 0, 0, 31068620, NULL, 'Cair di Luar', NULL, '2024-12-03 10:18:21', '2024-12-03 10:18:21', NULL),
(18, 'DO-20241203-0013', '2024-12-03 17:21:12', 12, 13, NULL, NULL, 8122.00, 3190, 25909180, 0, 300000, NULL, 1000000, 1000000, 0, 24609180, NULL, 'Tunai', NULL, '2024-12-03 10:22:45', '2024-12-03 10:22:45', NULL),
(19, 'DO-20241203-0014', '2024-12-03 17:22:45', 13, 14, NULL, NULL, 9979.00, 3180, 31733220, 0, 350000, NULL, 0, 0, 0, 31383220, NULL, 'Transfer', NULL, '2024-12-03 10:24:21', '2024-12-03 10:24:21', NULL),
(20, 'DO-20241203-0015', '2024-12-03 17:41:55', 14, 15, NULL, NULL, 8627.00, 3190, 27520130, 0, 650000, NULL, 0, 0, 0, 26870130, NULL, 'Tunai', NULL, '2024-12-03 10:42:50', '2024-12-03 10:42:50', NULL),
(21, 'DO-20241203-0016', '2024-12-03 17:42:50', 14, 15, NULL, NULL, 8221.00, 3190, 26224990, 0, 650000, NULL, 0, 0, 0, 25574990, NULL, 'Tunai', NULL, '2024-12-03 10:43:20', '2024-12-03 10:43:20', NULL),
(22, 'DO-20241203-0017', '2024-12-03 17:43:20', 5, 16, NULL, NULL, 6196.00, 3190, 19765240, 0, 0, NULL, 0, 0, 0, 19765240, NULL, 'Tunai', NULL, '2024-12-03 10:44:59', '2024-12-03 10:44:59', NULL),
(23, 'DO-20241203-0018', '2024-12-03 17:51:25', 15, 17, NULL, NULL, 1720.00, 3180, 5469600, 0, 0, NULL, 0, 0, 0, 5469600, NULL, 'Tunai', NULL, '2024-12-03 10:52:03', '2024-12-03 10:52:03', NULL),
(24, 'DO-20241203-0019', '2024-12-03 17:52:03', 16, 19, NULL, NULL, 7637.00, 3180, 24285660, 0, 80000, NULL, 0, 0, 0, 24205660, NULL, 'Transfer', NULL, '2024-12-03 10:53:10', '2024-12-03 10:53:10', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `perusahaan_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `perusahaan_id`, `name`, `email`, `is_active`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, NULL, 'Super Admin', 'superadmin@gmail.com', 1, '2024-12-03 04:36:57', '$2y$12$G6QDaHPQEpSZQpO47KXMrOyMYVWoCrAcVPFlCFV5qM5sIkJZO3IrO', NULL, '2024-12-03 04:36:57', '2024-12-03 04:36:57', NULL),
(2, NULL, 'Yondra', 'yondra@gmail.com', 1, '2024-12-03 04:36:57', '$2y$12$I3NbjZJK/8IFgJZDh/ZKaefdnqJ2jZgP7VmBEk88Dc6rUwwcHhf9e', NULL, '2024-12-03 04:36:57', '2024-12-03 04:36:57', NULL),
(3, NULL, 'Wendy', 'wendy@gmail.com', 1, '2024-12-03 04:36:57', '$2y$12$bBY045uqVfx1vTq2BtOsiOYi9c9tNsLtd0ExyTmNjKFiC/OBAVXB.', NULL, '2024-12-03 04:36:57', '2024-12-03 04:36:57', NULL),
(4, 1, 'Kasir 1', 'kasir1@gmail.com', 1, '2024-12-03 04:36:57', '$2y$12$I35M1dLsRFCQafUJf7Qk6et3GZzEQwIwdc7fZ9/.aBDoWtFwro28K', NULL, '2024-12-03 04:36:57', '2024-12-03 04:36:58', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kendaraan`
--
ALTER TABLE `kendaraan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kendaraan_supir_id_foreign` (`supir_id`);

--
-- Indexes for table `laporan_keuangan`
--
ALTER TABLE `laporan_keuangan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `laporan_keuangan_tanggal_index` (`tanggal`),
  ADD KEY `laporan_keuangan_jenis_transaksi_index` (`jenis_transaksi`),
  ADD KEY `laporan_keuangan_kategori_index` (`kategori`),
  ADD KEY `laporan_keuangan_sumber_transaksi_referensi_id_index` (`sumber_transaksi`,`referensi_id`),
  ADD KEY `laporan_keuangan_nominal_index` (`nominal`),
  ADD KEY `laporan_keuangan_created_at_index` (`created_at`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `operasional`
--
ALTER TABLE `operasional`
  ADD PRIMARY KEY (`id`),
  ADD KEY `operasional_penjual_id_foreign` (`penjual_id`),
  ADD KEY `operasional_user_id_foreign` (`user_id`),
  ADD KEY `operasional_tanggal_index` (`tanggal`),
  ADD KEY `operasional_operasional_index` (`operasional`),
  ADD KEY `operasional_nominal_index` (`nominal`),
  ADD KEY `operasional_tipe_nama_index` (`tipe_nama`),
  ADD KEY `operasional_tanggal_operasional_index` (`tanggal`,`operasional`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `pekerja`
--
ALTER TABLE `pekerja`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pekerja_nama_index` (`nama`),
  ADD KEY `pekerja_telepon_index` (`telepon`);

--
-- Indexes for table `penjuals`
--
ALTER TABLE `penjuals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `penjuals_nama_index` (`nama`),
  ADD KEY `penjuals_telepon_index` (`telepon`);

--
-- Indexes for table `perusahaans`
--
ALTER TABLE `perusahaans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `perusahaans_name_unique` (`name`),
  ADD KEY `perusahaans_telepon_index` (`telepon`),
  ADD KEY `perusahaans_email_index` (`email`),
  ADD KEY `perusahaans_npwp_index` (`npwp`);

--
-- Indexes for table `riwayat_pembayaran_hutangs`
--
ALTER TABLE `riwayat_pembayaran_hutangs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `riwayat_pembayaran_hutangs_penjual_id_foreign` (`penjual_id`),
  ADD KEY `riwayat_pembayaran_hutangs_pekerja_id_foreign` (`pekerja_id`),
  ADD KEY `riwayat_pembayaran_hutangs_operasional_id_foreign` (`operasional_id`),
  ADD KEY `riwayat_pembayaran_hutangs_tanggal_index` (`tanggal`),
  ADD KEY `riwayat_pembayaran_hutangs_tipe_penjual_id_pekerja_id_index` (`tipe`,`penjual_id`,`pekerja_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `supir`
--
ALTER TABLE `supir`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supir_nama_index` (`nama`),
  ADD KEY `supir_telepon_index` (`telepon`);

--
-- Indexes for table `transaksi_do`
--
ALTER TABLE `transaksi_do`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaksi_do_nomor_unique` (`nomor`),
  ADD KEY `transaksi_do_penjual_id_foreign` (`penjual_id`),
  ADD KEY `transaksi_do_supir_id_foreign` (`supir_id`),
  ADD KEY `transaksi_do_tanggal_penjual_id_index` (`tanggal`,`penjual_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_perusahaan_id_email_index` (`perusahaan_id`,`email`),
  ADD KEY `users_perusahaan_id_name_index` (`perusahaan_id`,`name`),
  ADD KEY `users_name_index` (`name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kendaraan`
--
ALTER TABLE `kendaraan`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `laporan_keuangan`
--
ALTER TABLE `laporan_keuangan`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `operasional`
--
ALTER TABLE `operasional`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `pekerja`
--
ALTER TABLE `pekerja`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `penjuals`
--
ALTER TABLE `penjuals`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `perusahaans`
--
ALTER TABLE `perusahaans`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `riwayat_pembayaran_hutangs`
--
ALTER TABLE `riwayat_pembayaran_hutangs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `supir`
--
ALTER TABLE `supir`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `transaksi_do`
--
ALTER TABLE `transaksi_do`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `kendaraan`
--
ALTER TABLE `kendaraan`
  ADD CONSTRAINT `kendaraan_supir_id_foreign` FOREIGN KEY (`supir_id`) REFERENCES `supir` (`id`);

--
-- Constraints for table `operasional`
--
ALTER TABLE `operasional`
  ADD CONSTRAINT `operasional_penjual_id_foreign` FOREIGN KEY (`penjual_id`) REFERENCES `penjuals` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `operasional_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `riwayat_pembayaran_hutangs`
--
ALTER TABLE `riwayat_pembayaran_hutangs`
  ADD CONSTRAINT `riwayat_pembayaran_hutangs_operasional_id_foreign` FOREIGN KEY (`operasional_id`) REFERENCES `operasional` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `riwayat_pembayaran_hutangs_pekerja_id_foreign` FOREIGN KEY (`pekerja_id`) REFERENCES `pekerja` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `riwayat_pembayaran_hutangs_penjual_id_foreign` FOREIGN KEY (`penjual_id`) REFERENCES `penjuals` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transaksi_do`
--
ALTER TABLE `transaksi_do`
  ADD CONSTRAINT `transaksi_do_penjual_id_foreign` FOREIGN KEY (`penjual_id`) REFERENCES `penjuals` (`id`),
  ADD CONSTRAINT `transaksi_do_supir_id_foreign` FOREIGN KEY (`supir_id`) REFERENCES `supir` (`id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_perusahaan_id_foreign` FOREIGN KEY (`perusahaan_id`) REFERENCES `perusahaans` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
