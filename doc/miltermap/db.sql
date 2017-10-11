SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `miltermap` (network)
--
CREATE DATABASE IF NOT EXISTS `miltermap` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin;
USE `miltermap`;

CREATE TABLE `config` (
  `name` varchar(30) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `conf` tinytext COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO `config` (`name`, `conf`) VALUES
('DISABLE ALL', 'DISABLE'),
('DKIM', '{\r\n\tinet:localhost:8891,\r\n\tdefault_action=accept\r\n}'),
('DMARC', '{\r\n\tinet:localhost:8893,\r\n\tdefault_action=accept\r\n}');

CREATE TABLE `milt` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(30) CHARACTER SET ascii COLLATE ascii_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE `net` (
  `idmilt` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `network` int(4) UNSIGNED NOT NULL,
  `netmask` int(4) UNSIGNED NOT NULL,
  `date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `datemod` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `exp` timestamp NOT NULL DEFAULT '2038-01-19 03:14:07',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `nlist` tinyint(3) UNSIGNED NOT NULL DEFAULT '1',
  `user` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT 'unknown',
  `reason` tinytext COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


ALTER TABLE `config`
  ADD PRIMARY KEY (`name`) USING BTREE;

ALTER TABLE `milt`
  ADD PRIMARY KEY (`id`,`name`) USING BTREE,
  ADD KEY `miltname` (`name`);

ALTER TABLE `net`
  ADD PRIMARY KEY (`idmilt`,`network`,`netmask`);

ALTER TABLE `milt`
  ADD CONSTRAINT `miltname` FOREIGN KEY (`name`) REFERENCES `config` (`name`) ON DELETE CASCADE,
  ADD CONSTRAINT `miltid` FOREIGN KEY (`id`) REFERENCES `net` (`idmilt`) ON DELETE CASCADE;

--
-- Database: `milteripmap` (ip)
--
CREATE DATABASE IF NOT EXISTS `milteripmap` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin;
USE `milteripmap`;

CREATE TABLE `config` (
  `name` varchar(30) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `conf` tinytext COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`name`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO `config` (`name`, `conf`) VALUES
('DISABLE ALL', 'DISABLE'),
('DKIM', '{\r\n\tinet:localhost:8891,\r\n\tdefault_action=accept\r\n}'),
('DMARC', '{\r\n\tinet:localhost:8893,\r\n\tdefault_action=accept\r\n}');

CREATE TABLE `milt` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(30) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  PRIMARY KEY (`id`,`name`) USING BTREE,
  KEY `miltname` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE `ips` (
  `idmilt` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip` int(4) unsigned NOT NULL,
  `date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `datemod` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `exp` timestamp NOT NULL DEFAULT '2038-01-19 03:14:07',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `nlist` tinyint(3) UNSIGNED NOT NULL DEFAULT '1',
  `user` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT 'unknown',
  `reason` tinytext COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`idmilt`,`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


ALTER TABLE `milt`
  ADD CONSTRAINT `miltname` FOREIGN KEY (`name`) REFERENCES `config` (`name`) ON DELETE CASCADE,
  ADD CONSTRAINT `miltid` FOREIGN KEY (`id`) REFERENCES `ips` (`idmilt`) ON DELETE CASCADE;
