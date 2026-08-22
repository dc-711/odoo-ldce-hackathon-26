-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 22, 2026 at 11:37 AM
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
-- Database: `globetrotter`
--

-- --------------------------------------------------------

--
-- Table structure for table `activities`
--

CREATE TABLE `activities` (
  `id` int(10) UNSIGNED NOT NULL,
  `stop_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(160) NOT NULL,
  `activity_type` varchar(80) DEFAULT NULL,
  `estimated_cost` decimal(10,2) DEFAULT 0.00,
  `start_time` time DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `duration_minutes` int(11) DEFAULT 0,
  `rating` decimal(2,1) DEFAULT 0.0,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activities`
--

INSERT INTO `activities` (`id`, `stop_id`, `name`, `activity_type`, `estimated_cost`, `start_time`, `notes`, `location`, `duration_minutes`, `rating`, `image`) VALUES
(1, 1, 'Amber Fort Guided Tour', 'Sightseeing', 500.00, '09:30:00', 'Hire an official guide at the main gate.', 'Devisinghpura, Amer, Jaipur', 180, 4.8, 'assets/img/amber.jpg'),
(2, 2, 'Uffizi Gallery Visit', 'Museum', 25.00, '10:00:00', 'Pre-book skip-the-line tickets.', 'Piazzale degli Uffizi, Firenze', 150, 4.9, 'assets/img/uffizi.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `cities`
--

CREATE TABLE `cities` (
  `id` int(10) UNSIGNED NOT NULL,
  `city_name` varchar(100) NOT NULL,
  `country` varchar(100) NOT NULL,
  `region` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `cost_index` decimal(5,2) DEFAULT 0.00,
  `popularity` int(11) DEFAULT 0,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cities`
--

INSERT INTO `cities` (`id`, `city_name`, `country`, `region`, `description`, `image`, `cost_index`, `popularity`, `latitude`, `longitude`, `created_at`) VALUES
(1, 'Jaipur', 'India', 'Rajasthan', 'Pink City famous for forts, palaces and vibrant heritage.', 'assets/img/jaipur.jpg', 60.00, 92, 26.9124000, 75.7873000, '2026-08-22 09:26:44'),
(2, 'Florence', 'Italy', 'Tuscany', 'Renowned for Renaissance art, architecture, and monuments.', 'assets/img/florence.jpg', 85.50, 98, 43.7696000, 11.2558000, '2026-08-22 09:26:44');

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` int(10) UNSIGNED NOT NULL,
  `trip_id` int(10) UNSIGNED NOT NULL,
  `category` enum('transport','stay','activities','meals','other') NOT NULL,
  `label` varchar(160) NOT NULL,
  `amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `expenses`
--

INSERT INTO `expenses` (`id`, `trip_id`, `category`, `label`, `amount`) VALUES
(1, 2, 'activities', 'Amber Fort Entry & Guide', 800.00),
(2, 3, 'stay', 'Boutique Hotel Florence (4 Nights)', 600.00);

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `city_id` int(10) UNSIGNED DEFAULT NULL,
  `activity_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `favorites`
--

INSERT INTO `favorites` (`id`, `user_id`, `city_id`, `activity_id`, `created_at`) VALUES
(1, 3, 1, NULL, '2026-08-22 09:26:44'),
(2, 4, NULL, 2, '2026-08-22 09:26:44');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `city_id` int(10) UNSIGNED DEFAULT NULL,
  `activity_id` int(10) UNSIGNED DEFAULT NULL,
  `rating` decimal(2,1) NOT NULL,
  `review_text` text DEFAULT NULL,
  `status` enum('visible','hidden') DEFAULT 'visible',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `city_id`, `activity_id`, `rating`, `review_text`, `status`, `created_at`) VALUES
(1, 3, 1, NULL, 4.8, 'Incredible architecture and food!', 'visible', '2026-08-22 09:26:44'),
(2, 4, NULL, 2, 5.0, 'Breathtaking masterpieces, a must-visit art museum.', 'visible', '2026-08-22 09:26:44');

-- --------------------------------------------------------

--
-- Table structure for table `stops`
--

CREATE TABLE `stops` (
  `id` int(10) UNSIGNED NOT NULL,
  `trip_id` int(10) UNSIGNED NOT NULL,
  `city` varchar(120) NOT NULL,
  `country` varchar(120) DEFAULT NULL,
  `arrival_date` date DEFAULT NULL,
  `departure_date` date DEFAULT NULL,
  `position` int(10) UNSIGNED DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stops`
--

INSERT INTO `stops` (`id`, `trip_id`, `city`, `country`, `arrival_date`, `departure_date`, `position`) VALUES
(1, 2, 'Jaipur', 'India', '2026-11-01', '2026-11-04', 1),
(2, 3, 'Florence', 'Italy', '2026-12-05', '2026-12-09', 1);

-- --------------------------------------------------------

--
-- Table structure for table `trips`
--

CREATE TABLE `trips` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(160) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `destination` varchar(160) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `cover_photo` varchar(255) DEFAULT NULL,
  `budget` decimal(12,2) DEFAULT 0.00,
  `currency` varchar(10) DEFAULT 'INR',
  `status` enum('draft','planned','completed','cancelled') DEFAULT 'draft',
  `visibility` enum('private','public') DEFAULT 'private'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `trips`
--

INSERT INTO `trips` (`id`, `user_id`, `name`, `start_date`, `end_date`, `destination`, `description`, `created_at`, `cover_photo`, `budget`, `currency`, `status`, `visibility`) VALUES
(1, 1, 'La dolce vita', '2026-10-03', '2026-10-11', 'Amalfi Coast, Italy', 'A slow journey along the coast.', '2026-08-22 05:16:11', NULL, 0.00, 'INR', 'draft', 'private'),
(2, 3, 'Rajasthan Cultural Tour', '2026-11-01', '2026-11-07', 'Jaipur, India', 'Exploring royal palaces and local bazaars.', '2026-08-22 09:26:44', 'assets/img/rajasthan.jpg', 25000.00, 'INR', 'planned', 'public'),
(3, 4, 'Tuscan Wine & Art Escape', '2026-12-05', '2026-12-12', 'Florence, Italy', 'Art museums and countryside vineyard visits.', '2026-08-22 09:26:44', 'assets/img/tuscany.jpg', 1500.00, 'EUR', 'draft', 'private');

-- --------------------------------------------------------

--
-- Table structure for table `trip_collaborators`
--

CREATE TABLE `trip_collaborators` (
  `id` int(10) UNSIGNED NOT NULL,
  `trip_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `role` enum('viewer','editor') DEFAULT 'viewer',
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `trip_collaborators`
--

INSERT INTO `trip_collaborators` (`id`, `trip_id`, `user_id`, `role`, `status`, `created_at`) VALUES
(1, 2, 4, 'editor', 'accepted', '2026-08-22 09:26:44'),
(2, 3, 3, 'viewer', 'pending', '2026-08-22 09:26:44');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `age` int(11) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `language` varchar(30) DEFAULT 'English',
  `status` enum('active','blocked') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `created_at`, `age`, `address`, `city`, `state`, `language`, `status`) VALUES
(1, 'Alex Morgan', 'alex@example.com', '$2y$10$g97Z6n/Q1pCByUXf1dYWhulmUqcnhciezbX88taxx35GNRPwmE.RC', 'user', '2026-08-22 05:16:11', NULL, NULL, NULL, NULL, 'English', 'active'),
(2, 'GlobeTrotter Admin', 'admin@globetrotter.local', '$2y$10$aPTbDTXJuCh9yTcjDt5sNumA1UrT.XqnAUc0qU8FI0Kn4aeHp/LlW', 'admin', '2026-08-22 05:16:11', NULL, NULL, NULL, NULL, 'English', 'active'),
(3, 'Sarah Jenkins', 'sarah@example.com', '$2y$10$e8R5...hash3', 'user', '2026-08-22 09:26:44', 28, '742 Evergreen Terrace', 'Springfield', 'OR', 'English', 'active'),
(4, 'Rohan Mehta', 'rohan@example.com', '$2y$10$k9L2...hash4', 'user', '2026-08-22 09:26:44', 25, '12 Ring Road', 'Ahmedabad', 'Gujarat', 'English', 'active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_activities_stop` (`stop_id`);

--
-- Indexes for table `cities`
--
ALTER TABLE `cities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_expenses_trip` (`trip_id`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_fav_user` (`user_id`),
  ADD KEY `fk_fav_city` (`city_id`),
  ADD KEY `fk_fav_activity` (`activity_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_rev_user` (`user_id`),
  ADD KEY `fk_rev_city` (`city_id`),
  ADD KEY `fk_rev_activity` (`activity_id`);

--
-- Indexes for table `stops`
--
ALTER TABLE `stops`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_stops_trip` (`trip_id`);

--
-- Indexes for table `trips`
--
ALTER TABLE `trips`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_trips_user` (`user_id`);

--
-- Indexes for table `trip_collaborators`
--
ALTER TABLE `trip_collaborators`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_collab_trip` (`trip_id`),
  ADD KEY `fk_collab_user` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activities`
--
ALTER TABLE `activities`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `cities`
--
ALTER TABLE `cities`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `stops`
--
ALTER TABLE `stops`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `trips`
--
ALTER TABLE `trips`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `trip_collaborators`
--
ALTER TABLE `trip_collaborators`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activities`
--
ALTER TABLE `activities`
  ADD CONSTRAINT `fk_activities_stop` FOREIGN KEY (`stop_id`) REFERENCES `stops` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `expenses`
--
ALTER TABLE `expenses`
  ADD CONSTRAINT `fk_expenses_trip` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `fk_fav_activity` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_fav_city` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_fav_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `fk_rev_activity` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_rev_city` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_rev_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stops`
--
ALTER TABLE `stops`
  ADD CONSTRAINT `fk_stops_trip` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `trips`
--
ALTER TABLE `trips`
  ADD CONSTRAINT `fk_trips_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `trip_collaborators`
--
ALTER TABLE `trip_collaborators`
  ADD CONSTRAINT `fk_collab_trip` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_collab_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

