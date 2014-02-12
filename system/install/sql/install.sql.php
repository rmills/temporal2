DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` ( `id` int(11) NOT NULL AUTO_INCREMENT, `block` tinyint(1) DEFAULT '0', `path` varchar(80) COLLATE utf8_bin DEFAULT NULL, `data` longtext COLLATE utf8_bin, `expires` int(9) DEFAULT NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
DROP TABLE IF EXISTS `groups`;
CREATE TABLE `groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `modules` text,
  `name` varchar(60) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;
insert  into `groups`(`id`,`modules`,`name`) values (1,'module\\editor,page\\login,page\\register,page\\zpage,page\\resetpass','public'),(2,'page\\login,page\\profile,page\\zpage','loggedin');
DROP TABLE IF EXISTS `images`;
CREATE TABLE `images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `file` varchar(20) COLLATE utf8_bin DEFAULT NULL,
  `orginal` text COLLATE utf8_bin,
  `name` text COLLATE utf8_bin,
  `folder` varchar(60) COLLATE utf8_bin DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
DROP TABLE IF EXISTS `pages`;
CREATE TABLE `pages` (
  `pid` int(11) NOT NULL AUTO_INCREMENT,
  `menu_title` varchar(200) COLLATE utf8_bin DEFAULT NULL,
  `title` varchar(200) COLLATE utf8_bin DEFAULT NULL,
  `parent` int(11) DEFAULT '0',
  `weight` int(11) DEFAULT '100',
  `published` varchar(11) COLLATE utf8_bin DEFAULT 'no',
  `meta_description` text COLLATE utf8_bin,
  `meta_keywords` text COLLATE utf8_bin,
  `url` varchar(200) COLLATE utf8_bin DEFAULT NULL,
  `template` varchar(200) COLLATE utf8_bin DEFAULT 'default.html',
  `z1` int(11) DEFAULT NULL,
  `z2` int(11) DEFAULT NULL,
  `z3` int(11) DEFAULT NULL,
  `z4` int(11) DEFAULT NULL,
  `z5` int(11) DEFAULT NULL,
  `z6` int(11) DEFAULT NULL,
  `z7` int(11) DEFAULT NULL,
  `z8` int(11) DEFAULT NULL,
  `z9` int(11) DEFAULT NULL,
  `z10` int(11) DEFAULT NULL,
  `z11` int(11) DEFAULT NULL,
  `z12` int(11) DEFAULT NULL,
  `z13` int(11) DEFAULT NULL,
  `z14` int(11) DEFAULT NULL,
  `z15` int(11) DEFAULT NULL,
  `z16` int(11) DEFAULT NULL,
  `z17` int(11) DEFAULT NULL,
  `z18` int(11) DEFAULT NULL,
  `z19` int(11) DEFAULT NULL,
  `z20` int(11) DEFAULT NULL,
  `status` varchar(10) COLLATE utf8_bin DEFAULT 'active',
  PRIMARY KEY (`pid`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
insert  into `pages`(`pid`,`menu_title`,`title`,`parent`,`weight`,`published`,`meta_description`,`meta_keywords`,`url`,`template`,`z1`,`z2`,`z3`,`z4`,`z5`,`z6`,`z7`,`z8`,`z9`,`z10`,`z11`,`z12`,`z13`,`z14`,`z15`,`z16`,`z17`,`z18`,`z19`,`z20`,`status`) values (1,'Home','Home',0,1,'no','This is the meta description. It helps google.','sample, temporal default, anything you want','home','home.html',1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,'active'),(23,'Inside Example','Inside Example',0,100,'no',NULL,NULL,'inside','inside.html',7,8,9,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,'active');
DROP TABLE IF EXISTS `pimage`;
CREATE TABLE `pimage` (
  `zone` int(11) NOT NULL,
  `image` int(11) DEFAULT NULL,
  PRIMARY KEY (`zone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
DROP TABLE IF EXISTS `usermod`;
CREATE TABLE `usermod` (
  `mid` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `data` text COLLATE utf8_bin
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
insert  into `usermod`(`mid`,`uid`,`data`) values ('Usermod-Avatar',6,'b:0;'),('Usermod-Website',6,'b:0;'),('Usermod-Avatar',1,'b:0;'),('Usermod-Website',1,'b:0;');
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `password` varchar(32) COLLATE utf8_bin DEFAULT NULL,
  `groups` text COLLATE utf8_bin,
  `super_user` varchar(3) COLLATE utf8_bin DEFAULT NULL,
  `status` varchar(11) COLLATE utf8_bin DEFAULT 'active',
  `salt` varchar(25) COLLATE utf8_bin DEFAULT NULL,
  `activation_code` varchar(7) COLLATE utf8_bin DEFAULT '0',
  `date_create` varchar(10) COLLATE utf8_bin DEFAULT NULL,
  `last_ip` varchar(12) COLLATE utf8_bin DEFAULT NULL,
  `reset_key` varchar(10) COLLATE utf8_bin DEFAULT NULL,
  `reset_request_ip` varchar(12) COLLATE utf8_bin DEFAULT NULL,
  `reset_request_time` int(8) DEFAULT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
insert  into `users`(`uid`,`email`,`name`,`password`,`groups`,`super_user`,`status`,`salt`,`activation_code`,`date_create`,`last_ip`,`reset_key`,`reset_request_ip`,`reset_request_time`) values (1,'guest@domain.com','guest',NULL,'1','no','active',NULL,'0',NULL,NULL,NULL,NULL,NULL);
DROP TABLE IF EXISTS `zones`;
CREATE TABLE `zones` (
  `z_id` int(11) NOT NULL AUTO_INCREMENT,
  `z_data` text COLLATE utf8_bin,
  `z_date` int(11) DEFAULT NULL,
  `z_pid` int(11) DEFAULT NULL,
  `z_parent` varchar(5) COLLATE utf8_bin DEFAULT NULL,
  `z_type` varchar(10) COLLATE utf8_bin DEFAULT 'new',
  `z_user` int(11) DEFAULT NULL,
  PRIMARY KEY (`z_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
insert  into `zones`(`z_id`,`z_data`,`z_date`,`z_pid`,`z_parent`,`z_type`,`z_user`) values (1,'%3Cp%3EThis+zone+is+blank.%3C%2Fp%3E',1361828335,1,'z1','new',6),(2,'%3Cp%3EThis+zone+is+blank.%3C%2Fp%3E',1361828337,1,'z2','new',6),(3,'%3Cp%3EThis+zone+is+blank.%3C%2Fp%3E',1361828341,1,'z3','new',6),(4,'%3Cp%3EThis+zone+is+blank.%3C%2Fp%3E',1361828347,22,'z1','new',6),(5,'%3Cp%3EThis+zone+is+blank.%3C%2Fp%3E',1361828355,22,'z3','new',6),(6,'%3Cp%3EThis+zone+is+blank.%3C%2Fp%3E',1361828358,22,'z2','new',6),(7,'%3Cp%3EThis+is+a+blank+zone.%3C%2Fp%3E',1361828453,23,'z1','new',6),(8,'%3Cp%3EThis+is+a+blank+zone.%3C%2Fp%3E',1361828454,23,'z2','new',6),(9,'%3Cp%3EThis+is+a+blank+zone.%3C%2Fp%3E',1361828457,23,'z3','new',6);
