

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";



DROP TABLE IF EXISTS `attention`;
CREATE TABLE IF NOT EXISTS `attention` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL,
  `eid` int(10) NOT NULL,
  `time` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;



DROP TABLE IF EXISTS `comment`;
CREATE TABLE IF NOT EXISTS `comment` (
  `cid` int(10) NOT NULL AUTO_INCREMENT,
  `eid` int(10) NOT NULL,
  `comment` varchar(255) CHARACTER SET utf8 NOT NULL,
  `user` varchar(255) CHARACTER SET utf8 NOT NULL,
  `mtime` int(10) NOT NULL,
  PRIMARY KEY (`cid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=12 ;



DROP TABLE IF EXISTS `event`;
CREATE TABLE IF NOT EXISTS `event` (
  `eid` int(10) NOT NULL AUTO_INCREMENT,
  `subject` varchar(255) NOT NULL,
  `etypeid` tinyint(4) NOT NULL,
  `level` tinyint(4) NOT NULL,
  `createtime` int(10) NOT NULL,
  `solvetime` int(10) NOT NULL,
  `closetime` int(10) NOT NULL,
  `affecttime` int(10) NOT NULL,
  `fuser` varchar(255) NOT NULL,
  `islock` tinyint(1) NOT NULL,
  `division` tinyint(4) NOT NULL,
  `who` varchar(255) NOT NULL COMMENT '责任人',
  `stypeid` tinyint(4) NOT NULL DEFAULT '1',
  `content` text NOT NULL,
  `description` varchar(255) NOT NULL,
  `affect` varchar(255) NOT NULL,
  `tomail` varchar(255) NOT NULL COMMENT '通知给谁',
  `view_count` int(10) NOT NULL,
  `summary` text NOT NULL,
  PRIMARY KEY (`eid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=265 ;



DROP TABLE IF EXISTS `mailgroup`;
CREATE TABLE IF NOT EXISTS `mailgroup` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL,
  `gname` varchar(255) NOT NULL,
  `mail_arr` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;



DROP TABLE IF EXISTS `measure`;
CREATE TABLE IF NOT EXISTS `measure` (
  `mid` int(10) NOT NULL AUTO_INCREMENT,
  `eid` int(10) NOT NULL,
  `measure` varchar(255) NOT NULL,
  `muser` varchar(255) NOT NULL,
  `mtime` int(10) NOT NULL,
  `status` tinyint(4) NOT NULL,
  PRIMARY KEY (`mid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;



DROP TABLE IF EXISTS `report`;
CREATE TABLE IF NOT EXISTS `report` (
  `eid` int(10) NOT NULL,
  `r_user` varchar(255) NOT NULL,
  `r_division` tinyint(4) NOT NULL,
  `content` text NOT NULL,
  `measure` text NOT NULL,
  `r_time` int(10) NOT NULL,
  PRIMARY KEY (`eid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



DROP TABLE IF EXISTS `schedule`;
CREATE TABLE IF NOT EXISTS `schedule` (
  `sid` int(10) NOT NULL AUTO_INCREMENT,
  `eid` int(10) NOT NULL,
  `stypeid` tinyint(4) NOT NULL,
  `s_subject` varchar(255) NOT NULL,
  `s_user` varchar(255) NOT NULL,
  `s_division` tinyint(4) NOT NULL,
  `s_time` int(10) NOT NULL,
  PRIMARY KEY (`sid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;



DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `realname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=386 ;


