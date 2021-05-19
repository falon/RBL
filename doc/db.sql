-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 19, 2021 at 10:43 AM
-- Server version: 8.0.21
-- PHP Version: 7.4.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;

--
-- Database: `rbl`
--
CREATE DATABASE IF NOT EXISTS `rbl` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `rbl`;

-- --------------------------------------------------------

--
-- Table structure for table `spamdomain`
--

CREATE TABLE `spamdomain` (
  `domain` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `datemod` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `exp` timestamp NOT NULL DEFAULT '2038-01-19 02:14:07',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `nlist` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `user` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unknown',
  `reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELATIONSHIPS FOR TABLE `spamdomain`:
--

-- --------------------------------------------------------

--
-- Table structure for table `spamhash`
--

CREATE TABLE `spamhash` (
  `text` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `datemod` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `exp` timestamp NOT NULL DEFAULT '2038-01-19 02:14:07',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `nlist` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `user` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unknown',
  `reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELATIONSHIPS FOR TABLE `spamhash`:
--

-- --------------------------------------------------------

--
-- Table structure for table `spamip`
--

CREATE TABLE `spamip` (
  `ip` int UNSIGNED NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `datemod` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `exp` timestamp NOT NULL DEFAULT '2038-01-19 02:14:07',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `nlist` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `user` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unknown',
  `reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELATIONSHIPS FOR TABLE `spamip`:
--

-- --------------------------------------------------------

--
-- Table structure for table `spamnet`
--

CREATE TABLE `spamnet` (
  `network` int UNSIGNED NOT NULL,
  `netmask` int UNSIGNED NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `datemod` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `exp` timestamp NOT NULL DEFAULT '2038-01-19 02:14:07',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `nlist` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `user` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unknown',
  `reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELATIONSHIPS FOR TABLE `spamnet`:
--

-- --------------------------------------------------------

--
-- Table structure for table `spamsender`
--

CREATE TABLE `spamsender` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `datemod` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `exp` timestamp NOT NULL DEFAULT '2038-01-19 02:14:07',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `nlist` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `user` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unknown',
  `reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELATIONSHIPS FOR TABLE `spamsender`:
--

-- --------------------------------------------------------

--
-- Table structure for table `spamusername`
--

CREATE TABLE `spamusername` (
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `datemod` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `exp` timestamp NOT NULL DEFAULT '2038-01-19 02:14:07',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `nlist` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `user` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unknown',
  `reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELATIONSHIPS FOR TABLE `spamusername`:
--

-- --------------------------------------------------------

--
-- Table structure for table `whitedomain`
--

CREATE TABLE `whitedomain` (
  `domain` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `datemod` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `exp` timestamp NOT NULL DEFAULT '2038-01-19 02:14:07',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `nlist` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `user` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unknown',
  `reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELATIONSHIPS FOR TABLE `whitedomain`:
--

-- --------------------------------------------------------

--
-- Table structure for table `whiteip`
--

CREATE TABLE `whiteip` (
  `ip` int UNSIGNED NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `datemod` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `exp` timestamp NOT NULL DEFAULT '2038-01-19 02:14:07',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `nlist` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `user` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unknown',
  `reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELATIONSHIPS FOR TABLE `whiteip`:
--

-- --------------------------------------------------------

--
-- Table structure for table `whitenet`
--

CREATE TABLE `whitenet` (
  `network` int UNSIGNED NOT NULL,
  `netmask` int UNSIGNED NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `datemod` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `exp` timestamp NOT NULL DEFAULT '2038-01-19 02:14:07',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `nlist` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `user` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unknown',
  `reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELATIONSHIPS FOR TABLE `whitenet`:
--

-- --------------------------------------------------------

--
-- Table structure for table `whitesender`
--

CREATE TABLE `whitesender` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `datemod` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `exp` timestamp NOT NULL DEFAULT '2038-01-19 02:14:07',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `nlist` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `user` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unknown',
  `reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELATIONSHIPS FOR TABLE `whitesender`:
--

--
-- Indexes for dumped tables
--

--
-- Indexes for table `spamdomain`
--
ALTER TABLE `spamdomain`
  ADD PRIMARY KEY (`domain`);

--
-- Indexes for table `spamhash`
--
ALTER TABLE `spamhash`
  ADD PRIMARY KEY (`text`);

--
-- Indexes for table `spamip`
--
ALTER TABLE `spamip`
  ADD PRIMARY KEY (`ip`);

--
-- Indexes for table `spamnet`
--
ALTER TABLE `spamnet`
  ADD PRIMARY KEY (`network`,`netmask`);

--
-- Indexes for table `spamsender`
--
ALTER TABLE `spamsender`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `spamusername`
--
ALTER TABLE `spamusername`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `whitedomain`
--
ALTER TABLE `whitedomain`
  ADD PRIMARY KEY (`domain`);

--
-- Indexes for table `whiteip`
--
ALTER TABLE `whiteip`
  ADD PRIMARY KEY (`ip`);

--
-- Indexes for table `whitenet`
--
ALTER TABLE `whitenet`
  ADD PRIMARY KEY (`network`,`netmask`);

--
-- Indexes for table `whitesender`
--
ALTER TABLE `whitesender`
  ADD PRIMARY KEY (`email`);
COMMIT;

