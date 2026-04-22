-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 22, 2026 at 06:10 PM
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
  `cart_id` bigint(20) NOT NULL,
  `user_id` varchar(20) NOT NULL,
  `product_id` varchar(20) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `selected_size` varchar(10) NOT NULL,
  `colour` varchar(50) DEFAULT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`cart_id`, `user_id`, `product_id`, `quantity`, `selected_size`, `colour`, `added_at`) VALUES
(1, 't101', 'WS020', 1, 'M', 'Multicolor', '2026-04-18 16:10:24'),
(2, 't101', 'WS018', 1, 'Free Size', 'Black', '2026-04-18 16:16:37'),
(4, 's001', 'MS019', 5, 'M', 'White', '2026-04-22 09:11:31'),
(5, 's001', 'MS019', 1, 'L', 'Black', '2026-04-22 09:35:35'),
(6, 's001', 'MS019', 1, 'XL', 'Black', '2026-04-22 09:35:51'),
(7, 's001', 'MS019', 1, '2XL', 'Black', '2026-04-22 09:36:08'),
(8, 't101', 'WS019', 1, 'S', 'Blue', '2026-04-22 13:07:11'),
(9, 't101', 'MP018', 1, 'M', 'BLACK', '2026-04-22 13:07:17'),
(10, 't101', 'MP018', 1, '', '', '2026-04-22 13:18:38');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` varchar(20) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `category_gender` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `category_gender`, `description`, `created_at`) VALUES
('CAT-MO-01', 'Cosplay', 'men others', 'Cosplay and themed costumes', '2026-04-13 12:48:31'),
('CAT-MP-01', 'Ice Silk', 'men pants', 'Ice Silk collection', '2026-04-13 12:48:31'),
('CAT-MP-02', 'Trousers Light', 'men pants', 'Lightweight trousers', '2026-04-13 12:48:31'),
('CAT-MP-03', 'Sport Shorts', 'men pants', 'Athletic and sports shorts', '2026-04-13 12:48:31'),
('CAT-MP-04', 'Shorts Pockets', 'men pants', 'Casual shorts with pockets', '2026-04-13 12:48:31'),
('CAT-MP-05', 'Cargo Short', 'men pants', 'Multi-pocket cargo shorts', '2026-04-13 12:48:31'),
('CAT-MP-06', 'Harem Pants', 'men pants', 'Harem and carrot cut pants', '2026-04-13 12:48:31'),
('CAT-MP-07', 'Jogger Pants', 'men pants', 'Joggers and streetwear bottoms', '2026-04-13 12:48:31'),
('CAT-MP-08', 'Jeans', 'men pants', 'Denim and jeans', '2026-04-13 12:48:31'),
('CAT-MP-09', 'Shorts', 'men pants', 'General casual shorts', '2026-04-13 12:48:31'),
('CAT-MP-10', 'Long Pants', 'men pants', 'General long trousers', '2026-04-13 12:48:31'),
('CAT-MS-01', 'HYPE', 'men shirt', 'HYPE brand apparel', '2026-04-13 12:48:31'),
('CAT-MS-02', 'TNTCO', 'men shirt', 'TNTCO brand apparel', '2026-04-13 12:48:31'),
('CAT-MS-03', 'RIC', 'men shirt', 'RIC brand apparel', '2026-04-13 12:48:31'),
('CAT-MS-04', 'Basic Top', 'men shirt', 'Premium and basic essentials', '2026-04-13 12:48:31'),
('CAT-MS-05', 'Polo', 'men shirt', 'Men Polo shirts', '2026-04-13 12:48:31'),
('CAT-WO-01', 'Hanfu', 'women other', 'Traditional Chinese Dynasty Style Hanfu', '2026-04-21 15:02:25'),
('CAT-WO-02', 'Cheongsam', 'women other', 'Modern and Classic Chinese Cheongsam (Qipao)', '2026-04-21 15:02:25'),
('CAT-WO-03', 'Evening Dress', 'women other', 'Elegant Evening Gowns and Party Dresses', '2026-04-21 15:02:25'),
('CAT-WP-01', 'Pleated Skirt', 'women pants and skirt', 'Pleated skirt collection', '2026-04-13 12:48:31'),
('CAT-WP-02', 'Korean', 'women pants and skirt', 'Korean style bottoms', '2026-04-13 12:48:31'),
('CAT-WP-03', 'Highwaist', 'women pants and skirt', 'Highwaist pants and skirts', '2026-04-13 12:48:31'),
('CAT-WP-04', 'Long Skirt', 'women pants and skirt', 'Elegant long skirts', '2026-04-13 12:48:31'),
('CAT-WP-06', 'Sports Shorts', 'women pants and skirt', 'Athletic shorts for women', '2026-04-13 12:48:31'),
('CAT-WP-07', 'Harem', 'women pants and skirt', 'Harem and loose fit pants', '2026-04-13 12:48:31'),
('CAT-WP-09', 'Capris', 'women pants and skirt', 'Capris and cropped pants', '2026-04-13 12:48:31'),
('CAT-WP-10', 'Wide Leg Shorts', 'women pants and skirt', 'Wide leg shorts', '2026-04-13 12:48:31'),
('CAT-WP-11', 'Skirt Pants', 'women pants and skirt', 'Skirt and pants fusion', '2026-04-13 12:48:31'),
('CAT-WP-13', 'Waterproof', 'women pants and skirt', 'Waterproof and outdoor wear', '2026-04-13 12:48:31'),
('CAT-WP-14', 'Wide Leg Pants', 'women pants and skirt', 'Wide leg slack and baggy pants', '2026-04-13 12:48:31'),
('CAT-WP-15', 'Midi Skirt', 'women pants and skirt', 'Midi length skirts', '2026-04-13 12:48:31'),
('CAT-WP-16', 'Long Skirt', 'women pants and skirt', 'Elegant long skirts (Maxi)', '2026-04-13 12:48:31'),
('CAT-WS-01', 'Korean', 'women shirt', 'Korean style tops', '2026-04-13 12:48:31'),
('CAT-WS-02', 'Polo', 'women shirt', 'Women Polo tops', '2026-04-13 12:48:31'),
('CAT-WS-03', 'French', 'women shirt', 'French style tops', '2026-04-13 12:48:31'),
('CAT-WS-04', 'American', 'women shirt', 'American style tops', '2026-04-13 12:48:31'),
('CAT-WS-05', 'Crop Top', 'women shirt', 'Crop tops', '2026-04-13 12:48:31');

-- --------------------------------------------------------

--
-- Table structure for table `favourites`
--

CREATE TABLE `favourites` (
  `favourite_id` varchar(20) NOT NULL,
  `user_id` varchar(20) NOT NULL,
  `product_id` varchar(20) NOT NULL,
  `category_id` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `favourites`
--

INSERT INTO `favourites` (`favourite_id`, `user_id`, `product_id`, `category_id`, `created_at`) VALUES
('1', 't104', 'MS001', '1', '2026-04-13 12:51:46'),
('FAV69e828618927e3.00', 's001', 'WS018', NULL, '2026-04-22 01:46:09'),
('FAV69e8c75519d386.59', 't101', 'WS019', NULL, '2026-04-22 13:04:21'),
('FAV69e8c755c88e44.18', 't101', 'WS018', NULL, '2026-04-22 13:04:21'),
('FAV69e8c7567cbd04.26', 't101', 'WS017', NULL, '2026-04-22 13:04:22'),
('FAV69e8c7f09a3488.86', 't101', 'MS019', NULL, '2026-04-22 13:06:56'),
('FAV69e8c9823aa9c6.89', 't101', 'WS020', NULL, '2026-04-22 13:13:38');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` varchar(20) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `type` varchar(50) DEFAULT NULL,
  `role` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `is_read`, `created_at`, `type`, `role`) VALUES
(4, 't101', 'Welcome to PXX Mall', 'Please complete your profile to get the best experience.', 0, '2026-04-20 14:10:16', 'system', 'member'),
(5, 't101', 'Order Success', 'Your order #MX1002 has been confirmed.', 0, '2026-04-20 14:10:16', 'order', 'member'),
(6, 't101', 'Restock Notice', 'The minimalist shirt you liked is back in stock!', 0, '2026-04-20 14:10:16', 'stock', 'member'),
(15, 's001', 'New Order Received', 'Order #PXX-9901 has been placed by member Wong.', 0, '2026-04-22 08:48:25', 'order', 'S.Admin'),
(16, 's001', 'Low Stock Alert', 'Product [Premium T-Shirt] is below 5 units.', 0, '2026-04-22 08:48:25', 'stock', 'S.Admin'),
(17, 's001', 'System Maintenance', 'Server will undergo maintenance at 2:00 AM tonight.', 0, '2026-04-22 08:48:25', 'system', 'S.Admin'),
(18, 's001', 'Refund Request', 'User Feng requested a refund for Order #PXX-8820.', 0, '2026-04-22 08:48:25', 'order', 'S.Admin');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(20) NOT NULL,
  `user_id` varchar(20) NOT NULL,
  `product_id` varchar(20) NOT NULL,
  `category_id` varchar(20) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `selected_size` varchar(20) DEFAULT NULL,
  `colour` varchar(50) DEFAULT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `bank_name` varchar(100) NOT NULL,
  `payment_status` enum('Pending','Paid','Failed','Canceled') DEFAULT 'Pending',
  `voucher_id` varchar(20) DEFAULT NULL,
  `voucher_discount_amount` decimal(10,2) DEFAULT 0.00,
  `points_used` int(11) DEFAULT 0,
  `points_discount_amount` decimal(10,2) DEFAULT 0.00,
  `points_earned` int(11) DEFAULT 0,
  `order_status` enum('Processing','Shipped','Delivered','Cancelled') DEFAULT 'Processing',
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_time` datetime DEFAULT NULL,
  `shipping_address` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `product_id`, `category_id`, `quantity`, `selected_size`, `colour`, `total_price`, `bank_name`, `payment_status`, `voucher_id`, `voucher_discount_amount`, `points_used`, `points_discount_amount`, `points_earned`, `order_status`, `order_date`, `payment_time`, `shipping_address`) VALUES
(1, 't101', 'WS001', 'CAT-MS-01', 1, 'XL', 'Apricot', 229.90, 'Public Bank', 'Paid', 'V002', 10.00, 100, 1.00, 15, 'Processing', '2026-04-22 15:32:25', '2026-04-22 23:32:25', 'Ground Floor, Bangunan Tan Sri Khaw Kai Boh (Block A), Jalan Genting Kelang,53300,Setapak,Wilayah Persekutuan Kuala Lumpur'),
(2, 't101', 'WS020', 'CAT-WS-01', 1, 'S', 'Multicolor', 15.00, 'Public Bank', 'Paid', NULL, 0.00, 100, 1.00, 15, 'Processing', '2026-04-22 15:40:52', '2026-04-22 23:40:52', 'Ground Floor, Bangunan Tan Sri Khaw Kai Boh (Block A), Jalan Genting Kelang,53300,Setapak,Wilayah Persekutuan Kuala Lumpur'),
(3, 't101', 'WS018', 'CAT-WS-01', 1, 'M', 'Pink', 15.00, 'Public Bank', 'Paid', NULL, 0.00, 100, 1.00, 20, 'Processing', '2026-04-22 15:42:35', '2026-04-22 23:42:35', 'Ground Floor, Bangunan Tan Sri Khaw Kai Boh (Block A), Jalan Genting Kelang,53300,Setapak,Wilayah Persekutuan Kuala Lumpur'),
(4, 't101', 'MO001', 'CAT-MO-01', 1, 'XL', 'Orange', 20.00, 'Public Bank', 'Paid', NULL, 0.00, 100, 1.00, 15, 'Processing', '2026-04-22 15:44:45', '2026-04-22 23:44:45', 'Ground Floor, Bangunan Tan Sri Khaw Kai Boh (Block A), Jalan Genting Kelang,53300,Setapak,Wilayah Persekutuan Kuala Lumpur');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` varchar(20) NOT NULL,
  `category_id` varchar(20) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `keywords` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `size` varchar(100) DEFAULT NULL,
  `colour` varchar(100) DEFAULT NULL,
  `rating` varchar(20) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `low_stock_threshold` int(11) DEFAULT 5,
  `sales_count` varchar(20) DEFAULT '0',
  `product_image` varchar(255) DEFAULT NULL,
  `status` enum('In stock','Low in stock','Out of stock') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `category_id`, `name`, `keywords`, `description`, `price`, `size`, `colour`, `rating`, `stock`, `low_stock_threshold`, `sales_count`, `product_image`, `status`, `created_at`) VALUES
('MO001', 'CAT-MO-01', 'LOKAP DESIGN T-SHIRT COTTON', 'Cosplay', '100% Cotton Lokap Design Tshirt. High quality printing.', 20.00, 'S,M,L,XL,2XL', 'Orange', '5.0', 0, 5, '10', 'MO001/main.jpg,MO001/orange.jpg', 'In stock', '2026-03-25 08:30:00'),
('MO002', 'CAT-MO-01', 'Wearable Cosplay Shark Costume Hoodie', 'Cosplay', 'Realistic shark designed wearable hoodie for events.', 42.00, 'Free Size', 'Blue', '4.5', 10, 5, '100', 'MO002/main.jpg,MO001/main2.jpg', 'In stock', '2026-04-13 03:45:21'),
('MO003', 'CAT-MO-01', 'T-shirt St John Ambulans Malaysia', 'Cosplay', 'Official SJAM T-Shirt for medical staff or cosplay.', 25.00, 'S,M,L,XL,2XL', 'Green', '4.9', 100, 5, '3800', 'MO003/main.jpg,MO003/main2.jpg', 'In stock', '2026-04-13 03:45:21'),
('MO004', 'CAT-MO-01', 'Malaysian Police Cadet Tee', 'Cosplay', 'Police cadet uniform t-shirt with short sleeve.', 22.00, 'S,M,L,XL,XXL', 'Blue', '4.9', 50, 5, '2000', 'MO004/main.jpg,MO004/main2.jpg', 'In stock', '2026-04-13 04:26:14'),
('MO005', 'CAT-MO-01', 'Halloween Taoist Zombie Costume', 'Cosplay', 'Traditional Chinese Ghost costume for Halloween events.', 32.00, 'S,M,L,XL', 'Black', '4.0', 10, 5, '146', 'MO005/main.jpg,MO005/main2.jpg', 'In stock', '2026-04-13 04:26:14'),
('MO006', 'CAT-MO-01', 'Inflatable Dinosaur Costume', 'Cosplay', 'Durable high quality inflatable dinosaur costume.', 85.00, '160-190cm', 'Brown', '4.9', 50, 5, '100', 'MO006/main.jpg,MO006/main2.jpg', 'In stock', '2026-04-13 04:26:14'),
('MP001', 'CAT-MP-01', 'Men Summer Ice Silk Knee Length Shorts', 'Ice silk', 'Original jogging pants for men! Local delivery within 24 hours. Simple & Fashion urban menswear.', 28.64, 'M,L,XL,2XL,3XL,4XL,5XL', 'BLACK, GREY, KHAKI', '4.6', 500, 15, '2400', 'MP001/black.jpg,MP001/grey.jpg,MP001/khaki.jpg', 'In stock', '2026-03-12 06:20:00'),
('MP002', 'CAT-MP-02', 'Men Spring Trousers Light Breathable', 'Trousers light', 'Nylon material. Straight Pants version. Suitable for Youth (18-25 Years Old). Casual style.', 65.78, 'S,M,L,XL,2XL', 'BLACK, DARK GRAY, LIGHT GRAY, LIGHT GREEN, ARMY GREEN', '4.6', 300, 15, '1200', 'MP002/black.jpg,MP002/darkgrey.jpg,MP002/lightgrey.jpg,MP002/lightgreen.jpg,MP002/armygreen.jpg', 'In stock', '2026-03-25 01:15:00'),
('MP003', 'CAT-MP-03', 'Men 3/4 Quarter Casual Straight Cut Sport Shorts', 'Sport Shorts', 'Versatile & Stylish for gym or travel. Breathable & Quick-Dry airy fabric. Trendy Korean Design.', 29.80, 'L,XL,2XL,3XL,4XL', 'BLACK, BLUE, DARK GRAY', '4.8', 400, 15, '3500', 'MP003/black.jpg,MP003/blue.jpg,MP003/darkgrey.jpg', 'In stock', '2026-04-02 03:30:00'),
('MP004', 'CAT-MP-04', 'Lovito Men Casual Pocket Structure Shorts', 'Shorts Pockets', 'Highlights: Pocket, Structure Line, Lightweight and Breathable. Perfect for weekend casual.', 32.45, 'M,L,XL,2XL', 'KHAKI', '3.9', 100, 15, '800', 'MP004/khaki.jpg', 'In stock', '2026-04-05 08:10:00'),
('MP005', 'CAT-MP-05', 'Men 6-pocket Cargo Short Casual Pants', 'Cargo Short', 'Best Quality distro stitching. Thick and durable material. Perfect for daily activities.', 25.99, 'M,L,XL,2XL,3XL,4XL', 'BLACK, ARMY GREEN, NAVY BLUE, KHAKI, MUSTARD, GREY, COFFEE', '4.8', 120, 15, '5400', 'MP005/black.jpg,MP005/armygreen.jpg,MP005/navyblue.jpg,MP005/khaki.jpg,MP005/mustard.jpg,MP005/grey.jpg,MP005/coffee.jpg', 'In stock', '2026-03-18 02:45:00'),
('MP006', 'CAT-MP-06', 'Men Casual Carrot Wide Leg Harem Pants', 'Harem Pants', 'Tropical-Friendly linen blend. Breathable and sweat-wicking. Designed for hip-hop vibes.', 49.80, 'L,XL,2XL,3XL,4XL', 'BLACK, GREEN', '4.9', 600, 15, '1200', 'MP006/black.jpg,MP006/green.jpg', 'In stock', '2026-04-10 06:20:00'),
('MP007', 'CAT-MP-07', 'Men Jogger Pants Cotton Lightweight Streetwear', 'Jogger Pants', 'Cotton lightweight summer harem trousers. Streetwear style for daily comfort.', 15.90, 'M,L,XL,2XL,3XL,4XL', 'BLACK, APRICOT, KHAKIS, GREEN', '4.9', 700, 15, '4300', 'MP007/black.jpg,MP007/apricot.jpg,MP007/khakis.jpg,MP007/green.jpg', 'In stock', '2026-03-22 09:55:00'),
('MP008', 'CAT-MP-08', 'Men Denim Short Pants Jeans', 'Jeans', 'High quality denim short pants. Classic and durable for multi occasions.', 35.00, 'M,L,XL,2XL', 'BLACK, LIGHT BLUE, MID BLUE, DARK BLUE', '4.9', 800, 15, '2100', 'MP008/black.jpg,MP008/lightblue.jpg,MP008/midblue.jpg,MP008/darkblue.jpg', 'In stock', '2026-03-29 00:30:00'),
('MP009', 'CAT-MP-08', 'Men Skinny Fit Denims Jeans Pant', 'Jeans', 'Elegant classical retro design. Comfortable denim material suitable for Malaysia weather.', 23.80, 'M,L,XL,2XL', 'BLACK, MID BLUE, DARK BLUE, LIGHT BLUE', '4.9', 500, 15, '5k', 'MP009/black.jpg,MP009/midblue.jpg,MP009/darkblue.jpg,MP009/lightblue.jpg', 'In stock', '2026-04-12 03:15:00'),
('MP010', 'CAT-MP-09', 'Men Shorts 3/4 Below the Knee', 'Shorts', 'Bebyterry Premium material. Cool and comfortable with abstract zigzag motif.', 14.06, 'L,XL,2XL,3XL', 'ASH DARK, BLACK, NAVY, SILVER', '4.8', 10000, 15, '8500', 'MP010/ashdark.jpg,MP010/black.jpg,MP010/navy.jpg,MP010/silver.jpg', 'In stock', '2026-03-15 07:05:00'),
('MP011', 'CAT-MP-09', 'Shorts Men Summer Casual Sports Shorts', 'Shorts', '3D Print shorts. Polyester and spandex. Fast drying, moisture absorption and sweat extraction.', 43.54, 'XS,S,M,L,XL,2XL,3XL', 'DARK GREY, WHITE, BLACK', '4.4', 13000, 15, '13000', 'MP011/darkgrey.jpg,MP011/white.jpg,MP011/black.jpg', 'In stock', '2026-04-01 02:40:00'),
('MP012', 'CAT-MP-09', 'High Quality Men Cool Material Shorts', 'Shorts', 'Dry cell active moisture wicking technology. Anti-bacterial odor-resistant treatment.', 47.54, 'XS,S,M,L,XL,2XL,3XL,4XL', 'BLUE, WHITE, RED, DARK GREY', '4.3', 9000, 15, '9000', 'MP012/blue.jpg,MP012/white.jpg,MP012/red.jpg,MP012/darkgrey.jpg', 'In stock', '2026-03-08 04:25:00'),
('MP013', 'CAT-MP-09', 'Casual Capris Breathable Letter Printed Pants', 'Shorts', 'Letter printed quick drying breathable pants. Soft material and well-fitting tailoring.', 38.67, 'XS,S,M,L,XL,2XL,3XL', 'STYLE1_BLACK-GOLD, STYLE1_BLACK-WHITE, STYLE2_WHITE-BLACK, STYLE2_BLACK-WHITE', '4.3', 6767, 15, '6767', 'MP013/style1_black-gold.jpg,MP013/style1_black-white.jpg,MP013/style2_white-black.jpg,MP013/style2_black-white.jpg', 'In stock', '2026-04-18 06:00:00'),
('MP014', 'CAT-MP-09', 'New Men Beach Sports Shorts High Quality', 'Shorts', '3D Print shorts. Polyester and spandex. Fast drying and sweat extraction fabrics.', 41.61, 'XS,S,M,L,XL,2XL,3XL,4XL', 'RED, YELLOW, BLUE, GREEN', '4.4', 7878, 15, '7878', 'MP014/red.jpg,MP014/yellow.jpg,MP014/blue.jpg,MP014/green.jpg', 'In stock', '2026-03-27 02:50:00'),
('MP015', 'CAT-MP-07', 'Men Jogger Trousers Premium Lotto', 'Jogger Pants', 'PREMIUM LOTTO material with right and left pockets. Neat screen printing LOGO.', 34.65, 'M,L,XL', 'BLACK, ASH, NAVY', '4.6', 4575, 15, '4575', 'MP015/black.jpg,MP015/ash.jpg,MP015/navy.jpg', 'In stock', '2026-04-06 01:20:00'),
('MP016', 'CAT-MP-10', 'Men Chino Long Pants Drawstring Elastic', 'Long Pants', 'Waist wears drawstring elastic. Features 2 front pockets and 1 back pocket design.', 25.85, 'S,M,L,XL,2XL,3XL', 'BLACK, ASH-GRAY, CREAM, MOCCAA', '4.4', 8888, 15, '8888', 'MP016/black.jpg,MP016/ash-gray.jpg,MP016/cream.jpg,MP016/moccaa.jpg', 'In stock', '2026-03-14 08:35:00'),
('MP017', 'CAT-MP-10', 'Long Training Sport Pants For Men', 'Long Pants', 'Babytery material with full elastic waist. Distro standard neat stitching for running.', 20.35, 'S,M,L,XL', 'BLACK, ASH YOUNG, NAVY, ASH OLD', '4.6', 7777, 15, '7777', 'MP017/black.jpg,MP017/ashyoung.jpg,MP017/navy.jpg,MP017/ashold.jpg', 'In stock', '2026-03-31 03:10:00'),
('MP018', 'CAT-MP-09', 'Men Motif Shorts Jumbo Distro', 'Shorts', 'High quality jumbo distro motif shorts. Cool and comfortable for daily activity.', 13.49, 'M,L,XL,2XL', 'BLACK, NAVY, CREAM, MOCA, ASH, GREEN ARMY', '4.8', 8999, 15, '8999', 'MP018/black.jpg,MP018/navy.jpg,MP018/cream.jpg,MP018/moca.jpg,MP018/ash.jpg,MP018/armygreen.jpg', 'In stock', '2026-04-14 00:50:00'),
('MP019', 'CAT-MP-09', 'Men Short Pants With Printed Patterns', 'Shorts', '100% cotton printed short pants. Featuring HYPE and NEWYORK style patterns.', 19.99, 'S,M,L,XL,2XL,3XL,4XL,5XL', 'BLACK_NEWYORK, NAVY_NEWYORK, GREY_NEWYORK, BLACK_HYPE1, BLACK_HYPE2, BLACK_HYPE3', '4.8', 15000, 15, '15000', 'MP019/black_newyork.jpg,MP019/navy_newyork.jpg,MP019/grey_newyork.jpg,MP019/black_hype1.jpg,MP019/black_hype2.jpg,MP019/black_hype3.jpg', 'In stock', '2026-03-10 05:40:00'),
('MP020', 'CAT-MP-09', 'Men Cargo Short Pants Army Patterns', 'Shorts', 'Drill material army cargo shorts with 5 pockets. Also available in Long Chino and Tartan.', 30.69, 'M,L,XL,2XL,3XL', 'GREEN, CREAM, BLACK, PLAIN', '4.3', 13400, 15, '13400', 'MP020/green.jpg,MP020/cream.jpg,MP020/black.jpg,MP020/plain.jpg', 'In stock', '2026-04-09 07:55:00'),
('MS001', 'CAT-MS-01', 'HYPE Collegiate Pullover Hoodie', 'HYPE', 'Collegiate design pullover hoodie for streetwear.', 229.90, 'XS,S,M,L,XL,XXL', 'Apricot', '4.8', 8, 5, '0', 'MS001/main.jpg,MS001/apricot.jpg', 'In stock', '2026-03-05 01:15:00'),
('MS002', 'CAT-MS-01', 'HYPE CNY Graphic Lion Tee', 'HYPE', 'CNY Seasonal Graphic Lion Tee. Loose fit.', 149.90, 'XS,S,M,L,XL,XXL', 'Red', '4.7', 50, 5, '0', 'MS002/main.jpg,MS002/red.jpg', 'In stock', '2026-03-12 06:30:00'),
('MS003', 'CAT-MS-01', 'HYPE CNY Graphic Chess Tee', 'HYPE', 'CNY Seasonal Graphic Chess Tee. Loose fit.', 149.90, 'XS,S,M,L,XL,XXL', 'Black', '4.6', 50, 5, '0', 'MS003/main.jpg,MS003/black.jpg', 'In stock', '2026-03-18 02:45:00'),
('MS004', 'CAT-MS-02', 'TNTCO Neighing Horse Tee', 'TNTCO', 'Premium quality graphic tee from TNTCO brand.', 199.00, 'XS,S,M,L,XL,XXL', 'Dark Green', '4.7', 35, 5, '0', 'MS004/main.jpg,MS004/darkgreen.jpg', 'In stock', '2026-03-22 03:20:00'),
('MS005', 'CAT-MS-02', 'TNTCO Patched Logo Hoodie', 'TNTCO', 'Patched Logo Hoodie in navy blue signature style.', 289.00, 'XS,S,M,L,XL,XXL', 'Navy', '4.8', 25, 5, '0', 'MS005/main.jpg,MS005/navy.jpg', 'In stock', '2026-03-27 08:55:00'),
('MS006', 'CAT-MS-02', 'TNTCO Cobra Logo Tee', 'TNTCO', 'Cobra Logo Tee in bold red graphic.', 99.00, 'XS,S,M,L,XL,XXL', 'Red', '4.5', 60, 5, '0', 'MS006/main.jpg,MS006/red.jpg', 'In stock', '2026-03-31 05:10:00'),
('MS007', 'CAT-MS-03', 'RIC Year of Dragon Tang Shirt', 'RIC', 'RIC 2024 Year of the Dragon Chinese-style shirt.', 189.00, 'XS,S,M,L,XL,XXL', 'Red', '4.9', 60, 5, '2000', 'MS007/main.jpg,MS007/red.jpg', 'In stock', '2026-04-02 00:40:00'),
('MS008', 'CAT-MS-01', 'HYPE Initial D 86 Tee', 'HYPE', 'HYPE X Initial D AE86 graphic tee.', 149.90, 'XS,S,M,L,XL,XXL', 'Black', '4.9', 45, 5, '0', 'MS008/main.jpg,MS008/black.jpg', 'In stock', '2026-04-04 07:25:00'),
('MS009', 'CAT-MS-01', 'HYPE Initial D Sliding Tee', 'HYPE', 'Initial D Sliding graphic tee. Loose fit.', 149.90, 'XS,S,M,L,XL,XXL', 'Black', '4.8', 45, 5, '0', 'MS009/main.jpg,MS009/balck.jpg', 'In stock', '2026-04-06 01:50:00'),
('MS010', 'CAT-MS-01', 'HYPE Initial D Keisuke Tee', 'HYPE', 'Initial D Keisuke Takahashi graphic tee.', 149.90, 'XS,S,M,L,XL,XXL', 'Black', '4.8', 45, 5, '0', 'MS010/main.jpg,MS010/black.jpg', 'In stock', '2026-04-08 03:15:00'),
('MS011', 'CAT-MS-01', 'HYPE Initial D Takumi Tee', 'HYPE', 'Initial D Takumi Fujiwara graphic tee.', 149.90, 'XS,S,M,L,XL,XXL', 'Black', '4.9', 45, 5, '0', 'MS011/main.jpg,MS011/black.jpg', 'In stock', '2026-04-10 06:00:00'),
('MS012', 'CAT-MS-01', 'HYPE COCA-COLA Heritage Shirt', 'HYPE', 'Loose fit digital print heritage shirt.', 179.90, 'XS,S,M,L,XL,XXL', 'Multicolor', '5.0', 5, 5, '0', 'MS012/main.jpg,MS012/multicolor.jpg', 'In stock', '2026-03-09 02:30:00'),
('MS013', 'CAT-MS-01', 'HYPE Mesh Jersey', 'HYPE', 'Oversize fit mesh fabric jersey.', 169.90, 'XS,S,M,L,XL,XXL', 'White', '4.6', 8, 5, '0', 'MS013/main.jpg,MS013/white.jpg', 'In stock', '2026-03-15 04:00:00'),
('MS014', 'CAT-MS-01', 'HYPE Seasonal Graphic Tee', 'HYPE', '82% cotton loose fit seasonal graphic tee.', 149.90, 'XS,S,M,L,XL,XXL', 'Black', '4.7', 50, 5, '0', 'MS014/main.jpg,MS014/black.jpg', 'In stock', '2026-04-01 08:20:00'),
('MS015', 'CAT-MS-04', 'Premium White Cotton T-Shirt', 'Basic Top', 'Classic white t-shirt made from 100% premium cotton.', 49.00, 'XS,S,M,L,XL,XXL', 'White', '4.5', 100, 5, '0', 'MS015/main.jpg,MS015/white.jpg', 'In stock', '2026-03-20 01:40:00'),
('MS016', 'CAT-MS-04', 'Warm Cotton Hoodie', 'Basic Top', 'Solid color warm cotton hoodie for daily wear.', 119.00, 'XS,S,M,L,XL', 'White', '4.4', 60, 5, '0', 'MS016/main.jpg,MS016/white.jpg', 'In stock', '2026-04-12 02:10:00'),
('MS017', 'CAT-MS-04', 'Formal Oxford Shirt', 'Basic Top', 'Classic oxford shirt for formal or office wear.', 95.00, 'XS,S,M,L,XL,XXL', 'Light Blue', '4.6', 70, 5, '0', 'MS017/main.jpg,MS017/lightblue.jpg', 'In stock', '2026-03-25 07:30:00'),
('MS018', 'CAT-MS-05', 'Polo Shirt Long Sleeve Plain', 'Polo', 'Standard formal plain long sleeve polo shirt.', 20.00, 'M,L,XL,2XL,3XL', 'Black,White,Blue', '4.8', 200, 5, '3000', 'MS018/main.jpg,MS018/white.jpg,MS018/black.jpg,MS018/blue.jpg', 'In stock', '2026-04-13 03:04:21'),
('MS019', 'CAT-MS-05', 'Ralph Lauren Slim Fit Polo', 'Polo', 'Custom slim fit stretch mesh polo shirt.', 888.00, 'S,M,L,XL,2XL', 'White,Black', '5.0', 10, 5, '26', 'MS019/main.jpg,MS019/black.jpg,MS019/white.jpg', 'In stock', '2026-04-13 03:10:55'),
('MS020', 'CAT-MS-05', 'Lovito Casual Geometric Polo', 'Polo', 'Casual geometric button spring polo shirt.', 15.00, 'M,L,XL,2XL', 'Multicolor', '4.0', 50, 5, '41', 'MS020/main.jpg,MS020/main2.jpg', 'In stock', '2026-04-13 03:16:55'),
('WO002', 'CAT-W01', 'Chinese Horse Face Skirt Set Hanfu Full Set Women', 'Adult Traditional', 'Fabric: Chiffon/Polyester (Polyester Fiber)\nPopular Elements: Printing And Dyeing Gradient\nStandard System: Wei Jin System', 50.00, 'XS,S,M,L,XL', 'Red, Blue', '5', 100, 10, '100', 'WO002/main.jpg,WO002/main2.jpg,WO002/blue.jpg,WO002/red.jpg', 'In stock', '2026-04-21 14:59:02'),
('WO003', 'CAT-WO-02', 'Green Cheongsam Dress Women', 'New Chinese Style', '🌿【Green Bamboo Reflecting the Moon · Spring Attire】🌿\nThis qipao is crafted from bamboo-green silk jacquard fabric, with three pairs of magnolia bud buttons adorning the diagonal front closure. The short sleeves reveal slender wrists. When walking, the bamboo leaf jacquard patterns on the skirt shift with the light, transitioning from deep emerald to soft cyan. The lining is made of wrinkle-resistant satin in a matching hue, with hidden side pockets seamlessly integrated into the seams.', 20.00, 'XS,S,M,L,XL', 'Green', '5', 100, 10, '100', 'WO003/main.jpg,WO003/main2.jpg,WO003/green.jpg', 'In stock', '2026-04-21 14:59:02'),
('WO004', 'CAT-WO-02', 'Red Cheongsam Women', 'Chinese Style', '📐Size Measurement Data📏：\nS: Length 85cm, Bust 80cm, Waist 64cm, Hip 88cm, Shoulder 36cm, Sleeve 16cm\nM: Length 86cm, Bust 84cm, Waist 88cm, Hip 92cm, Shoulder 37cm, Sleeve 16.5cm\nL: Length 87cm, Bust 88cm, Waist 72cm, Hip 94cm, Shoulder 38cm, Sleeve 17cm\nXl: Length 88cm, Bust 92cm, Waist 76cm, Hip 100cm, Shoulder 39cm, Sleeve 17.5cm\nNote: Due To Manual Measurement, The Size May Have 2cm/1in Error. The Color May Be Slightly Different Due To Different Monitor Or Light.', 30.00, 'XS,S,M,L,XL', 'Red', '4.4', 100, 10, '10', 'WO004/main.jpg,WO00/main2.jpg,WO004/red.jpg', 'In stock', '2026-04-21 14:59:02'),
('WO005', 'CAT-WO-03', 'Off-Shoulder Silk Dress', 'Seductive', 'Charming Silk Dress SC759 - Silk Shoulder Dress Hugging Body\nProduct information\nProduct Name: Silk Shoulder Dress SC759\nColor: White, Skin\nSize: S - M - L\nShape: Hugging the body to help flatter the figure\nDesign: Off shoulder – Slightly shed on one side\nMaterial: High quality Korean silk, soft, light and cool\nElasticity: Moderate\nInner lining: With thin lining', 50.00, 'XS,S,M,L,XL', 'White,Black', '4.9', 100, 10, '146', 'WO005/main.jpg,WO005/white.jpg,WO005/black.jpg', 'In stock', '2026-04-21 14:59:02'),
('WO006', 'CAT-WO-03', 'Blue Long Sequin Bridesmaid Temperament Dress', 'Evening Dress New', 'Dress style: square neck\nstyle: Temperament celebrity/sexy\nClothing style details: Solid color\nDress skirt length: longuette\nSleeve length: elbow sleeve/Middle sleeve\nFabric/texture of material: other/other\nComponent content: 71%-80%', 90.00, 'XS,S,M,L,XL', 'Blue', '4.9', 100, 10, '428', 'WO006/main.jpg,WO006/main2.jpg,WO006/blue.jpg', 'In stock', '2026-04-21 14:59:02'),
('WO007', 'CAT-WO-03', 'Feminine mesh dress made of soft mesh', 'Soft material', '✅【Highlight】\n🌷 Off-Shoulder\n🌷 Ribbon\n🌷 Fishtail\n✅【Size/cm】\n- S: Bust 75-88 | Waist 60-70 | Hip 79.5-91.5 | Length 131\n- M: Bust 79-92 | Waist 64-74 | Hip 83.5-95.5 | Length 133\n- L: Bust 83-96 | Waist 68-78 | Hip 87.5-99.5 | Length 135\n✅【FAQ】Ready Stock, Ship in 4-24 Hours, Arrive in 6-10 Days.', 100.00, 'XS,S,M,L,XL', 'White', '5', 100, 5, '10', 'WO007/main.jpg,WO007/main2.jpg,WO007/white.jpg', 'In stock', '2026-04-21 14:59:02'),
('WP001', 'CAT-WP-01', 'High Waist Pleated Midi Skirt', 'Pleated Skirt', 'High-quality mercerized treatment with smooth finish. A-line silhouette, Korean elegant style.', 34.66, 'S,M,L,XL,2XL', 'Apricot, Navy Blue, Dark Green', '4.1', 1000, 15, '2000', 'WP001/Apricot.jpg.jpg,WP001/Dark Blue.jpg.jpg,WP001/Navy Blue.jpg.jpg,WP001/White.jpg.jpg', 'In stock', '2026-03-12 02:00:00'),
('WP002', 'CAT-WP-02', 'Elegant A-Line Midi Skirt', 'Korean', 'Flattering A-Line silhouette with patchwork design. High-quality 270g thickened fabric.', 17.00, 'S,M,L,XL,XXL', 'White, Black, Cream, Pink, Purple, Brown, Gray', '4.5', 900, 15, '7400', 'WP002/Black.jpg.jpg,WP002/Brown.jpg.jpg,WP002/Cream.jpg.jpg,WP002/Gray.jpg.jpg,WP002/Pink.jpg.jpg,WP002/Purple.jpg.jpg,WP002/White.jpg.jpg', 'In stock', '2026-03-25 06:30:00'),
('WP003', 'CAT-WP-03', 'Retro Overalls High Waist Pants', 'Highwaist', 'American Retro style with functional pockets. Straight wide leg, high waist loose fit.', 18.84, 'S,M,L,XL,2XL,3XL', 'Black, Pink, Purple', '3.5', 1000, 15, '3500', 'WP003/Black.jpg.jpg,WP003/Pink.jpg.jpg,WP003/Purple.jpg.jpg', 'In stock', '2026-04-02 01:15:00'),
('WP004', 'CAT-WP-16', 'Korean High Waist Maxi Skirt', 'Long Skirt', 'High-quality breathable polyester. Full elastic waist with big hem umbrella silhouette.', 112.33, 'S,M,L,XL,2XL,3XL', 'Apricot, Black, Blue, Pink, White', '4.5', 999, 15, '4300', 'WP004/Apricot.jpg.jpg,WP004/Black.jpg.jpg,WP004/Pink.jpg.jpg,WP004/White.jpg.jpg', 'In stock', '2026-04-08 03:20:00'),
('WP005', 'CAT-WP-14', 'High Waist Wide-Leg Slimming Shorts', 'Wide Leg Pants', 'High waist and wide leg design for a slimming effect. Premium breathable suit fabric.', 43.96, 'S,M,L,2XL,3XL,4XL', 'Apricot, Black, Khaki, White', '5.0', 1000, 15, '4600', 'WP005/Apricot.jpg.jpg,WP005/Black.jpg.jpg,WP005/Khaki.jpg.jpg,WP005/White.jpg.jpg', 'In stock', '2026-04-05 02:00:00'),
('WP006', 'CAT-WP-03', 'Korean High Waist Wide Leg Jeans', 'Highwaist', 'High-quality durable denim. Relaxed loose-fitting with a tall look silhouette.', 19.90, 'S,M,L,XL,2XL', 'Black, Deep Blue, Light Blue', '4.6', 100, 15, '4100', 'WP006/Black.jpg.jpg,WP006/Deep Blue.jpg.jpg,WP006/Light Blue.jpg.jpg,WP006/White.jpg.jpg', 'In stock', '2026-04-05 00:45:00'),
('WP007', 'CAT-WP-06', 'Unisex Lotto Premium Sports Shorts', 'Sports Shorts', 'Quick dry Lotto Premium material. Perfect for sports, gym, or home wear.', 14.85, 'M,L,XL,XXL', 'Blue, Red, Magenta, White, Yellow', '5.0', 1000, 15, '5900', 'WP007/Blue.jpg.jpg,WP007/Magenta.jpg.jpg,WP007/Red.jpg.jpg,WP007/White.jpg.jpg,WP007/Yellow.jpg.jpg', 'In stock', '2026-03-29 05:10:00'),
('WP008', 'CAT-WP-07', 'Korean Thick Warm Harem Jogger Pants', 'Harem', 'Super thick wool material. Harem silhouette for a relaxed and trendy Korean look.', 26.29, 'S,M,L,XL', 'Black, White Morning', '4.0', 79, 15, '2500', 'WP008/Black.jpg.jpg,WP008/White Morning.jpg.jpg', 'In stock', '2026-04-10 09:30:00'),
('WP009', 'CAT-WP-03', 'Korean High Waist Denim Mini Shorts', 'Highwaist', 'High-quality denim and cotton. Features elasticity for better fit and slimming effect.', 56.60, 'S,M,L,2XL,3XL,4XL', 'Black, Blue, Dark Grey, Retro Blue', '4.3', 1000, 15, '3900', 'WP009/Black.jpg.jpg,WP009/Blue.jpg.jpg,WP009/Dark Grey.jpg.jpg,WP009/Retro Blue.jpg.jpg', 'In stock', '2026-03-22 04:40:00'),
('WP010', 'CAT-WP-09', 'Korean High Waist Harlan Capris', 'Capris', 'Premium wear-resistant and breathable polyester. Slimming harlan cut with loose fit.', 16.00, 'M,L,XL,2XL,3XL,4XL', 'Black, Green, Khaki', '4.7', 178, 15, '3100', 'WP010/Black.jpg.jpg,WP010/Green.jpg.jpg,WP010/Khaki.jpg.jpg', 'In stock', '2026-04-15 07:50:00'),
('WP011', 'CAT-WP-10', 'Wide Leg Five-Point Shorts', 'Wide Leg Shorts', '100% Premium cotton. Breathable and soft, perfect for hot summer days.', 12.00, 'S,M,L,XL,2XL,3XL', 'Black, Caramel, Green, Khaki, Red, White', '4.2', 520, 15, '2600', 'WP011/Black.jpg.jpg,WP011/Caramel.jpg.jpg,WP011/Green.jpg.jpg,WP011/Khaki.jpg.jpg,WP011/Red.jpg.jpg,WP011/White.jpg.jpg', 'In stock', '2026-03-14 02:05:00'),
('WP012', 'CAT-WP-03', 'Korean High Waist Pleated Skort', 'Highwaist', 'Premium Vitex fabric. Anti-exposure design with built-in safety shorts.', 48.39, 'XS,S,M,L,XL,XXL', 'Black, White', '3.9', 226, 15, '7200', 'WP012/Black.jpg.jpg,WP012/White.jpg.jpg', 'In stock', '2026-04-03 03:55:00'),
('WP013', 'CAT-WP-11', 'High Waist Faux Skirt Wide-Leg Pants', 'Skirt Pants', 'Combines the elegance of a skirt with the convenience of pants.', 33.55, 'S,M,L,XL', 'Black, Cream, Write, Pink', '3.4', 119, 15, '4500', 'WP013/Black.jpg.jpg,WP013/Cream.jpg.jpg,WP013/Pink.jpg.jpg,WP013/Write.jpg.jpg', 'In stock', '2026-03-31 01:20:00'),
('WP014', 'CAT-WP-14', 'Korean High Waist Wide Leg Slack Pants', 'Wide Leg Pants', 'Premium Snow and Rain fabric. Wrinkle-resistant and anti-static. Perfect for office.', 35.09, 'S,M,L,XL,2XL', 'Black, White, Brown, Green, Ice Cream', '5.0', 112, 15, '5400', 'WP014/Black.jpg.jpg,WP014/Brown.jpg.jpg,WP014/Ice Cream.jpg.jpg,WP014/White.jpg.jpg,WP014/Gray.jpg.jpg', 'In stock', '2026-04-12 06:10:00'),
('WP015', 'CAT-WP-01', 'Korean High Waist Pleated Maxi Skirt', 'Pleated Skirt', 'Flowy A-line silhouette with pleated texture. Provides a slimming and tall look.', 39.49, 'S,M,L,XL', 'Apricot, Black, Pink, Wathet', '4.8', 236, 15, '4600', 'WP015/Black.jpg.jpg,WP015/Blue.jpg.jpg,WP015/Cream.jpg.jpg,WP015/Pink.jpg.jpg,WP015/White.jpg.jpg', 'In stock', '2026-03-05 05:40:00'),
('WP016', 'CAT-WP-13', 'JEEP SPIRIT Waterproof Ski Pants', 'Waterproof', 'Reliable waterproof and windproof protection. Designed for winter adventures.', 71.69, 'S,M,L,XL,2XL,3XL', 'Pink, Black, Khaki, White', '4.6', 800, 15, '7000', 'WP016/Black.jpg.jpg,WP016/Khaki.jpg.jpg,WP016/Pink.jpg.jpg,WP016/White.jpg.jpg', 'In stock', '2026-03-24 02:15:00'),
('WP017', 'CAT-WP-14', 'Korean High Waist Wide Leg Suit Pants', 'Wide Leg Pants', 'Premium suit fabric creates a tall silhouette. Elastic waist with no-fly design.', 14.99, 'S,M,L-2XL', 'Advanced Black, Advanced Khaki, Advanced Smog Blue', '4.8', 700, 15, '5600', 'WP017/Advanced Black.jpg.jpg,WP017/Advanced Khaki.jpg.jpg,WP017/Advanced Smog Blue.jpg.jpg', 'In stock', '2026-04-06 08:30:00'),
('WP018', 'CAT-WP-14', 'Graffiti Street Style Baggy Pants', 'Wide Leg Pants', 'Retro street style baggy silhouette.', 14.99, 'M,L,XL,XXL', 'Black, Grey', '5.0', 170, 15, '7800', 'WP018/Black.jpg.jpg,WP018/Grey.jpg.jpg', 'In stock', '2026-03-28 00:50:00'),
('WP019', 'CAT-WP-15', 'High Waist A-Line Midi Skirt', 'Midi Skirt', 'Classic Chinos material durable skirt.', 45.69, 'M,L,XL,2XL,3XL', 'Black, Wine Red', '3.5', 400, 15, '8900', 'WP019/Black.jpg.jpg,WP019/Wine Red.jpg.jpg', 'In stock', '2026-04-11 03:15:00'),
('WP020', 'CAT-WP-16', 'Three Layer Lace Maxi Skirt', 'Long Skirt', 'Three-layer puffy lace design for modest fashion.', 35.90, '80cm, 90cm, 100cm', 'Grey, White, Black', '4.3', 800, 15, '8800', 'WP020/Black.jpg.jpg,WP020/Blue.jpg.jpg,WP020/Cream White.jpg.jpg,WP020/Dark Pink.jpg.jpg,WP020/Light Green.jpg.jpg,WP020/Pink.jpg.jpg,WP020/White.jpg.jpg', 'In stock', '2026-03-10 07:45:00'),
('WS001', 'CAT-WS-01', 'Korean Women Loose Long Sleeve Blouse', 'Korean', 'Condition:This product is available.\r\n\r\nMaterial:polyester\r\n\r\nSize(cm)\r\n\r\nSIZE S / length:65Cm,bust:90cm, shoulder：40cm，sleeve：55Cm\r\n\r\nSIZE M / length:66cm, bust:96cm, shoulder:41cm，sleeve：56cm\r\n\r\nSIZE L / length:67Cm, bust:102cm, shoulder：42cm，sleeve：57cm\r\n\r\nSIZE XL / length:68Cm, bust:106cm, shoulder：43cm，sleeve：58cm\r\n\r\nSIZE 2XL / length:69cm, bust:112cm, shoulder:44cm， sleeve:58cm\r\n\r\n(measurement in cm and please note 1cm=0.39 inch)', 23.00, 'S,M,L,XL,XXL', 'pink,white,black,green', '4.8', 4, 5, '2000', 'WS001/main.jpg,WS001/black.jpg,WS001/green.jpg,WS001/pink.jpg,WS001/white.jpg', 'In stock', '2026-03-12 02:20:00'),
('WS002', 'CAT-WS-01', 'Korean Women Slim Fit Button Top', 'Korean', 'Shipping time: fast delivery\r\n\r\nColor; White, Black, Flower Gray, American Gray\r\n\r\nSize; S, M, L, XL, XXL\r\n\r\nVersion Version; Slim Fit\r\n\r\n✅ ​​Fabric advantages\r\n\r\n✔️ ​​95% high-quality cotton + 5% spandex​​: skin-friendly and soft without stuffiness, elastic and tolerant without tightness\r\n\r\n✔️ ​​Seamless craftsmanship​​: fits the body without marks, upgraded comfort\r\n\r\n✔️ ​​Breathable and moisture-absorbent​​: can be worn in all seasons, keep the skin dry', 30.00, 'S,M,L,XL,2XL', 'White,Black,Grey', '4.8', 36, 5, '1000', 'WS002/main.jpg,WS002/black.jpg,WS002/white.jpg,WS002/white2.jpg,WS002/grey.jpg', 'In stock', '2026-03-25 06:15:00'),
('WS003', 'CAT-WS-01', 'VALUNEE Slim Fit Long Sleeve Shirt', 'Korean', '😊Parameter information:\r\n\r\nColor: white、blue、black、pink、sapphire\r\n\r\nMain fabric: polyester\r\n\r\nPattern: solid color\r\n\r\nType: Slim\r\n\r\nLength: Short\r\n\r\nSleeve length: Long sleeves\r\n\r\nThickness: regular\r\n\r\nSize: S~2XL\r\n\r\nStyle type: commuting\r\n\r\n1. The picture is 100% real shot.\r\n\r\n2. The size chart is measured manually, and the measurement error is within ±1 inch.', 29.00, 'S,M,L,XL,2XL', 'White,Blue,Sapphire', '4.8', 189, 5, '4000', 'WS003/main.jpg,WS003/blue.jpg,WS003/white.jpg,WS003/sapphire.jpg', 'In stock', '2026-04-02 01:30:00'),
('WS004', 'CAT-WS-02', 'College Style Short-Sleeved Polo', 'Polo', 'Size table\r\n\r\nS:Bust 96 Shoulder 38 Length 59 Sleeve 25\r\n\r\nM:Bust 100 Shoulder 39 Length 60 Sleeve Length 26\r\n\r\nL:Bust 104 Shoulder 40 Length 61 Sleeve 27\r\n\r\nXL:Bust 108 Shoulder 41 Length 62 Sleeve Length 28\r\n\r\nXXL:Bust 112 Shoulder 42 Length 63 Sleeve Length 29\r\n\r\nNotice:\r\n\r\n1.Due to the light and screen difference, the item\'s color may be slightly different from the pictures.\r\n\r\n2.Because of the different measurement methods, there may be 1-3cm of the allowable range error.', 20.00, 'S,M,L,XL,2XL', 'blue,black,apricot', '4.8', 23, 5, '4000', 'WS004/main.jpg,WS004/black.jpg,WS004/apricot.jpg,WS004/blue.jpg', 'In stock', '2026-04-05 03:00:00'),
('WS005', 'CAT-WS-01', 'Classic Women Long-sleeved Shirt', 'Korean', 'Shipped every day (except public holidays)\r\n• All products are in stock\r\n• shipped within 24 hours. \r\n• All products are 100% new. \r\n• Delivery time: 2-5 days \r\n• Note: If you still can\'t buy it, please consult our staff before buying. \r\n• The actual color of the product may be slightly different from the pictures shown on the website, which is caused by many factors, such as the brightness of the display and the brightness of the light. Please understand..', 20.00, 'Free Size', 'Blue', '4.9', 50, 5, '1000', 'WS005/main.jpg,WS005/pp2.jpg,WS005/pp3.jpg,WS005/pp4.jpg,WS005/pp5.jpg', 'In stock', '2026-03-08 08:45:00'),
('WS006', 'CAT-WS-03', 'French Style White Shirt with Bow', 'French', 'Color: White\r\n\r\nSize: S, M, L, XL\r\n\r\nMaterial Composition: Cotton, Polyester Fiber\r\n\r\nFit: Loose Fit\r\n\r\nStyle: Commuter\r\n\r\nCommuter Style: Ladylike\r\n\r\nSleeve Length: Long Sleeve\r\n\r\nSleeve Type: Balloon Sleeve\r\n\r\nPattern: Solid Color\r\n\r\nManufacturing Method: Woven', 30.00, 'S,M,L,XL,2XL', 'White', '4.9', 215, 5, '1000', 'WS006/main.jpg,WS006/pp2.jpg,WS006/pp3.jpg,WS006/pp4.jpg', 'In stock', '2026-04-10 00:20:00'),
('WS007', 'CAT-WS-01', 'Korean Aesthetic Round Neck T-shirt', 'Korean', 'Product information:\r\n\r\nM: Length 69/27.2, Bust 104/41, Shoulder 48/18.9, Sleeve 20/7.9\r\n\r\nL: Length 71/28, Bust 108/42.5, Shoulder 49/19.2, Sleeve 21/8.3\r\n\r\nXL: Length 73/28.7, Bust 112/44.1, Shoulder 50/19.7, Sleeve 22/8.7\r\n\r\nXXL: Length 75/29.5, Bust 116/45.7, Shoulder 51/20.1, Sleeve 23/9.1\r\n\r\nNote: Manual measurement unit: cm\r\n\r\n*Due to different measurement methods, an error of 1-3CM is allowed, and the error range does not belong to quality problems.\r\n\r\n*Due to light and screen differences, the color of the product may be slightly different from the picture. ', 20.00, 'S,M,L,XL,2XL', 'White,Black,Apricot', '4.9', 60, 5, '2000', 'WS007/main.jpg,WS007/black.jpg,WS007/brown.jpg,WS007/apricot.jpg,WS007/white.jpg', 'In stock', '2026-03-15 05:10:00'),
('WS008', 'CAT-WS-04', 'American One Shoulder Red T-shirt', 'American', '🌸Ready to deliver from Bangkok\r\n🚀Normal delivery time 2-3 days\r\n\r\n\r\n●Unit: (inch)\r\n\r\n\r\nS = Chest 30 \"Length 21\" Waist 24 \"\r\n\r\n\r\nM = Chest 32 \"Length 21.5\" Waist 26 \"\r\n\r\n\r\nL = Chest 33 \"Length 22\" Waist 28 \"\r\n\r\n\r\nXL = Chest 35 \"Length 23\" Waist 29 \"\r\n\r\n\r\n1301 size: (inch)\r\nS = Chest: 29.3-41 \'\' Length: 18.8 \'\'Sleeve Length: 3.2\'\'\r\n\r\n\r\nM = Chest: 30.9-42.6 \'\' Length: 19.1 \'\'Sleeve Length: 3.6\'\'\r\n\r\n\r\nL = Chest: 32.5-44.1 \'\' Length: 19.5 \'\'Sleeve Length: 4\'\'\r\n\r\n\r\nXL = Chest: 34-45.7 \'\' Length: 19.9 \'\'Sleeve Length: 4.4\'\'', 20.00, 'S,M,L,XL,2XL', 'Red,White,Black', '4.7', 28, 5, '3400', 'WS008/main.jpg,WS008/black.jpg,WS008/red.jpg,WS008/brown.jpg,WS008/red.jpg,WS008/white.jpg', 'In stock', '2026-04-01 09:50:00'),
('WS009', 'CAT-WS-05', 'Casual Round Neck Short Crop Top', 'Crop Top', 'Notice:\r\n\r\n1.Due to the light and screen difference, the item\'s color may be slightly different from the pictures.\r\n\r\n2.Because of the different measurement methods, there may be 1-3cm of the allowable range error.', 15.00, 'S,M,L,XL,2XL', 'Black,Brown,Khaki', '4.9', 70, 5, '4000', 'WS009/main.jpg,WS009/black.jpg,WS009/khaki.jpg,WS009/brown.jpg', 'In stock', '2026-03-20 04:00:00'),
('WS010', 'CAT-WS-05', 'Croptop T-Shirt With Bow Tie Front', 'Crop Top', '🔥🔥Free size T-shirt, about 52kg, it\'s so beautiful to wear\r\n\r\n💝💝💝💝💝💝💝💝💝💝💝💝\r\n\r\n🔥🔥The material is very good\r\n\r\n🎋🎋 Silk elastic 💘\r\n\r\n🍀🍀 Fine chiffon 💘\r\n\r\n🍃🍃 Khaki 💘\r\n\r\n🌵🌵 Kake fabric again 💘\r\n\r\n🔥🔥Khaki Skirts Of All Kinds\r\n\r\n🌴🌴 Kate leather...💘\r\n\r\n🌲🌲 Free size elastic waist 💘\r\n\r\n🌱🌱 Size: SML💘\r\n\r\n🔥🔥Shimmering lightning 💘\r\n\r\n🌵🌵 Welding sand. Chiffon. Kate. Silk... etc💘\r\n\r\n🐛🐛 Standard size free size under 53kg 💘', 15.00, 'S,M,L,XL,2XL', 'Black,Pink,Wine Red', '4.8', 100, 5, '3000', 'WS010/main.jpg,WS010/winered.jpg,WS010/pink.jpg,WS010/black.jpg', 'In stock', '2026-04-09 07:30:00'),
('WS011', 'CAT-WS-01', 'Korean V-Neck Button Slim Fit Top', 'Korean', 'Color; Pink, Black, White\r\n\r\nFabric:Cotton + Polyester\r\n\r\nSize; S, M, L, XL\r\n\r\nCollar Type; V-Neck\r\n\r\n✅ Fabric Advantages\r\n\r\n✔️ 75% Premium Cotton + 25% Spandex: Soft and gentle on the skin, breathable and not stuffy, highly elastic, and comfortable to wear.\r\n\r\n✔️ Craftsmanship: Suitable for all body types, enhancing comfort.\r\n\r\n✔️ Breathable and Moisture-wicking: Suitable for all seasons, keeping skin dry.', 15.00, 'S,M,L,XL,2XL', 'Pink,Black,White', '4.7', 150, 5, '359', 'WS011/main.jpg,WS011/black.jpg,WS011/white.jpg,WS011/pink.jpg', 'In stock', '2026-03-18 02:05:00'),
('WS012', 'CAT-WS-01', 'Korean Waist Striped Long Sleeve', 'Korean', 'Fabric: 90% polyester, 7% rayon cotton, 3% spandex\r\n\r\nPopular element: Splicing, stripes\r\n\r\nStyle: Korean style\r\n\r\nLength/sleeve length: Long/long sleeve.\r\n\r\nCollar type: V-collar\r\n\r\nClothing version: slim fit.\r\n\r\n\r\n\r\n🌟Measurement in cm and please note 1cm = 0.39 inch)\r\n\r\n🌸Please allow 1-3CM differs due to manual measurement', 30.00, 'S,M,L,XL,2XL', 'Black,Blue,White', '4.8', 200, 5, '63', 'WS012/main.jpg,WS012/main2.jpg,WS012/pink.jpg,WS012/white.jpg,WS012/blue.jpg,WS012/black.jpg', 'In stock', '2026-04-03 03:40:00'),
('WS013', 'CAT-WS-02', 'Casual Plain Ruched Polo Shirt', 'Polo', 'Shipping from Johor 🚛🚛🚛', 15.00, 'S,M,L,XL,2XL', 'Black,White,Gray,Dark Gray', '4.6', 298, 5, '378', 'WS013/main.jpg,WS013/white.jpg,WS013/drakgrey.jpg,WS013/blakc.jpg,WS013/gray.jpg', 'In stock', '2026-03-29 01:15:00'),
('WS014', 'CAT-WS-01', 'Women Casual Oversized Retro Tee', 'Korean', 'Purchase Note】\r\n\r\n①-Size doesn\'t fit all, please carefully check size chart and select the size based on your real size\r\n\r\n②- There is 1-3cm difference according to manual measurement.\r\n\r\n③- Due to different light and screen, a subtle color difference and real error is normal.', 15.00, 'S,M,L,XL,2XL', 'Black,Red,White', '4.8', 109, 5, '1000', 'WS014/main.jpg,WS014/a44white.jpg,WS014/a44black.jpg,WS014/a20red.jpg,WS014/a20black.jpg', 'In stock', '2026-04-11 06:50:00'),
('WS015', 'CAT-WS-01', 'HITTY Korean 100% Cotton Shirt', 'Korean', '🧶 HITTY Style: Hit The Trend Spot On — Classic Cotton Crew Neck Tee​​\r\n\r\n\r\n\r\n​​Fabric & Fit:​​\r\n\r\n\r\n\r\n​​Precision-Crafted Comfort:​​ 100% Premium Cotton, Luxury Wash for unmatched softness & lasting shape.\r\n\r\n\r\n\r\n​​Effortless Short Sleeves:​​ Strategically cut for agile movement and all-day ease.', 20.00, 'S,M,L,XL,2XL', 'Black,White,Brown,Apricot', '4.9', 221, 5, '454', 'WS015/main.jpg,WS015/apricot.jpg,WS015/brown.jpg,WS015/white.jpg,WS015/black.jpg', 'In stock', '2026-03-05 05:25:00'),
('WS016', 'CAT-WS-01', 'Oversized Summer Casual Loose Tee', 'Korean', 'S：Length：67 CM / 26 inch ；Bust：100 CM / 39 inch ；Shoulder：47 CM / 19 inch；Sleeve：19 CM / 7 inch\r\n\r\nM：Length：69 CM / 27 inch ；Bust：104 CM / 41 inch ；Shoulder：48 CM / 19 inch；Sleeve：20 CM / 8 inch\r\n\r\nL：Length：71 CM / 28 inch ；Bust：108 CM / 43 inch ；Shoulder：49 CM / 19 inch；Sleeve：21 CM / 8 inch\r\n\r\nXL：Length：73 CM / 29 inch ；Bust：112 CM / 44 inch ；Shoulder：50 CM / 20 inch；Sleeve：22 CM / 9 inch\r\n\r\n2XL：Length：75 CM / 30 inch ；Bust：116 CM / 46 inch ；Shoulder：51 CM / 20 inch；Sleeve：23 CM / 9 inch\r\n\r\nunits: cm / inch', 15.00, 'S,M,L,XL,2XL', 'White,Black,Apricot', '4.8', 200, 5, '576', 'WS016/main.jpg,WS015/apricot.jpg,WS015/white.jpg,WS015/main2.jpg', 'In stock', '2026-03-22 02:10:00'),
('WS017', 'CAT-WS-01', 'Printed Loose Strapless Cotton Tee', 'Korean', 'Material Features: Comfortable, Soft, Breathable, Skin-Friendly\r\n\r\nColor; Black, White, Navy Blue, Grey\r\n\r\nFabric; Polyester Fiber+Cotton\r\n\r\nSize; S, M, L, XL, 2XL\r\n\r\n✅ ​​Fabric advantages\r\n\r\n✔️ ​​95% high-quality cotton + 5% spandex​​: skin-friendly and soft without stuffiness, elastic and tolerant without tightness\r\n\r\n✔️ ​​Seamless craftsmanship​​: fits the body without marks, upgraded comfort\r\n\r\n✔️ ​​Breathable and moisture-absorbent​​: can be worn in all seasons, keep the skin dry', 15.00, 'S,M,L,XL,2XL', 'Navy Blue,Gray,White', '4.8', 215, 5, '100', 'WS017/main.jpg,WS017/black.jpg,WS017/white.jpg,WS017/darkgray.jpg,WS017/navyblue.jpg', 'In stock', '2026-04-06 08:00:00'),
('WS018', 'CAT-WS-01', 'Girl Short-Sleeve Knit Summer Top', 'Korean', 'Free size (35-55 kg)\r\n\r\n\r\n\r\nNotice:\r\n\r\n1.Due to the light and screen difference, the item\'s color may be slightly different from the pictures.\r\n\r\n2.Because of the different measurement methods, there may be 1-3cm of the allowable range error.', 15.00, 'Free Size', 'Black,Pink,Blue', '4.9', 50, 5, '376', 'WS018/main.jpg,WS018/blue.jpg,WS018/pink.jpg,WS018/black.jpg', 'In stock', '2026-03-31 00:45:00'),
('WS019', 'CAT-WS-01', 'Fashionable Striped Short-Sleeve', 'Korean', 'Color; Blue Stripes, Brown Stripes\r\n\r\nSize; S, M, L, XL, XXL\r\n\r\nFabric; Cotton+Polyester\r\n\r\nMaterial: The fabric is comfortable, soft, breathable, and not easy to pilling\r\n\r\nFeatures: Fashionable and versatile, suitable for people of all body types', 15.00, 'S,M,L,XL,2XL', 'Blue,Brown', '4.5', 100, 5, '90', 'WS019/main.jpg,WS018/blue.jpg,WS018/brown.jpg', 'In stock', '2026-04-12 03:20:00'),
('WS020', 'CAT-WS-01', 'Lovito x Gladish Casual T-Shirt', 'Korean', '✅HIGHLIGHTS \r\n\r\n-❤️Ruched \r\n\r\n-❤️Soft And Comfortable\r\n\r\n\r\n\r\n✅DESCRIPTIONS \r\n\r\nPattern: Plain\r\n\r\nStyle: Casual\r\n\r\nPlus Size: No\r\n\r\nMaterial: Polyester\r\n\r\nComposition: 95%Polyester+5%Spandex\r\n\r\nFit Type: Slim Fit\r\n\r\nSheer: No\r\n\r\nStretch: Slight Stretch\r\n\r\nRegion of Origin: Mainland China \r\n\r\n\r\n\r\n✅FITTING REFERENCE\r\n\r\nModel is wearing: S\r\n\r\nHeight: 165cm\r\n\r\nWeight: 55kg\r\n\r\nBust: 88cm\r\n\r\nWaist: 68cm\r\n\r\nHips: 93cm', 15.00, 'S,M,L,XL,2XL', 'Multicolor', '4.7', 50, 5, '120', 'WS020/main.jpg,WS018/black.jpg,WS018/white.jpg,WS018/darkgray.jpg', 'In stock', '2026-04-13 10:55:00');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `reviews_id` varchar(20) NOT NULL,
  `product_id` varchar(20) NOT NULL,
  `category_id` varchar(20) DEFAULT NULL,
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
  `Postcode` varchar(50) NOT NULL,
  `City` varchar(50) NOT NULL,
  `State` varchar(50) NOT NULL,
  `bank_name` varchar(100) NOT NULL,
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
  `email_verified` int(11) DEFAULT 1,
  `email_status` enum('Verified','Unverified') NOT NULL DEFAULT 'Verified',
  `phone_status` enum('Verified','Unverified') NOT NULL DEFAULT 'Verified'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `real_name`, `gender`, `birthday`, `password_hashed`, `phone_num`, `email`, `address`, `Postcode`, `City`, `State`, `bank_name`, `points`, `role`, `created_at`, `login_attempts`, `last_login_attempt`, `last_login`, `remember_token`, `reset_token`, `profile_photo`, `status`, `email_code`, `email_expiry`, `email_verified`, `email_status`, `phone_status`) VALUES
('a102', 'tdyadmin', 'teedeyong', 'Male', '0000-00-00', '$2a$12$8D7Kmk8DxAMFTlYmNsMwbuUurMl5w2lA/dCX4C6Qef6k0LnQbIlIq', '+601126408141', 'teedeyong@gmail.com', 'PV9, Jalan Kampung Wira Jaya, Taman Melati, 53300 Kuala Lumpur, Wilayah Persekutuan Kuala Lumpur', '0', '', '', '', 0, 'Admin', '2026-03-13 01:15:00', 0, NULL, '2026-04-11 15:49:26', NULL, NULL, 'tdy.jpg', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('a103', 'wyfadmin', 'wongyongfeng', 'Male', '0000-00-00', '$2a$12$8D7Kmk8DxAMFTlYmNsMwbuUurMl5w2lA/dCX4C6Qef6k0LnQbIlIq', 'N/A', 'N/A', 'N/A\r\n\r\n', '', '', '', '', 0, 'Admin', '2026-03-13 02:15:00', 0, NULL, '2026-03-27 15:24:59', NULL, NULL, 'wyf.jpg', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('a104', 'lhjadmin', 'leonghongjun', 'Male', '0000-00-00', '$2a$12$8D7Kmk8DxAMFTlYmNsMwbuUurMl5w2lA/dCX4C6Qef6k0LnQbIlIq', 'N/A', 'N/A', 'N/A', '0', '', '', '', 0, 'Admin', '2026-03-13 03:15:00', 0, NULL, '2026-03-12 17:00:00', 'zZ5xX8cC2v', NULL, 'default.jpg', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('am100', 'admin', 'admin', 'Male', '0000-00-00', '$2a$12$8D7Kmk8DxAMFTlYmNsMwbuUurMl5w2lA/dCX4C6Qef6k0LnQbIlIq', 'N/A', 'N/A', 'N/A', '0', '', '', '', 0, 'Admin', '2026-03-13 22:15:00', 0, NULL, '2026-03-27 15:30:34', NULL, NULL, 'default.jpg', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1001', 'amir_yusuf', 'Amirul bin Yusuf', 'Male', '1988-05-12', '$2y$10$X1aY2bZ3c4D5e6F7g8H9iO', '+6012 4785231', 'amirul.y311@gmail.com', 'No. 12, Jalan Ampang Utama, Taman Ampang', '50450', 'Kuala Lumpur', 'Wilayah Persekutuan Kuala Lumpur', 'Maybank2U', 125, 'Member', '2025-05-12 02:20:00', 0, NULL, '2026-03-10 14:05:22', 'zX8v1PqL9m', NULL, '/storage/profiles/amir_y.jpg', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1002', 'meiling_w', 'Wong Mei Ling', 'Female', '2001-07-25', '$2y$10$A9b8C7d6E5f4G3h2I1j0kL', '+6016 2945038', 'wong.ml725@yahoo.com', 'No. 45, Jalan Hijau 4, Taman Green Lane, Jelutong', '11600', 'Jelutong', 'Pulau Pinang', 'CIMB Clicks', 840, 'Member', '2025-08-20 07:45:10', 0, NULL, '2026-03-12 09:12:45', 'kL2mP9vR7s', NULL, 'https://ui-avatars.com/api/?name=Wong+Mei+Ling', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1003', 'shah_ali', 'Shahrizal bin Ali', 'Male', '1990-01-05', '$2y$10$QwE1rR2tT3yY4uU5iI6oOp', '+6011 58241190', 'shahrizal814@hotmail.com', 'No. 5, Persiaran P11, Taman P11, Presint 11', '62000', 'Putrajaya', 'Wilayah Persekutuan Putrajaya', 'Public Bank', 310, 'Member', '2026-01-05 01:00:00', 1, '2026-03-13 08:30:10', '2026-03-13 08:32:00', NULL, NULL, '/storage/profiles/shahrizal.png', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1004', 'lee_th', 'Lee Teck Huat', 'Male', '2003-11-11', '$2y$10$Zx1Cv2Bn3M4As5Df6Gh7Jk', '+6019 4421187', 'leeth_942@gmail.com', 'No. 8, Jalan OKK Awang Besar, Taman Labuan', '87000', 'Labuan', 'Wilayah Persekutuan Labuan', 'RHB Bank', 450, 'Member', '2025-11-11 14:15:30', 0, NULL, '2026-02-28 20:10:00', 'qW3eR4tY5u', 'rT9uI0pP1o', NULL, 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1005', 'rajan_k', 'Rajan a/l Kumar', 'Male', '1978-03-15', '$2y$10$Pl9Ok8Mj7Nh6Bg5Vf4Cd3X', '+6011 39582147', 'k.rajan618@yahoo.com', 'No. B-12-3, Jalan Seri Rejang, Taman Seri Rejang, Batu Caves', '68100', 'Batu Caves', 'Selangor', 'Hong Leong Bank', 92, 'Member', '2025-03-15 03:30:00', 3, '2026-03-13 12:45:00', '2026-01-20 10:00:00', NULL, NULL, '/storage/profiles/rajan.jpg', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1006', 'siti_bakar', 'Siti Nurhaliza binti Bakar', 'Female', '2000-06-01', '$2y$10$Ki8Ju7Hy6Gt5Fr4De3Sw2Q', '+6019 5824419', 'sitinur.b512@gmail.com', 'No. 102, Jalan Kebun, Taman Maznah', '40000', 'Shah Alam', 'Selangor', 'AmBank', 560, 'Member', '2025-06-01 06:20:00', 0, NULL, '2026-03-11 23:50:11', 'aA2sS3dD4f', NULL, 'https://ui-avatars.com/api/?name=Siti+Nurhaliza', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1007', 'chong_vk', 'Chong Vee Keat', 'Male', '1991-12-25', '$2y$10$Mo1Ni2Bu3Vy4Cx5Zq6Ww7E', '+6014 6275581', 'vkeat713@hotmail.com', 'No. 8, Jalan SS2/1, Taman Bahagia', '47300', 'Petaling Jaya', 'Selangor', 'Bank Islam', 45, 'Member', '2025-12-25 10:00:00', 0, NULL, '2026-03-05 07:20:15', 'zZ9xX8cC7v', NULL, '/storage/profiles/cvk.jpg', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1008', 'fati_zainal', 'Fatimah binti Zainal', 'Female', '2004-04-10', '$2y$10$Lo9Ki8Ju7Hy6Gt5Fr4De3S', '+6017 3194482', 'zainal.fati101@gmail.com', 'No. 21, Jalan Kuching, Taman Tasik Indah', '51200', 'Kuala Lumpur', 'Wilayah Persekutuan Kuala Lumpur', 'Alliance Bank', 980, 'Member', '2025-04-10 00:15:00', 0, NULL, '2026-03-12 16:30:00', 'pP1oO2iI3u', NULL, NULL, 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1009', 'izzah_p', 'Nurul Izzah', 'Female', '1996-09-18', '$2y$10$Pa1Qs2Wd3Ef4Rg5Th6Yj7U', '+6013 2294451', 'izzah.n402@yahoo.com', 'No. 22, Jalan P9G, Taman P9, Presint 9', '62250', 'Putrajaya', 'Wilayah Persekutuan Putrajaya', 'UOB Bank', 215, 'Member', '2025-09-18 04:00:00', 0, NULL, '2026-03-13 10:00:00', 'lL5kK6jJ7h', 'xY7zZ8wW9q', '/storage/profiles/izzah.jpg', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1010', 'ahmad_r', 'Ahmad Rosli', 'Male', '2002-02-14', '$2y$10$Az9Sx8Dc7Fv6Gb5Hn4Jm3K', '+6012 7748831', 'rosli.ahmad339@gmail.com', 'No. 15, Jalan Pohon Batu, Taman Mutiara', '87000', 'Labuan', 'Wilayah Persekutuan Labuan', 'OCBC Bank', 110, 'Member', '2026-02-14 06:00:00', 2, '2026-03-01 09:15:22', '2026-02-14 14:05:00', NULL, NULL, NULL, 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1011', 'kclow_92', 'Low Kean Choon', 'Male', '1982-08-20', '$2y$10$Q1w2E3r4T5y6U7i8O9p0A', '+6013 4821105', 'lowkc281@hotmail.com', 'No. 15, Jalan Kenanga 3, Taman Perling', '80250', 'Johor Bahru', 'Johor', 'Standard Chartered', 490, 'Member', '2025-02-20 08:30:00', 0, NULL, '2026-03-11 21:00:00', 'mM1nN2bB3v', NULL, NULL, 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1012', 'idris_ms', 'Idris bin Mat Saman', 'Male', '1977-07-07', '$2y$10$Z1x2C3v4B5n6M7a8S9d0F', '+6012 6631985', 'idris_ms961@gmail.com', 'No. 56, Jalan Hang Tuah, Taman Melaka Raya, Melaka Tengah', '75200', 'Melaka Tengah', 'Melaka', 'Maybank2U', 276, 'Member', '2025-07-07 03:00:00', 0, NULL, '2026-03-10 13:45:00', 'bB4vV5cC6x', NULL, '/storage/profiles/idris.png', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1013', 'sarah_van', 'Sarah Vanar', 'Female', '2005-10-30', '$2y$10$P1o2I3u4Y5t6R7e8W9q0L', '+6011 28504491', 'sarah_vnr05@yahoo.com', 'No. 10-5, Lorong Tabuan, Taman Tabuan Jaya', '93000', 'Kuching', 'Sarawak', 'CIMB Clicks', 110, 'Member', '2025-10-30 01:20:00', 0, NULL, '2026-03-12 14:10:00', 'sS7dD8fF9g', NULL, NULL, 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1014', 'lim_yheng', 'Lim Yi Heng', 'Male', '1991-05-20', '$2y$10$M1n2B3v4C5x6Z7q8W9e0R', '+6016 3394852', 'yiheng.l44@gmail.com', 'No. 34, Jalan Ipoh, Taman Kaya', '51200', 'Kuala Lumpur', 'Wilayah Persekutuan Kuala Lumpur', 'Public Bank', 67, 'Member', '2026-01-20 02:45:00', 0, NULL, '2026-03-13 11:30:00', 'hH0jJ9kK8l', NULL, '/storage/profiles/yhlim.jpg', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1015', 'aziz_hash', 'Azizul bin Hashim', 'Male', '1983-03-01', '$2y$10$K1j2H3g4F5d6S7a8Q9w0E', '+6019 7241150', 'azizul_h707@hotmail.com', 'No. 5, Jalan Sultan Ismail, Taman Bukit Bintang', '50250', 'Kuala Lumpur', 'Wilayah Persekutuan Kuala Lumpur', 'HSBC', 890, 'Member', '2025-03-01 05:00:00', 0, NULL, '2026-02-28 17:50:00', 'qQ1wW2eE3r', NULL, NULL, 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1016', 'tan_shwa', 'Tan Siew Hwa', 'Female', '1976-08-15', '$2y$10$B1n2M3a4S5d6F7g8H9j0K', '+6012 9482261', 'siewhwa_t219@yahoo.com', 'No. 12A, Jalan Bukit Kecil, Taman Bukit Mertajam', '14000', 'Bukit Mertajam', 'Pulau Pinang', 'Affin Bank', 23, 'Member', '2025-08-15 08:10:00', 0, NULL, '2026-03-01 12:00:00', 'eE4rR5tT6y', NULL, NULL, 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1017', 'arun_muthu', 'Arun a/l Muthu', 'Male', '2001-12-01', '$2y$10$X1z2C3v4B5n6M7q8W9e0R', '+6017 4825593', 'arun.m522@gmail.com', 'No. 9, Jalan Gasing, Taman Petaling Utama', '46000', 'Petaling Jaya', 'Selangor', 'Bank Rakyat', 412, 'Member', '2025-12-01 02:00:00', 1, '2026-03-10 08:00:00', '2026-03-10 08:05:00', NULL, NULL, '/storage/profiles/arun.jpg', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1018', 'nurul_ain', 'Nurul Ain binti Musa', 'Female', '1998-05-25', '$2y$10$O1p2I3u4Y5t6R7e8W9q0L', '+6013 9981109', 'ainmusa819@hotmail.com', 'No. 45, Jalan Enggang, Taman Keramat, Ampang', '68000', 'Ampang', 'Selangor', 'Bank Muamalat', 95, 'Member', '2025-05-25 01:30:00', 0, NULL, '2026-03-12 20:15:00', 'yY7uU8iI9o', NULL, NULL, 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1019', 'lee_jun', 'Lee Jun Hoong', 'Male', '2004-11-05', '$2y$10$V1c2X3z4A5s6D7f8G9h0J', '+6014 3098842', 'junhoong_l33@gmail.com', 'No. 88, Jalan Perak, Taman Jaya, Georgetown', '10150', 'Georgetown', 'Pulau Pinang', 'RHB Bank', 330, 'Member', '2025-11-05 06:40:00', 0, NULL, '2026-03-13 09:10:00', 'oO0pP1aA2s', NULL, '/storage/profiles/junlee.png', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1020', 'hassan_r', 'Hassan bin Rashid', 'Male', '1985-01-20', '$2y$10$N1m2B3v4C5x6Z7q8W9e0R', '+6018 6423371', 'rashid_h12@yahoo.com', 'No. 2, Jalan Tun Razak, Taman U-Thant', '50400', 'Kuala Lumpur', 'Wilayah Persekutuan Kuala Lumpur', 'Hong Leong Bank', 21, 'Member', '2025-01-20 09:15:00', 0, NULL, '2026-03-11 11:30:00', 'sS3dD4fF5g', NULL, NULL, 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1021', 'devi_subra', 'Devi a/p Subramaniam', 'Female', '2001-03-22', '$2y$10$S1d2F3g4H5j6K7l8M9n0B', '+6011 62055418', 'devi.s904@gmail.com', 'No. B-5-2, Jalan Kiara, Taman Mont Kiara', '60000', 'Kuala Lumpur', 'Wilayah Persekutuan Kuala Lumpur', 'Maybank2U', 178, 'Member', '2025-03-22 01:00:00', 0, NULL, '2026-03-13 07:45:00', 'fF6gG7hH8j', NULL, NULL, 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1022', 'ng_wtatt', 'Ng Wei Tatt', 'Male', '1985-06-14', '$2y$10$A1s2D3f4G5h6J7k8L9q0W', '+6016 4721159', 'weitatt.ng38@hotmail.com', 'No. 23, Jalan Austin 1, Taman Mount Austin', '81100', 'Johor Bahru', 'Johor', 'Public Bank', 290, 'Member', '2025-06-14 03:20:00', 0, NULL, '2026-03-12 18:00:00', 'jJ9kK0lL1z', NULL, '/storage/profiles/ngwt.jpg', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1023', 'kamil_ibra', 'Kamil bin Ibrahim', 'Male', '2000-09-01', '$2y$10$W1e2R3t4Y5u6I7o8P9p0Z', '+6012 5941123', 'kamil_ib516@gmail.com', 'No. 17, Lorong Damai, Taman Damai', '88000', 'Kota Kinabalu', 'Sabah', 'CIMB Clicks', 54, 'Member', '2025-09-01 02:00:00', 0, NULL, '2026-03-10 09:30:00', 'xX2cC3vV4b', NULL, NULL, 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1024', 'yap_lseng', 'Yap Lian Seng', 'Male', '1979-02-10', '$2y$10$Z1x2C3v4B5n6M7a8S9d0G', '+6019 2843361', 'lseng.yap87@yahoo.com', 'No. 5, Jalan Klang Lama, Taman OUG, Klang', '41000', 'Klang', 'Selangor', 'RHB Bank', 460, 'Member', '2025-02-10 06:15:00', 0, NULL, '2026-03-11 13:00:00', 'nN5mM6qQ7w', NULL, '/storage/profiles/yapls.png', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1025', 'zul_ahmad', 'Zulkifli bin Ahmad', 'Male', '1980-11-20', '$2y$10$Q1w2E3r4T5y6U7i8O9p0X', '+6013 9054418', 'zulkifli_a99@gmail.com', 'No. 14, Jalan Meru, Taman Klang Parade', '41050', 'Klang', 'Selangor', 'Hong Leong Bank', 12, 'Member', '2025-11-20 00:30:00', 0, NULL, '2026-03-13 06:15:00', 'eE8rR9tT0y', NULL, NULL, 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1026', 'liew_myee', 'Liew Mei Yee', 'Female', '2003-07-25', '$2y$10$M1n2B3v4C5x6Z7q8W9e0P', '+6014 5923307', 'meiyee_744@hotmail.com', 'No. 9, Lorong Indah 1, Taman Canning', '30000', 'Ipoh', 'Perak', 'AmBank', 310, 'Member', '2025-07-25 07:50:00', 0, NULL, '2026-03-12 10:20:00', 'uU1iI2oO3p', NULL, '/storage/profiles/meiyee.jpg', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1027', 'priya_nair', 'Priya a/p Nair', 'Female', '1996-04-05', '$2y$10$K1j2H3g4F5d6S7a8Q9w0O', '+6017 2841159', 'nair.priya55@gmail.com', 'No. 11, Jalan Gurney, Taman Datuk Keramat', '54000', 'Kuala Lumpur', 'Wilayah Persekutuan Kuala Lumpur', 'Bank Islam', 88, 'Member', '2025-04-05 04:00:00', 0, NULL, '2026-03-13 11:00:00', 'aA4sS5dD6f', NULL, NULL, 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1028', 'syed_mokh', 'Syed Mokhtar', 'Male', '1982-12-12', '$2y$10$B1n2M3a4S5d6F7g8H9j0U', '+6018 7319940', 'syed.m95@yahoo.com', 'No. 45, Jalan Putra, Taman Titiwangsa', '50350', 'Kuala Lumpur', 'Wilayah Persekutuan Kuala Lumpur', 'Alliance Bank', 155, 'Member', '2025-12-12 01:40:00', 0, NULL, '2026-03-01 14:00:00', 'gG7hH8jJ9k', NULL, '/storage/profiles/syed.jpg', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1029', 'teh_ck_tech', 'Teh Chee Keong', 'Male', '2001-08-01', '$2y$10$X1z2C3v4B5n6M7q8W9e0I', '+6011 59421108', 'ckteh_421@gmail.com', 'No. 7, Jalan SS21/3, Taman Damansara Utama', '47400', 'Petaling Jaya', 'Selangor', 'UOB Bank', 224, 'Member', '2025-08-01 08:00:00', 0, NULL, '2026-03-12 17:30:00', 'lL0zZ1xX2c', NULL, NULL, 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1030', 'rosnah_abd', 'Rosnah binti Abdullah', 'Female', '1983-03-10', '$2y$10$O1p2I3u4Y5t6R7e8W9q0Y', '+6012 3085521', 'rosnah.a106@hotmail.com', 'No. 89, Kampung Baru, Taman Terengganu', '20000', 'Kuala Terengganu', 'Terengganu', 'OCBC Bank', 31, 'Member', '2025-03-10 03:20:00', 0, NULL, '2026-03-10 11:25:00', 'vV3bB4nN5m', NULL, NULL, 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1031', 'ganesh_r', 'Ganesh a/l Ravi', 'Male', '1990-10-15', '$2y$10$V1c2X3z4A5s6D7f8G9h0T', '+6016 7194405', 'ganesh_ravi01@gmail.com', 'No. 15-3-2, Vista Komanwel, Taman Bukit Jalil', '57000', 'Kuala Lumpur', 'Wilayah Persekutuan Kuala Lumpur', 'Standard Chartered', 390, 'Member', '2025-10-15 05:45:00', 0, NULL, '2026-03-13 12:00:00', 'qQ6wW7eE8r', NULL, '/storage/profiles/ganesh.png', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1032', 'jas_lim', 'Jasmine Lim', 'Female', '2002-05-05', '$2y$10$N1m2B3v4C5x6Z7q8W9e0L', '+6019 4721184', 'jasmine.l819@yahoo.com', 'No. 22, Jalan Bukit Mewah, Taman Mewah', '93100', 'Kuching', 'Sarawak', 'HSBC', 120, 'Member', '2025-05-05 06:00:00', 0, NULL, '2026-03-09 10:00:00', 'tT9yY0uU1i', NULL, NULL, 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1033', 'faizal_ham', 'Faizal bin Hamzah', 'Male', '1986-01-15', '$2y$10$S1d2F3g4H5j6K7l8M9n0W', '+6013 2295581', 'faizal_h66@gmail.com', 'No. 31, Jalan Molek 2/1, Taman Molek', '81100', 'Johor Bahru', 'Johor', 'Affin Bank', 440, 'Member', '2025-01-15 00:30:00', 0, NULL, '2026-03-12 15:40:00', 'oO2pP3aA4s', NULL, '/storage/profiles/faizal.jpg', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1034', 'chew_wy', 'Chew Wah Yan', 'Male', '2004-09-20', '$2y$10$A1s2D3f4G5h6J7k8L9q0R', '+6014 9182245', 'wahyan.c107@hotmail.com', 'No. 4, Lorong Sultan, Taman Jaya', '46200', 'Petaling Jaya', 'Selangor', 'Bank Rakyat', 20, 'Member', '2025-09-20 09:10:00', 0, NULL, '2026-02-15 09:00:00', 'dD5fF6gG7h', NULL, NULL, 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1035', 'farah_daud', 'Farah binti Daud', 'Female', '1998-06-18', '$2y$10$W1e2R3t4Y5u6I7o8P9p0E', '+6017 6031195', 'farah.d932@gmail.com', 'No. 102, Jalan Loke Yew, Taman Miharja', '55200', 'Kuala Lumpur', 'Wilayah Persekutuan Kuala Lumpur', 'Bank Muamalat', 350, 'Member', '2025-06-18 02:20:00', 0, NULL, '2026-03-13 08:45:00', 'hH8jJ9kK0l', NULL, NULL, 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1036', 'kumar_s', 'Kumar a/l Selvam', 'Male', '2003-04-20', '$2y$10$Z1x2C3v4B5n6M7a8S9d0T', '+6018 3954481', 'selvam_k42@yahoo.com', 'No. 8, Jalan Seremban Jaya, Taman Seremban Jaya', '70300', 'Seremban', 'Negeri Sembilan', 'Maybank2U', 66, 'Member', '2025-04-20 03:00:00', 0, NULL, '2026-03-10 14:20:00', 'zZ1xX2cC3v', NULL, '/storage/profiles/kumar.jpg', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1037', 'chan_swai', 'Chan Siew Wai', 'Female', '1987-02-28', '$2y$10$Q1w2E3r4T5y6U7i8O9p0P', '+6011 19284453', 'sw_chan005@gmail.com', 'No. 56, Jalan Pudu, Taman Maluri', '55100', 'Kuala Lumpur', 'Wilayah Persekutuan Kuala Lumpur', 'CIMB Clicks', 278, 'Member', '2025-02-28 07:30:00', 0, NULL, '2026-03-12 11:00:00', 'bB4nN5mM6q', NULL, NULL, 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1038', 'khalid_m', 'Khalid bin Mansor', 'Male', '1982-07-14', '$2y$10$M1n2B3v4C5x6Z7q8W9e0Q', '+6012 7421159', 'khalid_m771@hotmail.com', 'No. 19, Jalan Putra, Taman Putra', '05000', 'Alor Setar', 'Kedah', 'Public Bank', 134, 'Member', '2025-07-14 01:15:00', 0, NULL, '2026-03-11 16:40:00', 'wW7eE8rR9t', NULL, NULL, 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1039', 'goh_pseng', 'Goh Poh Seng', 'Male', '2000-11-05', '$2y$10$K1j2H3g4F5d6S7a8Q9w0D', '+6016 5291184', 'ps.goh883@gmail.com', 'No. 14, Jalan Universiti, Taman Bukit', '46300', 'Petaling Jaya', 'Selangor', 'RHB Bank', 410, 'Member', '2025-11-05 06:00:00', 0, NULL, '2026-03-13 10:30:00', 'yY0uU1iI2o', NULL, '/storage/profiles/gohps.png', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1040', 'anis_fauzi', 'Anis binti Fauzi', 'Female', '1993-03-25', '$2y$10$B1n2M3a4S5d6F7g8H9j0S', '+6019 3381142', 'anis_f901@yahoo.com', 'No. Lot 22, Kampung Besut, Taman Besut', '22200', 'Besut', 'Terengganu', 'Hong Leong Bank', 82, 'Member', '2025-03-25 02:10:00', 0, NULL, '2026-03-01 09:00:00', 'pP3aA4sS5d', NULL, NULL, 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1041', 'ramesh_v', 'Ramesh a/l Velu', 'Male', '2004-08-18', '$2y$10$X1z2C3v4B5n6M7q8W9e0V', '+6013 2948815', 'ramesh_velu67@gmail.com', 'No. B-9-1, Sri Putamas, Taman Sri Putamas', '51200', 'Kuala Lumpur', 'Wilayah Persekutuan Kuala Lumpur', 'AmBank', 195, 'Member', '2025-08-18 08:45:00', 0, NULL, '2026-03-13 07:15:00', 'fF6gG7hH8j', NULL, NULL, 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1042', 'sim_k_t', 'Sim Kok Thai', 'Male', '1974-12-05', '$2y$10$O1p2I3u4Y5t6R7e8W9q0F', '+6014 7192258', 'simkt_618@hotmail.com', 'No. 7, Jalan Tebrau, Taman Sentosa', '80250', 'Johor Bahru', 'Johor', 'Bank Islam', 310, 'Member', '2025-12-05 05:20:00', 0, NULL, '2026-03-12 21:00:00', 'kK9lL0zZ1x', NULL, '/storage/profiles/simkt.jpg', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1043', 'zainab_ali', 'Zainab binti Ali', 'Female', '2001-05-10', '$2y$10$V1c2X3z4A5s6D7f8G9h0L', '+6017 4821159', 'zainab_a055@gmail.com', 'No. 6, Jalan Masjid, Taman Ipoh', '30000', 'Ipoh', 'Perak', 'Alliance Bank', 145, 'Member', '2025-05-10 03:30:00', 0, NULL, '2026-03-10 11:45:00', 'xX2cC3vV4b', NULL, NULL, 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1044', 'wong_cy', 'Wong Chee Yan', 'Male', '1989-09-02', '$2y$10$N1m2B3v4C5x6Z7q8W9e0M', '+6018 2049915', 'cywong105@yahoo.com', 'No. 10, Lorong Biru, Taman Jelutong, Jelutong', '11600', 'Jelutong', 'Pulau Pinang', 'UOB Bank', 270, 'Member', '2025-09-01 01:00:00', 0, NULL, '2026-03-12 13:20:00', 'nN5mM6qQ7w', NULL, '/storage/profiles/wongcy.png', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1045', 'firdaus_r', 'Firdaus bin Ramli', 'Male', '2005-02-15', '$2y$10$S1d2F3g4H5j6K7l8M9n0G', '+6011 49281105', 'firdaus_r401@gmail.com', 'No. Lot 3, Kampung Pantai, Taman Pantai, Melaka Tengah', '75200', 'Melaka Tengah', 'Melaka', 'OCBC Bank', 39, 'Member', '2025-02-15 06:00:00', 0, NULL, '2026-03-11 15:30:00', 'eE8rR9tT0y', NULL, NULL, 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1046', 'lakshmi_p', 'Lakshmi a/p Pillay', 'Female', '1980-10-10', '$2y$10$A1s2D3f4G5h6J7k8L9q0H', '+6012 6023391', 'lakshmi_p77@hotmail.com', 'No. 25, Jalan Gurney, Taman Gurney', '54000', 'Kuala Lumpur', 'Wilayah Persekutuan Kuala Lumpur', 'Standard Chartered', 480, 'Member', '2025-10-10 02:10:00', 0, NULL, '2026-03-13 09:00:00', 'uU1iI2oO3p', NULL, NULL, 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1047', 'tiew_slian', 'Tiew Siew Lian', 'Female', '2001-06-05', '$2y$10$W1e2R3t4Y5u6I7o8P9p0J', '+6016 3318847', 'sltiew808@gmail.com', 'No. 18, Jalan SS15/4, Taman Subang Jaya', '47500', 'Subang Jaya', 'Selangor', 'HSBC', 215, 'Member', '2025-06-05 07:00:00', 0, NULL, '2026-03-12 17:00:00', 'aA4sS5dD6f', NULL, '/storage/profiles/sltiew.jpg', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1048', 'hafiz_bah', 'Hafiz bin Baharudin', 'Male', '1991-01-25', '$2y$10$Z1x2C3v4B5n6M7a8S9d0K', '+6019 4028815', 'hafiz_b09@yahoo.com', 'No. 11, Jalan Tunku, Taman Tunku', '50480', 'Kuala Lumpur', 'Wilayah Persekutuan Kuala Lumpur', 'Affin Bank', 110, 'Member', '2025-01-25 04:00:00', 0, NULL, '2026-03-11 11:00:00', 'gG7hH8jJ9k', NULL, NULL, 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1049', 'choo_whong', 'Choo Wai Hong', 'Male', '2000-11-15', '$2y$10$Q1w2E3r4T5y6U7i8O9p0L', '+6013 5291147', 'choowh_712@gmail.com', 'No. 45, Lorong Damai, Taman Damai', '88000', 'Kota Kinabalu', 'Sabah', 'Bank Rakyat', 330, 'Member', '2025-11-15 00:30:00', 0, NULL, '2026-03-13 09:45:00', 'lL0zZ1xX2c', NULL, '/storage/profiles/choowh.png', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1050', 'kavi_mani', 'Kavitha a/p Mani', 'Female', '1988-08-22', '$2y$10$M1n2B3v4C5x6Z7q8W9e0N', '+6014 6284451', 'kavitha_m51@hotmail.com', 'No. 5, Jalan Seputeh, Taman Seputeh', '58000', 'Kuala Lumpur', 'Wilayah Persekutuan Kuala Lumpur', 'Bank Muamalat', 92, 'Member', '2025-08-22 06:20:00', 0, NULL, '2026-03-12 16:10:00', 'vV3bB4nN5m', NULL, NULL, 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1051', 'anoymous', 'N/A', 'Male', '0000-00-00', 'N/A', '+6N/A', 'N/A', 'N/A', '0', '', '', '', 0, 'Member', '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '', '', '', 'Active', NULL, NULL, 0, '', 'Verified'),
('m1052', 'unknown', 'N/A', 'Male', '0000-00-00', 'N/A', '+6N/A', 'N/A', 'N/A', '0', '', '', '', 0, 'Member', '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '', '', '', 'Active', NULL, NULL, 0, '', 'Verified'),
('m1060', 'llzzz', '', '', '0000-00-00', '$2y$10$0XJfYBYwCVbJCxBWdZInlORaJ/Q7Ox9mALCH4F3dK5Xo2Rt7d0OTK', '+6', '', '', '', '', '', '', 100, 'Member', '2026-04-16 23:45:00', 0, NULL, '2026-04-17 13:45:18', NULL, NULL, 'default.jpg', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1061', 'abc', NULL, NULL, NULL, '$2y$10$MI4GcsOWdjkZUhlaeNxuV.5grMeu6UVWLP/anaCII6TkKKQIKULQ2', NULL, NULL, NULL, '', '', '', '', 100, 'Member', '2026-04-16 23:47:27', 0, NULL, NULL, NULL, NULL, 'default.jpg', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1062', 'xyz', NULL, NULL, NULL, '$2y$10$rOiii/8.BKglg.RU9trHb.56z4kupCHvcFi6RVcIx8hxOBAG1KxIy', NULL, NULL, NULL, '', '', '', '', 100, 'Member', '2026-04-16 23:49:16', 0, NULL, NULL, NULL, NULL, 'default.jpg', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('s001', 'theboss', 'llz', 'Male', NULL, '$2a$12$8D7Kmk8DxAMFTlYmNsMwbuUurMl5w2lA/dCX4C6Qef6k0LnQbIlIq', NULL, NULL, NULL, '', '', '', '', 0, 'S.Admin', '2026-04-21 16:23:23', 0, NULL, '2026-04-22 22:18:54', NULL, NULL, 'theboss.jpeg', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('t101', 'llz', 'LIM LI ZHE', 'Male', '2007-06-03', '$2y$10$k.zQMQwuNbPl6OuFKlbiGuKl/JikM61tH3qNlUAhQbPwTltJwzZJW', '+60193019930', 'limlz-wm25@student.tarc.edu.my', 'Ground Floor, Bangunan Tan Sri Khaw Kai Boh (Block A), Jalan Genting Kelang', '53300', 'Setapak', 'Wilayah Persekutuan Kuala Lumpur', 'Public Bank', 100, 'Member', '2026-03-13 07:15:00', 0, '2026-04-20 17:41:48', '2026-04-22 22:57:49', NULL, NULL, 'llz.jpg', 'Active', '924364', '2026-04-22 22:16:31', 0, 'Unverified', 'Verified'),
('t102', 'tdytest', 'TEE DE YONG', 'Male', '0000-00-00', '$2a$12$8D7Kmk8DxAMFTlYmNsMwbuUurMl5w2lA/dCX4C6Qef6k0LnQbIlIq', '+601126408141', 'teedy-wm25@student.tarc.edu.my', 'Residensi PV9, No. 19, Jalan Melati Utama, Taman Melati', '53300', 'Setapak', 'Wilayah Persekutuan Kuala Lumpur', 'CIMB Clicks', 100, 'Member', '2026-03-13 00:15:00', 0, NULL, '2026-03-13 13:15:22', 'mM4nN1bB9v', NULL, 'tdy.jpg', 'Active', NULL, NULL, 0, 'Unverified', 'Verified'),
('t103', 'wyftest', 'WONG YONG FENG', 'Male', '0000-00-00', '$2a$12$8D7Kmk8DxAMFTlYmNsMwbuUurMl5w2lA/dCX4C6Qef6k0LnQbIlIq', '+601131780587', 'wongyf-wm25@student.tarc.edu.my', 'Tingkat 1-3, Blok 1, Jalan Setia Tropika', '81200', 'Johor Bahru', 'Johor', 'Public Bank', 100, 'Member', '2026-03-13 11:15:00', 0, NULL, '2026-03-11 08:30:00', 'sS2dD9fF1g', NULL, 'wyf.jpg', 'Active', NULL, NULL, 0, 'Unverified', 'Verified'),
('t104', 'lhjtest', 'LEONG HONG JUN', 'Male', '0000-00-00', '$2a$12$8D7Kmk8DxAMFTlYmNsMwbuUurMl5w2lA/dCX4C6Qef6k0LnQbIlIq', '+60177173669', 'leonghj-wm25@student.tarc.edu.my', 'Block 13-21A PV128,Jalan Genting Kelang, Taman Danau Kota', '53100', 'Setapak', 'Wilayah Persekutuan Kuala Lumpur', 'RHB Bank', 100, 'Member', '2026-03-12 21:15:00', 0, NULL, '2026-03-13 10:05:00', 'lL7kK3jJ2h', NULL, 'default.jpg', 'Active', NULL, NULL, 0, 'Unverified', 'Verified'),
('t105', 'test', 'test', 'Male', NULL, '$2a$12$8D7Kmk8DxAMFTlYmNsMwbuUurMl5w2lA/dCX4C6Qef6k0LnQbIlIq', NULL, 'N/A', 'N/A', '', '', '', 'Hong Leong Bank', 0, 'Member', '2026-03-27 10:44:12', 0, NULL, NULL, NULL, '', 'default.jpg', NULL, NULL, NULL, 0, '', 'Verified');

-- --------------------------------------------------------

--
-- Table structure for table `vouchers`
--

CREATE TABLE `vouchers` (
  `voucher_id` varchar(20) NOT NULL,
  `code` varchar(50) NOT NULL,
  `discount_type` enum('Free Shipping','Cashback','Others','New User') NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `discount_method` enum('-','*','+','Free Ship') NOT NULL,
  `min_spend` decimal(10,2) DEFAULT 0.00,
  `usage_limit` int(11) DEFAULT NULL,
  `used_count` int(11) DEFAULT 0,
  `expiry_date` datetime NOT NULL,
  `status` enum('Active','Inactive','Expired') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vouchers`
--

INSERT INTO `vouchers` (`voucher_id`, `code`, `discount_type`, `discount_value`, `discount_method`, `min_spend`, `usage_limit`, `used_count`, `expiry_date`, `status`, `created_at`) VALUES
('CB6001', 'CB5GET', 'Cashback', 5.00, '-', 30.00, 1000, 0, '2026-12-31 23:59:59', 'Active', '2026-04-22 13:56:29'),
('CB6002', 'CB10GET', 'Cashback', 10.00, '-', 60.00, 800, 0, '2026-12-31 23:59:59', 'Active', '2026-04-22 13:56:29'),
('CB6003', 'CB20GET', 'Cashback', 20.00, '-', 120.00, 500, 0, '2026-12-31 23:59:59', 'Active', '2026-04-22 13:56:29'),
('CB6004', 'CASH88', 'Cashback', 8.80, '-', 88.00, 888, 0, '2026-12-31 23:59:59', 'Active', '2026-04-22 13:56:29'),
('CB6005', 'CASH15', 'Cashback', 15.00, '-', 100.00, 300, 0, '2026-12-31 23:59:59', 'Active', '2026-04-22 13:56:29'),
('CB6006', 'VIPCB50', 'Cashback', 50.00, '-', 300.00, 100, 0, '2026-12-31 23:59:59', 'Active', '2026-04-22 13:56:29'),
('CB6007', 'SMALLCB', 'Cashback', 2.00, '-', 15.00, 2000, 0, '2026-12-31 23:59:59', 'Active', '2026-04-22 13:56:29'),
('CB6008', 'MIDCB30', 'Cashback', 30.00, '-', 200.00, 150, 0, '2026-12-31 23:59:59', 'Active', '2026-04-22 13:56:29'),
('CB6009', 'BIGCB100', 'Cashback', 100.00, '-', 600.00, 50, 0, '2026-12-31 23:59:59', 'Active', '2026-04-22 13:56:29'),
('CB6010', 'HAPPICB', 'Cashback', 7.00, '-', 50.00, 400, 0, '2026-12-31 23:59:59', 'Active', '2026-04-22 13:56:29'),
('V001', 'CB001', 'Cashback', 5.00, '+', 10.00, 550, 1, '2026-04-14 04:38:06', 'Active', '2026-04-14 02:39:11'),
('V002', 'NEWUSER', 'New User', 10.00, '-', 10.00, 1000, 1, '2026-04-14 04:36:45', 'Active', '2026-04-14 02:37:48'),
('V26001', 'OFF2FOR20', 'Others', 2.00, '-', 20.00, 1000, 0, '2026-12-31 23:59:59', 'Active', '2026-04-22 13:55:37'),
('V26002', 'OFF5FOR50', 'Others', 5.00, '-', 50.00, 800, 0, '2026-12-31 23:59:59', 'Active', '2026-04-22 13:55:37'),
('V26003', 'OFF10FOR80', 'Others', 10.00, '-', 80.00, 500, 0, '2026-12-31 23:59:59', 'Active', '2026-04-22 13:55:37'),
('V26004', 'OFF15FOR100', 'Others', 15.00, '-', 100.00, 500, 0, '2026-12-31 23:59:59', 'Active', '2026-04-22 13:55:37'),
('V26005', 'OFF30FOR200', 'Others', 30.00, '-', 200.00, 200, 0, '2026-12-31 23:59:59', 'Active', '2026-04-22 13:55:37'),
('V26006', 'OFF50FOR300', 'Others', 50.00, '-', 300.00, 100, 0, '2026-12-31 23:59:59', 'Active', '2026-04-22 13:55:37'),
('V26007', 'BIGOFF100', 'Others', 100.00, '-', 500.00, 50, 0, '2026-12-31 23:59:59', 'Active', '2026-04-22 13:55:37'),
('V26008', 'SUPERSAVE8', 'Others', 8.00, '-', 60.00, 300, 0, '2026-12-31 23:59:59', 'Active', '2026-04-22 13:55:37'),
('V26009', 'PROMO12', 'Others', 12.00, '-', 90.00, 400, 0, '2026-12-31 23:59:59', 'Active', '2026-04-22 13:55:37'),
('V26010', 'LOYALTY25', 'Others', 25.00, '-', 150.00, 100, 0, '2026-12-31 23:59:59', 'Active', '2026-04-22 13:55:37');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `cart_id` (`user_id`),
  ADD KEY `fk_cart_product` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `favourites`
--
ALTER TABLE `favourites`
  ADD PRIMARY KEY (`favourite_id`),
  ADD KEY `favourite_id` (`favourite_id`,`user_id`,`product_id`,`category_id`),
  ADD KEY `fk_fav_user` (`user_id`),
  ADD KEY `fk_fav_product` (`product_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `fk_order_user` (`user_id`),
  ADD KEY `fk_order_voucher` (`voucher_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`reviews_id`),
  ADD KEY `reviews_id` (`reviews_id`,`product_id`,`category_id`,`user_id`),
  ADD KEY `fk_review_user` (`user_id`),
  ADD KEY `fk_review_product` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `vouchers`
--
ALTER TABLE `vouchers`
  ADD PRIMARY KEY (`voucher_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `fk_cart_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cart_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `favourites`
--
ALTER TABLE `favourites`
  ADD CONSTRAINT `fk_fav_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_fav_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_order_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_order_voucher` FOREIGN KEY (`voucher_id`) REFERENCES `vouchers` (`voucher_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `fk_review_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_review_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
