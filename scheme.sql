SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


CREATE TABLE IF NOT EXISTS `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `steamid` varchar(32) NOT NULL,
  `active` int(11) NOT NULL DEFAULT '0',
  `superuser` int(11) NOT NULL,
  `quizlist` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

CREATE TABLE IF NOT EXISTS `logs` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `steamid` varchar(32) CHARACTER SET utf8 NOT NULL,
  `quiz_id` int(8) NOT NULL,
  `quest_id` int(8) NOT NULL,
  `answer` varchar(64) CHARACTER SET utf8 NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7075 ;

CREATE TABLE IF NOT EXISTS `questions` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `quiz_id` int(11) NOT NULL,
  `quest` varchar(256) NOT NULL,
  `imgurl` varchar(256) NOT NULL,
  `answer` varchar(64) NOT NULL,
  `status` int(1) NOT NULL,
  `altanswer` varchar(64) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=34 ;

CREATE TABLE IF NOT EXISTS `quiz` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `hash` varchar(128) NOT NULL,
  `title` varchar(64) NOT NULL,
  `descr` varchar(256) NOT NULL,
  `result` varchar(256) NOT NULL,
  `enddate` datetime NOT NULL,
  `status` int(8) NOT NULL,
  `created` datetime NOT NULL,
  `owner` varchar(32) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `id_2` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=17 ;

CREATE TABLE IF NOT EXISTS `records` (
  `steamid` varchar(32) NOT NULL,
  `username` varchar(64) NOT NULL,
  `date` datetime NOT NULL,
  `entered` datetime NOT NULL,
  `quiz_id` int(8) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `finished` int(1) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=262 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
