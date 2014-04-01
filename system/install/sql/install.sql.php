DROP TABLE IF EXISTS `blacklist`;

CREATE TABLE `blacklist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(20) COLLATE utf8_bin DEFAULT NULL,
  `time` int(11) DEFAULT NULL,
  `hits` int(11) DEFAULT '0',
  `last` varbinary(12) DEFAULT NULL,
  `notes` text COLLATE utf8_bin,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
DROP TABLE IF EXISTS `cache`;

CREATE TABLE `cache` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `block` tinyint(1) DEFAULT '0',
  `path` varchar(80) COLLATE utf8_bin DEFAULT NULL,
  `data` longtext COLLATE utf8_bin,
  `expires` int(9) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

DROP TABLE IF EXISTS `dimage`;

CREATE TABLE `dimage` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `folder` varchar(60) COLLATE utf8_bin DEFAULT NULL,
  `filename` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `orginal` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `owner` bigint(20) DEFAULT NULL,
  `catagory` int(11) DEFAULT NULL,
  `sub_cat1` int(11) DEFAULT NULL,
  `sub_cat2` int(11) DEFAULT NULL,
  `sub_cat3` int(11) DEFAULT NULL,
  `title` text COLLATE utf8_bin,
  `desc` text COLLATE utf8_bin,
  `upload_date` int(11) DEFAULT NULL,
  `display` varchar(8) COLLATE utf8_bin DEFAULT 'live',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=510 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

DROP TABLE IF EXISTS `dimage_catagory`;

CREATE TABLE `dimage_catagory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` text COLLATE utf8_bin,
  `title_pretty` text COLLATE utf8_bin,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

DROP TABLE IF EXISTS `fb_user`;

CREATE TABLE `fb_user` (
  `uid` bigint(20) NOT NULL AUTO_INCREMENT,
  `lid` bigint(20) DEFAULT '0',
  `id` bigint(20) DEFAULT '0',
  `name` text COLLATE utf8_bin,
  `first_name` text COLLATE utf8_bin,
  `last_name` text COLLATE utf8_bin,
  `link` text COLLATE utf8_bin,
  `username` text COLLATE utf8_bin,
  `gender` varchar(11) COLLATE utf8_bin DEFAULT NULL,
  `email` text COLLATE utf8_bin,
  `timezone` int(3) DEFAULT NULL,
  `locale` varchar(8) COLLATE utf8_bin DEFAULT NULL,
  `verified` int(2) DEFAULT NULL,
  `updated_time` varchar(30) COLLATE utf8_bin DEFAULT NULL,
  `updated_time_epoch` int(11) DEFAULT NULL,
  `init_ip` varchar(16) COLLATE utf8_bin DEFAULT NULL,
  `karma` int(11) DEFAULT '100',
  `profile_image` int(255) DEFAULT '0',
  `bio` text COLLATE utf8_bin,
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM AUTO_INCREMENT=310 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

DROP TABLE IF EXISTS `groups`;

CREATE TABLE `groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `modules` text,
  `name` varchar(60) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

insert  into `groups`(`id`,`modules`,`name`) values (1,'module\\editor,page\\login,page\\register,page\\zpage,page\\resetpass','public'),(2,'page\\login,page\\profile,page\\zpage','loggedin'),(10,'module\\media,module\\admin_bar,module\\admin_page,module\\admin_permissions,module\\editor2,module\\editor_raw,page\\admin','Editors');

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
  `has_sidebar` varchar(3) COLLATE utf8_bin DEFAULT 'no',
  `place_holder_only` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

DROP TABLE IF EXISTS `pimage`;

CREATE TABLE `pimage` (
  `zone` int(11) NOT NULL,
  `image` int(11) DEFAULT NULL,
  PRIMARY KEY (`zone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

DROP TABLE IF EXISTS `site_debug`;

CREATE TABLE `site_debug` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `stacktrace1` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `stacktrace2` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `stacktrace3` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `stacktrace4` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `msg` text COLLATE utf8_bin,
  `time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

DROP TABLE IF EXISTS `usercookie_cross`;

CREATE TABLE `usercookie_cross` (
  `id` bigint(30) NOT NULL AUTO_INCREMENT,
  `userid` bigint(30) DEFAULT NULL,
  `session` varchar(20) COLLATE utf8_bin DEFAULT NULL,
  `cookie` varchar(20) COLLATE utf8_bin DEFAULT NULL,
  `ip` varchar(16) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

DROP TABLE IF EXISTS `usermod`;

CREATE TABLE `usermod` (
  `mid` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `data` text COLLATE utf8_bin
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `password` varchar(32) COLLATE utf8_bin DEFAULT NULL,
  `groups` text COLLATE utf8_bin,
  `super_user` varchar(3) COLLATE utf8_bin DEFAULT 'no',
  `mod_user` varchar(3) COLLATE utf8_bin DEFAULT 'no',
  `status` varchar(11) COLLATE utf8_bin DEFAULT 'active',
  `salt` varchar(25) COLLATE utf8_bin DEFAULT NULL,
  `activation_code` varchar(7) COLLATE utf8_bin DEFAULT '0',
  `date_create` varchar(10) COLLATE utf8_bin DEFAULT NULL,
  `last_ip` varchar(12) COLLATE utf8_bin DEFAULT NULL,
  `reset_key` varchar(10) COLLATE utf8_bin DEFAULT NULL,
  `reset_request_ip` varchar(12) COLLATE utf8_bin DEFAULT NULL,
  `reset_request_time` int(8) DEFAULT NULL,
  `auth_provider` varchar(60) COLLATE utf8_bin DEFAULT 'site',
  `auth_id` varchar(200) COLLATE utf8_bin DEFAULT '0',
  `allow_file_upload` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;