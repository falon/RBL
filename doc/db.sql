-- phpMyAdmin SQL Dump
-- version 2.11.11.3
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generato il: 03 Feb, 2015 at 12:00 PM
-- Versione MySQL: 5.1.58
-- Versione PHP: 5.2.17

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `rbl`
--
CREATE DATABASE `rbl` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin;
USE `rbl`;

-- --------------------------------------------------------

--
-- Struttura della tabella `spamdomain`
--

CREATE TABLE IF NOT EXISTS `spamdomain` (
  `domain` varchar(255) COLLATE utf8_bin NOT NULL,
  `date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `datemod` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `exp` timestamp NOT NULL DEFAULT '2038-01-19 03:14:07',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `nlist` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `user` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT 'unknown',
  `reason` tinytext CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  PRIMARY KEY (`domain`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Struttura della tabella `spamip`
--

CREATE TABLE IF NOT EXISTS `spamip` (
  `ip` int(4) unsigned NOT NULL,
  `date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `datemod` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `exp` timestamp NOT NULL DEFAULT '2038-01-19 03:14:07',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `nlist` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `user` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT 'unknown',
  `reason` tinytext CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  PRIMARY KEY (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Struttura della tabella `spamnet`
--

CREATE TABLE IF NOT EXISTS `spamnet` (
  `network` int(4) unsigned NOT NULL,
  `netmask` int(4) unsigned NOT NULL,
  `date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `datemod` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `exp` timestamp NOT NULL DEFAULT '2038-01-19 03:14:07',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `nlist` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `user` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT 'unknown',
  `reason` tinytext CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  PRIMARY KEY (`network`,`netmask`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Struttura della tabella `spamsender`
--

CREATE TABLE IF NOT EXISTS `spamsender` (
  `email` varchar(255) COLLATE utf8_bin NOT NULL,
  `date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `datemod` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `exp` timestamp NOT NULL DEFAULT '2038-01-19 03:14:07',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `nlist` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `user` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT 'unknown',
  `reason` tinytext CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  PRIMARY KEY (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Struttura della tabella `whitedomain`
--

CREATE TABLE IF NOT EXISTS `whitedomain` (
  `domain` varchar(255) COLLATE utf8_bin NOT NULL,
  `date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `datemod` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `exp` timestamp NOT NULL DEFAULT '2038-01-19 03:14:07',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `nlist` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `user` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT 'unknown',
  `reason` tinytext CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  PRIMARY KEY (`domain`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Struttura della tabella `whiteip`
--

CREATE TABLE IF NOT EXISTS `whiteip` (
  `ip` int(4) unsigned NOT NULL,
  `date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `datemod` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `exp` timestamp NOT NULL DEFAULT '2038-01-19 03:14:07',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `nlist` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `user` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT 'unknown',
  `reason` tinytext CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  PRIMARY KEY (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Struttura della tabella `whitenet`
--

CREATE TABLE IF NOT EXISTS `whitenet` (
  `network` int(4) unsigned NOT NULL,
  `netmask` int(4) unsigned NOT NULL,
  `date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `datemod` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `exp` timestamp NOT NULL DEFAULT '2038-01-19 03:14:07',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `nlist` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `user` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT 'unknown',
  `reason` tinytext CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  PRIMARY KEY (`network`,`netmask`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Struttura della tabella `whitesender`
--

CREATE TABLE IF NOT EXISTS `whitesender` (
  `email` varchar(255) COLLATE utf8_bin NOT NULL,
  `date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `datemod` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `exp` timestamp NOT NULL DEFAULT '2038-01-19 03:14:07',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `nlist` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `user` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT 'unknown',
  `reason` tinytext CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  PRIMARY KEY (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Struttura della tabella `spamusername`
-- (users abused on SMTP Auth)
--

CREATE TABLE IF NOT EXISTS `spamusername` (
  `username` varchar(255) COLLATE utf8_bin NOT NULL,
  `date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `datemod` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `exp` timestamp NOT NULL DEFAULT '2038-01-19 03:14:07',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `nlist` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `user` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT 'unknown',
  `reason` tinytext CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  PRIMARY KEY (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
