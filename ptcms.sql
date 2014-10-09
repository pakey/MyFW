# Host: localhost  (Version: 5.5.38)
# Date: 2014-10-09 08:55:17
# Generator: MySQL-Front 5.3  (Build 4.120)

/*!40101 SET NAMES utf8 */;

#
# Structure for table "ptcms_admin_group"
#

DROP TABLE IF EXISTS `ptcms_admin_group`;
CREATE TABLE `ptcms_admin_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(10) NOT NULL DEFAULT '',
  `intro` varchar(255) NOT NULL DEFAULT '',
  `node` text NOT NULL,
  `create_user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建人',
  `update_user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '修改人',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='用户组信息表';

#
# Data for table "ptcms_admin_group"
#

/*!40000 ALTER TABLE `ptcms_admin_group` DISABLE KEYS */;
INSERT INTO `ptcms_admin_group` VALUES (1,'超级管理员','拥有所有权限','4,5,15,8,9,10,11,12,14,17,19,20,21,18,22,23,24,25,3,1,6,7,13,2,16',1,1,1412777739,1412778653);
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
) ENGINE=MyISAM AUTO_INCREMENT=26 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

#
# Data for table "ptcms_admin_node"
#

/*!40000 ALTER TABLE `ptcms_admin_node` DISABLE KEYS */;
INSERT INTO `ptcms_admin_node` VALUES (1,'常用',0,'','','',1,1,1,1,1412696793,1412757936),(2,'系统',0,'','','',1,1,1,1,1412696797,1412757936),(3,'系统概况',1,'','','',50,1,1,1,1412699544,1412757936),(4,'欢迎界面',3,'admin','index','welcome',10,1,1,1,1412699598,1412757936),(5,'系统探针',3,'admin','index','system',20,1,1,1,1412699662,1412757936),(6,'常用功能',1,'','','',50,1,1,1,1412699681,1412757936),(7,'开发功能',1,'','','',50,1,1,1,1412699705,1412757936),(8,'权限节点',7,'admin','node','index',50,1,1,1,1412699737,1412757936),(9,'添加节点',8,'admin','node','add',50,1,1,1,1412699752,1412757936),(10,'修改节点',8,'admin','node','edit',50,1,1,1,1412699787,1412757936),(11,'删除节点',8,'admin','node','del',50,1,1,1,1412699817,1412757936),(12,'批量操作',8,'admin','node','multi',50,1,1,1,1412700038,1412757936),(13,'系统设置',2,'','','',50,1,1,1,1412701712,1412757936),(14,'基本参数',13,'','','',50,1,1,1,1412701727,1412757936),(15,'添加菜单',6,'','','',50,1,1,1,1412701869,1412757936),(16,'管理员设置',2,'','','',50,1,1,1,1412749506,1412757936),(17,'用户管理',16,'admin','user','index',50,1,1,1,1412749542,1412757936),(18,'用户组管理',16,'admin','group','index',50,1,1,1,1412749571,1412757936),(19,'添加用户',17,'admin','user','add',50,1,1,1,1412749589,1412757936),(20,'修改用户',17,'admin','user','edit',50,1,1,1,1412749624,1412757936),(21,'删除用户',17,'admin','user','del',50,1,1,1,1412749640,1412757936),(22,'添加用户组',18,'admin','group','add',50,1,1,1,1412749659,1412757936),(23,'修改用户组',18,'admin','group','edit',50,1,1,1,1412749668,1412757936),(24,'删除用户组',18,'admin','group','del',50,1,1,1,1412749694,1412757936),(25,'批量操作',18,'admin','group','multi',50,1,1,1,1412749710,1412757936);
/*!40000 ALTER TABLE `ptcms_admin_node` ENABLE KEYS */;

#
# Structure for table "ptcms_admin_user"
#

DROP TABLE IF EXISTS `ptcms_admin_user`;
CREATE TABLE `ptcms_admin_user` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `passport_id` int(11) NOT NULL DEFAULT '0',
  `group_id` smallint(5) DEFAULT '0' COMMENT '用户组',
  `intro` varchar(255) NOT NULL DEFAULT '',
  `create_user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建人',
  `update_user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '修改人',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  `login_num` int(11) unsigned DEFAULT '0' COMMENT '登录次数',
  `login_ip` varchar(15) DEFAULT NULL,
  `login_time` int(11) unsigned DEFAULT '0' COMMENT '最后登录时间',
  `status` tinyint(3) unsigned DEFAULT '1' COMMENT '用户状态 1正常 0未审核',
  PRIMARY KEY (`id`),
  UNIQUE KEY `passport_id` (`passport_id`),
  KEY `group_id` (`group_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='用户信息表';

#
# Data for table "ptcms_admin_user"
#

/*!40000 ALTER TABLE `ptcms_admin_user` DISABLE KEYS */;
INSERT INTO `ptcms_admin_user` VALUES (1,1,1,'默认管理员帐号',1,1,1411978787,1412813064,12,'127.0.0.1',1412778449,1),(3,2,1,'haha',1,0,1412815173,0,0,NULL,0,1);
/*!40000 ALTER TABLE `ptcms_admin_user` ENABLE KEYS */;

#
# Structure for table "ptcms_caption"
#

DROP TABLE IF EXISTS `ptcms_caption`;
CREATE TABLE `ptcms_caption` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(50) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL COMMENT '名称',
  `description` varchar(255) DEFAULT NULL COMMENT '描述信息',
  PRIMARY KEY (`id`)
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
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `password` char(32) DEFAULT NULL,
  `salt` char(6) DEFAULT NULL,
  `reg_ip` varchar(15) DEFAULT NULL,
  `reg_time` int(11) unsigned DEFAULT '0',
  `login_ip` varchar(15) DEFAULT NULL,
  `login_time` int(11) unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `name` (`name`(5))
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='用户信息主表';

#
# Data for table "ptcms_passport"
#

/*!40000 ALTER TABLE `ptcms_passport` DISABLE KEYS */;
INSERT INTO `ptcms_passport` VALUES (1,'admin','db4dfadff18f7145ca4f6a2a15ff6303','565795','127.0.0.1',1411978787,'127.0.0.1',1412778449),(2,'test1',NULL,NULL,NULL,0,NULL,0),(3,'test2',NULL,NULL,NULL,0,NULL,0),(4,'test3',NULL,NULL,NULL,0,NULL,0);
/*!40000 ALTER TABLE `ptcms_passport` ENABLE KEYS */;

#
# Structure for table "ptcms_user"
#

DROP TABLE IF EXISTS `ptcms_user`;
CREATE TABLE `ptcms_user` (
  `user_id` int(11) NOT NULL DEFAULT '0',
  `group_id` smallint(6) DEFAULT NULL,
  `status` tinyint(3) unsigned DEFAULT '1' COMMENT '用户状态 1正常 0未审核',
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户信息表';

#
# Data for table "ptcms_user"
#

/*!40000 ALTER TABLE `ptcms_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `ptcms_user` ENABLE KEYS */;
