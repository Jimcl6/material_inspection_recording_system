-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 08, 2026 at 03:08 AM
-- Server version: 10.1.38-MariaDB
-- PHP Version: 5.6.40

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `fc_1_data_db_testing`
--

-- --------------------------------------------------------

--
-- Table structure for table `productionbatches`
--

CREATE TABLE `productionbatches` (
  `BatchID` int(11) NOT NULL,
  `ItemName` varchar(50) DEFAULT NULL,
  `ItemCode` varchar(50) DEFAULT NULL,
  `ProductionDate` date NOT NULL,
  `LetterCode` varchar(5) NOT NULL,
  `QRCode` varchar(20) NOT NULL,
  `MaterialLotNumber` varchar(50) NOT NULL,
  `ProduceQty` int(11) NOT NULL,
  `JobNumber` varchar(50) NOT NULL,
  `TotalQty` int(11) NOT NULL,
  `Remarks` text,
  `CreatedAt` datetime DEFAULT CURRENT_TIMESTAMP,
  `UpdatedAt` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `productionbatches`
--

INSERT INTO `productionbatches` (`BatchID`, `ItemName`, `ItemCode`, `ProductionDate`, `LetterCode`, `QRCode`, `MaterialLotNumber`, `ProduceQty`, `JobNumber`, `TotalQty`, `Remarks`, `CreatedAt`, `UpdatedAt`) VALUES
(1, NULL, NULL, '2026-01-07', 'A', '12312', '31231', 12, '31232', 0, NULL, '2026-01-07 08:27:30', '2026-01-07 08:27:30'),
(2, NULL, NULL, '2026-01-07', 'B', '1312', '1331', 1, '423232', 0, 'sample', '2026-01-07 08:32:16', '2026-01-07 08:32:16'),
(3, NULL, NULL, '2026-01-07', 'C', '5235', '523452', 1, '363645', 0, NULL, '2026-01-07 08:41:32', '2026-01-07 08:41:32');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `productionbatches`
--
ALTER TABLE `productionbatches`
  ADD PRIMARY KEY (`BatchID`),
  ADD KEY `IDX_Production_QR_Lot` (`QRCode`,`MaterialLotNumber`),
  ADD KEY `IDX_Production_Date` (`ProductionDate`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `productionbatches`
--
ALTER TABLE `productionbatches`
  MODIFY `BatchID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
