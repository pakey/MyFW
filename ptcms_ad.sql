# Host: localhost  (Version: 5.5.38)
# Date: 2014-10-10 08:45:08
# Generator: MySQL-Front 5.3  (Build 4.120)

/*!40101 SET NAMES utf8 */;

#
# Structure for table "ptcms_ad"
#

DROP TABLE IF EXISTS `ptcms_ad`;
CREATE TABLE `ptcms_ad` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '',
  `key` varchar(50) NOT NULL DEFAULT '',
  `width` smallint(6) DEFAULT '0',
  `height` smallint(6) DEFAULT '0',
  `code` text,
  `intro` varchar(255) NOT NULL DEFAULT '',
  `create_user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建人',
  `update_user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '修改人',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  `type` tinyint(3) DEFAULT '1' COMMENT '广告类型 1 html 2 js',
  `status` tinyint(3) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

#
# Data for table "ptcms_ad"
#

/*!40000 ALTER TABLE `ptcms_ad` DISABLE KEYS */;
INSERT INTO `ptcms_ad` VALUES (1,'ddd333','sss22',12,32,'123123','123',1,1,1412899971,1412900277,1,1),(2,'网站统计','tongji',0,0,'','',1,0,1412900092,0,1,1);
/*!40000 ALTER TABLE `ptcms_ad` ENABLE KEYS */;
