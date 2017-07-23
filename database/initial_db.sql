/*
Navicat MySQL Data Transfer

Source Server         : local
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : arm_bms_2017_08

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2017-07-23 20:58:21
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `system_assigned_group`
-- ----------------------------
DROP TABLE IF EXISTS `system_assigned_group`;
CREATE TABLE `system_assigned_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `user_group` int(11) NOT NULL,
  `revision` int(4) NOT NULL DEFAULT '1',
  `date_created` int(11) NOT NULL DEFAULT '0',
  `user_created` int(11) NOT NULL DEFAULT '0',
  `date_updated` int(11) DEFAULT NULL,
  `user_updated` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of system_assigned_group
-- ----------------------------
INSERT INTO `system_assigned_group` VALUES ('1', '1', '1', '1', '0', '0', null, null);
INSERT INTO `system_assigned_group` VALUES ('2', '2', '2', '1', '0', '0', null, null);

-- ----------------------------
-- Table structure for `system_history`
-- ----------------------------
DROP TABLE IF EXISTS `system_history`;
CREATE TABLE `system_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `controller` varchar(255) DEFAULT NULL,
  `table_id` int(11) NOT NULL,
  `table_name` varchar(50) NOT NULL,
  `data` varchar(255) NOT NULL,
  `user_id` varchar(20) NOT NULL,
  `action` varchar(20) NOT NULL,
  `date` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of system_history
-- ----------------------------
INSERT INTO `system_history` VALUES ('1', 'sys_user_group', '1', 'arm_bms_2017_08.system_user_group_role', '{\"date_updated\":1500821766,\"user_updated\":\"1\"}', '1', 'UPDATE', '1500821766');
INSERT INTO `system_history` VALUES ('2', 'sys_user_group', '2', 'arm_bms_2017_08.system_user_group_role', '{\"action0\":1,\"action1\":1,\"action2\":1,\"action3\":1,\"action4\":1,\"action5\":1,\"action6\":1,\"task_id\":2,\"user_group_id\":\"1\",\"user_created\":\"1\",\"date_created\":1500821766}', '1', 'INSERT', '1500821766');
INSERT INTO `system_history` VALUES ('3', 'sys_user_group', '3', 'arm_bms_2017_08.system_user_group_role', '{\"action0\":1,\"action1\":1,\"action2\":1,\"action3\":1,\"action4\":1,\"action5\":1,\"action6\":1,\"task_id\":3,\"user_group_id\":\"1\",\"user_created\":\"1\",\"date_created\":1500821766}', '1', 'INSERT', '1500821766');
INSERT INTO `system_history` VALUES ('4', 'sys_user_group', '4', 'arm_bms_2017_08.system_user_group_role', '{\"action0\":1,\"action1\":1,\"action2\":1,\"action3\":1,\"action4\":1,\"action5\":1,\"action6\":1,\"task_id\":4,\"user_group_id\":\"1\",\"user_created\":\"1\",\"date_created\":1500821766}', '1', 'INSERT', '1500821766');
INSERT INTO `system_history` VALUES ('5', 'sys_user_group', '5', 'arm_bms_2017_08.system_user_group_role', '{\"action0\":1,\"action1\":1,\"action2\":1,\"action3\":1,\"action4\":1,\"action5\":1,\"action6\":1,\"task_id\":5,\"user_group_id\":\"1\",\"user_created\":\"1\",\"date_created\":1500821766}', '1', 'INSERT', '1500821766');

-- ----------------------------
-- Table structure for `system_history_hack`
-- ----------------------------
DROP TABLE IF EXISTS `system_history_hack`;
CREATE TABLE `system_history_hack` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `controller` varchar(255) DEFAULT NULL,
  `action` varchar(255) DEFAULT 'Active',
  `action_id` int(11) DEFAULT '99',
  `other_info` text,
  `date_created` int(11) DEFAULT '0',
  `date_created_string` varchar(255) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of system_history_hack
-- ----------------------------

-- ----------------------------
-- Table structure for `system_session`
-- ----------------------------
DROP TABLE IF EXISTS `system_session`;
CREATE TABLE `system_session` (
  `id` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `ip_address` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `data` blob NOT NULL,
  PRIMARY KEY (`id`),
  KEY `system_session_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of system_session
-- ----------------------------
INSERT INTO `system_session` VALUES ('ocq18i1bubv51egg6uo46jd28ra9an8q', '::1', '1500821715', 0x5F5F63695F6C6173745F726567656E65726174657C693A313530303832313237353B757365725F69647C733A303A22223B);
INSERT INTO `system_session` VALUES ('q7b4ecutvls2puvs22n47d6ptfkhdbp8', '::1', '1500821855', 0x5F5F63695F6C6173745F726567656E65726174657C693A313530303832313731393B757365725F69647C733A313A2231223B);

-- ----------------------------
-- Table structure for `system_site_offline`
-- ----------------------------
DROP TABLE IF EXISTS `system_site_offline`;
CREATE TABLE `system_site_offline` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` varchar(11) NOT NULL DEFAULT 'Active',
  `date_created` int(11) NOT NULL DEFAULT '0',
  `user_created` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of system_site_offline
-- ----------------------------

-- ----------------------------
-- Table structure for `system_task`
-- ----------------------------
DROP TABLE IF EXISTS `system_task`;
CREATE TABLE `system_task` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'TASK',
  `parent` int(11) NOT NULL DEFAULT '0',
  `controller` varchar(500) NOT NULL,
  `ordering` smallint(6) NOT NULL DEFAULT '9999',
  `icon` varchar(255) NOT NULL DEFAULT 'menu.png',
  `status` varchar(11) NOT NULL DEFAULT 'Active',
  `date_created` int(11) NOT NULL DEFAULT '0',
  `user_created` int(11) NOT NULL DEFAULT '0',
  `date_updated` int(11) DEFAULT NULL,
  `user_updated` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of system_task
-- ----------------------------
INSERT INTO `system_task` VALUES ('1', 'System Settings', 'MODULE', '0', '', '1', 'menu.png', 'Active', '0', '0', null, null);
INSERT INTO `system_task` VALUES ('2', 'Module & Task', 'TASK', '1', 'Sys_module_task', '1', 'menu.png', 'Active', '0', '0', null, null);
INSERT INTO `system_task` VALUES ('3', 'User Group', 'TASK', '1', 'Sys_user_group', '2', 'menu.png', 'Active', '0', '0', null, null);
INSERT INTO `system_task` VALUES ('4', 'Users', 'TASK', '1', 'Sys_users', '4', 'menu.png', 'Active', '0', '0', null, null);
INSERT INTO `system_task` VALUES ('5', 'Site Offline', 'TASK', '1', 'Sys_site_offline', '5', 'menu.png', 'Active', '0', '0', null, null);

-- ----------------------------
-- Table structure for `system_user_group`
-- ----------------------------
DROP TABLE IF EXISTS `system_user_group`;
CREATE TABLE `system_user_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `status` varchar(11) NOT NULL DEFAULT 'Active',
  `ordering` tinyint(4) NOT NULL DEFAULT '99',
  `date_created` int(11) NOT NULL DEFAULT '0',
  `user_created` int(11) NOT NULL DEFAULT '0',
  `date_updated` int(11) DEFAULT NULL,
  `user_updated` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of system_user_group
-- ----------------------------
INSERT INTO `system_user_group` VALUES ('1', 'Super Admin', 'Active', '1', '0', '0', null, null);
INSERT INTO `system_user_group` VALUES ('2', 'Admin', 'Active', '1', '0', '0', null, null);

-- ----------------------------
-- Table structure for `system_user_group_role`
-- ----------------------------
DROP TABLE IF EXISTS `system_user_group_role`;
CREATE TABLE `system_user_group_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_group_id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `action0` tinyint(4) NOT NULL DEFAULT '0',
  `action1` tinyint(4) NOT NULL DEFAULT '0',
  `action2` tinyint(4) NOT NULL DEFAULT '0',
  `action3` tinyint(4) NOT NULL DEFAULT '0',
  `action4` tinyint(4) NOT NULL DEFAULT '0',
  `action5` tinyint(4) NOT NULL DEFAULT '0',
  `action6` tinyint(4) NOT NULL DEFAULT '0',
  `revision` int(11) NOT NULL DEFAULT '1',
  `date_created` int(11) NOT NULL DEFAULT '0',
  `user_created` int(11) NOT NULL DEFAULT '0',
  `date_updated` int(11) DEFAULT NULL,
  `user_updated` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of system_user_group_role
-- ----------------------------
INSERT INTO `system_user_group_role` VALUES ('1', '1', '3', '1', '1', '1', '1', '1', '1', '1', '2', '0', '0', '1500821766', '1');
INSERT INTO `system_user_group_role` VALUES ('2', '1', '2', '1', '1', '1', '1', '1', '1', '1', '1', '1500821766', '1', null, null);
INSERT INTO `system_user_group_role` VALUES ('3', '1', '3', '1', '1', '1', '1', '1', '1', '1', '1', '1500821766', '1', null, null);
INSERT INTO `system_user_group_role` VALUES ('4', '1', '4', '1', '1', '1', '1', '1', '1', '1', '1', '1500821766', '1', null, null);
INSERT INTO `system_user_group_role` VALUES ('5', '1', '5', '1', '1', '1', '1', '1', '1', '1', '1', '1500821766', '1', null, null);
