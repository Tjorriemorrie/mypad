-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               5.5.27 - MySQL Community Server (GPL)
-- Server OS:                    Win32
-- HeidiSQL Version:             8.0.0.4396
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping structure for table mypad.albums
DROP TABLE IF EXISTS `albums`;
CREATE TABLE IF NOT EXISTS `albums` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `artist_id` bigint(20) DEFAULT NULL,
  `createdAt` datetime NOT NULL,
  `modifiedAt` datetime DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `releasedAt` date DEFAULT NULL,
  `slots` smallint(6) DEFAULT NULL,
  `rated` smallint(6) NOT NULL,
  `rating` double DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_F4E2474FB7970CF8` (`artist_id`),
  CONSTRAINT `albums_ibfk_1` FOREIGN KEY (`artist_id`) REFERENCES `artists` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.


-- Dumping structure for table mypad.artists
DROP TABLE IF EXISTS `artists`;
CREATE TABLE IF NOT EXISTS `artists` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `createdAt` datetime NOT NULL,
  `modifiedAt` datetime DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `rated` smallint(6) NOT NULL,
  `rating` double DEFAULT NULL,
  `similared` smallint(6) NOT NULL,
  `similarAt` datetime DEFAULT NULL,
  `fullCounter` smallint(6) NOT NULL,
  `fullAt` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_68D3801E5E237E06` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.


-- Dumping structure for table mypad.feelings
DROP TABLE IF EXISTS `feelings`;
CREATE TABLE IF NOT EXISTS `feelings` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `createdAt` datetime NOT NULL,
  `modifiedAt` datetime DEFAULT NULL,
  `moodGood_id` bigint(20) DEFAULT NULL,
  `moodBad_id` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_490492F74978BF88` (`moodGood_id`),
  KEY `IDX_490492F7F9FDFACE` (`moodBad_id`),
  CONSTRAINT `feelings_ibfk_1` FOREIGN KEY (`moodGood_id`) REFERENCES `moods` (`id`),
  CONSTRAINT `feelings_ibfk_2` FOREIGN KEY (`moodBad_id`) REFERENCES `moods` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.


-- Dumping structure for table mypad.moods
DROP TABLE IF EXISTS `moods`;
CREATE TABLE IF NOT EXISTS `moods` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `createdAt` datetime NOT NULL,
  `modifiedAt` datetime DEFAULT NULL,
  `name` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.


-- Dumping structure for table mypad.ratings
DROP TABLE IF EXISTS `ratings`;
CREATE TABLE IF NOT EXISTS `ratings` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `winner_id` bigint(20) DEFAULT NULL,
  `loser_id` bigint(20) DEFAULT NULL,
  `createdAt` datetime NOT NULL,
  `modifiedAt` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_CEB607C95DFCD4B8` (`winner_id`),
  KEY `IDX_CEB607C91BCAA5F6` (`loser_id`),
  CONSTRAINT `ratings_ibfk_1` FOREIGN KEY (`winner_id`) REFERENCES `songs` (`id`),
  CONSTRAINT `ratings_ibfk_2` FOREIGN KEY (`loser_id`) REFERENCES `songs` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.


-- Dumping structure for table mypad.similars
DROP TABLE IF EXISTS `similars`;
CREATE TABLE IF NOT EXISTS `similars` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `createdAt` datetime NOT NULL,
  `modifiedAt` datetime DEFAULT NULL,
  `artistMain_id` bigint(20) DEFAULT NULL,
  `artistGood_id` bigint(20) DEFAULT NULL,
  `artistBad_id` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_957B9BCAA6F275EB` (`artistMain_id`),
  KEY `IDX_957B9BCAD8755E11` (`artistGood_id`),
  KEY `IDX_957B9BCA9070F480` (`artistBad_id`),
  CONSTRAINT `similars_ibfk_1` FOREIGN KEY (`artistMain_id`) REFERENCES `artists` (`id`),
  CONSTRAINT `similars_ibfk_2` FOREIGN KEY (`artistGood_id`) REFERENCES `artists` (`id`),
  CONSTRAINT `similars_ibfk_3` FOREIGN KEY (`artistBad_id`) REFERENCES `artists` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.


-- Dumping structure for table mypad.songs
DROP TABLE IF EXISTS `songs`;
CREATE TABLE IF NOT EXISTS `songs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `artist_id` bigint(20) DEFAULT NULL,
  `album_id` bigint(20) DEFAULT NULL,
  `codec` varchar(3) NOT NULL,
  `status` smallint(6) NOT NULL,
  `playcount` smallint(6) NOT NULL,
  `playedAt` datetime DEFAULT NULL,
  `track` smallint(6) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `rating` double DEFAULT NULL,
  `rated` smallint(6) NOT NULL,
  `ratedAt` datetime DEFAULT NULL,
  `priority` double NOT NULL,
  `path` varchar(255) NOT NULL,
  `createdAt` datetime NOT NULL,
  `modifiedAt` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_BAECB19BB548B0F` (`path`),
  KEY `IDX_BAECB19BB7970CF8` (`artist_id`),
  KEY `IDX_BAECB19B1137ABCF` (`album_id`),
  CONSTRAINT `songs_ibfk_1` FOREIGN KEY (`artist_id`) REFERENCES `artists` (`id`),
  CONSTRAINT `songs_ibfk_2` FOREIGN KEY (`album_id`) REFERENCES `albums` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
