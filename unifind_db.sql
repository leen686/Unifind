-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Apr 27, 2026 at 11:33 PM
-- Server version: 5.7.24
-- PHP Version: 8.2.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `unifind_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `administrators`
--

CREATE TABLE `administrators` (
  `adminID` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `permissions` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `administrators`
--

INSERT INTO `administrators` (`adminID`, `email`, `password`, `permissions`) VALUES
(1, 'admin@unifind.com', 'admin123', 'full');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `catID` int(11) NOT NULL,
  `catName` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`catID`, `catName`) VALUES
(3, 'Bag'),
(5, 'ID Card'),
(2, 'Keys'),
(6, 'Other'),
(4, 'Phone'),
(1, 'Wallet');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `reportID` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `description` text,
  `date` date NOT NULL,
  `location` varchar(150) DEFAULT NULL,
  `imageURL` varchar(255) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `userID` int(11) NOT NULL,
  `catID` int(11) NOT NULL,
  `adminID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`reportID`, `item_name`, `description`, `date`, `location`, `imageURL`, `phone`, `userID`, `catID`, `adminID`) VALUES
(1, 'Black Wallet', 'Found near the library study area.', '2026-03-21', 'Library', 'images/wallet.jpeg', '0500000002', 1, 1, NULL),
(2, 'Car Keys', 'Set of silver keys.', '2026-03-18', 'Parking Area', 'images/car key.jpeg', '0500000003', 2, 2, NULL),
(3, 'Backpack', 'Black backpack left in the hallway near classroom 204.', '2026-03-17', 'Building 5', 'images/backpack.webp', '0500000004', 3, 3, NULL),
(4, 'AirPods Case', 'White AirPods case found near the cafeteria.', '2026-03-16', 'Cafeteria', 'images/airpods.webp', '0500000005', 2, 6, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `saved_reports`
--

CREATE TABLE `saved_reports` (
  `userID` int(11) NOT NULL,
  `reportID` int(11) NOT NULL,
  `savedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `saved_reports`
--

INSERT INTO `saved_reports` (`userID`, `reportID`, `savedAt`) VALUES
(1, 1, '2026-04-28 02:20:55'),
(1, 2, '2026-04-28 02:20:55');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `userID` int(11) NOT NULL,
  `firstName` varchar(50) NOT NULL,
  `lastName` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`userID`, `firstName`, `lastName`, `email`, `password`, `phone`) VALUES
(1, 'Sara', 'Alqahtani', 'student1@ksu.edu.sa', '$2y$10$Mp3MTr1EUxFfFejP0mF5euc8P9dQ5QK6uQKJ2a42fJrP0Q3VQx2bS', '0551234567'),
(2, 'Nora', 'Alharbi', 'student2@ksu.edu.sa', '$2y$10$Mp3MTr1EUxFfFejP0mF5euc8P9dQ5QK6uQKJ2a42fJrP0Q3VQx2bS', '0559876543'),
(3, 'Layan', 'Alotaibi', 'student3@ksu.edu.sa', '$2y$10$Mp3MTr1EUxFfFejP0mF5euc8P9dQ5QK6uQKJ2a42fJrP0Q3VQx2bS', '0553332211');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `administrators`
--
ALTER TABLE `administrators`
  ADD PRIMARY KEY (`adminID`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`catID`),
  ADD UNIQUE KEY `catName` (`catName`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`reportID`),
  ADD KEY `userID` (`userID`),
  ADD KEY `catID` (`catID`),
  ADD KEY `adminID` (`adminID`);

--
-- Indexes for table `saved_reports`
--
ALTER TABLE `saved_reports`
  ADD PRIMARY KEY (`userID`,`reportID`),
  ADD KEY `reportID` (`reportID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`userID`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `administrators`
--
ALTER TABLE `administrators`
  MODIFY `adminID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `catID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `reportID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `userID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`) ON DELETE CASCADE,
  ADD CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`catID`) REFERENCES `categories` (`catID`),
  ADD CONSTRAINT `reports_ibfk_3` FOREIGN KEY (`adminID`) REFERENCES `administrators` (`adminID`) ON DELETE SET NULL;

--
-- Constraints for table `saved_reports`
--
ALTER TABLE `saved_reports`
  ADD CONSTRAINT `saved_reports_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`) ON DELETE CASCADE,
  ADD CONSTRAINT `saved_reports_ibfk_2` FOREIGN KEY (`reportID`) REFERENCES `reports` (`reportID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
