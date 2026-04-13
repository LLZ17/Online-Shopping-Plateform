-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 13, 2026 at 04:37 PM
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
  `cart_id` varchar(20) NOT NULL,
  `user_id` varchar(20) NOT NULL,
  `product_id` varchar(20) NOT NULL,
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
('1', 't104', 'MS001', '1', '2026-04-13 12:51:46');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` varchar(20) NOT NULL,
  `user_id` varchar(20) NOT NULL,
  `product_id` varchar(20) NOT NULL,
  `category_id` varchar(20) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `selected_size` varchar(20) DEFAULT NULL,
  `colour` varchar(50) DEFAULT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `bank_name` varchar(100) NOT NULL,
  `card_no` int(15) NOT NULL,
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

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` varchar(20) NOT NULL,
  `category_id` varchar(20) NOT NULL,
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
  `status` enum('In stock','Low in stock','Out of stock') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `category_id`, `name`, `keywords`, `description`, `price`, `size`, `colour`, `rating`, `stock`, `low_stock_threshold`, `sales_count`, `product_image`, `status`, `created_at`) VALUES
('MO001', 'CAT-MO-01', 'LOKAP DESIGN T-SHIRT COTTON', 'Cosplay', '100% Cotton Lokap Design Tshirt. High quality printing.', 20.00, 'S,M,L,XL,2XL', 'Orange', '5.0', 60, 5, 10, 'MO001/main.jpg', 'In stock', '2026-03-25 08:30:00'),
('MO002', 'CAT-MO-01', 'Wearable Cosplay Shark Costume Hoodie', 'Cosplay', 'Realistic shark designed wearable hoodie for events.', 42.00, 'Free Size', 'Blue', '4.5', 10, 5, 0, 'MO002/main.jpg', 'In stock', '2026-04-13 03:45:21'),
('MO003', 'CAT-MO-01', 'T-shirt St John Ambulans Malaysia', 'Cosplay', 'Official SJAM T-Shirt for medical staff or cosplay.', 25.00, 'S,M,L,XL,2XL', 'Green', '4.9', 100, 5, 3800, 'MO003/main.jpg', 'In stock', '2026-04-13 03:45:21'),
('MO004', 'CAT-MO-01', 'Malaysian Police Cadet Tee', 'Cosplay', 'Police cadet uniform t-shirt with short sleeve.', 22.00, 'S,M,L,XL,XXL', 'Blue', '4.9', 50, 5, 2000, 'MO004/main.jpg', 'In stock', '2026-04-13 04:26:14'),
('MO005', 'CAT-MO-01', 'Halloween Taoist Zombie Costume', 'Cosplay', 'Traditional Chinese Ghost costume for Halloween events.', 32.00, 'S,M,L,XL', 'Black', '4.0', 10, 5, 0, 'MO005/main.jpg', 'In stock', '2026-04-13 04:26:14'),
('MO006', 'CAT-MO-01', 'Inflatable Dinosaur Costume', 'Cosplay', 'Durable high quality inflatable dinosaur costume.', 85.00, '160-190cm', 'Brown', '4.9', 50, 5, 100, 'MO006/main.jpg', 'In stock', '2026-04-13 04:26:14'),
('MP001', 'CAT-MP-01', 'Men Summer Ice Silk Knee Length Shorts', 'Ice silk', 'Original jogging pants for men! Local delivery within 24 hours. Simple & Fashion urban menswear.', 28.64, 'M,L,XL,2XL,3XL,4XL,5XL', 'BLACK, GREY, KHAKI', '4.6', 500, 15, 2400, 'MP001/black.jpg,MP001/grey.jpg,MP001/khaki.jpg', 'In stock', '2026-03-12 06:20:00'),
('MP002', 'CAT-MP-02', 'Men Spring Trousers Light Breathable', 'Trousers light', 'Nylon material. Straight Pants version. Suitable for Youth (18-25 Years Old). Casual style.', 65.78, 'S,M,L,XL,2XL', 'BLACK, DARK GRAY, LIGHT GRAY, LIGHT GREEN, ARMY GREEN', '4.6', 300, 15, 1200, 'MP002/black.jpg,MP002/darkgrey.jpg,MP002/lightgrey.jpg,MP002/lightgreen.jpg,MP002/armygreen.jpg', 'In stock', '2026-03-25 01:15:00'),
('MP003', 'CAT-MP-03', 'Men 3/4 Quarter Casual Straight Cut Sport Shorts', 'Sport Shorts', 'Versatile & Stylish for gym or travel. Breathable & Quick-Dry airy fabric. Trendy Korean Design.', 29.80, 'L,XL,2XL,3XL,4XL', 'BLACK, BLUE, DARK GRAY', '4.8', 400, 15, 3500, 'MP003/black.jpg,MP003/blue.jpg,MP003/darkgrey.jpg', 'In stock', '2026-04-02 03:30:00'),
('MP004', 'CAT-MP-04', 'Lovito Men Casual Pocket Structure Shorts', 'Shorts Pockets', 'Highlights: Pocket, Structure Line, Lightweight and Breathable. Perfect for weekend casual.', 32.45, 'M,L,XL,2XL', 'KHAKI', '3.9', 100, 15, 800, 'MP004/khaki.jpg', 'In stock', '2026-04-05 08:10:00'),
('MP005', 'CAT-MP-05', 'Men 6-pocket Cargo Short Casual Pants', 'Cargo Short', 'Best Quality distro stitching. Thick and durable material. Perfect for daily activities.', 25.99, 'M,L,XL,2XL,3XL,4XL', 'BLACK, ARMY GREEN, NAVY BLUE, KHAKI, MUSTARD, GREY, COFFEE', '4.8', 1200, 15, 5400, 'MP005/black.jpg,MP005/armygreen.jpg,MP005/navyblue.jpg,MP005/khaki.jpg,MP005/mustard.jpg,MP005/grey.jpg,MP005/coffee.jpg', 'In stock', '2026-03-18 02:45:00'),
('MP006', 'CAT-MP-06', 'Men Casual Carrot Wide Leg Harem Pants', 'Harem Pants', 'Tropical-Friendly linen blend. Breathable and sweat-wicking. Designed for hip-hop vibes.', 49.80, 'L,XL,2XL,3XL,4XL', 'BLACK, GREEN', '4.9', 600, 15, 1200, 'MP006/black.jpg,MP006/green.jpg', 'In stock', '2026-04-10 06:20:00'),
('MP007', 'CAT-MP-07', 'Men Jogger Pants Cotton Lightweight Streetwear', 'Jogger Pants', 'Cotton lightweight summer harem trousers. Streetwear style for daily comfort.', 15.90, 'M,L,XL,2XL,3XL,4XL', 'BLACK, APRICOT, KHAKIS, GREEN', '4.9', 700, 15, 4300, 'MP007/black.jpg,MP007/apricot.jpg,MP007/khakis.jpg,MP007/green.jpg', 'In stock', '2026-03-22 09:55:00'),
('MP008', 'CAT-MP-08', 'Men Denim Short Pants Jeans', 'Jeans', 'High quality denim short pants. Classic and durable for multi occasions.', 35.00, 'M,L,XL,2XL', 'BLACK, LIGHT BLUE, MID BLUE, DARK BLUE', '4.9', 800, 15, 2100, 'MP008/black.jpg,MP008/lightblue.jpg,MP008/midblue.jpg,MP008/darkblue.jpg', 'In stock', '2026-03-29 00:30:00'),
('MP009', 'CAT-MP-08', 'Men Skinny Fit Denims Jeans Pant', 'Jeans', 'Elegant classical retro design. Comfortable denim material suitable for Malaysia weather.', 23.80, 'M,L,XL,2XL', 'BLACK, MID BLUE, DARK BLUE, LIGHT BLUE', '4.9', 5000, 15, 5000, 'MP009/black.jpg,MP009/midblue.jpg,MP009/darkblue.jpg,MP009/lightblue.jpg', 'In stock', '2026-04-12 03:15:00'),
('MP010', 'CAT-MP-09', 'Men Shorts 3/4 Below the Knee', 'Shorts', 'Bebyterry Premium material. Cool and comfortable with abstract zigzag motif.', 14.06, 'L,XL,2XL,3XL', 'ASH DARK, BLACK, NAVY, SILVER', '4.8', 10000, 15, 8500, 'MP010/ashdark.jpg,MP010/black.jpg,MP010/navy.jpg,MP010/silver.jpg', 'In stock', '2026-03-15 07:05:00'),
('MP011', 'CAT-MP-09', 'Shorts Men Summer Casual Sports Shorts', 'Shorts', '3D Print shorts. Polyester and spandex. Fast drying, moisture absorption and sweat extraction.', 43.54, 'XS,S,M,L,XL,2XL,3XL', 'DARK GREY, WHITE, BLACK', '4.4', 13000, 15, 13000, 'MP011/darkgrey.jpg,MP011/white.jpg,MP011/black.jpg', 'In stock', '2026-04-01 02:40:00'),
('MP012', 'CAT-MP-09', 'High Quality Men Cool Material Shorts', 'Shorts', 'Dry cell active moisture wicking technology. Anti-bacterial odor-resistant treatment.', 47.54, 'XS,S,M,L,XL,2XL,3XL,4XL', 'BLUE, WHITE, RED, DARK GREY', '4.3', 9000, 15, 9000, 'MP012/blue.jpg,MP012/white.jpg,MP012/red.jpg,MP012/darkgrey.jpg', 'In stock', '2026-03-08 04:25:00'),
('MP013', 'CAT-MP-09', 'Casual Capris Breathable Letter Printed Pants', 'Shorts', 'Letter printed quick drying breathable pants. Soft material and well-fitting tailoring.', 38.67, 'XS,S,M,L,XL,2XL,3XL', 'STYLE1_BLACK-GOLD, STYLE1_BLACK-WHITE, STYLE2_WHITE-BLACK, STYLE2_BLACK-WHITE', '4.3', 6767, 15, 6767, 'MP013/style1_black-gold.jpg,MP013/style1_black-white.jpg,MP013/style2_white-black.jpg,MP013/style2_black-white.jpg', 'In stock', '2026-04-18 06:00:00'),
('MP014', 'CAT-MP-09', 'New Men Beach Sports Shorts High Quality', 'Shorts', '3D Print shorts. Polyester and spandex. Fast drying and sweat extraction fabrics.', 41.61, 'XS,S,M,L,XL,2XL,3XL,4XL', 'RED, YELLOW, BLUE, GREEN', '4.4', 7878, 15, 7878, 'MP014/red.jpg,MP014/yellow.jpg,MP014/blue.jpg,MP014/green.jpg', 'In stock', '2026-03-27 02:50:00'),
('MP015', 'CAT-MP-07', 'Men Jogger Trousers Premium Lotto', 'Jogger Pants', 'PREMIUM LOTTO material with right and left pockets. Neat screen printing LOGO.', 34.65, 'M,L,XL', 'BLACK, ASH, NAVY', '4.6', 4575, 15, 4575, 'MP015/black.jpg,MP015/ash.jpg,MP015/navy.jpg', 'In stock', '2026-04-06 01:20:00'),
('MP016', 'CAT-MP-10', 'Men Chino Long Pants Drawstring Elastic', 'Long Pants', 'Waist wears drawstring elastic. Features 2 front pockets and 1 back pocket design.', 25.85, 'S,M,L,XL,2XL,3XL', 'BLACK, ASH-GRAY, CREAM, MOCCAA', '4.4', 8888, 15, 8888, 'MP016/black.jpg,MP016/ash-gray.jpg,MP016/cream.jpg,MP016/moccaa.jpg', 'In stock', '2026-03-14 08:35:00'),
('MP017', 'CAT-MP-10', 'Long Training Sport Pants For Men', 'Long Pants', 'Babytery material with full elastic waist. Distro standard neat stitching for running.', 20.35, 'S,M,L,XL', 'BLACK, ASH YOUNG, NAVY, ASH OLD', '4.6', 7777, 15, 7777, 'MP017/black.jpg,MP017/ashyoung.jpg,MP017/navy.jpg,MP017/ashold.jpg', 'In stock', '2026-03-31 03:10:00'),
('MP018', 'CAT-MP-09', 'Men Motif Shorts Jumbo Distro', 'Shorts', 'High quality jumbo distro motif shorts. Cool and comfortable for daily activity.', 13.49, 'M,L,XL,2XL', 'BLACK, NAVY, CREAM, MOCA, ASH, GREEN ARMY', '4.8', 8999, 15, 8999, 'MP018/black.jpg,MP018/navy.jpg,MP018/cream.jpg,MP018/moca.jpg,MP018/ash.jpg,MP018/armygreen.jpg', 'In stock', '2026-04-14 00:50:00'),
('MP019', 'CAT-MP-09', 'Men Short Pants With Printed Patterns', 'Shorts', '100% cotton printed short pants. Featuring HYPE and NEWYORK style patterns.', 19.99, 'S,M,L,XL,2XL,3XL,4XL,5XL', 'BLACK_NEWYORK, NAVY_NEWYORK, GREY_NEWYORK, BLACK_HYPE1, BLACK_HYPE2, BLACK_HYPE3', '4.8', 15000, 15, 15000, 'MP019/black_newyork.jpg,MP019/navy_newyork.jpg,MP019/grey_newyork.jpg,MP019/black_hype1.jpg,MP019/black_hype2.jpg,MP019/black_hype3.jpg', 'In stock', '2026-03-10 05:40:00'),
('MP020', 'CAT-MP-09', 'Men Cargo Short Pants Army Patterns', 'Shorts', 'Drill material army cargo shorts with 5 pockets. Also available in Long Chino and Tartan.', 30.69, 'M,L,XL,2XL,3XL', 'GREEN, CREAM, BLACK, PLAIN', '4.3', 13400, 15, 13400, 'MP020/green.jpg,MP020/cream.jpg,MP020/black.jpg,MP020/plain.jpg', 'In stock', '2026-04-09 07:55:00'),
('MS001', 'CAT-MS-01', 'HYPE Collegiate Pullover Hoodie', 'HYPE', 'Collegiate design pullover hoodie for streetwear.', 229.90, 'XS,S,M,L,XL,XXL', 'Black', '4.8', 8, 5, 0, 'hype_hoodie.jpg', 'In stock', '2026-03-05 01:15:00'),
('MS002', 'CAT-MS-01', 'HYPE CNY Graphic Lion Tee', 'HYPE', 'CNY Seasonal Graphic Lion Tee. Loose fit.', 149.90, 'XS,S,M,L,XL,XXL', 'White', '4.7', 50, 5, 0, 'hype_lion.jpg', 'In stock', '2026-03-12 06:30:00'),
('MS003', 'CAT-MS-01', 'HYPE CNY Graphic Chess Tee', 'HYPE', 'CNY Seasonal Graphic Chess Tee. Loose fit.', 149.90, 'XS,S,M,L,XL,XXL', 'Black', '4.6', 50, 5, 0, 'hype_chess.jpg', 'In stock', '2026-03-18 02:45:00'),
('MS004', 'CAT-MS-02', 'TNTCO Neighing Horse Tee', 'TNTCO', 'Premium quality graphic tee from TNTCO brand.', 199.00, 'XS,S,M,L,XL,XXL', 'White', '4.7', 35, 5, 0, 'tntco_horse.jpg', 'In stock', '2026-03-22 03:20:00'),
('MS005', 'CAT-MS-02', 'TNTCO Patched Logo Hoodie', 'TNTCO', 'Patched Logo Hoodie in navy blue signature style.', 289.00, 'XS,S,M,L,XL,XXL', 'Navy', '4.8', 25, 5, 0, 'tntco_hoodie.jpg', 'In stock', '2026-03-27 08:55:00'),
('MS006', 'CAT-MS-02', 'TNTCO Cobra Logo Tee', 'TNTCO', 'Cobra Logo Tee in bold red graphic.', 99.00, 'XS,S,M,L,XL,XXL', 'Red', '4.5', 60, 5, 0, 'tntco_cobra.jpg', 'In stock', '2026-03-31 05:10:00'),
('MS007', 'CAT-MS-03', 'RIC Year of Dragon Tang Shirt', 'RIC', 'RIC 2024 Year of the Dragon Chinese-style shirt.', 189.00, 'XS,S,M,L,XL,XXL', 'Red', '4.9', 60, 5, 2000, 'ric_tang_shirt.jpg', 'In stock', '2026-04-02 00:40:00'),
('MS008', 'CAT-MS-01', 'HYPE Initial D 86 Tee', 'HYPE', 'HYPE X Initial D AE86 graphic tee.', 149.90, 'XS,S,M,L,XL,XXL', 'Black', '4.9', 45, 5, 0, 'hype_86_tee.jpg', 'In stock', '2026-04-04 07:25:00'),
('MS009', 'CAT-MS-01', 'HYPE Initial D Sliding Tee', 'HYPE', 'Initial D Sliding graphic tee. Loose fit.', 149.90, 'XS,S,M,L,XL,XXL', 'White', '4.8', 45, 5, 0, 'hype_sliding_tee.jpg', 'In stock', '2026-04-06 01:50:00'),
('MS010', 'CAT-MS-01', 'HYPE Initial D Keisuke Tee', 'HYPE', 'Initial D Keisuke Takahashi graphic tee.', 149.90, 'XS,S,M,L,XL,XXL', 'Yellow', '4.8', 45, 5, 0, 'hype_initial_d_keisuke.jpg', 'In stock', '2026-04-08 03:15:00'),
('MS011', 'CAT-MS-01', 'HYPE Initial D Takumi Tee', 'HYPE', 'Initial D Takumi Fujiwara graphic tee.', 149.90, 'XS,S,M,L,XL,XXL', 'Blue', '4.9', 45, 5, 0, 'hype_initial_d_takumi.jpg', 'In stock', '2026-04-10 06:00:00'),
('MS012', 'CAT-MS-01', 'HYPE COCA-COLA Heritage Shirt', 'HYPE', 'Loose fit digital print heritage shirt.', 179.90, 'XS,S,M,L,XL,XXL', 'Multicolor', '5.0', 5, 5, 0, 'hype_cocacola_shirt.jpg', 'In stock', '2026-03-09 02:30:00'),
('MS013', 'CAT-MS-01', 'HYPE Mesh Jersey', 'HYPE', 'Oversize fit mesh fabric jersey.', 169.90, 'XS,S,M,L,XL,XXL', 'Black', '4.6', 8, 5, 0, 'hype_mesh_jersey.jpg', 'In stock', '2026-03-15 04:00:00'),
('MS014', 'CAT-MS-01', 'HYPE Seasonal Graphic Tee', 'HYPE', '82% cotton loose fit seasonal graphic tee.', 149.90, 'XS,S,M,L,XL,XXL', 'Grey', '4.7', 50, 5, 0, 'hype_seasonal_tee.jpg', 'In stock', '2026-04-01 08:20:00'),
('MS015', 'CAT-MS-04', 'Premium White Cotton T-Shirt', 'Basic Top', 'Classic white t-shirt made from 100% premium cotton.', 49.00, 'XS,S,M,L,XL,XXL', 'White', '4.5', 100, 5, 0, 'premium_tshirt.jpg', 'In stock', '2026-03-20 01:40:00'),
('MS016', 'CAT-MS-04', 'Warm Cotton Hoodie', 'Basic Top', 'Solid color warm cotton hoodie for daily wear.', 119.00, 'XS,S,M,L,XL', 'Grey', '4.4', 60, 5, 0, 'cotton_hoodie.jpg', 'In stock', '2026-04-12 02:10:00'),
('MS017', 'CAT-MS-04', 'Formal Oxford Shirt', 'Basic Top', 'Classic oxford shirt for formal or office wear.', 95.00, 'XS,S,M,L,XL,XXL', 'Light Blue', '4.6', 70, 5, 0, 'oxford_shirt.jpg', 'In stock', '2026-03-25 07:30:00'),
('MS018', 'CAT-MS-05', 'Polo Shirt Long Sleeve Plain', 'Polo', 'Standard formal plain long sleeve polo shirt.', 20.00, 'M,L,XL,2XL,3XL', 'Black,White,Blue', '4.8', 200, 5, 3000, 'MS018/main.jpg', 'In stock', '2026-04-13 03:04:21'),
('MS019', 'CAT-MS-05', 'Ralph Lauren Slim Fit Polo', 'Polo', 'Custom slim fit stretch mesh polo shirt.', 888.00, 'S,M,L,XL,2XL', 'White,Black', '5.0', 10, 5, 26, 'MS019/main.jpg', 'In stock', '2026-04-13 03:10:55'),
('MS020', 'CAT-MS-05', 'Lovito Casual Geometric Polo', 'Polo', 'Casual geometric button spring polo shirt.', 15.00, 'M,L,XL,2XL', 'Multicolor', '4.0', 50, 5, 41, 'MS020/main.jpg', 'In stock', '2026-04-13 03:16:55'),
('WP001', 'CAT-WP-01', 'High Waist Pleated Midi Skirt', 'Pleated Skirt', 'High-quality mercerized treatment with smooth finish. A-line silhouette, Korean elegant style.', 34.66, 'S,M,L,XL,2XL', 'Apricot, Navy Blue, Dark Green', '4.1', 1000, 15, 2000, 'img1.jpg,img2.jpg,img3.jpg,img4.jpg', 'In stock', '2026-03-12 02:00:00'),
('WP002', 'CAT-WP-02', 'Elegant A-Line Midi Skirt', 'Korean', 'Flattering A-Line silhouette with patchwork design. High-quality 270g thickened fabric.', 17.00, 'S,M,L,XL,XXL', 'White, Black, Cream, Pink, Purple, Brown, Gray', '4.5', 900, 15, 7400, 'img1.jpg,img2.jpg,img3.jpg,img4.jpg,img5.jpg,img6.jpg,img7.jpg', 'In stock', '2026-03-25 06:30:00'),
('WP003', 'CAT-WP-03', 'Retro Overalls High Waist Pants', 'Highwaist', 'American Retro style with functional pockets. Straight wide leg, high waist loose fit.', 18.84, 'S,M,L,XL,2XL,3XL', 'Black, Pink, Purple', '3.5', 1000, 15, 3500, 'img1.jpg,img2.jpg,img3.jpg', 'In stock', '2026-04-02 01:15:00'),
('WP004', 'CAT-WP-16', 'Korean High Waist Maxi Skirt', 'Long Skirt', 'High-quality breathable polyester. Full elastic waist with big hem umbrella silhouette.', 112.33, 'S,M,L,XL,2XL,3XL', 'Apricot, Black, Blue, Pink, White', '4.5', 999, 15, 4300, 'img1.jpg,img2.jpg,img3.jpg,img4.jpg', 'In stock', '2026-04-08 03:20:00'),
('WP005', 'CAT-WP-14', 'High Waist Wide-Leg Slimming Shorts', 'Wide Leg Pants', 'High waist and wide leg design for a slimming effect. Premium breathable suit fabric.', 43.96, 'S,M,L,2XL,3XL,4XL', 'Apricot, Black, Khaki, White', '5.0', 1000, 15, 4600, 'img1.jpg,img2.jpg,img3.jpg,img4.jpg', 'In stock', '2026-04-05 02:00:00'),
('WP006', 'CAT-WP-03', 'Korean High Waist Wide Leg Jeans', 'Highwaist', 'High-quality durable denim. Relaxed loose-fitting with a tall look silhouette.', 19.90, 'S,M,L,XL,2XL', 'Black, Deep Blue, Light Blue', '4.6', 100, 15, 4100, 'img1.jpg,img2.jpg,img3.jpg,img4.jpg', 'In stock', '2026-04-05 00:45:00'),
('WP007', 'CAT-WP-06', 'Unisex Lotto Premium Sports Shorts', 'Sports Shorts', 'Quick dry Lotto Premium material. Perfect for sports, gym, or home wear.', 14.85, 'M,L,XL,XXL', 'Blue, Red, Magenta, White, Yellow', '5.0', 1000, 15, 5900, 'img1.jpg,img2.jpg,img3.jpg,img4.jpg,img5.jpg', 'In stock', '2026-03-29 05:10:00'),
('WP008', 'CAT-WP-07', 'Korean Thick Warm Harem Jogger Pants', 'Harem', 'Super thick wool material. Harem silhouette for a relaxed and trendy Korean look.', 26.29, 'S,M,L,XL', 'Black, White Morning', '4.0', 79, 15, 2500, 'img1.jpg,img2.jpg', 'In stock', '2026-04-10 09:30:00'),
('WP009', 'CAT-WP-03', 'Korean High Waist Denim Mini Shorts', 'Highwaist', 'High-quality denim and cotton. Features elasticity for better fit and slimming effect.', 56.60, 'S,M,L,2XL,3XL,4XL', 'Black, Blue, Dark Grey, Retro Blue', '4.3', 1000, 15, 3900, 'img1.jpg,img2.jpg,img3.jpg,img4.jpg', 'In stock', '2026-03-22 04:40:00'),
('WP010', 'CAT-WP-09', 'Korean High Waist Harlan Capris', 'Capris', 'Premium wear-resistant and breathable polyester. Slimming harlan cut with loose fit.', 16.00, 'M,L,XL,2XL,3XL,4XL', 'Black, Green, Khaki', '4.7', 178, 15, 3100, 'img1.jpg,img2.jpg,img3.jpg', 'In stock', '2026-04-15 07:50:00'),
('WP011', 'CAT-WP-10', 'Wide Leg Five-Point Shorts', 'Wide Leg Shorts', '100% Premium cotton. Breathable and soft, perfect for hot summer days.', 12.00, 'S,M,L,XL,2XL,3XL', 'Black, Caramel, Green, Khaki, Red, White', '4.2', 520, 15, 2600, 'img1.jpg,img2.jpg,img3.jpg,img4.jpg,img5.jpg,img6.jpg', 'In stock', '2026-03-14 02:05:00'),
('WP012', 'CAT-WP-03', 'Korean High Waist Pleated Skort', 'Highwaist', 'Premium Vitex fabric. Anti-exposure design with built-in safety shorts.', 48.39, 'XS,S,M,L,XL,XXL', 'Black, White', '3.9', 226, 15, 7200, 'img1.jpg,img2.jpg', 'In stock', '2026-04-03 03:55:00'),
('WP013', 'CAT-WP-11', 'High Waist Faux Skirt Wide-Leg Pants', 'Skirt Pants', 'Combines the elegance of a skirt with the convenience of pants.', 33.55, 'S,M,L,XL', 'Black, Cream, Write, Pink', '3.4', 119, 15, 4500, 'img1.jpg,img2.jpg,img3.jpg,img4.jpg', 'In stock', '2026-03-31 01:20:00'),
('WP014', 'CAT-WP-14', 'Korean High Waist Wide Leg Slack Pants', 'Wide Leg Pants', 'Premium Snow and Rain fabric. Wrinkle-resistant and anti-static. Perfect for office.', 35.09, 'S,M,L,XL,2XL', 'Black, White, Brown, Green, Ice Cream', '5.0', 112, 15, 5400, 'img1.jpg,img2.jpg,img3.jpg,img4.jpg,img5.jpg', 'In stock', '2026-04-12 06:10:00'),
('WP015', 'CAT-WP-01', 'Korean High Waist Pleated Maxi Skirt', 'Pleated Skirt', 'Flowy A-line silhouette with pleated texture. Provides a slimming and tall look.', 39.49, 'S,M,L,XL', 'Apricot, Black, Pink, Wathet', '4.8', 236, 15, 4600, 'img1.jpg,img2.jpg,img3.jpg,img4.jpg', 'In stock', '2026-03-05 05:40:00'),
('WP016', 'CAT-WP-13', 'JEEP SPIRIT Waterproof Ski Pants', 'Waterproof', 'Reliable waterproof and windproof protection. Designed for winter adventures.', 71.69, 'S,M,L,XL,2XL,3XL', 'Pink, Black, Khaki, White', '4.6', 800, 15, 7000, 'img1.jpg,img2.jpg,img3.jpg,img4.jpg', 'In stock', '2026-03-24 02:15:00'),
('WP017', 'CAT-WP-14', 'Korean High Waist Wide Leg Suit Pants', 'Wide Leg Pants', 'Premium suit fabric creates a tall silhouette. Elastic waist with no-fly design.', 14.99, 'S,M,L-2XL', 'Advanced Black, Advanced Khaki, Advanced Smog Blue', '4.8', 700, 15, 5600, 'img1.jpg,img2.jpg,img3.jpg', 'In stock', '2026-04-06 08:30:00'),
('WP018', 'CAT-WP-14', 'Graffiti Street Style Baggy Pants', 'Wide Leg Pants', 'Retro street style baggy silhouette.', 14.99, 'M,L,XL,XXL', 'Black, Grey', '5.0', 170, 15, 7800, 'img1.jpg,img2.jpg', 'In stock', '2026-03-28 00:50:00'),
('WP019', 'CAT-WP-15', 'High Waist A-Line Midi Skirt', 'Midi Skirt', 'Classic Chinos material durable skirt.', 45.69, 'M,L,XL,2XL,3XL', 'Black, Wine Red', '3.5', 400, 15, 8900, 'img1.jpg,img2.jpg', 'In stock', '2026-04-11 03:15:00'),
('WP020', 'CAT-WP-16', 'Three Layer Lace Maxi Skirt', 'Long Skirt', 'Three-layer puffy lace design for modest fashion.', 35.90, '80cm, 90cm, 100cm', 'Grey, White, Black', '4.3', 800, 15, 8800, 'img1.jpg,img2.jpg,img3.jpg', 'In stock', '2026-03-10 07:45:00'),
('WS001', 'CAT-WS-01', 'Korean Women Loose Long Sleeve Blouse', 'Korean', 'Material: Polyester. Loose fit comfortable daily blouse.', 23.00, 'S,M,L,XL,XXL', 'pink,white,black,green', '4.8', 71, 5, 2000, 'WS001/main.jpg', 'In stock', '2026-03-12 02:20:00'),
('WS002', 'CAT-WS-01', 'Korean Women Slim Fit Button Top', 'Korean', 'Slim fit long sleeve top with button details.', 30.00, 'S,M,L,XL,2XL', 'White,Black,Grey', '4.8', 36, 5, 1000, 'WS002/white.jpg', 'In stock', '2026-03-25 06:15:00'),
('WS003', 'CAT-WS-01', 'VALUNEE Slim Fit Long Sleeve Shirt', 'Korean', 'Elegant slim fit shirt for office or casual wear.', 29.00, 'S,M,L,XL,2XL', 'White,Blue,Sapphire', '4.8', 189, 5, 4000, 'WS003/main.jpg', 'In stock', '2026-04-02 01:30:00'),
('WS004', 'CAT-WS-02', 'College Style Short-Sleeved Polo', 'Polo', 'Classic college style polo shirt for women.', 20.00, 'S,M,L,XL,2XL', 'blue,black,apricot', '4.8', 23, 5, 4000, 'WS004/main.jpg', 'In stock', '2026-04-05 03:00:00'),
('WS005', 'CAT-WS-01', 'Classic Women Long-sleeved Shirt', 'Korean', 'Classic long-sleeved shirt for daily wear.', 20.00, 'Free Size', 'Blue', '4.9', 50, 5, 1000, 'WS005/main.jpg', 'In stock', '2026-03-08 08:45:00'),
('WS006', 'CAT-WS-03', 'French Style White Shirt with Bow', 'French', 'High-end French style white shirt with bow design.', 30.00, 'S,M,L,XL,2XL', 'White', '4.9', 215, 5, 1000, 'WS006/main.jpg', 'In stock', '2026-04-10 00:20:00'),
('WS007', 'CAT-WS-01', 'Korean Aesthetic Round Neck T-shirt', 'Korean', 'Round neck aesthetic T-shirt for fashion lovers.', 20.00, 'S,M,L,XL,2XL', 'White,Black,Apricot', '4.9', 60, 5, 2000, 'WS007/main.jpg', 'In stock', '2026-03-15 05:10:00'),
('WS008', 'CAT-WS-04', 'American One Shoulder Red T-shirt', 'American', 'Sexy one shoulder design red t-shirt.', 20.00, 'S,M,L,XL,2XL', 'Red,White,Black', '4.7', 28, 5, 3400, 'WS008/main.jpg', 'In stock', '2026-04-01 09:50:00'),
('WS009', 'CAT-WS-05', 'Casual Round Neck Short Crop Top', 'Crop Top', 'Plain basic round neck short sleeve crop top.', 15.00, 'S,M,L,XL,2XL', 'Black,Brown,Khaki', '4.9', 70, 5, 4000, 'WS009/main.jpg', 'In stock', '2026-03-20 04:00:00'),
('WS010', 'CAT-WS-05', 'Croptop T-Shirt With Bow Tie Front', 'Crop Top', 'Cute bow tie front design crop top shirt.', 15.00, 'S,M,L,XL,2XL', 'Black,Pink,Wine Red', '4.8', 100, 5, 3000, 'WS010/main.jpg', 'In stock', '2026-04-09 07:30:00'),
('WS011', 'CAT-WS-01', 'Korean V-Neck Button Slim Fit Top', 'Korean', 'Cotton and Polyester blend slim fit V-neck top.', 15.00, 'S,M,L,XL,2XL', 'Pink,Black,White', '4.7', 150, 5, 359, 'WS011/main.jpg', 'In stock', '2026-03-18 02:05:00'),
('WS012', 'CAT-WS-01', 'Korean Waist Striped Long Sleeve', 'Korean', 'Waist striped long sleeve blouse for women.', 30.00, 'S,M,L,XL,2XL', 'Black,Blue,White', '4.8', 200, 5, 63, 'WS012/main.jpg', 'In stock', '2026-04-03 03:40:00'),
('WS013', 'CAT-WS-02', 'Casual Plain Ruched Polo Shirt', 'Polo', 'Plain polo shirt with ruched design details.', 15.00, 'S,M,L,XL,2XL', 'Black,White,Gray', '4.6', 298, 5, 378, 'WS013/main.jpg', 'In stock', '2026-03-29 01:15:00'),
('WS014', 'CAT-WS-01', 'Women Casual Oversized Retro Tee', 'Korean', 'Retro print oversized t-shirt for casual wear.', 15.00, 'S,M,L,XL,2XL', 'Black,Red,White', '4.8', 109, 5, 1000, 'WS014/main.jpg', 'In stock', '2026-04-11 06:50:00'),
('WS015', 'CAT-WS-01', 'HITTY Korean 100% Cotton Shirt', 'Korean', 'Classic oversized cotton shirt for trendy look.', 20.00, 'S,M,L,XL,2XL', 'Black,White,Brown', '4.9', 221, 5, 454, 'WS015/main.jpg', 'In stock', '2026-03-05 05:25:00'),
('WS016', 'CAT-WS-01', 'Oversized Summer Casual Loose Tee', 'Korean', 'Loose fit short sleeve t-shirt for summer.', 15.00, 'S,M,L,XL,2XL', 'White,Black,Apricot', '4.8', 200, 5, 576, 'WS016/main.jpg', 'In stock', '2026-03-22 02:10:00'),
('WS017', 'CAT-WS-01', 'Printed Loose Strapless Cotton Tee', 'Korean', 'Comfortable and breathable strapless cotton t-shirt.', 15.00, 'S,M,L,XL,2XL', 'Navy Blue,Gray,White', '4.8', 215, 5, 100, 'WS017/main.jpg', 'In stock', '2026-04-06 08:00:00'),
('WS018', 'CAT-WS-01', 'Girl Short-Sleeve Knit Summer Top', 'Korean', 'Fashion knit top for summer fashion.', 15.00, 'Free Size', 'Black,Pink,Blue', '4.9', 50, 5, 376, 'WS018/main.jpg', 'In stock', '2026-03-31 00:45:00'),
('WS019', 'CAT-WS-01', 'Fashionable Striped Short-Sleeve', 'Korean', 'Blue and brown striped short sleeve t-shirt.', 15.00, 'S,M,L,XL,2XL', 'Blue,Brown', '4.5', 100, 5, 90, 'WS019/main.jpg', 'In stock', '2026-04-12 03:20:00'),
('WS020', 'CAT-WS-01', 'Lovito x Gladish Casual T-Shirt', 'Korean', 'Special collaboration casual summer t-shirt.', 15.00, 'S,M,L,XL,2XL', 'Multicolor', '4.7', 50, 5, 120, 'WS020/main.jpg', 'In stock', '2026-04-13 10:55:00');

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
  `Postcode` varchar(20) NOT NULL,
  `City` varchar(50) NOT NULL,
  `State` varchar(50) NOT NULL,
  `bank_name` varchar(100) NOT NULL,
  `card_no` int(15) NOT NULL,
  `cvv_hashed` varchar(255) NOT NULL,
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

INSERT INTO `users` (`user_id`, `username`, `real_name`, `gender`, `birthday`, `password_hashed`, `phone_num`, `email`, `address`, `Postcode`, `City`, `State`, `bank_name`, `card_no`, `cvv_hashed`, `points`, `role`, `created_at`, `login_attempts`, `last_login_attempt`, `last_login`, `remember_token`, `reset_token`, `profile_photo`, `status`, `email_code`, `email_expiry`, `email_verified`, `email_status`, `phone_status`) VALUES
('001', 'theboss', 'limlizhe', 'Male', '2026-03-01', '$2a$12$8D7Kmk8DxAMFTlYmNsMwbuUurMl5w2lA/dCX4C6Qef6k0LnQbIlIq', '0132111222', 'pxxmallmalaysia@gmail.com', 'Level 33, Menara KPJ, Jln Tun Razak,', '50400', 'Kuala Lumpur', 'Wilayah Persekutuan Kuala Lumpur', '', 0, '0', 10000, 'S.Admin', '2026-03-01 05:14:52', 0, '2026-04-10 23:06:14', '2026-04-13 22:31:23', NULL, NULL, 'theboss.jpeg', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('a102', 'tdyadmin', 'teedeyong', 'Male', '0000-00-00', '$2a$12$8D7Kmk8DxAMFTlYmNsMwbuUurMl5w2lA/dCX4C6Qef6k0LnQbIlIq', '01126408141', 'teedeyong@gmail.com', 'PV9, Jalan Kampung Wira Jaya, Taman Melati, 53300 Kuala Lumpur, Wilayah Persekutuan Kuala Lumpur', '0', '', '', '', 0, '0', 0, 'Admin', '2026-03-13 01:15:00', 0, NULL, '2026-04-11 15:49:26', NULL, NULL, 'tdy.jpg', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('a103', 'wyfadmin', 'wongyongfeng', 'Male', '0000-00-00', '$2a$12$8D7Kmk8DxAMFTlYmNsMwbuUurMl5w2lA/dCX4C6Qef6k0LnQbIlIq', 'N/A', 'N/A', 'N/A\r\n\r\n', '', '', '', '', 0, '0', 0, 'Admin', '2026-03-13 02:15:00', 0, NULL, '2026-03-27 15:24:59', NULL, NULL, 'wyf.jpg', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('a104', 'lhjadmin', 'leonghongjun', 'Male', '0000-00-00', '$2a$12$8D7Kmk8DxAMFTlYmNsMwbuUurMl5w2lA/dCX4C6Qef6k0LnQbIlIq', 'N/A', 'N/A', 'N/A', '0', '', '', '', 0, '0', 0, 'Admin', '2026-03-13 03:15:00', 0, NULL, '2026-03-12 17:00:00', 'zZ5xX8cC2v', NULL, 'default.jpg', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('am100', 'admin', 'admin', 'Male', '0000-00-00', '$2a$12$8D7Kmk8DxAMFTlYmNsMwbuUurMl5w2lA/dCX4C6Qef6k0LnQbIlIq', 'N/A', 'N/A', 'N/A', '0', '', '', '', 0, '0', 0, 'Admin', '2026-03-13 22:15:00', 0, NULL, '2026-03-27 15:30:34', NULL, NULL, 'default.jpg', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1001', 'amir_yusuf', 'Amirul bin Yusuf', 'Male', '1988-05-12', '$2y$10$X1aY2bZ3c4D5e6F7g8H9iO', '012 4785231', 'amirul.y311@gmail.com', 'No. 12, Jalan Ampang Utama, Taman Ampang', '50450', 'Kuala Lumpur', 'Wilayah Persekutuan Kuala Lumpur', 'Maybank', 4592, 'f65ccfbfec288565c1d414275985547799fde0ed286c85a50bd0ec5faa01d1ac', 125, 'Member', '2025-05-12 02:20:00', 0, NULL, '2026-03-10 14:05:22', 'zX8v1PqL9m', NULL, '/storage/profiles/amir_y.jpg', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1002', 'meiling_w', 'Wong Mei Ling', 'Female', '2001-07-25', '$2y$10$A9b8C7d6E5f4G3h2I1j0kL', '016 2945038', 'wong.ml725@yahoo.com', 'No. 45, Jalan Hijau 4, Taman Green Lane, Jelutong', '11600', 'Jelutong', 'Pulau Pinang', 'CIMB Bank', 8103, 'c66bbe9d118f554bfdba35a609848b9ab2d9c22e6bed77be6f8a55e96c295549', 840, 'Member', '2025-08-20 07:45:10', 0, NULL, '2026-03-12 09:12:45', 'kL2mP9vR7s', NULL, 'https://ui-avatars.com/api/?name=Wong+Mei+Ling', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1003', 'shah_ali', 'Shahrizal bin Ali', 'Male', '1990-01-05', '$2y$10$QwE1rR2tT3yY4uU5iI6oOp', '011 58241190', 'shahrizal814@hotmail.com', 'No. 5, Persiaran P11, Taman P11, Presint 11', '62000', 'Putrajaya', 'Wilayah Persekutuan Putrajaya', 'Public Bank', 2274, '60f070e3393291d6f836bf0acdca6138eadc4dd1d168ccaf03ab17cf0464f81b', 310, 'Member', '2026-01-05 01:00:00', 1, '2026-03-13 08:30:10', '2026-03-13 08:32:00', NULL, NULL, '/storage/profiles/shahrizal.png', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1004', 'lee_th', 'Lee Teck Huat', 'Male', '2003-11-11', '$2y$10$Zx1Cv2Bn3M4As5Df6Gh7Jk', '019 4421187', 'leeth_942@gmail.com', 'No. 8, Jalan OKK Awang Besar, Taman Labuan', '87000', 'Labuan', 'Wilayah Persekutuan Labuan', 'RHB Bank', 6631, '766cb53c753baedac5dc782593e04694b3bae3aed057ac2ff98cc1aef6413137', 450, 'Member', '2025-11-11 14:15:30', 0, NULL, '2026-02-28 20:10:00', 'qW3eR4tY5u', 'rT9uI0pP1o', NULL, 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1005', 'rajan_k', 'Rajan a/l Kumar', 'Male', '1978-03-15', '$2y$10$Pl9Ok8Mj7Nh6Bg5Vf4Cd3X', '011 39582147', 'k.rajan618@yahoo.com', 'No. B-12-3, Jalan Seri Rejang, Taman Seri Rejang, Batu Caves', '68100', 'Batu Caves', 'Selangor', 'Hong Leong Bank', 5019, '33eb7e4ae43f9873d9c84c0f07b055946b24a71ca27daa60acbbf95b44c7c5e0', 92, 'Member', '2025-03-15 03:30:00', 3, '2026-03-13 12:45:00', '2026-01-20 10:00:00', NULL, NULL, '/storage/profiles/rajan.jpg', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1006', 'siti_bakar', 'Siti Nurhaliza binti Bakar', 'Female', '2000-06-01', '$2y$10$Ki8Ju7Hy6Gt5Fr4De3Sw2Q', '019 5824419', 'sitinur.b512@gmail.com', 'No. 102, Jalan Kebun, Taman Maznah', '40000', 'Shah Alam', 'Selangor', 'AmBank', 3387, '42f25adecf47629878e89e31b2073d1af009c9c76f4140a06313af5e5950eabc', 560, 'Member', '2025-06-01 06:20:00', 0, NULL, '2026-03-11 23:50:11', 'aA2sS3dD4f', NULL, 'https://ui-avatars.com/api/?name=Siti+Nurhaliza', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1007', 'chong_vk', 'Chong Vee Keat', 'Male', '1991-12-25', '$2y$10$Mo1Ni2Bu3Vy4Cx5Zq6Ww7E', '014 6275581', 'vkeat713@hotmail.com', 'No. 8, Jalan SS2/1, Taman Bahagia', '47300', 'Petaling Jaya', 'Selangor', 'Bank Islam', 1945, 'a2cc73deb383356e2c51d5616631e0071bdf5faba44812156af3526ebc6fba69', 45, 'Member', '2025-12-25 10:00:00', 0, NULL, '2026-03-05 07:20:15', 'zZ9xX8cC7v', NULL, '/storage/profiles/cvk.jpg', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1008', 'fati_zainal', 'Fatimah binti Zainal', 'Female', '2004-04-10', '$2y$10$Lo9Ki8Ju7Hy6Gt5Fr4De3S', '017 3194482', 'zainal.fati101@gmail.com', 'No. 21, Jalan Kuching, Taman Tasik Indah', '51200', 'Kuala Lumpur', 'Wilayah Persekutuan Kuala Lumpur', 'Alliance Bank', 7720, '0fecf9247f3ddc84db8a804fa3065c013baf6b7c2458c2ba2bf56c2e1d42ddd4', 980, 'Member', '2025-04-10 00:15:00', 0, NULL, '2026-03-12 16:30:00', 'pP1oO2iI3u', NULL, NULL, 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1009', 'izzah_p', 'Nurul Izzah', 'Female', '1996-09-18', '$2y$10$Pa1Qs2Wd3Ef4Rg5Th6Yj7U', '013 2294451', 'izzah.n402@yahoo.com', 'No. 22, Jalan P9G, Taman P9, Presint 9', '62250', 'Putrajaya', 'Wilayah Persekutuan Putrajaya', 'UOB Bank', 8842, '12f26af0dcdfae8fe4331d6a4c369edd549220cdeb119b3b1831b2a2cf77f281', 215, 'Member', '2025-09-18 04:00:00', 0, NULL, '2026-03-13 10:00:00', 'lL5kK6jJ7h', 'xY7zZ8wW9q', '/storage/profiles/izzah.jpg', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1010', 'ahmad_r', 'Ahmad Rosli', 'Male', '2002-02-14', '$2y$10$Az9Sx8Dc7Fv6Gb5Hn4Jm3K', '012 7748831', 'rosli.ahmad339@gmail.com', 'No. 15, Jalan Pohon Batu, Taman Mutiara', '87000', 'Labuan', 'Wilayah Persekutuan Labuan', 'OCBC Bank', 1056, 'fc95ce176603e9e1d1ffee39023b31dd856e00ad030526902604ed2a68a12c4b', 110, 'Member', '2026-02-14 06:00:00', 2, '2026-03-01 09:15:22', '2026-02-14 14:05:00', NULL, NULL, NULL, 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1011', 'kclow_92', 'Low Kean Choon', 'Male', '1982-08-20', '$2y$10$Q1w2E3r4T5y6U7i8O9p0A', '013 4821105', 'lowkc281@hotmail.com', 'No. 15, Jalan Kenanga 3, Taman Perling', '80250', 'Johor Bahru', 'Johor', 'Standard Chartered', 5928, '51d089cdaf0c968c94b80671489d22b6f79b1c57de80df880b008e9b37b49788', 490, 'Member', '2025-02-20 08:30:00', 0, NULL, '2026-03-11 21:00:00', 'mM1nN2bB3v', NULL, NULL, 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1012', 'idris_ms', 'Idris bin Mat Saman', 'Male', '1977-07-07', '$2y$10$Z1x2C3v4B5n6M7a8S9d0F', '012 6631985', 'idris_ms961@gmail.com', 'No. 56, Jalan Hang Tuah, Taman Melaka Raya, Melaka Tengah', '75200', 'Melaka Tengah', 'Melaka', 'Maybank', 3041, '5c3e9040008c91509e2d28e5308034b677d4e2cc0b386863d4883bdb747eba1c', 276, 'Member', '2025-07-07 03:00:00', 0, NULL, '2026-03-10 13:45:00', 'bB4vV5cC6x', NULL, '/storage/profiles/idris.png', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1013', 'sarah_van', 'Sarah Vanar', 'Female', '2005-10-30', '$2y$10$P1o2I3u4Y5t6R7e8W9q0L', '011 28504491', 'sarah_vnr05@yahoo.com', 'No. 10-5, Lorong Tabuan, Taman Tabuan Jaya', '93000', 'Kuching', 'Sarawak', 'CIMB Bank', 9412, '85daaf6f7055cd5736287faed9603d712920092c4f8fd0097ec3b650bf27530e', 110, 'Member', '2025-10-30 01:20:00', 0, NULL, '2026-03-12 14:10:00', 'sS7dD8fF9g', NULL, NULL, 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1014', 'lim_yheng', 'Lim Yi Heng', 'Male', '1991-05-20', '$2y$10$M1n2B3v4C5x6Z7q8W9e0R', '016 3394852', 'yiheng.l44@gmail.com', 'No. 34, Jalan Ipoh, Taman Kaya', '51200', 'Kuala Lumpur', 'Wilayah Persekutuan Kuala Lumpur', 'Public Bank', 4470, 'eaa1938017b2d55e02387d0837e1b56bc124ae8a17624e10e366bae6c4b9c834', 67, 'Member', '2026-01-20 02:45:00', 0, NULL, '2026-03-13 11:30:00', 'hH0jJ9kK8l', NULL, '/storage/profiles/yhlim.jpg', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1015', 'aziz_hash', 'Azizul bin Hashim', 'Male', '1983-03-01', '$2y$10$K1j2H3g4F5d6S7a8Q9w0E', '019 7241150', 'azizul_h707@hotmail.com', 'No. 5, Jalan Sultan Ismail, Taman Bukit Bintang', '50250', 'Kuala Lumpur', 'Wilayah Persekutuan Kuala Lumpur', 'HSBC Bank', 2683, '4eef24c6b8248c2271f6663f44ec0de3c2535ca396a22cf60051137d71721309', 890, 'Member', '2025-03-01 05:00:00', 0, NULL, '2026-02-28 17:50:00', 'qQ1wW2eE3r', NULL, NULL, 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1016', 'tan_shwa', 'Tan Siew Hwa', 'Female', '1976-08-15', '$2y$10$B1n2M3a4S5d6F7g8H9j0K', '012 9482261', 'siewhwa_t219@yahoo.com', 'No. 12A, Jalan Bukit Kecil, Taman Bukit Mertajam', '14000', 'Bukit Mertajam', 'Pulau Pinang', 'Affin Bank', 7154, 'd26eae87829adde551bf4b852f9da6b8c3c2db9b65b8b68870632a2db5f53e00', 23, 'Member', '2025-08-15 08:10:00', 0, NULL, '2026-03-01 12:00:00', 'eE4rR5tT6y', NULL, NULL, 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1017', 'arun_muthu', 'Arun a/l Muthu', 'Male', '2001-12-01', '$2y$10$X1z2C3v4B5n6M7q8W9e0R', '017 4825593', 'arun.m522@gmail.com', 'No. 9, Jalan Gasing, Taman Petaling Utama', '46000', 'Petaling Jaya', 'Selangor', 'Bank Rakyat', 8029, '98964da49d0a98402ed3d2d37b3350cf0aa346f522f8f1feb6b01cd680bc9455', 412, 'Member', '2025-12-01 02:00:00', 1, '2026-03-10 08:00:00', '2026-03-10 08:05:00', NULL, NULL, '/storage/profiles/arun.jpg', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1018', 'nurul_ain', 'Nurul Ain binti Musa', 'Female', '1998-05-25', '$2y$10$O1p2I3u4Y5t6R7e8W9q0L', '013 9981109', 'ainmusa819@hotmail.com', 'No. 45, Jalan Enggang, Taman Keramat, Ampang', '68000', 'Ampang', 'Selangor', 'Bank Muamalat', 1236, '0a2d643bfd24a028cd236e76575d828424ccffbfa47392bd09d8ca9dc85e2f8d', 95, 'Member', '2025-05-25 01:30:00', 0, NULL, '2026-03-12 20:15:00', 'yY7uU8iI9o', NULL, NULL, 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1019', 'lee_jun', 'Lee Jun Hoong', 'Male', '2004-11-05', '$2y$10$V1c2X3z4A5s6D7f8G9h0J', '014 3098842', 'junhoong_l33@gmail.com', 'No. 88, Jalan Perak, Taman Jaya, Georgetown', '10150', 'Georgetown', 'Pulau Pinang', 'RHB Bank', 5507, 'a4c6af0cb6f02dff01ba174e4cf11f24f73d9ed16ca7a1e3c9d831c0139faa5c', 330, 'Member', '2025-11-05 06:40:00', 0, NULL, '2026-03-13 09:10:00', 'oO0pP1aA2s', NULL, '/storage/profiles/junlee.png', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1020', 'hassan_r', 'Hassan bin Rashid', 'Male', '1985-01-20', '$2y$10$N1m2B3v4C5x6Z7q8W9e0R', '018 6423371', 'rashid_h12@yahoo.com', 'No. 2, Jalan Tun Razak, Taman U-Thant', '50400', 'Kuala Lumpur', 'Wilayah Persekutuan Kuala Lumpur', 'Hong Leong Bank', 3890, '05ada863a4cf9660fd8c68e2295f1d35b2264815f5b605003d6625bd9e0492cf', 21, 'Member', '2025-01-20 09:15:00', 0, NULL, '2026-03-11 11:30:00', 'sS3dD4fF5g', NULL, NULL, 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1021', 'devi_subra', 'Devi a/p Subramaniam', 'Female', '2001-03-22', '$2y$10$S1d2F3g4H5j6K7l8M9n0B', '011 62055418', 'devi.s904@gmail.com', 'No. B-5-2, Jalan Kiara, Taman Mont Kiara', '60000', 'Kuala Lumpur', 'Wilayah Persekutuan Kuala Lumpur', 'Maybank', 2948, '72933e3b31f0070af6478edc3becf96e1ee59917620e8c509cf0e6b360e29c02', 178, 'Member', '2025-03-22 01:00:00', 0, NULL, '2026-03-13 07:45:00', 'fF6gG7hH8j', NULL, NULL, 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1022', 'ng_wtatt', 'Ng Wei Tatt', 'Male', '1985-06-14', '$2y$10$A1s2D3f4G5h6J7k8L9q0W', '016 4721159', 'weitatt.ng38@hotmail.com', 'No. 23, Jalan Austin 1, Taman Mount Austin', '81100', 'Johor Bahru', 'Johor', 'Public Bank', 7712, '533eb9a8909f614c351b65e6b8aba1ffc2890735ce9a8a8936e17c05335dfa47', 290, 'Member', '2025-06-14 03:20:00', 0, NULL, '2026-03-12 18:00:00', 'jJ9kK0lL1z', NULL, '/storage/profiles/ngwt.jpg', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1023', 'kamil_ibra', 'Kamil bin Ibrahim', 'Male', '2000-09-01', '$2y$10$W1e2R3t4Y5u6I7o8P9p0Z', '012 5941123', 'kamil_ib516@gmail.com', 'No. 17, Lorong Damai, Taman Damai', '88000', 'Kota Kinabalu', 'Sabah', 'CIMB Bank', 1105, '8ede6b26343305e05c3c0029f4e830d4e8c2016869a9d1cd97b100b2a16dfd1c', 54, 'Member', '2025-09-01 02:00:00', 0, NULL, '2026-03-10 09:30:00', 'xX2cC3vV4b', NULL, NULL, 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1024', 'yap_lseng', 'Yap Lian Seng', 'Male', '1979-02-10', '$2y$10$Z1x2C3v4B5n6M7a8S9d0G', '019 2843361', 'lseng.yap87@yahoo.com', 'No. 5, Jalan Klang Lama, Taman OUG, Klang', '41000', 'Klang', 'Selangor', 'RHB Bank', 8836, '85daaf6f7055cd5736287faed9603d712920092c4f8fd0097ec3b650bf27530e', 460, 'Member', '2025-02-10 06:15:00', 0, NULL, '2026-03-11 13:00:00', 'nN5mM6qQ7w', NULL, '/storage/profiles/yapls.png', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1025', 'zul_ahmad', 'Zulkifli bin Ahmad', 'Male', '1980-11-20', '$2y$10$Q1w2E3r4T5y6U7i8O9p0X', '013 9054418', 'zulkifli_a99@gmail.com', 'No. 14, Jalan Meru, Taman Klang Parade', '41050', 'Klang', 'Selangor', 'Hong Leong Bank', 4421, 'ec1c7d93ba051204e4fea7e167f540c2136769c82329c53f5b7a0770bb237987', 12, 'Member', '2025-11-20 00:30:00', 0, NULL, '2026-03-13 06:15:00', 'eE8rR9tT0y', NULL, NULL, 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1026', 'liew_myee', 'Liew Mei Yee', 'Female', '2003-07-25', '$2y$10$M1n2B3v4C5x6Z7q8W9e0P', '014 5923307', 'meiyee_744@hotmail.com', 'No. 9, Lorong Indah 1, Taman Canning', '30000', 'Ipoh', 'Perak', 'AmBank', 5590, '2d1007980f49215311f7f1012e84f99b801eb5daeca04dedea3ada41cc45353b', 310, 'Member', '2025-07-25 07:50:00', 0, NULL, '2026-03-12 10:20:00', 'uU1iI2oO3p', NULL, '/storage/profiles/meiyee.jpg', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1027', 'priya_nair', 'Priya a/p Nair', 'Female', '1996-04-05', '$2y$10$K1j2H3g4F5d6S7a8Q9w0O', '017 2841159', 'nair.priya55@gmail.com', 'No. 11, Jalan Gurney, Taman Datuk Keramat', '54000', 'Kuala Lumpur', 'Wilayah Persekutuan Kuala Lumpur', 'Bank Islam', 3014, '0fbc9039145b6449a7765dcc00d3bd8377d93ac8cccda9f0292b5976e6d67c75', 88, 'Member', '2025-04-05 04:00:00', 0, NULL, '2026-03-13 11:00:00', 'aA4sS5dD6f', NULL, NULL, 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1028', 'syed_mokh', 'Syed Mokhtar', 'Male', '1982-12-12', '$2y$10$B1n2M3a4S5d6F7g8H9j0U', '018 7319940', 'syed.m95@yahoo.com', 'No. 45, Jalan Putra, Taman Titiwangsa', '50350', 'Kuala Lumpur', 'Wilayah Persekutuan Kuala Lumpur', 'Alliance Bank', 6628, 'e7866fdc6672f827c76f6124ca3eeaff44aff8b7caf4ee1469b2ab887e7e7875', 155, 'Member', '2025-12-12 01:40:00', 0, NULL, '2026-03-01 14:00:00', 'gG7hH8jJ9k', NULL, '/storage/profiles/syed.jpg', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1029', 'teh_ck_tech', 'Teh Chee Keong', 'Male', '2001-08-01', '$2y$10$X1z2C3v4B5n6M7q8W9e0I', '011 59421108', 'ckteh_421@gmail.com', 'No. 7, Jalan SS21/3, Taman Damansara Utama', '47400', 'Petaling Jaya', 'Selangor', 'UOB Bank', 1947, 'fd0f7e53c5b02b688a57ee37f3d52065cb168a7b9fd5a3abd93d37e1559fbd30', 224, 'Member', '2025-08-01 08:00:00', 0, NULL, '2026-03-12 17:30:00', 'lL0zZ1xX2c', NULL, NULL, 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1030', 'rosnah_abd', 'Rosnah binti Abdullah', 'Female', '1983-03-10', '$2y$10$O1p2I3u4Y5t6R7e8W9q0Y', '012 3085521', 'rosnah.a106@hotmail.com', 'No. 89, Kampung Baru, Taman Terengganu', '20000', 'Kuala Terengganu', 'Terengganu', 'OCBC Bank', 8032, '377adeb4cd4096adc7ca64b533938cffc6294a9b3534f883b2336a26252cda9a', 31, 'Member', '2025-03-10 03:20:00', 0, NULL, '2026-03-10 11:25:00', 'vV3bB4nN5m', NULL, NULL, 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1031', 'ganesh_r', 'Ganesh a/l Ravi', 'Male', '1990-10-15', '$2y$10$V1c2X3z4A5s6D7f8G9h0T', '016 7194405', 'ganesh_ravi01@gmail.com', 'No. 15-3-2, Vista Komanwel, Taman Bukit Jalil', '57000', 'Kuala Lumpur', 'Wilayah Persekutuan Kuala Lumpur', 'Standard Chartered', 4410, '85e36899399df701301f6741ffab57962a14326584b6f082ae0e87d90e492fd4', 390, 'Member', '2025-10-15 05:45:00', 0, NULL, '2026-03-13 12:00:00', 'qQ6wW7eE8r', NULL, '/storage/profiles/ganesh.png', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1032', 'jas_lim', 'Jasmine Lim', 'Female', '2002-05-05', '$2y$10$N1m2B3v4C5x6Z7q8W9e0L', '019 4721184', 'jasmine.l819@yahoo.com', 'No. 22, Jalan Bukit Mewah, Taman Mewah', '93100', 'Kuching', 'Sarawak', 'HSBC Bank', 2289, '1d28c120568c10e19b9d8abe8b66d0983fa3d2e11ee7751aca50f83c6f4a43aa', 120, 'Member', '2025-05-05 06:00:00', 0, NULL, '2026-03-09 10:00:00', 'tT9yY0uU1i', NULL, NULL, 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1033', 'faizal_ham', 'Faizal bin Hamzah', 'Male', '1986-01-15', '$2y$10$S1d2F3g4H5j6K7l8M9n0W', '013 2295581', 'faizal_h66@gmail.com', 'No. 31, Jalan Molek 2/1, Taman Molek', '81100', 'Johor Bahru', 'Johor', 'Affin Bank', 9934, 'aee4848a8580f31102073d34012cb3700fee3e61f9fcfd725fcfe5fa4a220ec3', 440, 'Member', '2025-01-15 00:30:00', 0, NULL, '2026-03-12 15:40:00', 'oO2pP3aA4s', NULL, '/storage/profiles/faizal.jpg', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1034', 'chew_wy', 'Chew Wah Yan', 'Male', '2004-09-20', '$2y$10$A1s2D3f4G5h6J7k8L9q0R', '014 9182245', 'wahyan.c107@hotmail.com', 'No. 4, Lorong Sultan, Taman Jaya', '46200', 'Petaling Jaya', 'Selangor', 'Bank Rakyat', 7751, 'a4ecdd704d258aa841bb3f9a1e3b0cafc59bd88810e542f8e7a0519809d78fe7', 20, 'Member', '2025-09-20 09:10:00', 0, NULL, '2026-02-15 09:00:00', 'dD5fF6gG7h', NULL, NULL, 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1035', 'farah_daud', 'Farah binti Daud', 'Female', '1998-06-18', '$2y$10$W1e2R3t4Y5u6I7o8P9p0E', '017 6031195', 'farah.d932@gmail.com', 'No. 102, Jalan Loke Yew, Taman Miharja', '55200', 'Kuala Lumpur', 'Wilayah Persekutuan Kuala Lumpur', 'Bank Muamalat', 3302, 'bc57590a33fe355e174396df60ac503f8e99763776307dcd8b9ce8dfe3c47a2a', 350, 'Member', '2025-06-18 02:20:00', 0, NULL, '2026-03-13 08:45:00', 'hH8jJ9kK0l', NULL, NULL, 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1036', 'kumar_s', 'Kumar a/l Selvam', 'Male', '2003-04-20', '$2y$10$Z1x2C3v4B5n6M7a8S9d0T', '018 3954481', 'selvam_k42@yahoo.com', 'No. 8, Jalan Seremban Jaya, Taman Seremban Jaya', '70300', 'Seremban', 'Negeri Sembilan', 'Maybank', 1167, '303c8bd55875dda240897db158acf70afe4226f300757f3518b86e6817c00022', 66, 'Member', '2025-04-20 03:00:00', 0, NULL, '2026-03-10 14:20:00', 'zZ1xX2cC3v', NULL, '/storage/profiles/kumar.jpg', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1037', 'chan_swai', 'Chan Siew Wai', 'Female', '1987-02-28', '$2y$10$Q1w2E3r4T5y6U7i8O9p0P', '011 19284453', 'sw_chan005@gmail.com', 'No. 56, Jalan Pudu, Taman Maluri', '55100', 'Kuala Lumpur', 'Wilayah Persekutuan Kuala Lumpur', 'CIMB Bank', 5542, '60f070e3393291d6f836bf0acdca6138eadc4dd1d168ccaf03ab17cf0464f81b', 278, 'Member', '2025-02-28 07:30:00', 0, NULL, '2026-03-12 11:00:00', 'bB4nN5mM6q', NULL, NULL, 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1038', 'khalid_m', 'Khalid bin Mansor', 'Male', '1982-07-14', '$2y$10$M1n2B3v4C5x6Z7q8W9e0Q', '012 7421159', 'khalid_m771@hotmail.com', 'No. 19, Jalan Putra, Taman Putra', '05000', 'Alor Setar', 'Kedah', 'Public Bank', 8820, '38b83caefa1ef26940f1d07bd4ec94c60809b0f88f2118e82ef8ec2d98938a84', 134, 'Member', '2025-07-14 01:15:00', 0, NULL, '2026-03-11 16:40:00', 'wW7eE8rR9t', NULL, NULL, 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1039', 'goh_pseng', 'Goh Poh Seng', 'Male', '2000-11-05', '$2y$10$K1j2H3g4F5d6S7a8Q9w0D', '016 5291184', 'ps.goh883@gmail.com', 'No. 14, Jalan Universiti, Taman Bukit', '46300', 'Petaling Jaya', 'Selangor', 'RHB Bank', 4473, '68c6c6e9ad314d1a5c4d647cfb6ed84265e47cbc2a05a54fb58ae74c0085ef29', 410, 'Member', '2025-11-05 06:00:00', 0, NULL, '2026-03-13 10:30:00', 'yY0uU1iI2o', NULL, '/storage/profiles/gohps.png', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1040', 'anis_fauzi', 'Anis binti Fauzi', 'Female', '1993-03-25', '$2y$10$B1n2M3a4S5d6F7g8H9j0S', '019 3381142', 'anis_f901@yahoo.com', 'No. Lot 22, Kampung Besut, Taman Besut', '22200', 'Besut', 'Terengganu', 'Hong Leong Bank', 2219, '28dae7c8bde2f3ca608f86d0e16a214dee74c74bee011cdfdd46bc04b655bc14', 82, 'Member', '2025-03-25 02:10:00', 0, NULL, '2026-03-01 09:00:00', 'pP3aA4sS5d', NULL, NULL, 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1041', 'ramesh_v', 'Ramesh a/l Velu', 'Male', '2004-08-18', '$2y$10$X1z2C3v4B5n6M7q8W9e0V', '013 2948815', 'ramesh_velu67@gmail.com', 'No. B-9-1, Sri Putamas, Taman Sri Putamas', '51200', 'Kuala Lumpur', 'Wilayah Persekutuan Kuala Lumpur', 'AmBank', 6684, '7f3ee9fe1fbf452b0c242614e1cad18b6dce034e5bc80763dff6332e872ad2ff', 195, 'Member', '2025-08-18 08:45:00', 0, NULL, '2026-03-13 07:15:00', 'fF6gG7hH8j', NULL, NULL, 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1042', 'sim_k_t', 'Sim Kok Thai', 'Male', '1974-12-05', '$2y$10$O1p2I3u4Y5t6R7e8W9q0F', '014 7192258', 'simkt_618@hotmail.com', 'No. 7, Jalan Tebrau, Taman Sentosa', '80250', 'Johor Bahru', 'Johor', 'Bank Islam', 3315, '5092c37bcbc9f0fb33cb0f9cab7aa5ae94ed0f1219773c380b143b7c1224d01b', 310, 'Member', '2025-12-05 05:20:00', 0, NULL, '2026-03-12 21:00:00', 'kK9lL0zZ1x', NULL, '/storage/profiles/simkt.jpg', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1043', 'zainab_ali', 'Zainab binti Ali', 'Female', '2001-05-10', '$2y$10$V1c2X3z4A5s6D7f8G9h0L', '017 4821159', 'zainab_a055@gmail.com', 'No. 6, Jalan Masjid, Taman Ipoh', '30000', 'Ipoh', 'Perak', 'Alliance Bank', 9908, '9b6c13f0d182b253c607005217881bbca28a5b04076842f6bc65580c3801a0b0', 145, 'Member', '2025-05-10 03:30:00', 0, NULL, '2026-03-10 11:45:00', 'xX2cC3vV4b', NULL, NULL, 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1044', 'wong_cy', 'Wong Chee Yan', 'Male', '1989-09-02', '$2y$10$N1m2B3v4C5x6Z7q8W9e0M', '018 2049915', 'cywong105@yahoo.com', 'No. 10, Lorong Biru, Taman Jelutong, Jelutong', '11600', 'Jelutong', 'Pulau Pinang', 'UOB Bank', 7741, 'f89f8d0e735a91c5269ab08d72fa27670d000e7561698d6e664e7b603f5c4e40', 270, 'Member', '2025-09-01 01:00:00', 0, NULL, '2026-03-12 13:20:00', 'nN5mM6qQ7w', NULL, '/storage/profiles/wongcy.png', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1045', 'firdaus_r', 'Firdaus bin Ramli', 'Male', '2005-02-15', '$2y$10$S1d2F3g4H5j6K7l8M9n0G', '011 49281105', 'firdaus_r401@gmail.com', 'No. Lot 3, Kampung Pantai, Taman Pantai, Melaka Tengah', '75200', 'Melaka Tengah', 'Melaka', 'OCBC Bank', 1123, '4779bc407343d916c5a4f6a996174046419bdd87ce6c609054dc8788b3dfc233', 39, 'Member', '2025-02-15 06:00:00', 0, NULL, '2026-03-11 15:30:00', 'eE8rR9tT0y', NULL, NULL, 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1046', 'lakshmi_p', 'Lakshmi a/p Pillay', 'Female', '1980-10-10', '$2y$10$A1s2D3f4G5h6J7k8L9q0H', '012 6023391', 'lakshmi_p77@hotmail.com', 'No. 25, Jalan Gurney, Taman Gurney', '54000', 'Kuala Lumpur', 'Wilayah Persekutuan Kuala Lumpur', 'Standard Chartered', 5562, '114bd151f8fb0c58642d2170da4ae7d7c57977260ac2cc8905306cab6b2acabc', 480, 'Member', '2025-10-10 02:10:00', 0, NULL, '2026-03-13 09:00:00', 'uU1iI2oO3p', NULL, NULL, 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1047', 'tiew_slian', 'Tiew Siew Lian', 'Female', '2001-06-05', '$2y$10$W1e2R3t4Y5u6I7o8P9p0J', '016 3318847', 'sltiew808@gmail.com', 'No. 18, Jalan SS15/4, Taman Subang Jaya', '47500', 'Subang Jaya', 'Selangor', 'HSBC Bank', 8890, '67eab6db6703cdf9acf656bbb09640fcde2ff197786adbd9ae9c14936fc8d159', 215, 'Member', '2025-06-05 07:00:00', 0, NULL, '2026-03-12 17:00:00', 'aA4sS5dD6f', NULL, '/storage/profiles/sltiew.jpg', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1048', 'hafiz_bah', 'Hafiz bin Baharudin', 'Male', '1991-01-25', '$2y$10$Z1x2C3v4B5n6M7a8S9d0K', '019 4028815', 'hafiz_b09@yahoo.com', 'No. 11, Jalan Tunku, Taman Tunku', '50480', 'Kuala Lumpur', 'Wilayah Persekutuan Kuala Lumpur', 'Affin Bank', 4437, '3055e0d8130c7a197bc6e020afe9bea1edef31f33b720cc326dd404d8e3f82d5', 110, 'Member', '2025-01-25 04:00:00', 0, NULL, '2026-03-11 11:00:00', 'gG7hH8jJ9k', NULL, NULL, 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1049', 'choo_whong', 'Choo Wai Hong', 'Male', '2000-11-15', '$2y$10$Q1w2E3r4T5y6U7i8O9p0L', '013 5291147', 'choowh_712@gmail.com', 'No. 45, Lorong Damai, Taman Damai', '88000', 'Kota Kinabalu', 'Sabah', 'Bank Rakyat', 2210, '6566230e3a3ce3774c1bbc7c18b590ae0f457bbcd511e90e3e7dca2a02e7addc', 330, 'Member', '2025-11-15 00:30:00', 0, NULL, '2026-03-13 09:45:00', 'lL0zZ1xX2c', NULL, '/storage/profiles/choowh.png', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1050', 'kavi_mani', 'Kavitha a/p Mani', 'Female', '1988-08-22', '$2y$10$M1n2B3v4C5x6Z7q8W9e0N', '014 6284451', 'kavitha_m51@hotmail.com', 'No. 5, Jalan Seputeh, Taman Seputeh', '58000', 'Kuala Lumpur', 'Wilayah Persekutuan Kuala Lumpur', 'Bank Muamalat', 6654, 'b1585fdb272b31401eaac5dd46a936c1c09b4861e53e23f12ac72fc077b3c82c', 92, 'Member', '2025-08-22 06:20:00', 0, NULL, '2026-03-12 16:10:00', 'vV3bB4nN5m', NULL, NULL, 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
('m1051', 'anoymous', 'N/A', 'Male', '0000-00-00', 'N/A', 'N/A', 'N/A', 'N/A', '0', '', '', '', 0, '0', 0, 'Member', '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '', '', '', 'Active', NULL, NULL, 0, '', 'Verified'),
('m1052', 'unknown', 'N/A', 'Male', '0000-00-00', 'N/A', 'N/A', 'N/A', 'N/A', '0', '', '', '', 0, '0', 0, 'Member', '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '', '', '', 'Active', NULL, NULL, 0, '', 'Verified'),
('t101', 'llz', 'LIM LI ZHE', 'Male', '2007-06-03', '$2a$12$8D7Kmk8DxAMFTlYmNsMwbuUurMl5w2lA/dCX4C6Qef6k0LnQbIlIq', '0193019930', 'limlz-wm25@student.tarc.edu.my', 'Ground Floor, Bangunan Tan Sri Khaw Kai Boh (Block A), Jalan Genting Kelang', '53300', 'Setapak', 'Wilayah Persekutuan Kuala Lumpur', 'Public Bank', 8888, '5627b4a8f9efbd8fbdadaf4177824186f8c734f320935c88e926bc027af6c50f', 100, 'Member', '2026-03-13 07:15:00', 0, NULL, '2026-04-13 22:12:54', NULL, NULL, 'llz.jpg', 'Active', '000795', '2026-04-12 17:48:01', 0, 'Unverified', 'Verified'),
('t102', 'tdytest', 'TEE DE YONG', 'Male', '0000-00-00', '$2a$12$8D7Kmk8DxAMFTlYmNsMwbuUurMl5w2lA/dCX4C6Qef6k0LnQbIlIq', '01126408141', 'teedy-wm25@student.tarc.edu.my', 'Residensi PV9, No. 19, Jalan Melati Utama, Taman Melati', '53300', 'Setapak', 'Wilayah Persekutuan Kuala Lumpur', 'CIMB Bank', 5593, '5d85be4cc5af40a7cf2c4f0818d92689c185fdea6566745ef26305d80413f483', 100, 'Member', '2026-03-13 00:15:00', 0, NULL, '2026-03-13 13:15:22', 'mM4nN1bB9v', NULL, 'tdy.jpg', 'Active', NULL, NULL, 0, 'Unverified', 'Verified'),
('t103', 'wyftest', 'WONG YONG FENG', 'Male', '0000-00-00', '$2a$12$8D7Kmk8DxAMFTlYmNsMwbuUurMl5w2lA/dCX4C6Qef6k0LnQbIlIq', '01131780587', 'wongyf-wm25@student.tarc.edu.my', 'Tingkat 1-3, Blok 1, Jalan Setia Tropika', '81200', 'Johor Bahru', 'Johor', 'Public Bank', 9934, 'c75de23d89df36ba921287616ee8edb4c986e328a78e033e57c1e5e2b59c838e', 100, 'Member', '2026-03-13 11:15:00', 0, NULL, '2026-03-11 08:30:00', 'sS2dD9fF1g', NULL, 'wyf.jpg', 'Active', NULL, NULL, 0, 'Unverified', 'Verified'),
('t104', 'lhjtest', 'LEONG HONG JUN', 'Male', '0000-00-00', '$2a$12$8D7Kmk8DxAMFTlYmNsMwbuUurMl5w2lA/dCX4C6Qef6k0LnQbIlIq', '0177173669', 'leonghj-wm25@student.tarc.edu.my', 'Block 13-21A PV128,Jalan Genting Kelang, Taman Danau Kota', '53100', 'Setapak', 'Wilayah Persekutuan Kuala Lumpur', 'RHB Bank', 2270, '97468f679ad305fa4dbbf17fd4bf18c41fb655f2d86162b1d91ad4f1e09814c1', 100, 'Member', '2026-03-12 21:15:00', 0, NULL, '2026-03-13 10:05:00', 'lL7kK3jJ2h', NULL, 'default.jpg', 'Active', NULL, NULL, 0, 'Unverified', 'Verified'),
('t105', 'test', 'test', 'Male', NULL, '$2a$12$8D7Kmk8DxAMFTlYmNsMwbuUurMl5w2lA/dCX4C6Qef6k0LnQbIlIq', NULL, 'N/A', 'N/A', '', '', '', 'Hong Leong Bank', 6641, 'fc95ce176603e9e1d1ffee39023b31dd856e00ad030526902604ed2a68a12c4b', 0, 'Member', '2026-03-27 10:44:12', 0, NULL, NULL, NULL, '', 'default.jpg', NULL, NULL, NULL, 0, '', 'Verified');

-- --------------------------------------------------------

--
-- Table structure for table `vouchers`
--

CREATE TABLE `vouchers` (
  `voucher_id` varchar(20) NOT NULL,
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
  ADD PRIMARY KEY (`favourite_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`reviews_id`);

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
