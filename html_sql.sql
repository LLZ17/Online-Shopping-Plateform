-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 12, 2026 at 06:30 PM
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
  `category_type` enum('Men','Women') DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
('', '', '', NULL, NULL, 0.00, NULL, NULL, '', 0, 5, 0, NULL, NULL, '2026-04-12 10:55:49'),
('MS001', '1', 'HYPE Signature Collegiate Pullover Hoodie', NULL, 'Signature collegiate design pullover hoodie. Comfortable fit with embroidered logo.', 229.90, 'XS,S,M,L,XL,XXL', NULL, '', 8, 5, 0, 'hype_hoodie.jpg', 'In stock', '2026-03-05 01:15:00'),
('MS002', '1', 'HYPE CNY Seasonal Graphic Lion Tee', NULL, 'CNY Seasonal Graphic Lion Tee. Loose fit cutting. 82% cotton, 18% polyester.', 149.90, 'XS,S,M,L,XL,XXL', NULL, '', 50, 5, 0, 'hype_lion.jpg', 'In stock', '2026-03-12 06:30:00'),
('MS003', '1', 'HYPE CNY Seasonal Graphic Chess Tee', NULL, 'CNY Seasonal Graphic Chess Tee. Loose fit cutting with unique chess pattern.', 149.90, 'XS,S,M,L,XL,XXL', NULL, '', 50, 5, 0, 'hype_chess.jpg', 'In stock', '2026-03-18 02:45:00'),
('MS004', '1', 'TNTCO Neighing Horse Tee', NULL, 'Neighing Horse graphic tee from TNTCO. Premium quality cotton.', 199.00, 'XS,S,M,L,XL,XXL', NULL, '', 35, 5, 0, 'tntco_horse.jpg', 'In stock', '2026-03-22 03:20:00'),
('MS005', '1', 'TNTCO Patched Logo Hoodie', NULL, 'Patched Logo Hoodie in navy. Features signature TNTCO patched logo.', 289.00, 'XS,S,M,L,XL,XXL', NULL, '', 25, 5, 0, 'tntco_hoodie.jpg', 'In stock', '2026-03-27 08:55:00'),
('MS006', '1', 'TNTCO Cobra Logo Tee', NULL, 'Cobra Logo Tee in red. Bold cobra graphic design.', 99.00, 'XS,S,M,L,XL,XXL', NULL, '', 60, 5, 0, 'tntco_cobra.jpg', 'In stock', '2026-03-31 05:10:00'),
('MS007', '1', 'RIC Year of Dragon Tang Shirt', NULL, 'RIC 2024 Year of the Dragon Tang Shirt. Chinese-style print with traditional knot button.', 189.00, 'XS,S,M,L,XL,XXL', NULL, '', 30, 5, 0, 'ric_tang_shirt.jpg', 'In stock', '2026-04-02 00:40:00'),
('MS008', '1', 'HYPE Initial D 86 Tee', NULL, 'Initial D 86 graphic tee. Features the iconic AE86 design.', 149.90, 'XS,S,M,L,XL,XXL', NULL, '', 45, 5, 0, 'hype_86_tee.jpg', 'In stock', '2026-04-04 07:25:00'),
('MS009', '1', 'HYPE Initial D Sliding Tee', NULL, 'Initial D Sliding graphic tee. Loose fit with high quality print.', 149.90, 'XS,S,M,L,XL,XXL', NULL, '', 45, 5, 0, 'hype_sliding_tee.jpg', 'In stock', '2026-04-06 01:50:00'),
('MS010', '1', 'HYPE Initial D Keisuke Tee', NULL, 'Initial D Keisuke Takahashi graphic tee.', 149.90, 'XS,S,M,L,XL,XXL', NULL, '', 45, 5, 0, 'hype_initial_d_keisuke.jpg', 'In stock', '2026-04-08 03:15:00'),
('MS011', '1', 'HYPE Initial D Takumi Tee', NULL, 'Initial D Takumi Fujiwara graphic tee. The legendary driver.', 149.90, 'XS,S,M,L,XL,XXL', NULL, '', 45, 5, 0, 'hype_initial_d_takumi.jpg', 'In stock', '2026-04-10 06:00:00'),
('MS012', '1', 'HYPE COCA-COLA Heritage Open Collar Shirt', NULL, 'Loose fit cutting. 97% polyester, 3% spandex. Digital graphic prints with embroidery.', 179.90, 'XS,S,M,L,XL,XXL', NULL, '', 5, 5, 0, 'hype_cocacola_shirt.jpg', 'In stock', '2026-03-09 02:30:00'),
('MS013', '1', 'HYPE Mesh Jersey', NULL, 'Oversize fit cutting. Mesh fabric with pristine embroidery.', 169.90, 'XS,S,M,L,XL,XXL', NULL, '', 8, 5, 0, 'hype_mesh_jersey.jpg', 'In stock', '2026-03-15 04:00:00'),
('MS014', '1', 'HYPE Seasonal Graphic Tee', NULL, 'Loose fit cutting. 82% cotton, 18% polyester. Interlock 250gsm with printing.', 149.90, 'XS,S,M,L,XL,XXL', NULL, '', 50, 5, 0, 'hype_seasonal_tee.jpg', 'In stock', '2026-04-01 08:20:00'),
('MS015', '1', 'Premium White T-Shirt', NULL, 'Classic white t-shirt made from 100% cotton.', 49.00, 'XS,S,M,L,XL,XXL', NULL, '', 100, 5, 0, 'premium_tshirt.jpg', 'In stock', '2026-03-20 01:40:00'),
('MS016', '1', 'Cotton Hoodie', NULL, 'Warm cotton hoodie for casual wear.', 119.00, 'XS,S,M,L,XL', NULL, '', 60, 5, 0, 'cotton_hoodie.jpg', 'In stock', '2026-04-12 02:10:00'),
('MS017', '1', 'Oxford Shirt', NULL, 'Formal oxford shirt for office wear.', 95.00, 'XS,S,M,L,XL,XXL', NULL, '', 70, 5, 0, 'oxford_shirt.jpg', 'In stock', '2026-03-25 07:30:00'),
('MS018', '', 'LOKAP DESIGN T-SHIRT COTTON TSHIRT', 'Cosplay', 'Lokap Design Tshirt \r\n\r\n100%Cotton\r\n\r\nReady Stock in Malaysia', 20.00, 'S,M,L,XL,2XL', 'Orange', '5.0', 59, 0, 0, NULL, 'In stock', '2026-03-25 08:30:00'),
('WP001', '2', 'High Waist Pleated Midi Skirt', 'pleated skirt', 'Mercerized fabric, smooth finish with A-line silhouette. Korean Style, Elegant and Minimalist.', 34.66, 'S,M,L,XL,2XL', 'Apricot, Navy Blue, Dark Blue', '4.1', 1000, 15, 2000, 'img1.jpg', 'In stock', '2026-03-12 02:00:00'),
('WP002', '2', 'Elegant A-Line Midi Skirt', 'Korean Style', 'Mercerized fabric with medium elasticity. Flattering silhouette with high waist design.', 17.00, 'S,M,L,XL,XXL', 'White, Black, Cream, Pink, Purple, Brown, Gray', '4.5', 900, 15, 7400, 'img1.jpg', 'In stock', '2026-03-25 06:30:00'),
('WP003', '2', 'Retro Overalls Pants', 'highwaist', 'American Retro style with functional pockets. Straight wide leg and high waist design.', 18.84, 'S,M,L,XL,2XL,3XL', 'Black, Pink, Purple', '3.5', 1000, 15, 3500, 'img1.jpg', 'In stock', '2026-04-02 01:15:00'),
('WP004', '2', 'Korean High Waist Maxi Skirt', 'skirt labuh', 'Breathable polyester fabric with full elastic waist. Elegant big hem umbrella silhouette.', 112.33, 'S,M,L,XL,2XL,3XL', 'Apricot, Black, Blue, Pink, White', '4.5', 999, 15, 4300, 'img1.jpg', 'In stock', '2026-04-08 03:20:00'),
('WP005', '2', 'High Waist Wide-Leg Shorts', 'wideleg', 'Slimming effect with practical side pockets. Premium breathable polyester suit fabric.', 43.96, 'S,M,L,XL,2XL,3XL,4XL', 'Apricot, Black, Khaki, White', '5.0', 1000, 15, 4600, 'img1.jpg', 'In stock', '2026-03-18 08:00:00'),
('WP006', '2', 'Korean High Waist Wide Leg Jeans', 'highwaist', 'Durable denim fabric providing a tall look. Relaxed loose-fitting with full length inseam.', 19.90, 'S,M,L,XL,2XL', 'Light Blue, Deep Blue, Black, White', '4.6', 100, 15, 4100, 'img1.jpg', 'In stock', '2026-04-05 00:45:00'),
('WP007', '2', 'Unisex Lotto Premium Sports Shorts', 'sportsshorts', 'Quick dry Lotto Premium material. Soft, durable and perfect for gym or running.', 14.85, 'M,L,XL,XXL', 'Blue, Red, Magenta, White, Yellow', '5.0', 1000, 15, 5900, 'img1.jpg', 'In stock', '2026-03-29 05:10:00'),
('WP008', '2', 'Korean Thick Warm Jogger Pants', 'harem', 'Super thick wool material. Harem silhouette for a relaxed and trendy look. High waist.', 26.29, 'S,M,L,XL', 'Black, White Morning', '4.0', 79, 15, 2500, 'img1.jpg', 'In stock', '2026-04-10 09:30:00'),
('WP009', '2', 'Korean High Waist Denim Shorts', 'denimshorts', 'Elastic high waist for a slimming effect. High-quality denim and cotton material.', 56.60, 'S,M,L,XL,2XL,3XL,4XL', 'Black, Light Blue, Dark Grey, Retro Blue', '4.3', 1000, 15, 3900, 'img1.jpg', 'In stock', '2026-03-22 04:40:00'),
('WP010', '2', 'Korean High Waist Harlan Capris', 'capris', 'Premium polyester fabric. Wear-resistant and breathable loose wide leg style.', 16.00, 'M,L,XL,2XL,3XL,4XL', 'Black, Khaki, Green', '4.7', 178, 15, 3100, 'img1.jpg', 'In stock', '2026-04-15 07:50:00'),
('WP011', '2', 'Wide Leg Five-Point Shorts', 'widelegshorts', '100% Premium cotton. Breathable and skin-friendly. Features large functional pockets.', 12.00, 'S,M,L,XL,2XL,3XL', 'Black, Caramel, Green, Khaki, White, Red', '4.2', 52, 15, 2600, 'img1.jpg', 'In stock', '2026-03-14 02:05:00'),
('WP012', '2', 'Korean High Waist Pleated Skort', 'highwaist', 'Built-in safety shorts with Vitex fabric. Wrinkle-resistant and maintains shape.', 48.39, 'XS,S,M,L,XL,2XL', 'Black, White Stone', '3.9', 226, 15, 2900, 'img1.jpg', 'In stock', '2026-04-03 03:55:00'),
('WP013', '2', 'High Waist Faux Skirt Wide-Leg Pants', 'skirtpants', 'Combines elegance of a skirt with convenience of pants. Cool stretch breathable fabric.', 33.55, 'S,M,L,XL', 'Beige, Black, Gray, Pink', '3.4', 119, 15, 4500, 'img1.jpg', 'In stock', '2026-03-31 01:20:00'),
('WP014', '2', 'High Waist Wide Leg Slack Pants', 'readystock', 'Premium Snow and Rain fabric. Wrinkle-resistant and anti-static. Perfect for office.', 35.09, 'S,M,L,XL,2XL', 'Black, White, Brown, Ice Cream, Green', '5.0', 112, 15, 5400, 'img1.jpg', 'In stock', '2026-04-12 06:10:00'),
('WP015', '2', 'Korean High Waist Pleated Maxi Skirt', 'pleatedskirt', 'Flowy A-line silhouette with pleated texture. Provides a slimming and tall look.', 39.49, 'S,M,L,XL', 'Light Blue, Pink, Apricot, Black', '4.8', 236, 15, 4600, 'img1.jpg', 'In stock', '2026-03-05 05:40:00'),
('WP016', '2', 'JEEP SPIRIT Waterproof Ski Pants', 'waterproof', 'Brand JEEP SPIRIT. Reliable waterproof and windproof protection for winter adventures.', 71.69, 'S,M,L,XL,2XL,3XL', 'Khaki, Pink, White, Black', '4.6', 800, 15, 7000, 'img1.jpg', 'In stock', '2026-03-24 02:15:00'),
('WP017', '2', 'Korean High Waist Wide Leg Suit Pants', 'widelegpants', 'Straight wide leg creates a tall silhouette. Premium knitting suit fabric. Elastic waist.', 14.99, 'S,M,L-2XL', 'Advanced Black, Khaki, Smog Blue', '4.8', 700, 15, 5600, 'img1.jpg', 'In stock', '2026-04-06 08:30:00'),
('WP018', '2', 'GDTIMA American Graffiti Baggy Pants', 'readystock', 'Retro street style baggy silhouette. Lightweight and breathable for summer wear.', 14.99, 'M,L,XL,XXL', 'Black, Grey', '5.0', 170, 15, 7800, 'img1.jpg', 'In stock', '2026-03-28 00:50:00'),
('WP019', '2', 'High Waist A-Line Midi Skirt', 'midiskirt', 'Classic Chinos material. Thick, durable and non-see-through. Functional side pockets.', 45.69, 'M,L,XL,2XL,3XL', 'Black, Wine Red', '3.5', 400, 15, 8900, 'img1.jpg', 'In stock', '2026-04-11 03:15:00'),
('WP020', '2', 'Korean Three Layer Lace Maxi Skirt', 'skirtpanjang', 'Three-layer puffy lace design. Large skirt kembang for an elegant flowing look.', 35.90, '80cm, 90cm, 100cm', 'Grey, White, Black, Apricot, Pink, Green, Coffee', '4.3', 800, 15, 8800, 'img1.jpg', 'In stock', '2026-03-10 07:45:00'),
('WS001', '1', 'Korean Women Loose Long Sleeve Shirt Blouse', 'Korean', 'Loose long sleeve shirt blouse for women.', 23.00, 'S,M,L,XL,XXL', 'pink,white,black,light green', '4.8', 7, 5, 12, NULL, 'In stock', '2026-03-12 02:20:00'),
('WS002', '1', 'Korean Women Slim Fit Button Long Sleeve Top', 'Korean', 'Solid color slim fit fashionable button design.', 30.00, 'S,M,L,XL,2XL', 'White,Black,Grey', '4.8', 36, 5, 1000, NULL, 'In stock', '2026-03-25 06:15:00'),
('WS003', '1', 'VALUNEE Slim Fit Long Sleeve Shirt', 'Korean', 'Elegant casual office blouse, Muslimah friendly.', 29.00, 'S,M,L,XL,2XL', 'White,Blue,Sapphire', '4.8', 18, 5, 4000, NULL, 'In stock', '2026-04-02 01:30:00'),
('WS004', '1', 'Polo Shirt College Style Short-Sleeved T-shirt', 'Polo', 'Color matching short-sleeved T-shirt women loose.', 20.00, 'S,M,L,XL,2XL', 'blue,black,apricot', '4.8', 23, 5, 4000, NULL, 'In stock', '2026-04-05 03:00:00'),
('WS005', '1', 'Long-sleeved Shirt for Women', 'Korean', 'Classic long-sleeved shirt for daily wear.', 20.00, 'Free Size', 'Blue', '4.9', 50, 5, 1000, NULL, 'In stock', '2026-03-08 08:45:00'),
('WS006', '1', 'French Style White Shirt with Bow', 'French', 'High-end long sleeve top with bow design.', 30.00, 'S,M,L,XL,2XL', 'White', '4.9', 21, 5, 1000, NULL, 'In stock', '2026-04-10 00:20:00'),
('WS007', '1', 'Korean Fashion Aesthetic Round Neck T-shirt', 'Korean', 'Round neck aesthetic T-shirt (220g).', 20.00, 'S,M,L,XL,2XL', 'White,Black,Apricot,Brown', '4.9', 60, 5, 2000, NULL, 'In stock', '2026-03-15 05:10:00'),
('WS008', '1', 'American Girl Red One Shoulder T-shirt', 'American', 'Off-shoulder long-sleeved T-shirt.', 20.00, 'S,M,L,XL,2XL', 'White,Black,Red,Brown', '4.7', 28, 5, 3400, NULL, 'In stock', '2026-04-01 09:50:00'),
('WS009', '1', 'Casual Plain Round Neck Short Sleeve Crop Top', 'Crop top', 'Slim fit basic T-shirt for women.', 15.00, 'S,M,L,XL,2XL', 'Black,Brown,Khaki,White', '4.9', 69, 5, 4000, NULL, 'In stock', '2026-03-20 04:00:00'),
('WS010', '1', 'Croptop T-Shirt With Bow Tie Front', 'Crop top', 'Fashionable croptop with front bow tie.', 15.00, 'S,M,L,XL,2XL', 'Black,Pink,Green', '4.8', 100, 5, 3000, NULL, 'In stock', '2026-04-09 07:30:00'),
('WS011', '1', 'Women Korean V-Neck Button Slim Fit Top', 'Korean', 'Slim fit long sleeves V-neck top.', 15.00, 'S,M,L,XL,2XL', 'Pink,Black,White', '4.7', 150, 5, 359, NULL, 'In stock', '2026-03-18 02:05:00'),
('WS012', '1', 'Korean Waist Striped Long Sleeve Blouse', 'Korean', 'Formal shirt for women office wear.', 30.00, 'S,M,L,XL,2XL', 'Black,Blue,White,Pink', '4.8', 200, 5, 63, NULL, 'In stock', '2026-04-03 03:40:00'),
('WS013', '1', 'Casual Plain Ruched Polo Shirt', 'Polo', 'Slim fit short sleeve polo shirt.', 15.00, 'S,M,L,XL,2XL', 'Black,White,Gray,Darkgray', '4.6', 298, 5, 378, NULL, 'In stock', '2026-03-29 01:15:00'),
('WS014', '1', 'Women Casual Oversized Retro Print T-shirt', 'Korean', 'Short sleeve retro print Korean style.', 15.00, 'S,M,L,XL,2XL', 'Black,Red,White', '4.8', 109, 5, 1000, NULL, 'In stock', '2026-04-11 06:50:00'),
('WS015', '1', 'HITTY Korean 100% Cotton Oversized Shirt', 'Korean', 'Premium 100% cotton print oversized shirt.', 20.00, 'S,M,L,XL,2XL', 'Black,White,Brown,Apricot', '4.9', 221, 5, 454, NULL, 'In stock', '2026-03-05 05:25:00'),
('WS016', '1', 'Oversized Summer Casual Loose Short T-shirt', 'Korean', 'Women summer casual loose short top.', 15.00, 'S,M,L,XL,2XL', 'White,Black,Apricot', '4.8', 200, 5, 576, NULL, 'In stock', '2026-03-22 02:10:00'),
('WS017', '1', 'Printed Loose Strapless Pure Cotton T-Shirt', 'Korean', 'Fashion printed loose strapless short-sleeved.', 15.00, 'S,M,L,XL,2XL', 'Navy Blue,Dark Gray,White', '4.8', 215, 5, 0, NULL, 'In stock', '2026-04-06 08:00:00'),
('WS018', '1', 'Girl Short-Sleeve Knit Top Summer New', 'Korean', 'Korean chic style T-shirt blouse.', 15.00, 'S,M,L,XL,2XL', 'Black,Pink,Blue', '4.9', 50, 5, 376, NULL, 'In stock', '2026-03-31 00:45:00'),
('WS019', '1', 'Fashionable Striped Short-Sleeved T-shirt', 'Korean', 'Korean style fashionable striped top.', 15.00, 'S,M,L,XL,2XL', 'Blue,Brown', '4.5', 100, 5, 90, NULL, 'In stock', '2026-04-12 03:20:00'),
('WS020', '1', 'Lovito x Gladish Casual Summer/Spring T-Shirt', 'Korean', 'Casual summer and spring T-Shirt for women.', 20.00, 'S,M,L,XL,2XL', 'White,Dark Gray,Light Gray', '4.8', 61, 5, 500, NULL, 'In stock', '2026-03-10 07:30:00');

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
('001', 'theboss', 'limlizhe', 'Male', '2026-03-01', '$2a$12$8D7Kmk8DxAMFTlYmNsMwbuUurMl5w2lA/dCX4C6Qef6k0LnQbIlIq', '0132111222', 'pxxmallmalaysia@gmail.com', 'Level 33, Menara KPJ, Jln Tun Razak,', '50400', 'Kuala Lumpur', 'Wilayah Persekutuan Kuala Lumpur', '', 0, '0', 10000, 'Admin', '2026-03-01 05:14:52', 0, '2026-04-10 23:06:14', '2026-04-12 18:32:03', NULL, NULL, 'theboss.jpeg', 'Active', NULL, NULL, 1, 'Verified', 'Verified'),
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
('t101', 'llz', 'LIM LI ZHE', 'Male', '2007-06-03', '$2a$12$8D7Kmk8DxAMFTlYmNsMwbuUurMl5w2lA/dCX4C6Qef6k0LnQbIlIq', '0193019930', 'limlz-wm25@student.tarc.edu.my', 'Ground Floor, Bangunan Tan Sri Khaw Kai Boh (Block A), Jalan Genting Kelang', '53300', 'Setapak', 'Wilayah Persekutuan Kuala Lumpur', 'Public Bank', 8888, '5627b4a8f9efbd8fbdadaf4177824186f8c734f320935c88e926bc027af6c50f', 100, 'Member', '2026-03-13 07:15:00', 0, NULL, '2026-04-12 17:21:57', NULL, NULL, 'llz.jpg', 'Active', '000795', '2026-04-12 17:48:01', 0, 'Unverified', 'Verified'),
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
