/*
 Navicat Premium Data Transfer

 Source Server         : PHPServer
 Source Server Type    : MySQL
 Source Server Version : 50726
 Source Host           : localhost:3306
 Source Schema         : softwarestore

 Target Server Type    : MySQL
 Target Server Version : 50726
 File Encoding         : 65001

 Date: 20/04/2022 18:06:49
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for tp_commodity
-- ----------------------------
DROP TABLE IF EXISTS `tp_commodity`;
CREATE TABLE `tp_commodity`  (
  `comm_id` int(11) NOT NULL AUTO_INCREMENT,
  `comm_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `comm_classify` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `comm_reserve` int(11) NOT NULL,
  `sales_volume` int(11) NOT NULL,
  `sales` int(11) NULL DEFAULT NULL COMMENT '销量',
  `photo` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`comm_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '商品信息表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tp_commodity
-- ----------------------------

-- ----------------------------
-- Table structure for tp_orderhistory
-- ----------------------------
DROP TABLE IF EXISTS `tp_orderhistory`;
CREATE TABLE `tp_orderhistory`  (
  `history_id` int(11) NOT NULL AUTO_INCREMENT,
  `goods_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `quantity` bigint(255) NOT NULL,
  `unit_price` decimal(10, 4) NOT NULL,
  `total` decimal(10, 4) NOT NULL,
  `create_time` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`history_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '历史订单记录表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tp_orderhistory
-- ----------------------------

-- ----------------------------
-- Table structure for tp_password
-- ----------------------------
DROP TABLE IF EXISTS `tp_password`;
CREATE TABLE `tp_password`  (
  `pwd_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `password` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `salt` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`pwd_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '存储用户的密码及盐' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tp_password
-- ----------------------------

-- ----------------------------
-- Table structure for tp_purchaseorder
-- ----------------------------
DROP TABLE IF EXISTS `tp_purchaseorder`;
CREATE TABLE `tp_purchaseorder`  (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `goods_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `quantity` bigint(20) NOT NULL COMMENT '数量',
  `unit_price` decimal(10, 4) NOT NULL COMMENT '单价，保留小数点4位',
  `total` decimal(10, 4) NOT NULL,
  `create_time` datetime(0) NOT NULL ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`order_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '订单表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tp_purchaseorder
-- ----------------------------

-- ----------------------------
-- Table structure for tp_realname
-- ----------------------------
DROP TABLE IF EXISTS `tp_realname`;
CREATE TABLE `tp_realname`  (
  `card_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NULL DEFAULT NULL,
  `IDCard` blob NOT NULL,
  PRIMARY KEY (`card_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '存储用户实名认证的表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tp_realname
-- ----------------------------

-- ----------------------------
-- Table structure for tp_shipaddr
-- ----------------------------
DROP TABLE IF EXISTS `tp_shipaddr`;
CREATE TABLE `tp_shipaddr`  (
  `ship_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `ship_address` json NOT NULL,
  PRIMARY KEY (`ship_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '单独维护一个用户收货地址表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tp_shipaddr
-- ----------------------------

-- ----------------------------
-- Table structure for tp_shopcart
-- ----------------------------
DROP TABLE IF EXISTS `tp_shopcart`;
CREATE TABLE `tp_shopcart`  (
  `cart_id` int(11) NOT NULL AUTO_INCREMENT,
  `goods_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `quantity` bigint(255) NOT NULL,
  `unit_price` decimal(10, 4) NOT NULL,
  `total` decimal(10, 4) NOT NULL,
  `create_time` datetime(0) NOT NULL ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`cart_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '购物车表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tp_shopcart
-- ----------------------------

-- ----------------------------
-- Table structure for tp_stock
-- ----------------------------
DROP TABLE IF EXISTS `tp_stock`;
CREATE TABLE `tp_stock`  (
  `stock_id` int(11) NOT NULL AUTO_INCREMENT,
  `comm_stock` bigint(255) NOT NULL,
  `create_time` datetime(0) NOT NULL ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`stock_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '商品库存信息表' ROW_FORMAT = Fixed;

-- ----------------------------
-- Records of tp_stock
-- ----------------------------

-- ----------------------------
-- Table structure for tp_stockinout
-- ----------------------------
DROP TABLE IF EXISTS `tp_stockinout`;
CREATE TABLE `tp_stockinout`  (
  `inout_id` int(11) NOT NULL AUTO_INCREMENT,
  `stock_id` int(11) NOT NULL,
  `quantity` bigint(255) NOT NULL,
  `now_stock` bigint(255) NOT NULL COMMENT '现有库存数量',
  `update_time` datetime(0) NOT NULL ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`inout_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '商品库存信息更改表，会时刻变化' ROW_FORMAT = Fixed;

-- ----------------------------
-- Records of tp_stockinout
-- ----------------------------

-- ----------------------------
-- Table structure for tp_user
-- ----------------------------
DROP TABLE IF EXISTS `tp_user`;
CREATE TABLE `tp_user`  (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `age` varchar(3) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `email` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `phone_number` varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `IDCard` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `create_time` datetime(0) NOT NULL ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`user_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tp_user
-- ----------------------------

SET FOREIGN_KEY_CHECKS = 1;
