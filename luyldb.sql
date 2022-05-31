/*
 Navicat Premium Data Transfer

 Source Server         : 127.0.0.1-33306
 Source Server Type    : MySQL
 Source Server Version : 50733
 Source Host           : 127.0.0.1:33306
 Source Schema         : luyldb

 Target Server Type    : MySQL
 Target Server Version : 50733
 File Encoding         : 65001

 Date: 31/05/2022 18:58:35
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for city
-- ----------------------------
DROP TABLE IF EXISTS `city`;
CREATE TABLE `city` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of city
-- ----------------------------
BEGIN;
INSERT INTO `city` (`id`, `name`) VALUES (2, '武汉');
INSERT INTO `city` (`id`, `name`) VALUES (3, '北京');
COMMIT;

SET FOREIGN_KEY_CHECKS = 1;
