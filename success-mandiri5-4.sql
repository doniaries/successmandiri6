-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 20, 2024 at 08:12 AM
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
-- Database: `success-mandiri5`
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
('356a192b7913b04c54574d18c28d46e6395428ab', 'i:1;', 1732086869),
('356a192b7913b04c54574d18c28d46e6395428ab:timer', 'i:1732086868;', 1732086868),
('a17961fa74e9275d529f489537f179c05d50c2f3', 'i:2;', 1732085219),
('a17961fa74e9275d529f489537f179c05d50c2f3:timer', 'i:1732085219;', 1732085219);

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
  `tipe_pihak` enum('penjual','user') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cara_pembayaran` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tunai/Transfer/Cair di Luar',
  `keterangan` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `laporan_keuangan`
--

INSERT INTO `laporan_keuangan` (`id`, `tanggal`, `jenis_transaksi`, `kategori`, `sub_kategori`, `nominal`, `sumber_transaksi`, `referensi_id`, `nomor_referensi`, `pihak_terkait`, `tipe_pihak`, `cara_pembayaran`, `keterangan`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '2024-11-20 14:01:37', 'Pemasukan', 'DO', 'Upah Bongkar', 100000, 'DO', 1, 'DO-20241120-0001', 'Budi', 'penjual', 'Tunai', 'Upah bongkar dari DO DO-20241120-0001', '2024-11-20 07:02:12', '2024-11-20 07:02:12', NULL),
(2, '2024-11-20 14:01:37', 'Pemasukan', 'DO', 'Biaya Lain', 110000, 'DO', 1, 'DO-20241120-0001', 'Budi', 'penjual', 'Tunai', 'Biaya lain dari DO DO-20241120-0001: ', '2024-11-20 07:02:12', '2024-11-20 07:02:12', NULL),
(3, '2024-11-20 14:01:37', 'Pemasukan', 'DO', 'Bayar Hutang', 120000, 'DO', 1, 'DO-20241120-0001', 'Budi', 'penjual', 'Tunai', 'Pembayaran hutang dari DO DO-20241120-0001', '2024-11-20 07:02:12', '2024-11-20 07:02:12', NULL),
(4, '2024-11-20 14:01:37', 'Pengeluaran', 'DO', 'Pembayaran DO', 1670000, 'DO', 1, 'DO-20241120-0001', 'Budi', 'penjual', 'Tunai', 'Pembayaran DO DO-20241120-0001 via Tunai', '2024-11-20 07:02:12', '2024-11-20 07:02:12', NULL),
(5, '2024-11-20 14:21:01', 'Pemasukan', 'Operasional', 'Bayar Hutang', 500000, 'Operasional', 1, 'OP-00001', 'Dudung', 'penjual', 'Tunai', '-', '2024-11-20 07:22:48', '2024-11-20 07:22:48', NULL);

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
(6, '2024_01_01_000004_create_pekerjas_table', 1),
(7, '2024_01_01_000005_create_transaksi_do_table', 1),
(8, '2024_01_01_000006_create_operasional_table', 1),
(9, '2024_01_01_000007_create_laporan_keuangan_table', 1),
(10, '2024_11_18_182348_create_riwayat_pembayaran_hutangs_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `operasional`
--

CREATE TABLE `operasional` (
  `id` bigint UNSIGNED NOT NULL,
  `tanggal` datetime NOT NULL,
  `operasional` enum('pemasukan','pengeluaran') COLLATE utf8mb4_unicode_ci NOT NULL,
  `kategori` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipe_nama` enum('penjual','user') COLLATE utf8mb4_unicode_ci NOT NULL,
  `penjual_id` bigint UNSIGNED DEFAULT NULL,
  `pekerja_id` bigint DEFAULT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `nominal` decimal(15,0) NOT NULL,
  `keterangan` text COLLATE utf8mb4_unicode_ci,
  `file_bukti` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `operasional`
--

INSERT INTO `operasional` (`id`, `tanggal`, `operasional`, `kategori`, `tipe_nama`, `penjual_id`, `pekerja_id`, `user_id`, `nominal`, `keterangan`, `file_bukti`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '2024-11-20 14:21:01', 'pemasukan', 'bayar_hutang', 'penjual', 2, NULL, NULL, 500000, NULL, NULL, '2024-11-20 07:22:48', '2024-11-20 07:22:48', NULL);

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

--
-- Dumping data for table `pekerja`
--

INSERT INTO `pekerja` (`id`, `nama`, `alamat`, `telepon`, `pendapatan`, `hutang`, `riwayat_bayar`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Udin', 'Banjar Tengah', '464636', '0', '0', NULL, '2024-11-20 07:18:14', '2024-11-20 07:18:14', NULL);

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
(1, 'Budi', 'Jl. Supplier No. 1', '081345678901', 380000, NULL, '2024-11-20 06:46:25', '2024-11-20 07:02:12', NULL),
(2, 'Dudung', 'Jl. Distributor No. 2', '081345678902', 2000000, NULL, '2024-11-20 06:46:25', '2024-11-20 06:46:25', NULL),
(3, 'Wahyudi', 'Jl. Mitra No. 3', '081345678903', 0, NULL, '2024-11-20 06:46:25', '2024-11-20 06:46:25', NULL);

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
(1, 'CV SUCCESS MANDIRI', 99160000, 'Dusun Sungai Moran Nagari Kamang', '+62 823-8921-9670', NULL, 'Yondra', '12.345.678.9-123.000', 'perusahaan-logo/logo success.png', 1, '2024-11-20 06:46:25', '2024-11-20 07:22:48', NULL),
(2, 'Koperasi Success Mandiri', 150000000, 'Sungai Moran, Nagari Kamang', '+62 852-7845-1122', NULL, 'Yondra', '12.345.678.9-124.000', NULL, 1, '2024-11-20 06:46:25', '2024-11-20 06:46:25', NULL),
(3, 'CV SUCCESS', 120000000, 'Sungai Moran, Nagari Kamang', '+62 813-6677-8899', NULL, 'Yondra', '12.345.678.9-125.000', NULL, 1, '2024-11-20 06:46:25', '2024-11-20 06:46:25', NULL);

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
('umK1Nr0TnzhNu7kUkt5vzBNImJ0qebbQiyKHJCHx', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36', 'YTo4OntzOjY6Il90b2tlbiI7czo0MDoiTWVtdWZTbUEzb0VyMHEzbEJTbDI0Rk9IUFBxZUttTHVGM2lpM0I4NiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDU6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9sYXBvcmFuLWtldWFuZ2FucyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6MzoidXJsIjthOjA6e31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO3M6MTc6InBhc3N3b3JkX2hhc2hfd2ViIjtzOjYwOiIkMnkkMTIkWFFrRXBvSTdtSC50Vi5uNzVsYkQ4T2dXaHU4UzRYQW9wSHpwZEs0SFdVWUdYT0RybU90eC4iO3M6NjoidGFibGVzIjthOjI6e3M6MjE6Ikxpc3RUcmFuc2Frc2lEb3Nfc29ydCI7YToyOntzOjY6ImNvbHVtbiI7TjtzOjk6ImRpcmVjdGlvbiI7Tjt9czoxNzoiTGlzdFBlbmp1YWxzX3NvcnQiO2E6Mjp7czo2OiJjb2x1bW4iO047czo5OiJkaXJlY3Rpb24iO047fX1zOjg6ImZpbGFtZW50IjthOjA6e319', 1732090326);

-- --------------------------------------------------------

--
-- Table structure for table `transaksi_do`
--

CREATE TABLE `transaksi_do` (
  `id` bigint UNSIGNED NOT NULL,
  `nomor` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal` datetime NOT NULL,
  `penjual_id` bigint UNSIGNED NOT NULL,
  `nomor_polisi` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tonase` decimal(10,2) NOT NULL,
  `harga_satuan` decimal(15,0) NOT NULL,
  `total` decimal(15,0) NOT NULL,
  `upah_bongkar` decimal(15,0) NOT NULL,
  `biaya_lain` decimal(15,0) NOT NULL DEFAULT '0',
  `keterangan_biaya_lain` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hutang_awal` decimal(15,0) NOT NULL,
  `pembayaran_hutang` decimal(12,0) NOT NULL,
  `sisa_hutang_penjual` decimal(12,0) NOT NULL,
  `sisa_bayar` decimal(15,0) NOT NULL,
  `file_do` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cara_bayar` enum('Tunai','Transfer') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Tunai',
  `status_bayar` enum('Belum Lunas','Lunas') COLLATE utf8mb4_unicode_ci NOT NULL,
  `catatan` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transaksi_do`
--

INSERT INTO `transaksi_do` (`id`, `nomor`, `tanggal`, `penjual_id`, `nomor_polisi`, `tonase`, `harga_satuan`, `total`, `upah_bongkar`, `biaya_lain`, `keterangan_biaya_lain`, `hutang_awal`, `pembayaran_hutang`, `sisa_hutang_penjual`, `sisa_bayar`, `file_do`, `cara_bayar`, `status_bayar`, `catatan`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'DO-20241120-0001', '2024-11-20 14:01:37', 1, NULL, 400.00, 5000, 2000000, 100000, 110000, NULL, 500000, 120000, 380000, 1670000, NULL, 'Tunai', 'Lunas', NULL, '2024-11-20 07:02:12', '2024-11-20 07:02:12', NULL);

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
(1, NULL, 'Super Admin', 'superadmin@gmail.com', 1, '2024-11-20 06:46:24', '$2y$12$XQkEpoI7mH.tV.n75lbD8OgWhu8S4XAopHzpdK4HWUYGXODrmOtx.', NULL, '2024-11-20 06:46:24', '2024-11-20 06:46:24', NULL),
(2, 1, 'Kasir 1', 'kasir1@gmail.com', 1, '2024-11-20 06:46:25', '$2y$12$LKV4QcI1QF.h03Hk32EcruY5KZeNXWEJp6473ojoGMiNYTRz5MDOy', NULL, '2024-11-20 06:46:25', '2024-11-20 06:46:25', NULL);

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
  ADD KEY `perusahaans_name_index` (`name`),
  ADD KEY `perusahaans_email_index` (`email`),
  ADD KEY `perusahaans_telepon_index` (`telepon`),
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
-- Indexes for table `transaksi_do`
--
ALTER TABLE `transaksi_do`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaksi_do_nomor_unique` (`nomor`),
  ADD KEY `transaksi_do_penjual_id_foreign` (`penjual_id`),
  ADD KEY `transaksi_do_tanggal_penjual_id_index` (`tanggal`,`penjual_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_name_index` (`name`),
  ADD KEY `users_email_index` (`email`),
  ADD KEY `users_perusahaan_id_email_index` (`perusahaan_id`,`email`),
  ADD KEY `users_perusahaan_id_name_index` (`perusahaan_id`,`name`);

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
-- AUTO_INCREMENT for table `laporan_keuangan`
--
ALTER TABLE `laporan_keuangan`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `operasional`
--
ALTER TABLE `operasional`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pekerja`
--
ALTER TABLE `pekerja`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `penjuals`
--
ALTER TABLE `penjuals`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `perusahaans`
--
ALTER TABLE `perusahaans`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `riwayat_pembayaran_hutangs`
--
ALTER TABLE `riwayat_pembayaran_hutangs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transaksi_do`
--
ALTER TABLE `transaksi_do`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

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
  ADD CONSTRAINT `transaksi_do_penjual_id_foreign` FOREIGN KEY (`penjual_id`) REFERENCES `penjuals` (`id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_perusahaan_id_foreign` FOREIGN KEY (`perusahaan_id`) REFERENCES `perusahaans` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
