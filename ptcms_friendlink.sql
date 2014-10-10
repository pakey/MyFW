# Host: localhost  (Version: 5.5.38)
# Date: 2014-10-10 08:45:16
# Generator: MySQL-Front 5.3  (Build 4.120)

/*!40101 SET NAMES utf8 */;

#
# Structure for table "ptcms_friendlink"
#

DROP TABLE IF EXISTS `ptcms_friendlink`;
CREATE TABLE `ptcms_friendlink` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL DEFAULT '',
  `url` varchar(100) NOT NULL DEFAULT '',
  `logo` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL COMMENT '描述信息',
  `ordernum` smallint(5) unsigned NOT NULL DEFAULT '50',
  `color` varchar(20) NOT NULL COMMENT '颜色代码',
  `isbold` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否加粗',
  `create_user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建人',
  `update_user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '修改人',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态 1可用 0禁用',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

#
# Data for table "ptcms_friendlink"
#

/*!40000 ALTER TABLE `ptcms_friendlink` DISABLE KEYS */;
INSERT INTO `ptcms_friendlink` VALUES (6,'PTCMS工作室','http://www.ptcms.com','','PTCMS官方网站',10,'red',1,1,1,1412859114,1412862704,1);
/*!40000 ALTER TABLE `ptcms_friendlink` ENABLE KEYS */;
