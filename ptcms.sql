# Host: localhost  (Version: 5.5.38)
# Date: 2014-10-08 08:29:48
# Generator: MySQL-Front 5.3  (Build 4.120)

/*!40101 SET NAMES utf8 */;

#
# Structure for table "ptcms_admin_auth"
#

DROP TABLE IF EXISTS `ptcms_admin_auth`;
CREATE TABLE `ptcms_admin_auth` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(50) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `node_id` varchar(255) DEFAULT NULL,
  `ordernum` smallint(5) unsigned DEFAULT NULL,
  `create_user_id` int(11) unsigned DEFAULT '0' COMMENT '创建人',
  `update_user_id` int(11) unsigned DEFAULT '0' COMMENT '修改人',
  `create_time` int(11) unsigned DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned DEFAULT '0' COMMENT '修改时间',
  `status` tinyint(3) DEFAULT '1',
  PRIMARY KEY (`Id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

#
# Data for table "ptcms_admin_auth"
#

/*!40000 ALTER TABLE `ptcms_admin_auth` DISABLE KEYS */;
/*!40000 ALTER TABLE `ptcms_admin_auth` ENABLE KEYS */;

#
# Structure for table "ptcms_admin_group"
#

DROP TABLE IF EXISTS `ptcms_admin_group`;
CREATE TABLE `ptcms_admin_group` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(10) DEFAULT NULL,
  `node` text NOT NULL,
  `auth` text NOT NULL,
  `create_user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建人',
  `update_user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '修改人',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`Id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='用户组信息表';

#
# Data for table "ptcms_admin_group"
#

/*!40000 ALTER TABLE `ptcms_admin_group` DISABLE KEYS */;
INSERT INTO `ptcms_admin_group` VALUES (1,'超级管理员','','',0,0,0,0);
/*!40000 ALTER TABLE `ptcms_admin_group` ENABLE KEYS */;

#
# Structure for table "ptcms_admin_node"
#

DROP TABLE IF EXISTS `ptcms_admin_node`;
CREATE TABLE `ptcms_admin_node` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '文档ID',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '标题',
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上级分类ID',
  `module` varchar(20) DEFAULT NULL,
  `controller` varchar(50) DEFAULT NULL,
  `action` varchar(50) DEFAULT NULL,
  `ordernum` smallint(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序（同级有效）',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态',
  `create_user_id` int(11) unsigned DEFAULT '0' COMMENT '创建人',
  `update_user_id` int(11) unsigned DEFAULT '0' COMMENT '修改人',
  `create_time` int(11) unsigned DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`),
  KEY `status` (`status`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

#
# Data for table "ptcms_admin_node"
#

/*!40000 ALTER TABLE `ptcms_admin_node` DISABLE KEYS */;
INSERT INTO `ptcms_admin_node` VALUES (1,'常用',0,'','','',1,1,1,0,1412696793,0),(2,'系统',0,'','','',1,1,1,0,1412696797,0),(3,'系统概况',1,'','','',50,1,1,0,1412699544,0),(4,'欢迎界面',3,'admin','index','welcome',50,1,1,0,1412699598,0),(5,'系统探针',3,'admin','index','system',50,1,1,0,1412699662,0),(6,'常用功能',1,'','','',50,1,1,0,1412699681,0),(7,'开发功能',1,'','','',50,1,1,0,1412699705,0),(8,'权限节点',7,'admin','node','index',50,1,1,0,1412699737,0),(9,'添加节点',8,'admin','node','add',50,1,1,0,1412699752,0),(10,'修改节点',8,'admin','node','edit',50,1,1,0,1412699787,0),(11,'删除节点',8,'admin','node','del',50,1,1,0,1412699817,0),(12,'批量操作',8,'admin','node','multi',50,1,1,0,1412700038,0),(13,'系统设置',2,'','','',50,1,1,0,1412701712,0),(14,'基本参数',13,'','','',50,1,1,0,1412701727,0),(15,'添加菜单',6,'','','',50,1,1,0,1412701869,0);
/*!40000 ALTER TABLE `ptcms_admin_node` ENABLE KEYS */;

#
# Structure for table "ptcms_admin_user"
#

DROP TABLE IF EXISTS `ptcms_admin_user`;
CREATE TABLE `ptcms_admin_user` (
  `passport_id` int(11) NOT NULL DEFAULT '0',
  `group_id` smallint(5) DEFAULT '0' COMMENT '用户组',
  `intro` varchar(255) DEFAULT NULL,
  `create_user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建人',
  `update_user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '修改人',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  `login_num` int(11) unsigned DEFAULT '0' COMMENT '登录次数',
  `login_ip` varchar(15) DEFAULT NULL,
  `login_time` int(11) unsigned DEFAULT '0' COMMENT '最后登录时间',
  `status` tinyint(3) unsigned DEFAULT '1' COMMENT '用户状态 1正常 0未审核',
  PRIMARY KEY (`passport_id`),
  KEY `group_id` (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='用户信息表';

#
# Data for table "ptcms_admin_user"
#

/*!40000 ALTER TABLE `ptcms_admin_user` DISABLE KEYS */;
INSERT INTO `ptcms_admin_user` VALUES (1,1,NULL,1,1,1411978787,1411978787,6,'127.0.0.1',1412695652,1);
/*!40000 ALTER TABLE `ptcms_admin_user` ENABLE KEYS */;

#
# Structure for table "ptcms_caption"
#

DROP TABLE IF EXISTS `ptcms_caption`;
CREATE TABLE `ptcms_caption` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(50) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL COMMENT '名称',
  `description` varchar(255) DEFAULT NULL COMMENT '描述信息',
  PRIMARY KEY (`Id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='key中文解释';

#
# Data for table "ptcms_caption"
#

/*!40000 ALTER TABLE `ptcms_caption` DISABLE KEYS */;
/*!40000 ALTER TABLE `ptcms_caption` ENABLE KEYS */;

#
# Structure for table "ptcms_passport"
#

DROP TABLE IF EXISTS `ptcms_passport`;
CREATE TABLE `ptcms_passport` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `password` char(32) DEFAULT NULL,
  `salt` char(6) DEFAULT NULL,
  `reg_ip` varchar(15) DEFAULT NULL,
  `reg_time` int(11) unsigned DEFAULT '0',
  `login_ip` varchar(15) DEFAULT NULL,
  `login_time` int(11) unsigned DEFAULT '0',
  PRIMARY KEY (`Id`),
  KEY `name` (`name`(5))
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='用户信息主表';

#
# Data for table "ptcms_passport"
#

/*!40000 ALTER TABLE `ptcms_passport` DISABLE KEYS */;
INSERT INTO `ptcms_passport` VALUES (1,'admin','db4dfadff18f7145ca4f6a2a15ff6303','565795','127.0.0.1',1411978787,'127.0.0.1',1412695652);
/*!40000 ALTER TABLE `ptcms_passport` ENABLE KEYS */;

#
# Structure for table "ptcms_user"
#

DROP TABLE IF EXISTS `ptcms_user`;
CREATE TABLE `ptcms_user` (
  `user_Id` int(11) NOT NULL DEFAULT '0',
  `group_id` smallint(6) DEFAULT NULL,
  `status` tinyint(3) unsigned DEFAULT '1' COMMENT '用户状态 1正常 0未审核',
  PRIMARY KEY (`user_Id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户信息表';

#
# Data for table "ptcms_user"
#

/*!40000 ALTER TABLE `ptcms_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `ptcms_user` ENABLE KEYS */;
