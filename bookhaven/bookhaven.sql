-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 07, 2025 at 08:28 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.1.17

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bookhaven`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `check_in` date NOT NULL,
  `check_out` date NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','cancelled','completed') DEFAULT 'pending',
  `special_requests` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `room_id`, `check_in`, `check_out`, `total_price`, `status`, `special_requests`, `created_at`, `updated_at`) VALUES
(1, 2, 2, '2025-10-30', '2025-10-31', 1500.00, 'completed', '', '2025-10-29 05:41:05', '2025-11-01 10:29:16'),
(2, 2, 5, '2025-10-30', '2025-11-05', 24000.00, 'completed', '', '2025-10-29 05:44:11', '2025-11-01 10:29:13'),
(3, 2, 7, '2025-10-30', '2025-11-04', 17500.00, 'cancelled', '', '2025-10-29 05:44:23', '2025-10-29 05:45:53'),
(4, 3, 4, '2025-10-30', '2025-11-06', 17500.00, 'cancelled', '', '2025-10-29 05:47:20', '2025-10-29 05:47:31'),
(6, 5, 2, '2025-11-01', '2025-11-03', 3000.00, 'completed', '', '2025-11-01 10:26:30', '2025-11-01 10:28:49'),
(7, 5, 9, '2025-11-01', '2025-11-03', 3400.00, 'completed', '', '2025-11-01 10:30:08', '2025-11-01 10:31:32'),
(9, 6, 50, '2025-11-01', '2025-11-03', 2600.00, 'completed', 'huhu', '2025-11-01 12:33:40', '2025-11-05 15:51:02'),
(10, 6, 50, '2025-11-01', '2025-11-04', 3900.00, 'completed', '', '2025-11-01 12:33:56', '2025-11-05 15:50:56'),
(11, 6, 3, '2025-11-01', '2025-11-03', 5000.00, 'completed', '', '2025-11-01 12:34:24', '2025-11-05 15:50:55'),
(12, 6, 5, '2025-11-21', '2025-11-22', 4000.00, 'cancelled', '', '2025-11-01 12:34:55', '2025-11-01 12:35:00'),
(13, 5, 50, '2025-11-06', '2025-11-07', 1300.00, 'confirmed', '', '2025-11-05 16:18:26', '2025-11-05 16:28:26'),
(14, 3, 9, '2025-11-07', '2025-11-08', 1700.00, 'confirmed', '', '2025-11-07 07:09:49', '2025-11-07 07:11:27');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','credit_card','debit_card','online') DEFAULT 'cash',
  `status` enum('pending','completed','failed','refunded') DEFAULT 'pending',
  `transaction_id` varchar(100) DEFAULT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `room_type` varchar(50) NOT NULL,
  `room_number` varchar(20) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `capacity` int(11) DEFAULT 2,
  `status` enum('available','occupied','maintenance') DEFAULT 'available',
  `image` varchar(255) DEFAULT NULL,
  `amenities` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `room_type`, `room_number`, `description`, `price`, `capacity`, `status`, `image`, `amenities`, `created_at`, `updated_at`) VALUES
(2, 'Single', '102', 'All Free!', 1500.00, 1, 'available', 'assets/images/rooms/single2.jpg', 'Free WiFi, Air Conditioning, TV, Mini Fridge', '2025-10-29 05:32:25', '2025-11-05 15:51:47'),
(3, 'Double', '201', 'Spacious double room with king-size bed. Ideal for couples seeking comfort and relaxation.', 2500.00, 2, 'available', 'assets/images/rooms/double.jpg', 'Free WiFi, Air Conditioning, TV, Mini Bar, Safe', '2025-10-29 05:32:25', '2025-11-05 15:50:55'),
(4, 'Double', '202', 'Modern double room with city view. Features premium bedding and contemporary design.', 2500.00, 2, 'available', 'assets/images/rooms/double2.jpg', 'Free WiFi, Air Conditioning, TV, Mini Bar, Safe', '2025-10-29 05:32:25', '2025-11-01 09:43:03'),
(5, 'Deluxe', '301', 'All Free!', 4000.00, 2, 'available', 'assets/images/rooms/deluxe.jpg', 'Free WiFi, Air Conditioning, Smart TV, Mini Bar, Coffee Maker, Balcony', '2025-10-29 05:32:25', '2025-11-01 10:42:06'),
(6, 'Deluxe', '302', 'Premium deluxe room with elegant furnishings. Perfect for special occasions and extended stays.', 4000.00, 2, 'available', 'assets/images/rooms/deluxe2.jpg', 'Free WiFi, Air Conditioning, Smart TV, Mini Bar, Coffee Maker, Balcony', '2025-10-29 05:32:25', '2025-11-01 09:51:18'),
(7, 'Family', '401', 'Large family room with multiple beds. Accommodates up to 4 guests comfortably.', 3500.00, 4, 'available', 'assets/images/rooms/family.jpg', 'Free WiFi, Air Conditioning, TV, Mini Fridge, Extra Beds, Play Area', '2025-10-29 05:32:25', '2025-10-29 06:01:37'),
(8, 'Family', '402', 'Spacious family suite perfect for creating memories. Features separate sleeping areas and entertainment options.', 3500.00, 4, 'available', 'assets/images/rooms/family2.jpg', 'Free WiFi, Air Conditioning, TV, Mini Fridge, Extra Beds, Play Area', '2025-10-29 05:32:25', '2025-11-01 09:54:04'),
(9, 'Single', '101', 'All Free!', 1700.00, 1, 'occupied', 'assets/images/rooms/single.jpg', 'Free all', '2025-10-29 05:58:20', '2025-11-07 07:11:27'),
(50, 'Single', '777', 'ads', 1300.00, 1, 'occupied', 'assets/images/rooms/1761999404_aacd47a514af.jpg', 'asd', '2025-11-01 12:16:44', '2025-11-05 16:28:26');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','guest') DEFAULT 'guest',
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `phone`, `created_at`, `updated_at`) VALUES
(1, 'Administrator', 'admin@bookhaven.com', '$2y$10$S64yBrvxhlmotRMLRrzlKuf9vezwjBXNp.q9iE1eI5nMQUz2KTvqu', 'admin', NULL, '2025-10-29 05:32:25', '2025-10-29 05:38:32'),
(2, 'Heinrich John Corpuz', 'heinrich123@gmail.com', '$2y$10$.rjCfwBJiX6csz9kITRgJetDxMOhlLhtcuuX5.sKv2CygaVINRFTm', 'guest', '09565780265', '2025-10-29 05:40:02', '2025-10-29 05:40:02'),
(3, 'Jessa Mae Ronquillo', 'jessa123@gmail.com', '$2y$10$nLlqb4RuBTvwOzUyFiakneSpfhw74R3b6gwziOah4b8zDcGsT8XaO', 'guest', '09456321753', '2025-10-29 05:46:48', '2025-10-29 05:46:48'),
(4, 'Mark Joseph Marquez', 'mj123@gmail.com', '$2y$10$a9LT.qHvgnPPQMe5mQZwnuK38pTkpYODn6Xek2nuNzPBLvYab/fJ.', 'guest', '0974512385', '2025-10-29 05:52:08', '2025-10-29 05:52:08'),
(5, 'Erwin Smith', 'erwin123@gmail.com', '$2y$10$AVCOFLrqVSMWfBVrZ9DTkO8X9gnF6qpqFZhT4hnTmSRxZEArXeVlW', 'guest', '09565780265', '2025-11-01 10:26:01', '2025-11-01 10:26:01'),
(6, 'Sasha Blouse', 'sasha123@gmail.com', '$2y$10$ZbdUBp6TQp29CdqbC880q.icfTNHgRnkMqTQAMZiVnXGXjnwQJceO', 'guest', '09476838424', '2025-11-01 12:29:42', '2025-11-01 12:29:42');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `room_id` (`room_id`),
  ADD KEY `idx_booking_status` (`status`),
  ADD KEY `idx_booking_dates` (`check_in`,`check_out`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `room_number` (`room_number`),
  ADD KEY `idx_room_status` (`status`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_user_email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
