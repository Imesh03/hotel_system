-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 21, 2026 at 07:30 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hotel_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `about_us_content`
--

CREATE TABLE `about_us_content` (
  `id` int(11) NOT NULL,
  `section_key` varchar(50) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `media_type` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `about_us_content`
--

INSERT INTO `about_us_content` (`id`, `section_key`, `title`, `content`, `media_type`) VALUES
(1, 'story', NULL, 'Born from a passion for timeless hospitality, Hotel Panama began its journey as a boutique sanctuary designed to blend modern luxury with tropical elegance. Over the years, we have grown into a premier destination where every corner tells a tale of comfort, culture, and curated experiences. From welcoming weary travelers to hosting unforgettable celebrations, our history is built on a foundation of creating a true \'home away from home\' for guests from across the globe.', NULL),
(2, 'vision', NULL, 'To be recognized as a benchmark of refined hospitality, where luxury meets authenticity. Our vision is to continuously inspire and delight our guests by delivering flawless service, sustainable luxury practices, and unforgettable moments. We strive to craft an environment where exceptional comfort, culinary excellence, and heartwarming hospitality come together to redefine the modern travel experience.', NULL),
(3, 'why_us', NULL, 'Unmatched Premium Luxury: Immersive architectural design coupled with state-of-the-art amenities to ensure your stay is nothing short of extraordinary.\r\n\r\nExquisite Culinary Mastery: From authentic local delicacies to curated international cuisines prepared by top chefs, our kitchen promises a world-class dining experience.\r\n\r\nGuest-Centric Hospitality: Our dedicated team is committed to providing a personalized, seamless service that anticipates your every need from check-in to check-out.\r\n\r\nPrime & Serene Location: Perfectly situated to offer a tranquil escape from the hustle and bustle, while keeping you effortlessly connected to key local attractions', NULL),
(6, 'media', NULL, '1784378740_OIP.webp', 'image'),
(7, 'media', NULL, '1784378755_R.jfif', 'image'),
(8, 'media', NULL, '1784378775_OIP (2).webp', 'image'),
(9, 'media', NULL, '1784378791_d455626b1608d2a276892f49e577bd08.jpg', 'image'),
(10, 'media', NULL, '1784378869_image.jpg', 'image');

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `security_question` varchar(255) NOT NULL DEFAULT 'What is your favorite hotel name?',
  `security_answer` varchar(255) NOT NULL DEFAULT 'hotel panama'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `created_at`, `security_question`, `security_answer`) VALUES
(1, 'admin', '$2y$10$I4zSn8Q.xUEbWkLrkLol3O7PSYY5mh8jppkTE/yI93HeeTTiOAfKu', '2026-07-08 17:00:29', 'What is your favorite hotel name?', 'hotel panama');

-- --------------------------------------------------------

--
-- Table structure for table `extra_orders`
--

CREATE TABLE `extra_orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `order_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `rating` int(11) NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `file_type` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_visible` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `user_id`, `message`, `rating`, `file_path`, `file_type`, `created_at`, `is_visible`) VALUES
(10, 50, 'An Unforgettable Luxury Experience!\r\nI had a wonderful stay at this hotel. The dark luxury theme of the website truly reflects the real ambiance of the hotel—elegant and high-end. The online food ordering and room service system is incredibly fast and efficient. The room was pristine, the food was absolutely delicious, and the staff provided top-notch hospitality. Highly recommended for anyone seeking a perfect luxury getaway!\r\n', 5, '1784379588_OIP (2).jpg', 'image', '2026-07-18 12:59:48', 1);

-- --------------------------------------------------------

--
-- Table structure for table `hotel_content`
--

CREATE TABLE `hotel_content` (
  `id` int(11) NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `content_path` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hotel_content`
--

INSERT INTO `hotel_content` (`id`, `type`, `content_path`, `title`, `is_active`) VALUES
(2, 'image', 'uploads/welcome/1777618315_katlat.jfif', 'Welcome Luxery Hotel', 1),
(3, 'text', 'No 123 , Peradeniya road, Kandy', 'Have you Great day !', 1),
(4, 'text', 'Are you Hungry', 'what do you looking', 1);

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `id` int(11) NOT NULL,
  `item_name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `category` enum('food','beverages','snacks','service') NOT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `sub_category` enum('breakfast','lunch','dinner','none') DEFAULT 'none'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`id`, `item_name`, `description`, `price`, `image_path`, `category`, `is_available`, `sub_category`) VALUES
(1, 'Chicken Rice', '', 1100.00, '1784374606_chiken.jpg', 'food', 1, 'lunch'),
(2, 'Kiribath', 'Kiribath 1 piece with chilly ', 50.00, '1775408441_14.jpg', 'food', 1, 'breakfast'),
(4, 'Udu Wade', '', 50.00, '1775497210_11a.jpg', 'snacks', 1, 'none'),
(5, 'Halapa', '', 70.00, '1775499637_7.jpg', 'food', 1, 'breakfast'),
(6, 'Cutlet', '', 30.00, '1775499773_katlat.jfif', 'food', 1, 'lunch'),
(7, 'Roti', 'with Lunumiris', 50.00, '1775499803_Polroti.webp', 'food', 1, 'breakfast'),
(8, 'Egg Hoppers', 'with Lunumiris', 90.00, '1777044376_02.jpg', 'food', 1, 'breakfast'),
(10, 'Avocado juice', 'alipera', 350.00, '1784373200_avocado.jpg', 'beverages', 1, 'none'),
(11, 'Biriyani (Chicken)', 'biriyani', 1300.00, '1784373352_biriyani.jpg', 'food', 1, 'lunch'),
(12, 'Cheese Kottu', 'Kottu', 1200.00, '1784373456_cheese.jpg', 'food', 1, 'dinner'),
(13, 'Coffee', '', 120.00, '1784374755_coffee.jpg', 'beverages', 1, 'none'),
(14, 'Cappuccino', '', 200.00, '1784374827_cuppuchino.jpg', 'beverages', 1, 'none'),
(15, 'Dolphin Kotto', '', 1250.00, '1784374900_dplphi.jpg', 'food', 1, 'dinner'),
(16, 'Ice coffee', '', 250.00, '1784374937_ice cofee.jpg', 'beverages', 1, 'none'),
(17, 'Lemon Juice', '', 200.00, '1784374980_lemon.jpg', 'beverages', 1, 'none'),
(18, 'Sea Food Rice', '', 1500.00, '1784375040_seafood.jpg', 'food', 1, 'lunch'),
(19, 'Tea', '', 100.00, '1784375104_tea.jpg', 'beverages', 1, 'none'),
(20, 'Chicken Kottu', '', 1300.00, '1784375191_OIP (1).jpg', 'food', 1, 'dinner');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `user_id`, `message`, `is_read`, `created_at`) VALUES
(1, 2, 'ඔබේ ඇණවුම (Udu Wade) සාර්ථකව ලබා දී අවසන්. ස්තූතියි!', 1, '2026-04-07 12:58:54'),
(2, 2, 'ඔබේ ඇණවුම (Roti) සාර්ථකව ලබා දී අවසන්. ස්තූතියි!', 1, '2026-04-07 12:58:57'),
(3, 2, 'ඔබේ ඇණවුම (Egg Hoppers) සාර්ථකව ලබා දී අවසන්. ස්තූතියි!', 1, '2026-04-07 12:58:58'),
(4, 2, 'ඔබේ ඇණවුම (Egg Hoppers) සාර්ථකව ලබා දී අවසන්. ස්තූතියි!', 1, '2026-04-07 12:59:00'),
(5, 2, 'ඔබේ ඇණවුම (Roti) සාර්ථකව ලබා දී අවසන්. ස්තූතියි!', 1, '2026-04-07 12:59:01'),
(6, 2, 'ඔබේ ඇණවුම (Kiribath) සාර්ථකව ලබා දී අවසන්. ස්තූතියි!', 1, '2026-04-07 12:59:02'),
(7, 2, 'ඔබේ ඇණවුම (Egg Hoppers) සාර්ථකව ලබා දී අවසන්. ස්තූතියි!', 1, '2026-04-07 13:06:29'),
(8, 2, 'ඔබේ ඇණවුම (Roti) සාර්ථකව ලබා දී අවසන්. ස්තූතියි!', 1, '2026-04-07 13:13:30'),
(9, 2, 'ඔබේ ඇණවුම (Kiribath) සාර්ථකව ලබා දී අවසන්. ස්තූතියි!', 1, '2026-04-07 13:13:32'),
(10, 2, 'ඔබේ ඇණවුම (Cutlet) සාර්ථකව ලබා දී අවසන්. ස්තූතියි!', 1, '2026-04-07 13:13:34'),
(11, 2, 'ඔබේ ඇණවුම (Egg Hoppers (x1), Kiribath (x1)) අප ලබාගත්තා. දැන් එය පිළියෙළ කරමින් පවතී.', 1, '2026-04-07 13:30:58'),
(12, 2, 'ඔබේ ඇණවුම (Egg Hoppers (x1), Kiribath (x1)) දැන් සූදානම් (Ready)! ඉක්මනින් ඔබ වෙත ලැබෙනු ඇත.', 1, '2026-04-07 13:31:27'),
(13, 2, 'ඔබේ ඇණවුම (Egg Hoppers (x1), Kiribath (x1)) සාර්ථකව ලබා දී අවසන්. ස්තූතියි!', 1, '2026-04-07 13:31:43'),
(14, 2, 'ඔබේ ඇණවුම (Kiribath (x1)) සාර්ථකව ලබා දී අවසන්. ස්තූතියි!', 1, '2026-04-07 14:06:38'),
(15, 2, 'ඔබේ ඇණවුම (Kiribath (x1)) අප ලබාගත්තා. දැන් එය පිළියෙළ කරමින් පවතී.', 1, '2026-04-07 14:26:33'),
(16, 2, 'ඔබේ ඇණවුම (Kiribath (x1)) සාර්ථකව ලබා දී අවසන්. ස්තූතියි!', 1, '2026-04-07 14:26:35'),
(17, 2, 'ඔබේ ඇණවුම (Cutlet (x1), Udu Wade (x1), Kiribath (x1), Egg Hoppers (x1)) අප ලබාගත්තා. දැන් එය පිළියෙළ කරමින් පවතී.', 1, '2026-04-07 21:47:31'),
(18, 2, 'ඔබේ ඇණවුම (Cutlet (x1), Udu Wade (x1), Kiribath (x1), Egg Hoppers (x1)) සාර්ථකව ලබා දී අවසන්. ස්තූතියි!', 1, '2026-04-07 21:47:33'),
(19, 2, 'ඔබේ ඇණවුම (Egg Hoppers (x3)) සාර්ථකව ලබා දී අවසන්. ස්තූතියි!', 1, '2026-04-07 21:50:40'),
(20, 2, 'ඔබේ ඇණවුම (Egg Hoppers (x1), Kiribath (x1)) සාර්ථකව ලබා දී අවසන්. ස්තූතියි!', 1, '2026-04-07 23:40:09'),
(21, 5, 'ඔබේ ඇණවුම (Egg Hoppers (x1), Kiribath (x1), Roti (x4)) අප ලබාගත්තා. දැන් එය පිළියෙළ කරමින් පවතී.', 1, '2026-04-07 23:40:11'),
(22, 5, 'ඔබේ ඇණවුම (Egg Hoppers (x1), Kiribath (x1), Roti (x4)) දැන් සූදානම් (Ready)! ඉක්මනින් ඔබ වෙත ලැබෙනු ඇත.', 1, '2026-04-07 23:40:12'),
(23, 5, 'ඔබේ ඇණවුම (Egg Hoppers (x1), Kiribath (x1), Roti (x4)) සාර්ථකව ලබා දී අවසන්. ස්තූතියි!', 1, '2026-04-07 23:40:13'),
(24, 6, 'ඔබේ ඇණවුම (Cutlet (x3), Egg Hoppers (x1)) අප ලබාගත්තා. දැන් එය පිළියෙළ කරමින් පවතී.', 0, '2026-04-07 23:57:00'),
(25, 6, 'ඔබේ ඇණවුම (Cutlet (x3), Egg Hoppers (x1)) දැන් සූදානම් (Ready)! ඉක්මනින් ඔබ වෙත ලැබෙනු ඇත.', 0, '2026-04-07 23:57:01'),
(26, 6, 'ඔබේ ඇණවුම (Cutlet (x3), Egg Hoppers (x1)) සාර්ථකව ලබා දී අවසන්. ස්තූතියි!', 0, '2026-04-07 23:57:02'),
(27, 7, 'ඔබේ ඇණවුම (Udu Wade (x6), Egg Hoppers (x3)) සාර්ථකව ලබා දී අවසන්. ස්තූතියි!', 1, '2026-04-08 22:42:22'),
(28, 2, 'ඔබේ ඇණවුම (Halapa (x1), Kiribath (x1), Egg Hoppers (x1), Egg Hoppers (x2)) සාර්ථකව ලබා දී අවසන්. ස්තූතියි!', 1, '2026-04-08 22:42:24'),
(29, 11, 'ඔබේ ඇණවුම (Egg Hoppers (x1)) සාර්ථකව ලබා දී අවසන්. ස්තූතියි!', 0, '2026-04-09 00:36:52'),
(30, 13, 'ඔබේ ඇණවුම (Kiribath (x2), Egg Hoppers (x3)) අප ලබාගත්තා. දැන් එය පිළියෙළ කරමින් පවතී.', 1, '2026-04-23 21:17:07'),
(31, 13, 'ඔබේ ඇණවුම (Kiribath (x2), Egg Hoppers (x3)) දැන් සූදානම් (Ready)! ඉක්මනින් ඔබ වෙත ලැබෙනු ඇත.', 1, '2026-04-23 21:17:27'),
(32, 13, 'ඔබේ ඇණවුම (Kiribath (x2), Egg Hoppers (x3)) සාර්ථකව ලබා දී අවසන්. ස්තූතියි!', 1, '2026-04-23 21:17:58'),
(33, 13, 'ඔබේ ඇණවුම (Halapa (x2), Kiribath (x3)) අප ලබාගත්තා. දැන් එය පිළියෙළ කරමින් පවතී.', 1, '2026-04-24 20:25:26'),
(34, 13, 'ඔබේ ඇණවුම (Halapa (x2), Kiribath (x3)) අප ලබාගත්තා. දැන් එය පිළියෙළ කරමින් පවතී.', 1, '2026-04-24 20:51:02'),
(35, 13, 'ඔබේ ඇණවුම (Kiribath (x3), Halapa (x2)) අප ලබාගත්තා. දැන් එය පිළියෙළ කරමින් පවතී.', 0, '2026-04-26 21:12:43'),
(36, 13, 'ඔබේ ඇණවුම (Kiribath (x3), Halapa (x2)) අප ලබාගත්තා. දැන් එය පිළියෙළ කරමින් පවතී.', 0, '2026-04-26 21:13:17'),
(37, 13, 'ඔබේ ඇණවුම (Kiribath (x3), Halapa (x2)) අප ලබාගත්තා. දැන් එය පිළියෙළ කරමින් පවතී.', 0, '2026-04-26 21:13:18'),
(38, 24, 'ඔබේ ඇණවුම (Egg Hoppers (x3), Kiribath (x3)) අප ලබාගත්තා. දැන් එය පිළියෙළ කරමින් පවතී.', 1, '2026-04-26 21:13:32'),
(39, 13, 'ඔබේ ඇණවුම (Kiribath (x3), Halapa (x2)) දැන් සූදානම් (Ready)! ඉක්මනින් ඔබ වෙත ලැබෙනු ඇත.', 0, '2026-04-26 21:14:00'),
(40, 24, 'ඔබේ ඇණවුම (Egg Hoppers (x3), Kiribath (x3)) දැන් සූදානම් (Ready)! ඉක්මනින් ඔබ වෙත ලැබෙනු ඇත.', 1, '2026-04-26 21:14:24'),
(41, 24, 'ඔබේ ඇණවුම (Egg Hoppers (x3), Kiribath (x3)) සාර්ථකව ලබා දී අවසන්. ස්තූතියි!', 1, '2026-04-26 21:14:44'),
(42, 25, 'ඔබේ ඇණවුම (Egg Hoppers (x1)) අප ලබාගත්තා. දැන් එය පිළියෙළ කරමින් පවතී.', 1, '2026-05-01 12:37:18'),
(43, 25, 'ඔබේ ඇණවුම (Egg Hoppers (x1)) දැන් සූදානම් (Ready)! ඉක්මනින් ඔබ වෙත ලැබෙනු ඇත.', 1, '2026-05-01 12:37:19'),
(44, 25, 'ඔබේ ඇණවුම (Egg Hoppers (x1)) සාර්ථකව ලබා දී අවසන්. ස්තූතියි!', 1, '2026-05-01 12:37:21'),
(45, 20, 'ඔබේ ඇණවුම (Egg Hoppers (x1)) අප ලබාගත්තා. දැන් එය පිළියෙළ කරමින් පවතී.', 0, '2026-05-10 11:42:45'),
(46, 20, 'ඔබේ ඇණවුම (Egg Hoppers (x1)) දැන් සූදානම් (Ready)! ඉක්මනින් ඔබ වෙත ලැබෙනු ඇත.', 0, '2026-05-10 11:42:47'),
(47, 20, 'ඔබේ ඇණවුම (Egg Hoppers (x1)) සාර්ථකව ලබා දී අවසන්. ස්තූතියි!', 0, '2026-05-10 11:42:51'),
(48, 13, 'ඔබේ ඇණවුම (Kiribath (x3), Halapa (x2)) සාර්ථකව ලබා දී අවසන්. ස්තූතියි!', 0, '2026-05-10 11:43:23'),
(49, 21, 'ඔබේ ඇණවුම (Kiribath (x1)) අප ලබාගත්තා. දැන් එය පිළියෙළ කරමින් පවතී.', 1, '2026-05-10 12:47:52'),
(50, 21, 'ඔබේ ඇණවුම (Kiribath (x1)) දැන් සූදානම් (Ready)! ඉක්මනින් ඔබ වෙත ලැබෙනු ඇත.', 1, '2026-05-10 12:48:01'),
(51, 21, 'ඔබේ ඇණවුම (Kiribath (x1)) සාර්ථකව ලබා දී අවසන්. ස්තූතියි!', 1, '2026-05-10 12:48:03'),
(52, 21, 'ඔබේ ඇණවුම (Egg Hoppers (x1)) අප ලබාගත්තා. දැන් එය පිළියෙළ කරමින් පවතී.', 0, '2026-05-10 13:03:26'),
(53, 21, 'ඔබේ ඇණවුම (Egg Hoppers (x1)) දැන් සූදානම් (Ready)! ඉක්මනින් ඔබ වෙත ලැබෙනු ඇත.', 0, '2026-05-10 13:03:28'),
(54, 24, 'ඔබේ ඇණවුම (Cutlet (x1), Kiribath (x1)) අප ලබාගත්තා. දැන් එය පිළියෙළ කරමින් පවතී.', 1, '2026-06-19 12:25:31'),
(55, 24, 'ඔබේ ඇණවුම (Cutlet (x1), Kiribath (x1)) දැන් සූදානම් (Ready)! ඉක්මනින් ඔබ වෙත ලැබෙනු ඇත.', 1, '2026-06-19 12:25:33'),
(56, 24, 'ඔබේ ඇණවුම (Cutlet (x1), Kiribath (x1)) සාර්ථකව ලබා දී අවසන්. ස්තූතියි!', 1, '2026-06-19 12:25:36'),
(57, 21, 'ඔබේ ඇණවුම (Egg Hoppers (x1)) සාර්ථකව ලබා දී අවසන්. ස්තූතියි!', 0, '2026-06-19 12:35:21'),
(58, 24, 'ඔබේ ඇණවුම (Kiribath (x1)) සාර්ථකව ලබා දී අවසන්. ස්තූතියි!', 1, '2026-06-19 12:35:22'),
(59, 24, 'ඔබේ ඇණවුම (Egg Hoppers (x1), Egg Hoppers (x1)) සාර්ථකව ලබා දී අවසන්. ස්තූතියි!', 1, '2026-06-19 12:57:11'),
(60, 24, 'ඔබ ඉල්ලා සිටි \'Laundry Service\' සේවාව අප වෙත ලැබුණි. (Service Request Received)', 1, '2026-06-19 14:03:28'),
(61, 24, 'ඔබගේ \'Laundry Service\' සේවාව ලබාදීමට අප සූදානම්, දැන් අප නිලධාරියෙකු ඔබගේ කාමරයට පැමිණෙනු ඇත. (Staff is on the way)', 1, '2026-06-19 14:04:15'),
(62, 24, 'ඔබ ලබාගත් \'Laundry Service\' සේවාව සාර්ථකව අවසන් කරන ලදී. ස්තූතියි! (Service Completed)', 1, '2026-06-19 14:04:33'),
(63, 24, 'ඔබේ ඇණවුම (Udu Wade (x5)) අප ලබාගත්තා. දැන් එය පිළියෙළ කරමින් පවතී.', 1, '2026-06-19 15:24:26'),
(64, 24, 'ඔබේ ඇණවුම (Udu Wade (x5)) දැන් සූදානම් (Ready)! ඉක්මනින් ඔබ වෙත ලැබෙනු ඇත.', 1, '2026-06-19 15:25:19'),
(65, 24, 'ඔබේ ඇණවුම (Egg Hoppers (x3), Halapa (x1), Kiribath (x1)) සාර්ථකව ලබා දී අවසන්. ස්තූතියි!', 1, '2026-06-19 15:45:37'),
(66, 24, 'ඔබේ ඇණවුම (Egg Hoppers (x1)) අප ලබාගත්තා. දැන් එය පිළියෙළ කරමින් පවතී.', 1, '2026-06-19 16:37:29'),
(67, 24, 'ඔබේ ඇණවුම (Egg Hoppers (x1)) සාර්ථකව ලබා දී අවසන්. ස්තූතියි!', 0, '2026-06-22 13:07:41'),
(68, 26, 'ඔබේ ඇණවුම (Halapa (x1), Kiribath (x1)) සාර්ථකව ලබා දී අවසන්. ස්තූතියි!', 1, '2026-06-22 13:07:53'),
(69, 26, 'ඔබ ඉල්ලා සිටි \'Room Cleaning\' සේවාව අප වෙත ලැබුණි. (Service Request Received)', 1, '2026-06-22 13:21:58'),
(70, 26, 'ඔබගේ \'Room Cleaning\' සේවාව ලබාදීමට අප සූදානම්, දැන් අප නිලධාරියෙකු ඔබගේ කාමරයට පැමිණෙනු ඇත. (Staff is on the way)', 1, '2026-06-22 13:22:00'),
(71, 26, 'ඔබ ලබාගත් \'Room Cleaning\' සේවාව සාර්ථකව අවසන් කරන ලදී. ස්තූතියි! (Service Completed)', 1, '2026-06-22 13:22:03'),
(72, 27, 'ඔබේ ඇණවුම (Halapa (x3)) අප ලබාගත්තා. දැන් එය පිළියෙළ කරමින් පවතී.', 1, '2026-07-04 12:50:21'),
(73, 27, 'ඔබේ ඇණවුම (Halapa (x3)) දැන් සූදානම් (Ready)! ඉක්මනින් ඔබ වෙත ලැබෙනු ඇත.', 1, '2026-07-04 12:50:47'),
(74, 27, 'ඔබේ ඇණවුම (Halapa (x3)) සාර්ථකව ලබා දී අවසන්. ස්תූතියි!', 1, '2026-07-04 12:51:32'),
(75, 27, 'ඔබේ ඇණවුම (Kiribath (x1), Halapa (x1)) අප ලබාගත්තා. දැන් එය පිළියෙළ කරමින් පවතී.', 1, '2026-07-08 00:38:42'),
(76, 27, 'ඔබේ ඇණවුම (Kiribath (x1)) අප ලබාගත්තා. දැන් එය පිළියෙළ කරමින් පවතී.', 1, '2026-07-08 01:00:40'),
(77, 27, 'ඔබේ ඇණවුම (Kiribath (x1), Kiribath (x1), Halapa (x1)) අප ලබාගත්තා. දැන් එය පිළියෙළ කරමින් පවතී.', 1, '2026-07-08 01:01:03'),
(78, 27, 'ඔබේ ඇණවුම (Kiribath (x1), Kiribath (x1), Halapa (x1)) සාර්ථකව ලබා දී අවසන්. ස්תූතියි!', 1, '2026-07-08 01:03:38'),
(79, 26, 'ඔබේ ඇණවුම (Kiribath (x2)) දැන් සූදානම් (Ready)! ඉක්මනින් ඔබ වෙත ලැබෙනු ඇත.', 0, '2026-07-08 01:03:46'),
(80, 26, 'ඔබේ ඇණවුම (Kiribath (x2)) සාර්ථකව ලබා දී අවසන්. ස්תූතියි!', 0, '2026-07-08 01:03:48'),
(81, 27, 'ඔබේ ඇණවුම (Egg Hoppers (x1)) අප ලබාගත්තා. දැන් එය පිළියෙළ කරමින් පවතී.', 1, '2026-07-08 01:06:27'),
(82, 27, 'ඔබේ ඇණවුම (Egg Hoppers (x1)) දැන් සූදානම් (Ready)! ඉක්මනින් ඔබ වෙත ලැබෙනු ඇත.', 1, '2026-07-08 01:06:49'),
(83, 27, 'ඔබේ ඇණවුම (Halapa (x4)) අප ලබාගත්තා. දැන් එය පිළියෙළ කරමින් පවතී.', 1, '2026-07-08 01:26:58'),
(84, 27, 'ඔබේ ඇණවුම (Halapa (x4), Egg Hoppers (x1)) අප ලබාගත්තා. දැන් එය පිළියෙළ කරමින් පවතී.', 1, '2026-07-08 01:27:03'),
(85, 27, 'ඔබේ ඇණවුම (Halapa (x4), Egg Hoppers (x1)) අප ලබාගත්තා. දැන් එය පිළියෙළ කරමින් පවතී.', 1, '2026-07-08 01:27:09'),
(86, 27, 'ඔබේ ඇණවුම (Halapa (x4), Egg Hoppers (x1)) අප ලබාගත්තා. දැන් එය පිළියෙළ කරමින් පවතී.', 1, '2026-07-08 01:27:12'),
(87, 27, 'ඔබේ ඇණවුම (Halapa (x4), Egg Hoppers (x1)) දැන් සූදානම් (Ready)! ඉක්මනින් ඔබ වෙත ලැබෙනු ඇත.', 1, '2026-07-08 01:27:51'),
(88, 27, 'ඔබේ ඇණවුම (Halapa (x4), Egg Hoppers (x1)) සාර්ථකව ලබා දී අවසන්. ස්תූතියි!', 1, '2026-07-08 01:28:29'),
(89, 27, 'ඔබ ඉල්ලා සිටි \'Room Cleaning\' සේවාව අප වෙත ලැබුණි. (Service Request Received)', 1, '2026-07-08 01:29:58'),
(90, 27, 'ඔබේ ඇණවුම (Kiribath (x1)) අප ලබාගත්තා. දැන් එය පිළියෙළ කරමින් පවතී.', 1, '2026-07-08 01:38:19'),
(91, 28, 'ඔබේ ඇණවුම (Egg Hoppers (x1)) අප ලබාගත්තා. දැන් එය පිළියෙළ කරමින් පවතී.', 1, '2026-07-08 01:38:23'),
(92, 28, 'ඔබේ ඇණවුම (Egg Hoppers (x1)) දැන් සූදානම් (Ready)! ඉක්මනින් ඔබ වෙත ලැබෙනු ඇත.', 1, '2026-07-08 01:38:27'),
(93, 28, 'ඔබේ ඇණවුම (Halapa (x1), Kiribath (x1), Egg Hoppers (x1)) අප ලබාගත්තා. දැන් එය පිළියෙළ කරමින් පවතී.', 1, '2026-07-08 21:04:20'),
(94, 28, 'ඔබේ ඇණවුම (Egg Hoppers (x1), Halapa (x1), Kiribath (x1), Egg Hoppers (x1)) දැන් සූදානම් (Ready)! ඉක්මනින් ඔබ වෙත ලැබෙනු ඇත.', 1, '2026-07-08 21:04:27'),
(95, 28, 'ඔබේ ඇණවුම (Egg Hoppers (x1), Halapa (x1), Kiribath (x1), Egg Hoppers (x1)) සාර්ථකව ලබා දී අවසන්. ස්תූතියි!', 1, '2026-07-08 21:04:40'),
(96, 27, 'ඔබ ලබාගත් \'Room Cleaning\' සේවාව සාර්ථකව අවසන් කරන ලදී.සේවාවට අදාල ගාස්තුව තව ස්වල්ප මොහොතකින් ඔබගේ බිල්පත වෙත ඇතුළත් කරනු ලැබේ. ගැටලුවක් තිබේ නම් විමසන්න.ස්තූතියි! (Service Completed)', 1, '2026-07-08 21:53:19'),
(97, 27, 'ඔබේ ඇණවුම (Kiribath (x1), Halapa (x1), Kiribath (x1), Kiribath (x1), Kiribath (x1), String  Hoppers (x1), Halapa (x1)) සාර්ථකව ලබා දී අවසන්. ස්තූතියි!', 1, '2026-07-08 23:48:03'),
(98, 27, 'ඔබේ ඇණවුම (Kiribath (x1)) අප ලබාගත්තා. දැන් එය පිළියෙළ කරමින් පවතී.', 1, '2026-07-08 23:48:30'),
(99, 40, 'ඔබ ඉල්ලා සිටි \'Laundry Service\' සේවාව අප වෙත ලැබුණි. (Service Request Received)', 1, '2026-07-17 16:30:34'),
(100, 27, 'ඔබේ ඇණවුම (Kiribath (x1)) අප ලබාගත්තා. දැන් එය පිළියෙළ කරමින් පවතී.', 0, '2026-07-17 16:33:36'),
(101, 40, 'ඔබේ ඇණවුම (Kiribath (x1)) අප ලබාගත්තා. දැන් එය පිළියෙළ කරමින් පවතී.', 1, '2026-07-17 16:33:47'),
(102, 27, 'ඔබේ ඇණවුම (Kiribath (x1)) සාර්ථකව ලබා දී අවසන්. ස්තූතියි!', 0, '2026-07-17 16:33:52'),
(103, 40, 'ඔබගේ \'Laundry Service\' සේවාව ලබාදීමට අප සූදානම්, දැන් අප නිලධාරියෙකු ඔබගේ කාමරයට පැමිණෙනු ඇත. (Staff is on the way)', 1, '2026-07-17 16:35:24'),
(104, 40, 'ඔබ ලබාගත් \'Ironing Service\' සේවාව සාර්ථකව අවසන් කරන ලදී.සේවාවට අදාල ගාස්තුව තව ස්වල්ප මොහොතකින් ඔබගේ බිල්පත වෙත ඇතුළත් කරනු ලැබේ. ගැටලුවක් තිබේ නම් විමසන්න.ස්තූතියි! (Service Completed)', 1, '2026-07-17 16:35:30'),
(105, 40, 'ඔබ ඉල්ලා සිටි \'Room Cleaning\' සේවාව අප වෙත ලැබුණි. (Service Request Received)', 1, '2026-07-17 16:59:27'),
(106, 40, 'ඔබගේ \'Room Cleaning\' සේවාව ලබාදීමට අප සූදානම්, දැන් අප නිලධාරියෙකු ඔබගේ කාමරයට පැමිණෙනු ඇත. (Staff is on the way)', 1, '2026-07-17 16:59:31'),
(107, 40, 'ඔබ ලබාගත් \'Room Cleaning\' සේවාව සාර්ථකව අවසන් කරන ලදී.සේවාවට අදාල ගාස්තුව තව ස්වල්ප මොහොතකින් ඔබගේ බිල්පත වෙත ඇතුළත් කරනු ලැබේ. ගැටලුවක් තිබේ නම් විමසන්න.ස්තූතියි! (Service Completed)', 0, '2026-07-17 17:05:37'),
(108, 43, 'ඔබගේ \'Room Cleaning\' සේවාව ලබාදීමට අප සූදානම්, දැන් අප නිලධාරියෙකු ඔබගේ කාමරයට පැමිණෙනු ඇත. (Staff is on the way)', 0, '2026-07-17 19:34:40'),
(109, 40, 'ඔබේ ඇණවුම (Kiribath (x1)) සාර්ථකව ලබා දී අවසන්. ස්තූතියි!', 0, '2026-07-17 23:14:35'),
(110, 53, 'ඔබේ ඇණවුම (Egg Hoppers (x1)) දැන් සූදානම් (Ready)! ඉක්මනින් ඔබ වෙත ලැබෙනු ඇත.', 0, '2026-07-17 23:31:02'),
(111, 40, 'ඔබ ලබාගත් \'Laundry Service\' සේවාව සාර්ථකව අවසන් කරන ලදී.සේවාවට අදාල ගාස්තුව තව ස්වල්ප මොහොතකින් ඔබගේ බිල්පත වෙත ඇතුළත් කරනු ලැබේ. ගැටලුවක් තිබේ නම් විමසන්න.ස්තූතියි! (Service Completed)', 0, '2026-07-18 13:36:37'),
(112, 40, 'ඔබ ලබාගත් \'Laundry Service\' සේවාව සාර්ථකව අවසන් කරන ලදී.සේවාවට අදාල ගාස්තුව තව ස්වල්ප මොහොතකින් ඔබගේ බිල්පත වෙත ඇතුළත් කරනු ලැබේ. ගැටලුවක් තිබේ නම් විමසන්න.ස්තූතියි! (Service Completed)', 0, '2026-07-18 13:36:40'),
(113, 43, 'ඔබ ලබාගත් \'Room Cleaning\' සේවාව සාර්ථකව අවසන් කරන ලදී.සේවාවට අදාල ගාස්තුව තව ස්වල්ප මොහොතකින් ඔබගේ බිල්පත වෙත ඇතුළත් කරනු ලැබේ. ගැටලුවක් තිබේ නම් විමසන්න.ස්තූතියි! (Service Completed)', 0, '2026-07-18 13:36:44'),
(114, 43, 'ඔබ ලබාගත් \'Room Cleaning\' සේවාව සාර්ථකව අවසන් කරන ලදී.සේවාවට අදාල ගාස්තුව තව ස්වල්ප මොහොතකින් ඔබගේ බිල්පත වෙත ඇතුළත් කරනු ලැබේ. ගැටලුවක් තිබේ නම් විමසන්න.ස්තූතියි! (Service Completed)', 0, '2026-07-18 13:36:50'),
(115, 50, 'ඔබ ඉල්ලා සිටි \'Room Cleaning\' සේවාව අප වෙත ලැබුණි. (Service Request Received)', 1, '2026-07-18 13:36:56'),
(116, 53, 'ඔබේ ඇණවුම (Chicken Rice (x1)) අප ලබාගත්තා. දැන් එය පිළියෙළ කරමින් පවතී.', 0, '2026-07-23 10:35:17'),
(117, 50, 'ඔබේ ඇණවුම (Biriyani (Chicken) (x1), Ice coffee (x3)) අප ලබාගත්තා. දැන් එය පිළියෙළ කරමින් පවතී.', 0, '2026-07-23 10:35:21'),
(118, 53, 'ඔබේ ඇණවුම (Chicken Rice (x1)) සාර්ථකව ලබා දී අවසන්. ස්තූතියි!', 0, '2026-07-23 10:36:21'),
(119, 50, 'ඔබේ ඇණවුම (Biriyani (Chicken) (x1), Ice coffee (x3)) සාර්ථකව ලබා දී අවසන්. ස්තූතියි!', 0, '2026-07-23 10:36:25'),
(120, 55, 'ඔබේ ඇණවුම (Halapa (x1)) අප ලබාගත්තා. දැන් එය පිළියෙළ කරමින් පවතී.', 1, '2026-07-23 10:36:28'),
(121, 55, 'Your order is ready (Kiribath (x1), Halapa (x3)) ', 1, '2026-07-23 12:53:15'),
(122, 55, ' \'Room Cleaning\' Service Request Received', 0, '2026-07-23 12:57:14');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `total_price` decimal(10,2) DEFAULT NULL,
  `order_type` enum('online','qr_scan','manual') DEFAULT 'online',
  `status` enum('pending','processing','ready','complete') DEFAULT 'pending',
  `order_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `media_path` varchar(255) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services_requests`
--

CREATE TABLE `services_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `service_name` varchar(255) NOT NULL,
  `cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` varchar(50) DEFAULT 'Pending',
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services_requests`
--

INSERT INTO `services_requests` (`id`, `user_id`, `service_name`, `cost`, `status`, `requested_at`) VALUES
(1, 24, 'Laundry Service', 1500.00, 'Complete', '2026-06-19 08:32:51'),
(2, 26, 'Room Cleaning', 0.00, 'Complete', '2026-06-22 07:51:47'),
(3, 27, 'Room Cleaning', 0.00, 'Complete', '2026-07-07 19:59:42'),
(4, 40, 'Laundry Service', 0.00, 'Complete', '2026-07-17 11:00:12'),
(5, 40, 'Laundry Service', 0.00, 'Complete', '2026-07-17 11:04:52'),
(6, 40, 'Ironing Service', 0.00, 'Complete', '2026-07-17 11:04:59'),
(7, 40, 'Room Cleaning', 0.00, 'Complete', '2026-07-17 11:05:40'),
(8, 43, 'Room Cleaning', 0.00, 'Complete', '2026-07-17 14:03:50'),
(9, 43, 'Room Cleaning', 0.00, 'Complete', '2026-07-17 14:05:13'),
(10, 50, 'Room Cleaning', 0.00, 'Received', '2026-07-18 08:06:24'),
(11, 55, 'Laundry Service', 0.00, 'Pending', '2026-07-23 07:07:15'),
(12, 55, 'Room Cleaning', 0.00, 'Received', '2026-07-23 07:26:56');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `nic_number` varchar(20) NOT NULL,
  `login_password` varchar(255) DEFAULT NULL,
  `phone_number` varchar(15) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `room_number` varchar(10) NOT NULL,
  `check_in_datetime` datetime NOT NULL,
  `check_out_datetime` datetime NOT NULL,
  `price_per_session` decimal(10,2) NOT NULL,
  `qr_token` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `status` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `about_us_content`
--
ALTER TABLE `about_us_content`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `extra_orders`
--
ALTER TABLE `extra_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hotel_content`
--
ALTER TABLE `hotel_content`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `services_requests`
--
ALTER TABLE `services_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `qr_token` (`qr_token`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `about_us_content`
--
ALTER TABLE `about_us_content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `extra_orders`
--
ALTER TABLE `extra_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `hotel_content`
--
ALTER TABLE `hotel_content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=123;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `services_requests`
--
ALTER TABLE `services_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `extra_orders`
--
ALTER TABLE `extra_orders`
  ADD CONSTRAINT `extra_orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `menu_items` (`id`);

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `services_requests`
--
ALTER TABLE `services_requests`
  ADD CONSTRAINT `services_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
