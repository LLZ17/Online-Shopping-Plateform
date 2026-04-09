-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 30, 2026 at 05:29 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `html_sql`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `user_id` varchar(20) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `selected_size` varchar(10) NOT NULL,
  `colour` varchar(50) DEFAULT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `category_type` enum('Men','Women') DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `favourites`
--

CREATE TABLE `favourites` (
  `id` int(11) NOT NULL,
  `user_id` varchar(20) NOT NULL,
  `product_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` varchar(50) NOT NULL,
  `user_id` varchar(20) NOT NULL,
  `product_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `selected_size` varchar(20) DEFAULT NULL,
  `colour` varchar(50) DEFAULT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `payment_method` enum('FPX','E-Wallet','Credit/Debit card','Cash Payment','COD') DEFAULT NULL,
  `payment_details` text DEFAULT NULL,
  `payment_status` enum('Pending','Paid','Failed','Canceled') DEFAULT 'Pending',
  `voucher_id` varchar(50) DEFAULT NULL,
  `voucher_discount_amount` decimal(10,2) DEFAULT 0.00,
  `points_used` int(11) DEFAULT 0,
  `points_discount_amount` decimal(10,2) DEFAULT 0.00,
  `points_earned` int(11) DEFAULT 0,
  `order_status` enum('Processing','Shipped','Delivered','Cancelled') DEFAULT 'Processing',
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_time` datetime DEFAULT NULL,
  `shipping_address` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `keywords` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `size` varchar(100) DEFAULT NULL,
  `colour` varchar(100) DEFAULT NULL,
  `rating` varchar(20) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `low_stock_threshold` int(11) DEFAULT 5,
  `sales_count` int(11) DEFAULT 0,
  `product_image` varchar(255) DEFAULT NULL,
  `status` enum('Instock','Low in stock','Out of stock') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `user_id` varchar(20) NOT NULL,
  `rating` decimal(2,1) NOT NULL DEFAULT 5.0,
  `comment` text DEFAULT NULL,
  `review_photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` varchar(20) NOT NULL,
  `username` varchar(50) NOT NULL,
  `real_name` varchar(100) DEFAULT NULL,
  `gender` enum('Male','Female') DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `password_hashed` varchar(255) NOT NULL,
  `phone_num` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `Postcode` varchar(20) NOT NULL,
  `City` varchar(50) NOT NULL,
  `State` varchar(50) NOT NULL,
  `points` int(11) DEFAULT 0,
  `role` enum('Admin','S.Admin','Member') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `login_attempts` int(11) DEFAULT 0,
  `last_login_attempt` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `reset_token` varchar(100) DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT 'default.jpg',
  `status` enum('Active','Blocked') DEFAULT NULL,
  `email_code` varchar(6) DEFAULT NULL,
  `email_expiry` datetime DEFAULT NULL,
  `email_verified` int(11) DEFAULT 0,
  `email_status` enum('Verified','Unverified') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `real_name`, `gender`, `birthday`, `password_hashed`, `phone_num`, `email`, `address`, `Postcode`, `City`, `State`, `points`, `role`, `created_at`, `login_attempts`, `last_login_attempt`, `last_login`, `remember_token`, `reset_token`, `profile_photo`, `status`, `email_code`, `email_expiry`, `email_verified`, `email_status`) VALUES
('001', 'theboss', 'limlizhe', 'Male', '2026-03-01', '$2a$12$8D7Kmk8DxAMFTlYmNsMwbuUurMl5w2lA/dCX4C6Qef6k0LnQbIlIq', '0132111222', 'theboss@pxxmall.my', 'Level 33, Menara KPJ, Jln Tun Razak,', '50400', 'Kuala Lumpur', 'Wilayah Persekutuan Kuala Lumpur', 10000, 'Admin', '2026-03-01 05:14:52', 0, NULL, '2026-03-28 22:43:54', NULL, NULL, 'theboss.jpeg', 'Active', NULL, NULL, 0, 'Verified'),
('a102', 'tdyadmin', 'teedeyong', 'Male', '0000-00-00', '$2a$12$8D7Kmk8DxAMFTlYmNsMwbuUurMl5w2lA/dCX4C6Qef6k0LnQbIlIq', '01126408141', 'teedeyong@gmail.com', 'PV9, Jalan Kampung Wira Jaya, Taman Melati, 53300 Kuala Lumpur, Wilayah Persekutuan Kuala Lumpur', '0', '', '', 0, 'Admin', '2026-03-13 01:15:00', 0, NULL, '2026-03-27 15:34:07', NULL, NULL, 'tdy.jpg', 'Active', NULL, NULL, 0, 'Verified'),
('a103', 'wyfadmin', 'wongyongfeng', 'Male', '0000-00-00', '$2a$12$8D7Kmk8DxAMFTlYmNsMwbuUurMl5w2lA/dCX4C6Qef6k0LnQbIlIq', 'N/A', 'N/A', 'N/A\r\n\r\n', '', '', '', 0, 'Admin', '2026-03-13 02:15:00', 0, NULL, '2026-03-27 15:24:59', NULL, NULL, 'wyf.jpg', 'Active', NULL, NULL, 0, 'Verified'),
('a104', 'lhjadmin', 'leonghongjun', 'Male', '0000-00-00', '$2a$12$8D7Kmk8DxAMFTlYmNsMwbuUurMl5w2lA/dCX4C6Qef6k0LnQbIlIq', 'N/A', 'N/A', 'N/A', '0', '', '', 0, 'Admin', '2026-03-13 03:15:00', 0, NULL, '2026-03-12 17:00:00', 'zZ5xX8cC2v', NULL, 'default.jpg', 'Active', NULL, NULL, 0, 'Verified'),
('am100', 'admin', 'admin', 'Male', '0000-00-00', '$2a$12$8D7Kmk8DxAMFTlYmNsMwbuUurMl5w2lA/dCX4C6Qef6k0LnQbIlIq', 'N/A', 'N/A', 'N/A', '0', '', '', 0, 'Admin', '2026-03-13 22:15:00', 0, NULL, '2026-03-27 15:30:34', NULL, NULL, 'default.jpg', 'Active', NULL, NULL, 0, 'Verified'),
('m1001', 'amir_yusuf', 'Amirul bin Yusuf', 'Male', '1988-05-12', '$2y$10$X1aY2bZ3c4D5e6F7g8H9iO', '012 4785231', 'amirul.y311@gmail.com', 'No. 12, Jalan Ampang Utama, Taman Ampang', '50450', 'Kuala Lumpur', 'Wilayah Persekutuan Kuala Lumpur', 125, 'Member', '2025-05-12 02:20:00', 0, NULL, '2026-03-10 14:05:22', 'zX8v1PqL9m', NULL, '/storage/profiles/amir_y.jpg', 'Active', NULL, NULL, 0, 'Verified'),
('m1002', 'meiling_w', 'Wong Mei Ling', 'Female', '2001-07-25', '$2y$10$A9b8C7d6E5f4G3h2I1j0kL', '016 2945038', 'wong.ml725@yahoo.com', 'No. 45, Jalan Hijau 4, Taman Green Lane, Jelutong', '11600', 'Jelutong', 'Pulau Pinang', 840, 'Member', '2025-08-20 07:45:10', 0, NULL, '2026-03-12 09:12:45', 'kL2mP9vR7s', NULL, 'https://ui-avatars.com/api/?name=Wong+Mei+Ling', 'Active', NULL, NULL, 0, 'Verified'),
('m1003', 'shah_ali', 'Shahrizal bin Ali', 'Male', '1990-01-05', '$2y$10$QwE1rR2tT3yY4uU5iI6oOp', '011 58241190', 'shahrizal814@hotmail.com', 'No. 5, Persiaran P11, Taman P11, Presint 11', '62000', 'Putrajaya', 'Wilayah Persekutuan Putrajaya', 310, 'Member', '2026-01-05 01:00:00', 1, '2026-03-13 08:30:10', '2026-03-13 08:32:00', NULL, NULL, '/storage/profiles/shahrizal.png', 'Active', NULL, NULL, 0, 'Verified'),
('m1004', 'lee_th', 'Lee Teck Huat', 'Male', '2003-11-11', '$2y$10$Zx1Cv2Bn3M4As5Df6Gh7Jk', '019 4421187', 'leeth_942@gmail.com', 'No. 8, Jalan OKK Awang Besar, Taman Labuan', '87000', 'Labuan', 'Wilayah Persekutuan Labuan', 450, 'Member', '2025-11-11 14:15:30', 0, NULL, '2026-02-28 20:10:00', 'qW3eR4tY5u', 'rT9uI0pP1o', NULL, 'Active', NULL, NULL, 0, 'Verified'),
('m1005', 'rajan_k', 'Rajan a/l Kumar', 'Male', '1978-03-15', '$2y$10$Pl9Ok8Mj7Nh6Bg5Vf4Cd3X', '011 39582147', 'k.rajan618@yahoo.com', 'No. B-12-3, Jalan Seri Rejang, Taman Seri Rejang, Batu Caves', '68100', 'Batu Caves', 'Selangor', 92, 'Member', '2025-03-15 03:30:00', 3, '2026-03-13 12:45:00', '2026-01-20 10:00:00', NULL, NULL, '/storage/profiles/rajan.jpg', 'Active', NULL, NULL, 0, 'Verified'),
('m1006', 'siti_bakar', 'Siti Nurhaliza binti Bakar', 'Female', '2000-06-01', '$2y$10$Ki8Ju7Hy6Gt5Fr4De3Sw2Q', '019 5824419', 'sitinur.b512@gmail.com', 'No. 102, Jalan Kebun, Taman Maznah', '40000', 'Shah Alam', 'Selangor', 560, 'Member', '2025-06-01 06:20:00', 0, NULL, '2026-03-11 23:50:11', 'aA2sS3dD4f', NULL, 'https://ui-avatars.com/api/?name=Siti+Nurhaliza', 'Active', NULL, NULL, 0, 'Verified'),
('m1007', 'chong_vk', 'Chong Vee Keat', 'Male', '1991-12-25', '$2y$10$Mo1Ni2Bu3Vy4Cx5Zq6Ww7E', '014 6275581', 'vkeat713@hotmail.com', 'No. 8, Jalan SS2/1, Taman Bahagia', '47300', 'Petaling Jaya', 'Selangor', 45, 'Member', '2025-12-25 10:00:00', 0, NULL, '2026-03-05 07:20:15', 'zZ9xX8cC7v', NULL, '/storage/profiles/cvk.jpg', 'Active', NULL, NULL, 0, 'Verified'),
('m1008', 'fati_zainal', 'Fatimah binti Zainal', 'Female', '2004-04-10', '$2y$10$Lo9Ki8Ju7Hy6Gt5Fr4De3S', '017 3194482', 'zainal.fati101@gmail.com', 'No. 21, Jalan Kuching, Taman Tasik Indah', '51200', 'Kuala Lumpur', 'Wilayah Persekutuan Kuala Lumpur', 980, 'Member', '2025-04-10 00:15:00', 0, NULL, '2026-03-12 16:30:00', 'pP1oO2iI3u', NULL, NULL, 'Active', NULL, NULL, 0, 'Verified'),
('m1009', 'izzah_p', 'Nurul Izzah', 'Female', '1996-09-18', '$2y$10$Pa1Qs2Wd3Ef4Rg5Th6Yj7U', '013 2294451', 'izzah.n402@yahoo.com', 'No. 22, Jalan P9G, Taman P9, Presint 9', '62250', 'Putrajaya', 'Wilayah Persekutuan Putrajaya', 215, 'Member', '2025-09-18 04:00:00', 0, NULL, '2026-03-13 10:00:00', 'lL5kK6jJ7h', 'xY7zZ8wW9q', '/storage/profiles/izzah.jpg', 'Active', NULL, NULL, 0, 'Verified'),
('m1010', 'ahmad_r', 'Ahmad Rosli', 'Male', '2002-02-14', '$2y$10$Az9Sx8Dc7Fv6Gb5Hn4Jm3K', '012 7748831', 'rosli.ahmad339@gmail.com', 'No. 15, Jalan Pohon Batu, Taman Mutiara', '87000', 'Labuan', 'Wilayah Persekutuan Labuan', 110, 'Member', '2026-02-14 06:00:00', 2, '2026-03-01 09:15:22', '2026-02-14 14:05:00', NULL, NULL, NULL, 'Active', NULL, NULL, 0, 'Verified'),
('m1011', 'kclow_92', 'Low Kean Choon', 'Male', '1982-08-20', '$2y$10$Q1w2E3r4T5y6U7i8O9p0A', '013 4821105', 'lowkc281@hotmail.com', 'No. 15, Jalan Kenanga 3, Taman Perling', '80250', 'Johor Bahru', 'Johor', 490, 'Member', '2025-02-20 08:30:00', 0, NULL, '2026-03-11 21:00:00', 'mM1nN2bB3v', NULL, NULL, 'Active', NULL, NULL, 0, 'Verified'),
('m1012', 'idris_ms', 'Idris bin Mat Saman', 'Male', '1977-07-07', '$2y$10$Z1x2C3v4B5n6M7a8S9d0F', '012 6631985', 'idris_ms961@gmail.com', 'No. 56, Jalan Hang Tuah, Taman Melaka Raya, Melaka Tengah', '75200', 'Melaka Tengah', 'Melaka', 276, 'Member', '2025-07-07 03:00:00', 0, NULL, '2026-03-10 13:45:00', 'bB4vV5cC6x', NULL, '/storage/profiles/idris.png', 'Active', NULL, NULL, 0, 'Verified'),
('m1013', 'sarah_van', 'Sarah Vanar', 'Female', '2005-10-30', '$2y$10$P1o2I3u4Y5t6R7e8W9q0L', '011 28504491', 'sarah_vnr05@yahoo.com', 'No. 10-5, Lorong Tabuan, Taman Tabuan Jaya', '93000', 'Kuching', 'Sarawak', 110, 'Member', '2025-10-30 01:20:00', 0, NULL, '2026-03-12 14:10:00', 'sS7dD8fF9g', NULL, NULL, 'Active', NULL, NULL, 0, 'Verified'),
('m1014', 'lim_yheng', 'Lim Yi Heng', 'Male', '1991-05-20', '$2y$10$M1n2B3v4C5x6Z7q8W9e0R', '016 3394852', 'yiheng.l44@gmail.com', 'No. 34, Jalan Ipoh, Taman Kaya', '51200', 'Kuala Lumpur', 'Wilayah Persekutuan Kuala Lumpur', 67, 'Member', '2026-01-20 02:45:00', 0, NULL, '2026-03-13 11:30:00', 'hH0jJ9kK8l', NULL, '/storage/profiles/yhlim.jpg', 'Active', NULL, NULL, 0, 'Verified'),
('m1015', 'aziz_hash', 'Azizul bin Hashim', 'Male', '1983-03-01', '$2y$10$K1j2H3g4F5d6S7a8Q9w0E', '019 7241150', 'azizul_h707@hotmail.com', 'No. 5, Jalan Sultan Ismail, Taman Bukit Bintang', '50250', 'Kuala Lumpur', 'Wilayah Persekutuan Kuala Lumpur', 890, 'Member', '2025-03-01 05:00:00', 0, NULL, '2026-02-28 17:50:00', 'qQ1wW2eE3r', NULL, NULL, 'Active', NULL, NULL, 0, 'Verified'),
('m1016', 'tan_shwa', 'Tan Siew Hwa', 'Female', '1976-08-15', '$2y$10$B1n2M3a4S5d6F7g8H9j0K', '012 9482261', 'siewhwa_t219@yahoo.com', 'No. 12A, Jalan Bukit Kecil, Taman Bukit Mertajam', '14000', 'Bukit Mertajam', 'Pulau Pinang', 23, 'Member', '2025-08-15 08:10:00', 0, NULL, '2026-03-01 12:00:00', 'eE4rR5tT6y', NULL, NULL, 'Active', NULL, NULL, 0, 'Verified'),
('m1017', 'arun_muthu', 'Arun a/l Muthu', 'Male', '2001-12-01', '$2y$10$X1z2C3v4B5n6M7q8W9e0R', '017 4825593', 'arun.m522@gmail.com', 'No. 9, Jalan Gasing, Taman Petaling Utama', '46000', 'Petaling Jaya', 'Selangor', 412, 'Member', '2025-12-01 02:00:00', 1, '2026-03-10 08:00:00', '2026-03-10 08:05:00', NULL, NULL, '/storage/profiles/arun.jpg', 'Active', NULL, NULL, 0, 'Verified'),
('m1018', 'nurul_ain', 'Nurul Ain binti Musa', 'Female', '1998-05-25', '$2y$10$O1p2I3u4Y5t6R7e8W9q0L', '013 9981109', 'ainmusa819@hotmail.com', 'No. 45, Jalan Enggang, Taman Keramat, Ampang', '68000', 'Ampang', 'Selangor', 95, 'Member', '2025-05-25 01:30:00', 0, NULL, '2026-03-12 20:15:00', 'yY7uU8iI9o', NULL, NULL, 'Active', NULL, NULL, 0, 'Verified'),
('m1019', 'lee_jun', 'Lee Jun Hoong', 'Male', '2004-11-05', '$2y$10$V1c2X3z4A5s6D7f8G9h0J', '014 3098842', 'junhoong_l33@gmail.com', 'No. 88, Jalan Perak, Taman Jaya, Georgetown', '10150', 'Georgetown', 'Pulau Pinang', 330, 'Member', '2025-11-05 06:40:00', 0, NULL, '2026-03-13 09:10:00', 'oO0pP1aA2s', NULL, '/storage/profiles/junlee.png', 'Active', NULL, NULL, 0, 'Verified'),
('m1020', 'hassan_r', 'Hassan bin Rashid', 'Male', '1985-01-20', '$2y$10$N1m2B3v4C5x6Z7q8W9e0R', '018 6423371', 'rashid_h12@yahoo.com', 'No. 2, Jalan Tun Razak, Taman U-Thant', '50400', 'Kuala Lumpur', 'Wilayah Persekutuan Kuala Lumpur', 21, 'Member', '2025-01-20 09:15:00', 0, NULL, '2026-03-11 11:30:00', 'sS3dD4fF5g', NULL, NULL, 'Active', NULL, NULL, 0, 'Verified'),
('m1021', 'devi_subra', 'Devi a/p Subramaniam', 'Female', '2001-03-22', '$2y$10$S1d2F3g4H5j6K7l8M9n0B', '011 62055418', 'devi.s904@gmail.com', 'No. B-5-2, Jalan Kiara, Taman Mont Kiara', '60000', 'Kuala Lumpur', 'Wilayah Persekutuan Kuala Lumpur', 178, 'Member', '2025-03-22 01:00:00', 0, NULL, '2026-03-13 07:45:00', 'fF6gG7hH8j', NULL, NULL, 'Active', NULL, NULL, 0, 'Verified'),
('m1022', 'ng_wtatt', 'Ng Wei Tatt', 'Male', '1985-06-14', '$2y$10$A1s2D3f4G5h6J7k8L9q0W', '016 4721159', 'weitatt.ng38@hotmail.com', 'No. 23, Jalan Austin 1, Taman Mount Austin', '81100', 'Johor Bahru', 'Johor', 290, 'Member', '2025-06-14 03:20:00', 0, NULL, '2026-03-12 18:00:00', 'jJ9kK0lL1z', NULL, '/storage/profiles/ngwt.jpg', 'Active', NULL, NULL, 0, 'Verified'),
('m1023', 'kamil_ibra', 'Kamil bin Ibrahim', 'Male', '2000-09-01', '$2y$10$W1e2R3t4Y5u6I7o8P9p0Z', '012 5941123', 'kamil_ib516@gmail.com', 'No. 17, Lorong Damai, Taman Damai', '88000', 'Kota Kinabalu', 'Sabah', 54, 'Member', '2025-09-01 02:00:00', 0, NULL, '2026-03-10 09:30:00', 'xX2cC3vV4b', NULL, NULL, 'Active', NULL, NULL, 0, 'Verified'),
('m1024', 'yap_lseng', 'Yap Lian Seng', 'Male', '1979-02-10', '$2y$10$Z1x2C3v4B5n6M7a8S9d0G', '019 2843361', 'lseng.yap87@yahoo.com', 'No. 5, Jalan Klang Lama, Taman OUG, Klang', '41000', 'Klang', 'Selangor', 460, 'Member', '2025-02-10 06:15:00', 0, NULL, '2026-03-11 13:00:00', 'nN5mM6qQ7w', NULL, '/storage/profiles/yapls.png', 'Active', NULL, NULL, 0, 'Verified'),
('m1025', 'zul_ahmad', 'Zulkifli bin Ahmad', 'Male', '1980-11-20', '$2y$10$Q1w2E3r4T5y6U7i8O9p0X', '013 9054418', 'zulkifli_a99@gmail.com', 'No. 14, Jalan Meru, Taman Klang Parade', '41050', 'Klang', 'Selangor', 12, 'Member', '2025-11-20 00:30:00', 0, NULL, '2026-03-13 06:15:00', 'eE8rR9tT0y', NULL, NULL, 'Active', NULL, NULL, 0, 'Verified'),
('m1026', 'liew_myee', 'Liew Mei Yee', 'Female', '2003-07-25', '$2y$10$M1n2B3v4C5x6Z7q8W9e0P', '014 5923307', 'meiyee_744@hotmail.com', 'No. 9, Lorong Indah 1, Taman Canning', '30000', 'Ipoh', 'Perak', 310, 'Member', '2025-07-25 07:50:00', 0, NULL, '2026-03-12 10:20:00', 'uU1iI2oO3p', NULL, '/storage/profiles/meiyee.jpg', 'Active', NULL, NULL, 0, 'Verified'),
('m1027', 'priya_nair', 'Priya a/p Nair', 'Female', '1996-04-05', '$2y$10$K1j2H3g4F5d6S7a8Q9w0O', '017 2841159', 'nair.priya55@gmail.com', 'No. 11, Jalan Gurney, Taman Datuk Keramat', '54000', 'Kuala Lumpur', 'Wilayah Persekutuan Kuala Lumpur', 88, 'Member', '2025-04-05 04:00:00', 0, NULL, '2026-03-13 11:00:00', 'aA4sS5dD6f', NULL, NULL, 'Active', NULL, NULL, 0, 'Verified'),
('m1028', 'syed_mokh', 'Syed Mokhtar', 'Male', '1982-12-12', '$2y$10$B1n2M3a4S5d6F7g8H9j0U', '018 7319940', 'syed.m95@yahoo.com', 'No. 45, Jalan Putra, Taman Titiwangsa', '50350', 'Kuala Lumpur', 'Wilayah Persekutuan Kuala Lumpur', 155, 'Member', '2025-12-12 01:40:00', 0, NULL, '2026-03-01 14:00:00', 'gG7hH8jJ9k', NULL, '/storage/profiles/syed.jpg', 'Active', NULL, NULL, 0, 'Verified'),
('m1029', 'teh_ck_tech', 'Teh Chee Keong', 'Male', '2001-08-01', '$2y$10$X1z2C3v4B5n6M7q8W9e0I', '011 59421108', 'ckteh_421@gmail.com', 'No. 7, Jalan SS21/3, Taman Damansara Utama', '47400', 'Petaling Jaya', 'Selangor', 224, 'Member', '2025-08-01 08:00:00', 0, NULL, '2026-03-12 17:30:00', 'lL0zZ1xX2c', NULL, NULL, 'Active', NULL, NULL, 0, 'Verified'),
('m1030', 'rosnah_abd', 'Rosnah binti Abdullah', 'Female', '1983-03-10', '$2y$10$O1p2I3u4Y5t6R7e8W9q0Y', '012 3085521', 'rosnah.a106@hotmail.com', 'No. 89, Kampung Baru, Taman Terengganu', '20000', 'Kuala Terengganu', 'Terengganu', 31, 'Member', '2025-03-10 03:20:00', 0, NULL, '2026-03-10 11:25:00', 'vV3bB4nN5m', NULL, NULL, 'Active', NULL, NULL, 0, 'Verified'),
('m1031', 'ganesh_r', 'Ganesh a/l Ravi', 'Male', '1990-10-15', '$2y$10$V1c2X3z4A5s6D7f8G9h0T', '016 7194405', 'ganesh_ravi01@gmail.com', 'No. 15-3-2, Vista Komanwel, Taman Bukit Jalil', '57000', 'Kuala Lumpur', 'Wilayah Persekutuan Kuala Lumpur', 390, 'Member', '2025-10-15 05:45:00', 0, NULL, '2026-03-13 12:00:00', 'qQ6wW7eE8r', NULL, '/storage/profiles/ganesh.png', 'Active', NULL, NULL, 0, 'Verified'),
('m1032', 'jas_lim', 'Jasmine Lim', 'Female', '2002-05-05', '$2y$10$N1m2B3v4C5x6Z7q8W9e0L', '019 4721184', 'jasmine.l819@yahoo.com', 'No. 22, Jalan Bukit Mewah, Taman Mewah', '93100', 'Kuching', 'Sarawak', 120, 'Member', '2025-05-05 06:00:00', 0, NULL, '2026-03-09 10:00:00', 'tT9yY0uU1i', NULL, NULL, 'Active', NULL, NULL, 0, 'Verified'),
('m1033', 'faizal_ham', 'Faizal bin Hamzah', 'Male', '1986-01-15', '$2y$10$S1d2F3g4H5j6K7l8M9n0W', '013 2295581', 'faizal_h66@gmail.com', 'No. 31, Jalan Molek 2/1, Taman Molek', '81100', 'Johor Bahru', 'Johor', 440, 'Member', '2025-01-15 00:30:00', 0, NULL, '2026-03-12 15:40:00', 'oO2pP3aA4s', NULL, '/storage/profiles/faizal.jpg', 'Active', NULL, NULL, 0, 'Verified'),
('m1034', 'chew_wy', 'Chew Wah Yan', 'Male', '2004-09-20', '$2y$10$A1s2D3f4G5h6J7k8L9q0R', '014 9182245', 'wahyan.c107@hotmail.com', 'No. 4, Lorong Sultan, Taman Jaya', '46200', 'Petaling Jaya', 'Selangor', 20, 'Member', '2025-09-20 09:10:00', 0, NULL, '2026-02-15 09:00:00', 'dD5fF6gG7h', NULL, NULL, 'Active', NULL, NULL, 0, 'Verified'),
('m1035', 'farah_daud', 'Farah binti Daud', 'Female', '1998-06-18', '$2y$10$W1e2R3t4Y5u6I7o8P9p0E', '017 6031195', 'farah.d932@gmail.com', 'No. 102, Jalan Loke Yew, Taman Miharja', '55200', 'Kuala Lumpur', 'Wilayah Persekutuan Kuala Lumpur', 350, 'Member', '2025-06-18 02:20:00', 0, NULL, '2026-03-13 08:45:00', 'hH8jJ9kK0l', NULL, NULL, 'Active', NULL, NULL, 0, 'Verified'),
('m1036', 'kumar_s', 'Kumar a/l Selvam', 'Male', '2003-04-20', '$2y$10$Z1x2C3v4B5n6M7a8S9d0T', '018 3954481', 'selvam_k42@yahoo.com', 'No. 8, Jalan Seremban Jaya, Taman Seremban Jaya', '70300', 'Seremban', 'Negeri Sembilan', 66, 'Member', '2025-04-20 03:00:00', 0, NULL, '2026-03-10 14:20:00', 'zZ1xX2cC3v', NULL, '/storage/profiles/kumar.jpg', 'Active', NULL, NULL, 0, 'Verified'),
('m1037', 'chan_swai', 'Chan Siew Wai', 'Female', '1987-02-28', '$2y$10$Q1w2E3r4T5y6U7i8O9p0P', '011 19284453', 'sw_chan005@gmail.com', 'No. 56, Jalan Pudu, Taman Maluri', '55100', 'Kuala Lumpur', 'Wilayah Persekutuan Kuala Lumpur', 278, 'Member', '2025-02-28 07:30:00', 0, NULL, '2026-03-12 11:00:00', 'bB4nN5mM6q', NULL, NULL, 'Active', NULL, NULL, 0, 'Verified'),
('m1038', 'khalid_m', 'Khalid bin Mansor', 'Male', '1982-07-14', '$2y$10$M1n2B3v4C5x6Z7q8W9e0Q', '012 7421159', 'khalid_m771@hotmail.com', 'No. 19, Jalan Putra, Taman Putra', '05000', 'Alor Setar', 'Kedah', 134, 'Member', '2025-07-14 01:15:00', 0, NULL, '2026-03-11 16:40:00', 'wW7eE8rR9t', NULL, NULL, 'Active', NULL, NULL, 0, 'Verified'),
('m1039', 'goh_pseng', 'Goh Poh Seng', 'Male', '2000-11-05', '$2y$10$K1j2H3g4F5d6S7a8Q9w0D', '016 5291184', 'ps.goh883@gmail.com', 'No. 14, Jalan Universiti, Taman Bukit', '46300', 'Petaling Jaya', 'Selangor', 410, 'Member', '2025-11-05 06:00:00', 0, NULL, '2026-03-13 10:30:00', 'yY0uU1iI2o', NULL, '/storage/profiles/gohps.png', 'Active', NULL, NULL, 0, 'Verified'),
('m1040', 'anis_fauzi', 'Anis binti Fauzi', 'Female', '1993-03-25', '$2y$10$B1n2M3a4S5d6F7g8H9j0S', '019 3381142', 'anis_f901@yahoo.com', 'No. Lot 22, Kampung Besut, Taman Besut', '22200', 'Besut', 'Terengganu', 82, 'Member', '2025-03-25 02:10:00', 0, NULL, '2026-03-01 09:00:00', 'pP3aA4sS5d', NULL, NULL, 'Active', NULL, NULL, 0, 'Verified'),
('m1041', 'ramesh_v', 'Ramesh a/l Velu', 'Male', '2004-08-18', '$2y$10$X1z2C3v4B5n6M7q8W9e0V', '013 2948815', 'ramesh_velu67@gmail.com', 'No. B-9-1, Sri Putamas, Taman Sri Putamas', '51200', 'Kuala Lumpur', 'Wilayah Persekutuan Kuala Lumpur', 195, 'Member', '2025-08-18 08:45:00', 0, NULL, '2026-03-13 07:15:00', 'fF6gG7hH8j', NULL, NULL, 'Active', NULL, NULL, 0, 'Verified'),
('m1042', 'sim_k_t', 'Sim Kok Thai', 'Male', '1974-12-05', '$2y$10$O1p2I3u4Y5t6R7e8W9q0F', '014 7192258', 'simkt_618@hotmail.com', 'No. 7, Jalan Tebrau, Taman Sentosa', '80250', 'Johor Bahru', 'Johor', 310, 'Member', '2025-12-05 05:20:00', 0, NULL, '2026-03-12 21:00:00', 'kK9lL0zZ1x', NULL, '/storage/profiles/simkt.jpg', 'Active', NULL, NULL, 0, 'Verified'),
('m1043', 'zainab_ali', 'Zainab binti Ali', 'Female', '2001-05-10', '$2y$10$V1c2X3z4A5s6D7f8G9h0L', '017 4821159', 'zainab_a055@gmail.com', 'No. 6, Jalan Masjid, Taman Ipoh', '30000', 'Ipoh', 'Perak', 145, 'Member', '2025-05-10 03:30:00', 0, NULL, '2026-03-10 11:45:00', 'xX2cC3vV4b', NULL, NULL, 'Active', NULL, NULL, 0, 'Verified'),
('m1044', 'wong_cy', 'Wong Chee Yan', 'Male', '1989-09-02', '$2y$10$N1m2B3v4C5x6Z7q8W9e0M', '018 2049915', 'cywong105@yahoo.com', 'No. 10, Lorong Biru, Taman Jelutong, Jelutong', '11600', 'Jelutong', 'Pulau Pinang', 270, 'Member', '2025-09-01 01:00:00', 0, NULL, '2026-03-12 13:20:00', 'nN5mM6qQ7w', NULL, '/storage/profiles/wongcy.png', 'Active', NULL, NULL, 0, 'Verified'),
('m1045', 'firdaus_r', 'Firdaus bin Ramli', 'Male', '2005-02-15', '$2y$10$S1d2F3g4H5j6K7l8M9n0G', '011 49281105', 'firdaus_r401@gmail.com', 'No. Lot 3, Kampung Pantai, Taman Pantai, Melaka Tengah', '75200', 'Melaka Tengah', 'Melaka', 39, 'Member', '2025-02-15 06:00:00', 0, NULL, '2026-03-11 15:30:00', 'eE8rR9tT0y', NULL, NULL, 'Active', NULL, NULL, 0, 'Verified'),
('m1046', 'lakshmi_p', 'Lakshmi a/p Pillay', 'Female', '1980-10-10', '$2y$10$A1s2D3f4G5h6J7k8L9q0H', '012 6023391', 'lakshmi_p77@hotmail.com', 'No. 25, Jalan Gurney, Taman Gurney', '54000', 'Kuala Lumpur', 'Wilayah Persekutuan Kuala Lumpur', 480, 'Member', '2025-10-10 02:10:00', 0, NULL, '2026-03-13 09:00:00', 'uU1iI2oO3p', NULL, NULL, 'Active', NULL, NULL, 0, 'Verified'),
('m1047', 'tiew_slian', 'Tiew Siew Lian', 'Female', '2001-06-05', '$2y$10$W1e2R3t4Y5u6I7o8P9p0J', '016 3318847', 'sltiew808@gmail.com', 'No. 18, Jalan SS15/4, Taman Subang Jaya', '47500', 'Subang Jaya', 'Selangor', 215, 'Member', '2025-06-05 07:00:00', 0, NULL, '2026-03-12 17:00:00', 'aA4sS5dD6f', NULL, '/storage/profiles/sltiew.jpg', 'Active', NULL, NULL, 0, 'Verified'),
('m1048', 'hafiz_bah', 'Hafiz bin Baharudin', 'Male', '1991-01-25', '$2y$10$Z1x2C3v4B5n6M7a8S9d0K', '019 4028815', 'hafiz_b09@yahoo.com', 'No. 11, Jalan Tunku, Taman Tunku', '50480', 'Kuala Lumpur', 'Wilayah Persekutuan Kuala Lumpur', 110, 'Member', '2025-01-25 04:00:00', 0, NULL, '2026-03-11 11:00:00', 'gG7hH8jJ9k', NULL, NULL, 'Active', NULL, NULL, 0, 'Verified'),
('m1049', 'choo_whong', 'Choo Wai Hong', 'Male', '2000-11-15', '$2y$10$Q1w2E3r4T5y6U7i8O9p0L', '013 5291147', 'choowh_712@gmail.com', 'No. 45, Lorong Damai, Taman Damai', '88000', 'Kota Kinabalu', 'Sabah', 330, 'Member', '2025-11-15 00:30:00', 0, NULL, '2026-03-13 09:45:00', 'lL0zZ1xX2c', NULL, '/storage/profiles/choowh.png', 'Active', NULL, NULL, 0, 'Verified'),
('m1050', 'kavi_mani', 'Kavitha a/p Mani', 'Female', '1988-08-22', '$2y$10$M1n2B3v4C5x6Z7q8W9e0N', '014 6284451', 'kavitha_m51@hotmail.com', 'No. 5, Jalan Seputeh, Taman Seputeh', '58000', 'Kuala Lumpur', 'Wilayah Persekutuan Kuala Lumpur', 92, 'Member', '2025-08-22 06:20:00', 0, NULL, '2026-03-12 16:10:00', 'vV3bB4nN5m', NULL, NULL, 'Active', NULL, NULL, 0, 'Verified'),
('m1051', 'anoymous', 'N/A', 'Male', '0000-00-00', 'N/A', 'N/A', 'N/A', 'N/A', '0', '', '', 0, 'Member', '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '', '', '', 'Active', NULL, NULL, 0, 'Verified'),
('m1052', 'unknown', 'N/A', 'Male', '0000-00-00', 'N/A', 'N/A', 'N/A', 'N/A', '0', '', '', 0, 'Member', '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '', '', '', 'Active', NULL, NULL, 0, 'Verified'),
('t101', 'llz', 'LIM LI ZHE', 'Male', '2026-03-03', '$2a$12$8D7Kmk8DxAMFTlYmNsMwbuUurMl5w2lA/dCX4C6Qef6k0LnQbIlIq', '0193019930', 'limlz-wm25@student.tarc.edu.my', 'Ground Floor, Bangunan Tan Sri Khaw Kai Boh (Block A), Jalan Genting Kelang', '53300', 'Setapak', 'Wilayah Persekutuan Kuala Lumpur', 100, 'Member', '2026-03-13 07:15:00', 0, NULL, '2026-03-30 23:16:33', '83d653df705a033f99f1f6e2bab1ae3452afb6762ccad8216d85c0806cba2098', NULL, 'llz.jpg', 'Active', '444932', '2026-03-29 17:37:14', 0, 'Verified'),
('t102', 'tdytest', 'TEE DE YONG', 'Male', '0000-00-00', '$2a$12$8D7Kmk8DxAMFTlYmNsMwbuUurMl5w2lA/dCX4C6Qef6k0LnQbIlIq', '01126408141', 'teedy-wm25@student.tarc.edu.my', 'Residensi PV9, No. 19, Jalan Melati Utama, Taman Melati', '53300', 'Setapak', 'Wilayah Persekutuan Kuala Lumpur', 100, 'Member', '2026-03-13 00:15:00', 0, NULL, '2026-03-13 13:15:22', 'mM4nN1bB9v', NULL, 'tdy.jpg', 'Active', NULL, NULL, 0, 'Verified'),
('t103', 'wyftest', 'WONG YONG FENG', 'Male', '0000-00-00', '$2a$12$8D7Kmk8DxAMFTlYmNsMwbuUurMl5w2lA/dCX4C6Qef6k0LnQbIlIq', '01131780587', 'wongyf-wm25@student.tarc.edu.my', 'Tingkat 1-3, Blok 1, Jalan Setia Tropika', '81200', 'Johor Bahru', 'Johor', 100, 'Member', '2026-03-13 11:15:00', 0, NULL, '2026-03-11 08:30:00', 'sS2dD9fF1g', NULL, 'wyf.jpg', 'Active', NULL, NULL, 0, 'Verified'),
('t104', 'lhjtest', 'LEONG HONG JUN', 'Male', '0000-00-00', '$2a$12$8D7Kmk8DxAMFTlYmNsMwbuUurMl5w2lA/dCX4C6Qef6k0LnQbIlIq', '0177173669', 'leonghj-wm25@student.tarc.edu.my', 'Block 13-21A PV128,Jalan Genting Kelang, Taman Danau Kota', '53100', 'Setapak', 'Wilayah Persekutuan Kuala Lumpur', 100, 'Member', '2026-03-12 21:15:00', 0, NULL, '2026-03-13 10:05:00', 'lL7kK3jJ2h', NULL, 'default.jpg', 'Active', NULL, NULL, 0, 'Verified'),
('t105', 'test', 'test', 'Male', NULL, '$2a$12$8D7Kmk8DxAMFTlYmNsMwbuUurMl5w2lA/dCX4C6Qef6k0LnQbIlIq', NULL, 'N/A', 'N/A', '', '', '', 0, 'Member', '2026-03-27 10:44:12', 0, NULL, NULL, NULL, NULL, 'default.jpg', NULL, NULL, NULL, 0, 'Verified');

-- --------------------------------------------------------

--
-- Table structure for table `vouchers`
--

CREATE TABLE `vouchers` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `discount_type` enum('Free Shipping','Cashback','Others','New User') NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `min_spend` decimal(10,2) DEFAULT 0.00,
  `usage_limit` int(11) DEFAULT NULL,
  `used_count` int(11) DEFAULT 0,
  `expiry_date` datetime NOT NULL,
  `status` enum('Active','Inactive','Expired') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `favourites`
--
ALTER TABLE `favourites`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`,`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `vouchers`
--
ALTER TABLE `vouchers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `favourites`
--
ALTER TABLE `favourites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vouchers`
--
ALTER TABLE `vouchers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
