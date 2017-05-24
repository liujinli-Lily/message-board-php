# Host: localhost  (Version: 5.5.47)
# Date: 2017-05-24 16:18:15
# Generator: MySQL-Front 5.3  (Build 4.234)

/*!40101 SET NAMES utf8 */;

#
# Structure for table "adminuser"
#

CREATE TABLE `adminuser` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `username` char(16) NOT NULL DEFAULT '',
  `password` varchar(60) NOT NULL,
  `mark` varchar(50) DEFAULT NULL,
  `createtime` int(10) DEFAULT NULL,
  `lasttime` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

#
# Data for table "adminuser"
#

INSERT INTO `adminuser` VALUES (1,'admin','123456',NULL,NULL,NULL);

#
# Structure for table "guestbook"
#

CREATE TABLE `guestbook` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `nickname` char(16) NOT NULL DEFAULT '',
  `email` varchar(60) DEFAULT NULL,
  `content` text NOT NULL,
  `createtime` int(10) unsigned NOT NULL DEFAULT '0',
  `reply` text,
  `replytime` int(10) unsigned DEFAULT NULL,
  `face` tinyint(2) unsigned NOT NULL DEFAULT '1',
  `qq` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;

#
# Data for table "guestbook"
#

INSERT INTO `guestbook` VALUES (1,'水电费','12345667@qq.com','当上了飞机耍大牌风建瓯市当上了飞机耍大牌风建瓯市当上了飞机耍大牌风建瓯市当上了飞机耍大牌风建瓯市',0,NULL,NULL,1,NULL),(2,'werewr','wrwe','weretre',1495531953,NULL,NULL,1,NULL),(3,'werewr','wrwe','weretre',1495531958,NULL,NULL,1,NULL),(4,'ertd','awrsd','wtretgrsd',1495532084,NULL,NULL,1,NULL),(5,'ertd','awrsd','wtretgrsd',1495532099,NULL,NULL,1,NULL),(6,'ertd','awrsd','wtretgrsd',1495532110,NULL,NULL,1,NULL),(7,'ertd','awrsd','wtretgrsd',1495532122,NULL,NULL,1,NULL),(8,'drftg','ert','56',1495532132,NULL,NULL,1,NULL),(9,'ertd','awrsd','wtretgrsd',1495532150,NULL,NULL,1,NULL),(10,'1111','2222','34444',1495532159,NULL,NULL,1,NULL),(11,'1111','2222','34444',1495532167,NULL,NULL,1,NULL),(12,'1111','2222','34444',1495532195,NULL,NULL,1,NULL),(13,'','12342','435646',1495591364,NULL,NULL,0,NULL),(15,'56','234','如风达告诉对方',1495591901,NULL,NULL,14,NULL),(16,'2342','123','玩儿',1495592921,NULL,NULL,4,NULL),(17,'踢踢','333','二维',1495593601,NULL,NULL,4,NULL),(18,'45','546','56 你',1495593762,NULL,NULL,7,NULL),(19,'好好','555','是谁说',1495594044,NULL,NULL,7,NULL),(20,'也有','66','呃呃呃',1495594090,NULL,NULL,13,NULL),(21,'他','56','容易',1495594759,NULL,NULL,14,NULL),(22,'体育','547','开嗯嗯扩扩扩扩扩',1495594861,NULL,NULL,12,NULL);
