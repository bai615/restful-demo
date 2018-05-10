/*
Navicat MySQL Data Transfer

Source Server         : mysql-local
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : restful_db

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2018-05-09 11:07:31
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for article
-- ----------------------------
DROP TABLE IF EXISTS `article`;
CREATE TABLE `article` (
  `article_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(40) NOT NULL,
  `content` text NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`article_id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of article
-- ----------------------------
INSERT INTO `article` VALUES ('16', '文章标题', '文章内容', '2', '2018-05-07 16:54:14');
INSERT INTO `article` VALUES ('17', '文章标题', '文章内容', '2', '2018-05-07 16:54:14');
INSERT INTO `article` VALUES ('18', '文章标题', '文章内容', '2', '2018-05-07 16:54:16');
INSERT INTO `article` VALUES ('19', '文章标题', '文章内容', '2', '2018-05-07 16:54:17');
INSERT INTO `article` VALUES ('20', '文章标题', '文章内容', '2', '2018-05-07 16:54:20');

-- ----------------------------
-- Table structure for user
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(20) NOT NULL,
  `password` char(32) NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of user
-- ----------------------------
INSERT INTO `user` VALUES ('1', 'admin', 'adminpwd', '0000-00-00 00:00:00');
INSERT INTO `user` VALUES ('2', 'admin1', '454dcca79c9550d0837129152f96db4d', '2018-05-07 11:08:52');
INSERT INTO `user` VALUES ('3', 'admin2', '4fcea5ce0294f6b31c8a4de0848ab6e4', '2018-05-07 11:13:45');
