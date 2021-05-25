ALTER TABLE `spamdomain` ENGINE = InnoDB;
ALTER TABLE `spamip` ENGINE = InnoDB;
ALTER TABLE `spamnet` ENGINE = InnoDB;
ALTER TABLE `spamsender` ENGINE = InnoDB;
ALTER TABLE `spamusername` ENGINE = InnoDB;
ALTER TABLE `whitedomain` ENGINE = InnoDB;
ALTER TABLE `whiteip` ENGINE = InnoDB;
ALTER TABLE `whitenet` ENGINE = InnoDB;
ALTER TABLE `whitesender` ENGINE = InnoDB;
FLUSH TABLE `spamdomain`;
FLUSH TABLE `spamip`;
FLUSH TABLE `spamnet`;
FLUSH TABLE `spamusername`;
FLUSH TABLE `whitedomain`;
FLUSH TABLE `whiteip`;
FLUSH TABLE `whitenet`;
FLUSH TABLE `whitesender`;
ALTER DATABASE `rbl` DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `rbl`.`spamdomain` DEFAULT  CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `rbl`.`spamip` DEFAULT  CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `rbl`.`spamnet` DEFAULT  CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `rbl`.`spamsender` DEFAULT  CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `rbl`.`spamusername` DEFAULT  CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `rbl`.`whitedomain` DEFAULT  CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `rbl`.`whiteip` DEFAULT  CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `rbl`.`whitenet` DEFAULT  CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `rbl`.`whitesender` DEFAULT  CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE `spamsender` CHANGE `user` `user` VARCHAR(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unknown';
ALTER TABLE `spamsender` CHANGE `reason` `reason` TINYTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `spamnet` CHANGE `user` `user` VARCHAR(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unknown';
ALTER TABLE `spamnet` CHANGE `reason` `reason` TINYTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `spamip` CHANGE `user` `user` VARCHAR(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unknown';
ALTER TABLE `spamdomain` CHANGE `reason` `reason` TINYTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `spamusername` CHANGE `user` `user` VARCHAR(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unknown';
ALTER TABLE `spamusername` CHANGE `reason` `reason` TINYTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `whitedomain` CHANGE `user` `user` VARCHAR(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unknown';
ALTER TABLE `whitedomain` CHANGE `reason` `reason` TINYTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `whiteip` CHANGE `user` `user` VARCHAR(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unknown';
ALTER TABLE `whiteip` CHANGE `reason` `reason` TINYTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `whitenet` CHANGE `user` `user` VARCHAR(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unknown';
ALTER TABLE `whitenet` CHANGE `reason` `reason` TINYTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `whitesender` CHANGE `user` `user` VARCHAR(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unknown';
ALTER TABLE `whitesender` CHANGE `reason` `reason` TINYTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;

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

ALTER TABLE `spamhash`
  ADD PRIMARY KEY (`text`);
