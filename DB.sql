-- MySQL dump 10.13  Distrib 5.7.40, for Linux (x86_64)
--
-- Host: localhost    Database: trxswapbot
-- ------------------------------------------------------
-- Server version	5.7.40-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `t_admin`
--

DROP TABLE IF EXISTS `t_admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t_admin` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `head` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态：0.禁用 1.正常',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `white_ip` VARCHAR(3000) NULL DEFAULT NULL COMMENT '白名单IP',
  PRIMARY KEY (`id`),
  UNIQUE KEY `t_admin_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_admin`
--

LOCK TABLES `t_admin` WRITE;
/*!40000 ALTER TABLE `t_admin` DISABLE KEYS */;
INSERT INTO `t_admin` VALUES (1,'trxadmin','$2y$10$4YoRzHXWRiSQH5Sdte09Zew6V98eAZPrZLw70V8xRok3STnxMUwIe','',1,NULL,NULL,'2023-12-02 16:28:30','');
/*!40000 ALTER TABLE `t_admin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t_energy_ai_trusteeship`
--

DROP TABLE IF EXISTS `t_energy_ai_trusteeship`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t_energy_ai_trusteeship` (
  `rid` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `bot_rid` int(11) NOT NULL COMMENT '机器人ID',
  `tg_uid` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'tg用户ID',
  `wallet_addr` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '监控地址',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态:0开启,1关闭,2管理员关闭(会员禁止开启)',
  `current_bandwidth_quantity` bigint(20) NOT NULL DEFAULT '0' COMMENT '当前带宽数量',
  `current_energy_quantity` bigint(20) NOT NULL DEFAULT '0' COMMENT '当前能量数量',
  `min_energy_quantity` int(11) NOT NULL DEFAULT '32000' COMMENT '能量低于值购买',
  `per_buy_energy_quantity` int(11) NOT NULL DEFAULT '32000' COMMENT '每次购买能量数量',
  `total_buy_energy_quantity` bigint(20) NOT NULL DEFAULT '0' COMMENT '总已购买能量数量',
  `total_used_trx` decimal(14,2) NOT NULL DEFAULT '0.00' COMMENT '总已花费trx',
  `total_buy_quantity` int(11) NOT NULL DEFAULT '0' COMMENT '总购买次数',
  `is_buy` char(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N' COMMENT '是否立马购买:Y立马下单,B下单中,监控了余额后,不足最低资源时,改为Y',
  `is_notice` char(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N' COMMENT '是否通知用户:Y需要通知,自动购买后改为Y,通知用户后改为N.A表示用户不需要通知',
  `is_notice_admin` char(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N' COMMENT '是否通知管理员:Y需要通知,通知后改为N.A表示管理员不需要通知',
  `last_buy_time` datetime DEFAULT NULL COMMENT '最后一次购买时间',
  `last_used_trx` decimal(14,2) DEFAULT NULL COMMENT '最后一次购买花费trx',
  `comments` varchar(2000) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '备注',
  `create_by` int(11) NOT NULL COMMENT '创建人',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_by` int(11) DEFAULT NULL COMMENT '修改人',
  `update_time` datetime DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`rid`),
  UNIQUE KEY `idx_energy_ai_trusteeship_1` (`wallet_addr`),
  KEY `idx_energy_ai_trusteeship_2` (`tg_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='机器人能量智能下单表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_energy_ai_trusteeship`
--

LOCK TABLES `t_energy_ai_trusteeship` WRITE;
/*!40000 ALTER TABLE `t_energy_ai_trusteeship` DISABLE KEYS */;
/*!40000 ALTER TABLE `t_energy_ai_trusteeship` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t_energy_platform`
--

DROP TABLE IF EXISTS `t_energy_platform`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t_energy_platform` (
  `rid` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `poll_group` char(1) NOT NULL COMMENT '轮询组:A,B,C',
  `platform_name` tinyint(4) NOT NULL COMMENT '能量平台',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态:0开启,1关闭',
  `platform_uid` varchar(100) DEFAULT NULL COMMENT '平台用户UID',
  `permission_id` int(11) NOT NULL DEFAULT '0' COMMENT '签名权限ID,当能量平台为自己质押的时候才用,用于多签钱包签名	',
  `platform_balance` decimal(28,6) NOT NULL DEFAULT '0' COMMENT '平台用户余额',
  `alert_platform_balance` decimal(28,6) NOT NULL DEFAULT '0' COMMENT '平台用户余额预警值',
  `platform_apikey` varchar(3000) DEFAULT NULL COMMENT '平台用户apikey',
  `tg_notice_obj` varchar(200) DEFAULT NULL COMMENT '通知对象ID',
  `tg_notice_bot_rid` int(11) DEFAULT NULL COMMENT '通知机器人ID',
  `seq_sn` int(11) NOT NULL DEFAULT '0' COMMENT '轮询排序,数字越大越先',
  `last_alert_time` datetime DEFAULT NULL COMMENT '余额预警时间',
  `comments` varchar(3000) DEFAULT NULL COMMENT '备注',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`rid`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COMMENT='能量平台轮询表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_energy_platform`
--

LOCK TABLES `t_energy_platform` WRITE;
/*!40000 ALTER TABLE `t_energy_platform` DISABLE KEYS */;
INSERT INTO `t_energy_platform` VALUES (1,'A',3,1,'TBwHofgkkkttttttttttt',0,'233694','0',NULL,'6666666',1,98,'2023-11-25 14:23:00','','2023-11-25 13:06:13','2023-11-30 17:11:46'),(2,'A',2,1,'-',0,'864.1','0',NULL,'6666666',1,1,'2023-11-25 14:23:06','','2023-11-01 13:06:41','2023-11-30 19:25:43'),(3,'A',1,1,'3333',0,'910.426948','0',NULL,'6666666',1,90,NULL,'','2023-11-25 15:03:04','2023-11-30 22:02:38'),(4,'A',4,1,'user',0,'36.36','0',NULL,'6666666',1,999,NULL,'','2023-11-27 15:16:34',NULL);
/*!40000 ALTER TABLE `t_energy_platform` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t_energy_platform_bot`
--

DROP TABLE IF EXISTS `t_energy_platform_bot`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t_energy_platform_bot` (
  `rid` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `bot_rid` int(11) NOT NULL COMMENT '机器人ID',
  `poll_group` char(1) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '轮询组',
  `tg_admin_uid` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'TG管理员用户ID,多个英文逗号隔开',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态:0开启,1关闭',
  `receive_wallet` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '收款钱包',
  `get_tx_time` datetime DEFAULT NULL COMMENT '开始拉取交易时间',
  `tg_notice_obj_receive` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'TG通知对象(收款),可多个,英文逗号隔开,可为用户或者群组',
  `tg_notice_obj_send` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'TG通知对象(成功),可多个,英文逗号隔开,可为用户或者群组',
  `comments` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '备注',
  `is_open_ai_trusteeship` char(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N' COMMENT '是否开启智能监控:Y开启',
  `trx_price_energy_32000` int(11) DEFAULT NULL COMMENT '32000能量TRX价格',
  `trx_price_energy_65000` int(11) DEFAULT NULL COMMENT '65000能量TRX价格',
  `per_energy_day` int(11) DEFAULT NULL COMMENT '智能代理期限:0一小时,1一天,3三天',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`rid`),
  KEY `idx_energy_platform_1` (`bot_rid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='机器人能量表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_energy_platform_bot`
--

LOCK TABLES `t_energy_platform_bot` WRITE;
/*!40000 ALTER TABLE `t_energy_platform_bot` DISABLE KEYS */;
INSERT INTO `t_energy_platform_bot` VALUES (3,1,'A','6666666',0,'tttttttttttttttt','2023-11-25 00:00:00','6666666','-111111','','N',6,12,1,'2023-11-25 02:06:35','2023-12-02 11:36:36');
/*!40000 ALTER TABLE `t_energy_platform_bot` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t_energy_platform_order`
--

DROP TABLE IF EXISTS `t_energy_platform_order`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t_energy_platform_order` (
  `rid` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `energy_platform_rid` int(11) NOT NULL COMMENT '能量平台ID',
  `energy_platform_bot_rid` int(11) NOT NULL COMMENT '机器人能量ID',
  `platform_name` tinyint(4) NOT NULL COMMENT '能量平台',
  `platform_uid` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '平台用户UID',
  `source_type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '下单来源:1人工下单,2自动下单',
  `receive_address` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '接收地址',
  `platform_order_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '平台订单号',
  `energy_amount` int(11) NOT NULL COMMENT '代理能量',
  `energy_day` tinyint(4) NOT NULL COMMENT '代理期限:0一小时,1一天,3三天',
  `energy_time` datetime NOT NULL COMMENT '代理时间',
  `recovery_status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '回收状态:1不用回收,2待回收,3已回收',
  `recovery_time` datetime DEFAULT NULL COMMENT '回收时间',
  `use_trx` decimal(14,6) DEFAULT NULL COMMENT '消耗TRX,当为自己质押时,回收时使用',
  PRIMARY KEY (`rid`),
  UNIQUE KEY `idx_energy_platform_order_1` (`platform_order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='能量订单表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_energy_platform_order`
--

LOCK TABLES `t_energy_platform_order` WRITE;
/*!40000 ALTER TABLE `t_energy_platform_order` DISABLE KEYS */;
/*!40000 ALTER TABLE `t_energy_platform_order` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t_energy_platform_package`
--

DROP TABLE IF EXISTS `t_energy_platform_package`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t_energy_platform_package` (
  `rid` int(11) NOT NULL AUTO_INCREMENT COMMENT '主ID',
  `bot_rid` int(11) NOT NULL COMMENT '机器人ID',
  `package_type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '套餐类型:1能量,2带宽',
  `package_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '套餐名称',
  `energy_amount` int(11) NOT NULL COMMENT '套餐量',
  `energy_day` tinyint(4) NOT NULL COMMENT '套餐期限:0一小时,1一天,3三天	',
  `trx_price` decimal(14,2) NOT NULL DEFAULT '0.00' COMMENT 'trx售价,0表示不能用trx支付',
  `usdt_price` decimal(14,2) NOT NULL DEFAULT '0.00' COMMENT 'usdt售价,0表示不能用trx支付',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态:0开启,1关闭',
  `seq_sn` int(11) NOT NULL DEFAULT '0' COMMENT '排序,数字越大越靠前',
  `create_by` int(11) DEFAULT NULL COMMENT '创建人',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_by` int(11) DEFAULT NULL COMMENT '修改人',
  `update_time` datetime DEFAULT NULL COMMENT '修改时间',
  `callback_data` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '回调数据,不重复,随机生成',
  `show_notes` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '显示说明',
  `package_pic` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '套餐图片',
  PRIMARY KEY (`rid`),
  UNIQUE KEY `idx_energy_platform_package_2` (`callback_data`) USING BTREE,
  KEY `idx_energy_platform_package_1` (`bot_rid`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='机器人能量套餐表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_energy_platform_package`
--

LOCK TABLES `t_energy_platform_package` WRITE;
/*!40000 ALTER TABLE `t_energy_platform_package` DISABLE KEYS */;

INSERT INTO `t_energy_platform_package` (`rid`, `bot_rid`, `package_type`, `package_name`, `energy_amount`, `energy_day`, `trx_price`, `usdt_price`, `status`, `seq_sn`, `create_by`, `create_time`, `update_by`, `update_time`, `callback_data`, `show_notes`, `package_pic`) VALUES
(1, 1, 1, '免费1笔|1小时(对方有U)', 32000, 0, '3.00', '0.50', 0, 99, NULL, '2023-08-16 22:53:45', NULL, '2024-02-25 16:45:59', 'energy_bc7edcf8e85bdffadac37e41f2feb369', '付款成功将获得<b>1笔</b>免费USDT转账手续费(60分钟内使用)\n能量数量: <b>32000</b>\n对比节省：<b>10.2559 TRX</b>\n⚠️↓↓↓<b>支付金额不能错误，否则无法到账</b>↓↓↓', NULL),
(2, 1, 1, '免费1笔|1小时(对方无U)', 65000, 0, '6.00', '1.00', 0, 98, NULL, '2023-08-16 23:27:14', NULL, '2024-02-25 16:46:23', 'energy_bc7edcf8e85bdffadac37e41f2feb364', '付款成功将获得<b>1笔/1小时</b>免费USDT转账手续费(1小时内使用，转给无USDT地址)\n能量数量: <b>65000</b>\n对比节省: <b>20.2677 TRX</b>\n⚠️↓↓↓<b>支付金额不能错误，否则无法到账</b>↓↓↓', NULL),
(3, 1, 1, '3笔|1小时(对方有U)', 96000, 0, '9.00', '1.50', 0, 97, NULL, '2023-08-16 23:31:54', NULL, '2024-02-25 16:47:32', 'energy_bc7edcf8e85bdffadac37e41f2feb362', '付款成功将获得<b>3</b>笔免费USDT转账手续费(1个小时内使用)\n能量数量: <b>96000</b>\n对比节省：<b>33 TRX</b>\n⚠️↓↓↓<b>支付金额不能错误，否则无法到账</b>↓↓↓', NULL),
(4, 1, 1, '4笔|1小时(对方有U)', 128000, 0, '12.00', '2.00', 0, 96, NULL, '2023-08-16 23:34:19', NULL, '2024-02-25 16:48:09', 'energy_bc7edcf8e85bdffadac37e41f2feb361', '付款成功将获得<b>4笔/小时</b>免费USDT转账手续费(1小时内使用)\n能量数量: <b>128000</b>\n对比节省: <b>44 TRX</b>\n⚠️↓↓↓<b>支付金额不能错误，否则无法到账</b>↓↓↓', NULL),
(5, 1, 1, '5笔|1小时(对方有U)', 160000, 0, '15.00', '2.50', 0, 95, NULL, '2023-08-17 00:28:58', NULL, '2024-02-25 16:49:01', 'energy_2c1ec34ee36b27b35403e4f9f18ee5ff', '付款成功将获得<b>5笔/小时</b>免费USDT转账手续费(1小时内使用)\n能量数量: <b>160000</b>\n对比节省: <b>55 TRX</b>\n⚠️↓↓↓<b>支付金额不能错误，否则无法到账</b>↓↓↓', NULL),
(6, 1, 1, '6笔|1小时(对方有U)', 192000, 0, '18.00', '3.00', 0, 94, NULL, '2023-08-19 00:42:04', NULL, '2024-02-25 16:49:35', 'energy_481639fd1b584be1e5dac45630e3f736', '付款成功将获得<b>6笔/小时</b>免费USDT转账手续费(1小时内使用)\n能量数量: <b>192000</b>\n对比节省: <b>66 TRX</b>\n⚠️↓↓↓<b>支付金额不能错误，否则无法到账</b>↓↓↓', NULL),
(7, 1, 1, '7笔|1小时(对方有U)', 224000, 0, '21.00', '3.50', 0, 93, NULL, '2023-08-19 00:44:29', NULL, '2024-02-25 16:50:02', 'energy_bacf03cebf12a8ac5a428ee7fe90d7cb', '付款成功将获得<b>7笔/小时</b>免费USDT转账手续费(1小时内使用)\n能量数量: <b>224000</b>\n对比节省：<b>77 TRX</b>\n⚠️↓↓↓<b>支付金额不能错误，否则无法到账</b>↓↓↓', NULL),
(8, 1, 1, '8笔|1小时(对方有U)', 256000, 0, '24.00', '4.00', 0, 92, NULL, '2023-08-19 00:45:57', NULL, '2024-02-25 16:50:33', 'energy_c70b1fbe610b9ef365181cb3c22804be', '付款成功将获得<b>8笔/小时</b>免费USDT转账手续费(1小时内使用)\n能量数量: <b>256000</b>\n对比节省: <b>88 TRX</b>\n⚠️↓↓↓<b>支付金额不能错误，否则无法到账</b>↓↓↓', NULL),
(9, 1, 1, '9笔|1小时(对方有U)', 288000, 0, '27.00', '4.50', 0, 91, NULL, '2023-08-19 00:47:25', NULL, '2024-02-25 16:51:04', 'energy_d5e8ca3fb78447942819d507ca3e55f1', '付款成功将获得<b>9笔/小时</b>免费USDT转账手续费(1小时内使用)\n能量数量: <b>288000</b>\n对比节省：<b>99 TRX</b>\n⚠️↓↓↓<b>支付金额不能错误，否则无法到账</b>↓↓↓', NULL),
(10, 1, 1, '10笔|1小时(对方有U)', 320000, 0, '30.00', '5.00', 0, 90, NULL, '2023-08-19 00:48:48', NULL, '2024-02-25 16:51:59', 'energy_fe4a5f5119c39f7089ee412749b080aa', '付款成功将获得<b>10笔/小时</b>免费USDT转账手续费(1小时内使用)\n能量数量: <b>320000</b>\n对比节省: <b>110 TRX</b>\n⚠️↓↓↓<b>支付金额不能错误，否则无法到账</b>↓↓↓', NULL),
(11, 1, 1, '20笔|1小时(对方有U)', 640000, 0, '60.00', '10.00', 0, 89, NULL, '2023-08-19 00:52:47', NULL, '2024-02-25 16:53:45', 'energy_2e4d7f44481ee72fdd824058bdee5dea', '付款成功将获得<b>20笔/小时</b>免费USDT转账手续费(1小时内使用)\n能量数量: <b>640000</b>\n对比节省: <b>240 TRX</b>\n⚠️↓↓↓<b>支付金额不能错误，否则无法到账</b>↓↓↓', NULL),
(12, 1, 1, '50笔|1小时(对方有U)', 1600000, 0, '150.00', '25.00', 0, 88, NULL, '2023-08-19 00:54:29', NULL, '2024-02-25 16:54:57', 'energy_f632ffe6a47ae7e778c897fda0c3ae0f', '付款成功将获得<b>50笔/小时</b>免费USDT转账手续费(1小时内使用)\n能量数量: <b>320000</b>\n对比节省: <b>580 TRX</b>\n⚠️↓↓↓<b>支付金额不能错误，否则无法到账</b>↓↓↓', NULL);
/*!40000 ALTER TABLE `t_energy_platform_package` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t_energy_wallet_trade_list`
--

DROP TABLE IF EXISTS `t_energy_wallet_trade_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t_energy_wallet_trade_list` (
  `rid` int(11) NOT NULL AUTO_INCREMENT COMMENT '主ID',
  `tx_hash` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '交易hash',
  `transferfrom_address` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '发送钱包地址,交易发起方',
  `transferto_address` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '接收钱包地址,交易接收方',
  `coin_name` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '交易币名',
  `amount` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '交易数额',
  `timestamp` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '交易时间',
  `process_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '处理状态,数据字典',
  `process_time` datetime DEFAULT NULL COMMENT '处理时间',
  `process_comments` varchar(2000) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '处理备注',
  `get_time` datetime DEFAULT NULL COMMENT '拉取交易时间',
  `energy_platform_rid` int(11) DEFAULT NULL COMMENT '能量平台ID',
  `energy_platform_bot_rid` int(11) DEFAULT NULL COMMENT '机器人能量ID',
  `platform_order_rid` int(11) DEFAULT NULL COMMENT '能量订单表ID',
  `energy_package_rid` int(11) DEFAULT NULL COMMENT '能量套餐表ID',
  `tg_notice_status_receive` char(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N' COMMENT 'TG收款通知状态:N/Y,如果配置不通知,状态也更新为Y',
  `tg_notice_status_send` char(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N' COMMENT 'TG成功通知状态:N/Y,如果配置不通知,状态也更新为Y',
  PRIMARY KEY (`rid`),
  UNIQUE KEY `idx_energy_wallet_trade_list_1` (`tx_hash`),
  KEY `idx_energy_wallet_trade_list_2` (`transferfrom_address`),
  KEY `idx_energy_wallet_trade_list_3` (`transferto_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='能量钱包交易明细';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_energy_wallet_trade_list`
--

LOCK TABLES `t_energy_wallet_trade_list` WRITE;
/*!40000 ALTER TABLE `t_energy_wallet_trade_list` DISABLE KEYS */;
/*!40000 ALTER TABLE `t_energy_wallet_trade_list` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t_fms_recharge_order`
--

DROP TABLE IF EXISTS `t_fms_recharge_order`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t_fms_recharge_order` (
  `rid` int(11) NOT NULL AUTO_INCREMENT COMMENT '主ID',
  `bot_rid` int(11) NOT NULL COMMENT '机器人ID',
  `recharge_tg_uid` varchar(100) NOT NULL COMMENT '充值TG用户ID',
  `recharge_tg_username` varchar(100) DEFAULT NULL COMMENT '充值TG用户名',
  `recharge_coin_name` varchar(50) NOT NULL COMMENT '充值币种',
  `recharge_pay_price` decimal(12,4) NOT NULL COMMENT '充值金额',
  `need_pay_price` decimal(12,4) NOT NULL COMMENT '应支付金额',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态:0待支付,1已充值,2已过期,3会员取消',
  `comments` varchar(255) DEFAULT NULL COMMENT '备注',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  `expire_time` datetime NOT NULL COMMENT '过期时间',
  `cancel_time` datetime DEFAULT NULL COMMENT '取消时间',
  `complete_time` datetime DEFAULT NULL COMMENT '完成时间',
  `tx_hash` varchar(100) DEFAULT NULL COMMENT '交易hash',
  PRIMARY KEY (`rid`),
  KEY `idx_fms_recharge_order_1` (`bot_rid`),
  KEY `idx_fms_recharge_order_2` (`need_pay_price`,`status`),
  KEY `idx_fms_recharge_order_3` (`tx_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='充值表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_fms_recharge_order`
--

LOCK TABLES `t_fms_recharge_order` WRITE;
/*!40000 ALTER TABLE `t_fms_recharge_order` DISABLE KEYS */;
/*!40000 ALTER TABLE `t_fms_recharge_order` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t_fms_wallet_trade_list`
--

DROP TABLE IF EXISTS `t_fms_wallet_trade_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t_fms_wallet_trade_list` (
  `rid` int(11) NOT NULL AUTO_INCREMENT COMMENT '主ID',
  `tx_hash` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '交易hash',
  `transferfrom_address` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '发送钱包地址,交易发起方	',
  `transferto_address` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '接收钱包地址,交易接收方',
  `coin_name` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '交易币名',
  `amount` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '交易数额',
  `timestamp` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '交易时间',
  `process_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '处理状态,数据字典',
  `process_time` datetime DEFAULT NULL COMMENT '处理时间',
  `process_comments` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '处理备注',
  `get_time` datetime DEFAULT NULL COMMENT '拉取交易时间',
  `tg_notice_status_receive` char(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N' COMMENT 'TG收款通知状态:N/Y,如果配置不通知,状态也更新为Y',
  `tg_notice_status_send` char(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N' COMMENT 'TG成功通知状态:N/Y,如果配置不通知,状态也更新为Y',
  `recharge_order_rid` int(11) DEFAULT NULL COMMENT '充值订单表ID',
  PRIMARY KEY (`rid`),
  UNIQUE KEY `idx_fms_wallet_trade_list_1` (`tx_hash`),
  KEY `idx_fms_wallet_trade_list_2` (`transferfrom_address`),
  KEY `idx_fms_wallet_trade_list_3` (`transferto_address`),
  KEY `idx_fms_wallet_trade_list_4` (`process_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='机器人充值交易记录表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_fms_wallet_trade_list`
--

LOCK TABLES `t_fms_wallet_trade_list` WRITE;
/*!40000 ALTER TABLE `t_fms_wallet_trade_list` DISABLE KEYS */;
/*!40000 ALTER TABLE `t_fms_wallet_trade_list` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t_model_has_permissions`
--

DROP TABLE IF EXISTS `t_model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t_model_has_permissions` (
  `permission_id` int(10) unsigned NOT NULL,
  `model_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `t_model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `t_model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `t_permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_model_has_permissions`
--

LOCK TABLES `t_model_has_permissions` WRITE;
/*!40000 ALTER TABLE `t_model_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `t_model_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t_model_has_roles`
--

DROP TABLE IF EXISTS `t_model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t_model_has_roles` (
  `role_id` int(10) unsigned NOT NULL,
  `model_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `t_model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `t_model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `t_roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_model_has_roles`
--

LOCK TABLES `t_model_has_roles` WRITE;
/*!40000 ALTER TABLE `t_model_has_roles` DISABLE KEYS */;
INSERT INTO `t_model_has_roles` VALUES (1,'App\\Models\\Admin\\Admin',1),(1,'App\\Models\\Admin\\Admin',2),(1,'App\\Models\\Admin\\Admin',6);
/*!40000 ALTER TABLE `t_model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t_monitor_bot`
--

DROP TABLE IF EXISTS `t_monitor_bot`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t_monitor_bot` (
  `rid` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `bot_rid` int(11) NOT NULL COMMENT '机器人ID',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态:0开启,1关闭',
  `price_usdt_5` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '价格:5个,0表示不开启',
  `price_usdt_10` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '价格:10个,0表示不开启',
  `price_usdt_20` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '价格:20个,0表示不开启',
  `price_usdt_50` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '价格:50个,0表示不开启',
  `price_usdt_100` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '价格:100个,0表示不开启',
  `price_usdt_200` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '价格:200个,0表示不开启',
  `comments` varchar(3000) DEFAULT NULL COMMENT '备注',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`rid`),
  UNIQUE KEY `idx_monitor_bot_1` (`bot_rid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COMMENT='机器人监控表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_monitor_bot`
--

LOCK TABLES `t_monitor_bot` WRITE;
/*!40000 ALTER TABLE `t_monitor_bot` DISABLE KEYS */;
INSERT INTO `t_monitor_bot` VALUES (2,1,0,5.0000,8.0000,13.0000,24.0000,38.0000,56.0000,'','2023-11-25 19:54:54','2023-11-25 23:44:54');
/*!40000 ALTER TABLE `t_monitor_bot` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t_monitor_wallet`
--

DROP TABLE IF EXISTS `t_monitor_wallet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t_monitor_wallet` (
  `rid` int(11) NOT NULL AUTO_INCREMENT COMMENT '主ID',
  `chain_type` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '链类型:trc波场,eth以太,okx欧易,bsc币安',
  `monitor_wallet` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '监控钱包地址',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态:0开启,1关闭,关闭则不监控余额',
  `bot_rid` int(11) DEFAULT NULL COMMENT '机器人ID,用于发送通知的机器人,没有则不推送',
  `tg_notice_obj` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'TG通知对象,可多个,英文逗号隔开,可为用户或者群组',
  `balance_alert` decimal(14,2) NOT NULL DEFAULT '0.00' COMMENT '余额预警金额,0表示不监控余额',
  `balance_amount` decimal(14,2) NOT NULL DEFAULT '0.00' COMMENT '当前余额',
  `comments` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '备注',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`rid`),
  UNIQUE KEY `idx_monitor_wallet_1` (`chain_type`,`monitor_wallet`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='监控钱包设置';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_monitor_wallet`
--

LOCK TABLES `t_monitor_wallet` WRITE;
/*!40000 ALTER TABLE `t_monitor_wallet` DISABLE KEYS */;
/*!40000 ALTER TABLE `t_monitor_wallet` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t_permissions`
--

DROP TABLE IF EXISTS `t_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t_permissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `route` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_permissions`
--

LOCK TABLES `t_permissions` WRITE;
/*!40000 ALTER TABLE `t_permissions` DISABLE KEYS */;
INSERT INTO `t_permissions` VALUES (5,'主页','web','2020-09-22 08:33:50','2020-09-22 08:33:50','',0),(6,'主页列表','web','2020-09-22 08:37:02','2020-09-22 08:37:02','admin.home',5),(7,'系统管理','web','2020-09-22 08:37:37','2020-09-22 08:37:37','',0),(8,'管理员管理','web','2020-09-22 08:38:14','2020-09-22 08:38:14','admin.system.admin.index',7),(9,'权限管理','web','2020-09-22 08:38:23','2020-09-22 08:38:23','admin.system.permission.index',7),(10,'角色管理','web','2020-09-22 08:38:34','2020-09-22 08:38:34','admin.system.role.index',7),(11,'添加管理员','web','2020-09-22 08:39:33','2020-09-22 08:39:33','admin.system.admin.add',8),(12,'修改管理员状态','web','2020-09-22 09:23:59','2020-09-22 09:23:59','admin.system.admin.change_status',8),(13,'修改管理员资料','web','2020-09-22 09:24:12','2020-09-22 09:24:12','admin.system.admin.update',8),(14,'删除管理员','web','2020-09-22 09:24:21','2020-09-22 09:24:21','admin.system.admin.delete',8),(15,'添加角色','web','2020-09-22 09:25:14','2020-09-22 09:25:14','admin.system.role.add',10),(16,'编辑角色权限','web','2020-09-22 09:25:23','2020-09-22 09:25:23','admin.system.role.show_permissions',10),(17,'修改角色名称','web','2020-09-22 09:25:30','2020-09-22 09:25:30','admin.system.role.update',10),(18,'删除角色','web','2020-09-22 09:25:38','2020-09-22 09:25:38','admin.system.role.del',10),(19,'系统设置','web','2021-07-09 05:42:29','2021-07-09 05:42:29','',0),(20,'配置信息','web','2021-07-09 05:42:47','2021-07-09 05:42:47','admin.setting.config.index',19),(21,'搜索查询_配置信息','web','2021-07-09 05:42:55','2021-07-09 05:42:55','admin.search',20),(22,'数据字典','web','2021-07-09 05:43:06','2021-07-09 05:43:06','admin.setting.dictionary.index',19),(23,'添加数据字典','web','2021-07-09 05:43:27','2021-07-09 05:43:27','admin.setting.dictionary.store',22),(24,'编辑数据字典','web','2021-07-09 05:43:38','2021-07-09 05:43:38','admin.setting.dictionary.update',22),(25,'删除数据字典','web','2021-07-09 05:43:49','2021-07-09 05:43:49','admin.setting.dictionary.delete',22),(26,'搜索查询_数据字典','web','2021-07-09 05:43:56','2021-07-09 05:43:56','admin.search',22),(27,'应用升级','web','2021-07-09 05:44:09','2021-07-09 05:44:09','admin.setting.app_version.index',19),(28,'升级应用升级','web','2021-07-09 05:44:25','2021-07-09 05:44:25','admin.setting.app_version.store',27),(29,'编辑应用升级','web','2021-07-09 05:44:36','2021-07-09 05:44:36','admin.setting.app_version.edit',27),(30,'搜索查询_应用升级','web','2021-07-09 05:44:53','2021-07-09 05:44:53','admin.search',27);
/*!40000 ALTER TABLE `t_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t_premium_platform`
--

DROP TABLE IF EXISTS `t_premium_platform`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t_premium_platform` (
  `rid` int(11) NOT NULL AUTO_INCREMENT COMMENT '主ID',
  `bot_rid` int(11) NOT NULL COMMENT '机器人ID',
  `platform_name` tinyint(4) NOT NULL COMMENT '会员平台',
  `tg_admin_uid` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'TG管理员用户ID,多个英文逗号隔开',
  `platform_hash` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '平台hash',
  `platform_cookie` varchar(3000) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '平台cookie',
  `platform_phrase` varchar(3000) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '助记词',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态:0开启,1关闭',
  `receive_wallet` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '收款钱包',
  `get_tx_time` datetime DEFAULT NULL COMMENT '开始拉取交易时间',
  `tg_notice_obj_receive` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'TG通知对象(收款),可多个,英文逗号隔开,可为用户或者群组',
  `tg_notice_obj_send` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'TG通知对象(成功),可多个,英文逗号隔开,可为用户或者群组',
  `comments` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '备注',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`rid`),
  KEY `idx_premium_platform_1` (`bot_rid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='机器人会员平台表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_premium_platform`
--

LOCK TABLES `t_premium_platform` WRITE;
/*!40000 ALTER TABLE `t_premium_platform` DISABLE KEYS */;
INSERT INTO `t_premium_platform` VALUES (1,1,1,'6666666','23deweww',NULL,NULL,0,'ttttttttttttttt','2023-11-21 00:00:00','6666666','-1111111','','2023-11-21 23:45:17',NULL);
/*!40000 ALTER TABLE `t_premium_platform` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t_premium_platform_order`
--

DROP TABLE IF EXISTS `t_premium_platform_order`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t_premium_platform_order` (
  `rid` int(11) NOT NULL AUTO_INCREMENT COMMENT '主ID',
  `bot_rid` int(11) NOT NULL COMMENT '机器人ID',
  `premium_platform_rid` int(11) NOT NULL COMMENT '机器人会员平台ID',
  `source_type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '下单来源:1人工下单,2自动下单',
  `buy_tg_uid` varchar(100) NOT NULL COMMENT '下单TG用户ID',
  `buy_tg_username` varchar(100) DEFAULT NULL COMMENT '下单TG用户名',
  `premium_tg_username` varchar(100) NOT NULL COMMENT '开通会员用户名',
  `need_pay_usdt` decimal(12,4) NOT NULL COMMENT '应支付USDT',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态:0待支付,1待开通,2已开通,3已过期,4会员取消',
  `premium_platform_package_rid` int(11) NOT NULL COMMENT '机器人会员套餐ID',
  `premium_package_month` int(11) NOT NULL COMMENT '开通会员月份',
  `comments` varchar(255) DEFAULT NULL COMMENT '备注',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  `expire_time` datetime NOT NULL COMMENT '过期时间',
  `cancel_time` datetime DEFAULT NULL COMMENT '取消时间',
  `complete_time` datetime DEFAULT NULL COMMENT '完成时间',
  `recipient` varchar(1000) DEFAULT NULL COMMENT '用户唯一标识(第一次返回)',
  `tx_hash` varchar(200) DEFAULT NULL COMMENT '交易哈希',
  PRIMARY KEY (`rid`),
  KEY `idx_premium_platform_order_1` (`bot_rid`),
  KEY `idx_premium_platform_order_2` (`need_pay_usdt`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='机器人会员订单表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_premium_platform_order`
--

LOCK TABLES `t_premium_platform_order` WRITE;
/*!40000 ALTER TABLE `t_premium_platform_order` DISABLE KEYS */;
/*!40000 ALTER TABLE `t_premium_platform_order` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t_premium_platform_package`
--

DROP TABLE IF EXISTS `t_premium_platform_package`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t_premium_platform_package` (
  `rid` int(11) NOT NULL AUTO_INCREMENT COMMENT '主ID',
  `bot_rid` int(11) NOT NULL COMMENT '机器人ID',
  `premium_platform_rid` int(11) NOT NULL COMMENT '机器人会员平台ID',
  `package_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '套餐名称',
  `package_month` int(11) NOT NULL COMMENT '套餐月份',
  `usdt_price` decimal(14,2) NOT NULL COMMENT 'usdt售价',
  `callback_data` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '回调数据,不重复,随机生成	',
  `seq_sn` int(11) NOT NULL DEFAULT '0' COMMENT '排序,数字越大越靠前',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态:0开启,1关闭',
  `show_notes` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '显示说明',
  `package_pic` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '套餐图片',
  `comments` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '备注',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`rid`),
  UNIQUE KEY `idx_premium_platform_package_2` (`callback_data`),
  KEY `idx_premium_platform_package_1` (`bot_rid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='机器人会员套餐表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_premium_platform_package`
--

LOCK TABLES `t_premium_platform_package` WRITE;
/*!40000 ALTER TABLE `t_premium_platform_package` DISABLE KEYS */;
INSERT INTO `t_premium_platform_package` VALUES (1,1,1,'3个月 价格 15 USDT',3,15.00,'premium_aee6034741a76c9c947844f196fc58e3',1,0,'',NULL,NULL,'2023-11-21 23:46:14',NULL),(2,1,1,'6个月 价格 25 USDT',6,25.00,'premium_310fe79585da99e1b3edd1393ff6a36a',2,0,'',NULL,NULL,'2023-11-21 23:46:31',NULL),(3,1,1,'12个月 价格 45 USDT',12,45.00,'premium_a21a27e202cdc317de8466ef7250a6f1',3,0,'',NULL,NULL,'2023-11-21 23:46:44',NULL);
/*!40000 ALTER TABLE `t_premium_platform_package` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t_premium_wallet_trade_list`
--

DROP TABLE IF EXISTS `t_premium_wallet_trade_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t_premium_wallet_trade_list` (
  `rid` int(11) NOT NULL AUTO_INCREMENT COMMENT '主ID',
  `tx_hash` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '交易hash',
  `transferfrom_address` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '发送钱包地址,交易发起方	',
  `transferto_address` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '接收钱包地址,交易接收方',
  `coin_name` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '交易币名',
  `amount` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '交易数额',
  `timestamp` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '交易时间',
  `process_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '处理状态,数据字典',
  `process_time` datetime DEFAULT NULL COMMENT '处理时间',
  `process_comments` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '处理备注',
  `get_time` datetime DEFAULT NULL COMMENT '拉取交易时间',
  `tg_notice_status_receive` char(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N' COMMENT 'TG收款通知状态:N/Y,如果配置不通知,状态也更新为Y',
  `tg_notice_status_send` char(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N' COMMENT 'TG成功通知状态:N/Y,如果配置不通知,状态也更新为Y',
  `platform_order_rid` int(11) DEFAULT NULL COMMENT '会员订单表ID',
  `premium_package_rid` int(11) DEFAULT NULL COMMENT '会员套餐表ID',
  `premium_platform_rid` int(11) DEFAULT NULL COMMENT '会员平台表ID',
  PRIMARY KEY (`rid`),
  UNIQUE KEY `idx_premium_wallet_trade_list_1` (`tx_hash`),
  KEY `idx_premium_wallet_trade_list_2` (`transferfrom_address`),
  KEY `idx_premium_wallet_trade_list_3` (`transferto_address`),
  KEY `idx_premium_wallet_trade_list_4` (`process_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='机器人会员交易记录表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_premium_wallet_trade_list`
--

LOCK TABLES `t_premium_wallet_trade_list` WRITE;
/*!40000 ALTER TABLE `t_premium_wallet_trade_list` DISABLE KEYS */;
/*!40000 ALTER TABLE `t_premium_wallet_trade_list` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t_role_has_permissions`
--

DROP TABLE IF EXISTS `t_role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t_role_has_permissions` (
  `permission_id` int(10) unsigned NOT NULL,
  `role_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `t_role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `t_role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `t_permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `t_role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `t_roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_role_has_permissions`
--

LOCK TABLES `t_role_has_permissions` WRITE;
/*!40000 ALTER TABLE `t_role_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `t_role_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t_roles`
--

DROP TABLE IF EXISTS `t_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t_roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_roles`
--

LOCK TABLES `t_roles` WRITE;
/*!40000 ALTER TABLE `t_roles` DISABLE KEYS */;
INSERT INTO `t_roles` VALUES (1,'超级管理员','web',NULL,NULL,NULL);
/*!40000 ALTER TABLE `t_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t_shop_goods`
--

DROP TABLE IF EXISTS `t_shop_goods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t_shop_goods` (
  `rid` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `goods_name` varchar(50) NOT NULL COMMENT '商品名称',
  `goods_type` tinyint(4) NOT NULL COMMENT '商品类型:1虚拟卡密',
  `goods_usdt_price` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '商品价格(USDT):当对应卡密价格位0时,取商品价格',
  `goods_trx_price` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '商品价格(TRX):当对应卡密价格位0时,取商品价格',
  `show_notes` varchar(1000) DEFAULT NULL COMMENT '显示说明',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态:0开启,1关闭',
  `seq_sn` int(11) NOT NULL DEFAULT '0' COMMENT '排序:数字越大越考前',
  `comments` varchar(3000) DEFAULT NULL COMMENT '备注',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`rid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商城商品表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_shop_goods`
--

LOCK TABLES `t_shop_goods` WRITE;
/*!40000 ALTER TABLE `t_shop_goods` DISABLE KEYS */;
/*!40000 ALTER TABLE `t_shop_goods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t_shop_goods_bot`
--

DROP TABLE IF EXISTS `t_shop_goods_bot`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t_shop_goods_bot` (
  `rid` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `goods_rid` int(11) NOT NULL COMMENT '商品主ID',
  `bot_rid` int(11) NOT NULL COMMENT '机器人ID',
  `goods_usdt_discount` decimal(4,2) NOT NULL DEFAULT '1.00' COMMENT '商品折扣(USDT):1表示无折扣,0.8表示8折',
  `goods_trx_discount` decimal(4,2) NOT NULL DEFAULT '1.00' COMMENT '商品折扣(TRX):1表示无折扣,0.8表示8折',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态:0开启,1关闭',
  `comments` varchar(3000) DEFAULT NULL COMMENT '备注',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`rid`),
  UNIQUE KEY `idx_shop_goods_bot_1` (`goods_rid`,`bot_rid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商城商品机器人表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_shop_goods_bot`
--

LOCK TABLES `t_shop_goods_bot` WRITE;
/*!40000 ALTER TABLE `t_shop_goods_bot` DISABLE KEYS */;
/*!40000 ALTER TABLE `t_shop_goods_bot` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t_shop_goods_cdkey`
--

DROP TABLE IF EXISTS `t_shop_goods_cdkey`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t_shop_goods_cdkey` (
  `rid` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `goods_rid` int(11) NOT NULL COMMENT '商品主ID',
  `cdkey_no` varchar(100) NOT NULL COMMENT '卡号,钱包地址',
  `cdkey_pwd` varchar(2000) NOT NULL COMMENT '卡密,钱包私钥',
  `cdkey_usdt_price` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '卡密价格(USDT):当卡密价格位0时,取商品价格',
  `cdkey_trx_price` decimal(16,4) NOT NULL DEFAULT '0.0000' COMMENT '卡密价格(TRX):当卡密价格位0时,取商品价格',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态:0待上架,1售卖中,2已售卖',
  `seq_sn` int(11) NOT NULL DEFAULT '0' COMMENT '排序:数字越大越考前',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`rid`),
  UNIQUE KEY `idx_shop_goods_cdkey_2` (`cdkey_no`),
  KEY `idx_shop_goods_cdkey_1` (`goods_rid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商城商品卡密表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_shop_goods_cdkey`
--

LOCK TABLES `t_shop_goods_cdkey` WRITE;
/*!40000 ALTER TABLE `t_shop_goods_cdkey` DISABLE KEYS */;
/*!40000 ALTER TABLE `t_shop_goods_cdkey` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t_shop_order`
--

DROP TABLE IF EXISTS `t_shop_order`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t_shop_order` (
  `rid` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `bot_rid` int(11) NOT NULL COMMENT '机器人ID',
  `tg_uid` varchar(50) NOT NULL COMMENT '下单tgID',
  `tg_username` varchar(100) DEFAULT NULL COMMENT '下单tg用户名',
  `cdkey_no` varchar(100) DEFAULT NULL COMMENT '卡号',
  `cdkey_pwd` varchar(2000) DEFAULT NULL COMMENT '卡密',
  `pay_type` tinyint(4) NOT NULL COMMENT '支付方式:1trx余额,2usdt余额',
  `pay_price` varchar(50) DEFAULT NULL COMMENT '支付金额',
  `comments` varchar(3000) DEFAULT NULL COMMENT '备注',
  `pay_time` datetime DEFAULT NULL COMMENT '支付时间',
  PRIMARY KEY (`rid`),
  KEY `idx_shop_order_1` (`cdkey_no`),
  KEY `idx_shop_order_2` (`tg_username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商城订单表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_shop_order`
--

LOCK TABLES `t_shop_order` WRITE;
/*!40000 ALTER TABLE `t_shop_order` DISABLE KEYS */;
/*!40000 ALTER TABLE `t_shop_order` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t_sys_config`
--

DROP TABLE IF EXISTS `t_sys_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t_sys_config` (
  `rid` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `config_key` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '配置项key',
  `config_val` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '配置项value',
  `comments` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '备注',
  `create_by` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '创建人',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_by` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '修改人',
  `update_time` datetime DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`rid`),
  UNIQUE KEY `idx_ice_sys_config_1` (`config_key`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='系统配置';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_sys_config`
--

LOCK TABLES `t_sys_config` WRITE;
/*!40000 ALTER TABLE `t_sys_config` DISABLE KEYS */;
INSERT INTO `t_sys_config` VALUES (1,'job_url','{\"url\":\"http:\\/\\/tgbot-job:9503\"}','任务域名url','1','2022-05-05 12:55:54','1','2022-05-05 12:55:54'),(2,'ton_url','{\"url\":\"http:\\/\\/host.docker.internal:4444\\/api\\/premium\"}','ton支付接口url(不需要开通tg会员,用不到这个接口)','1','2022-05-05 12:55:54','1','2022-05-05 12:55:54');
/*!40000 ALTER TABLE `t_sys_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t_telegram_bot`
--

DROP TABLE IF EXISTS `t_telegram_bot`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t_telegram_bot` (
  `rid` int(11) NOT NULL AUTO_INCREMENT COMMENT '主ID',
  `bot_token` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '机器人token',
  `bot_admin_username` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '-' COMMENT '机器人管理用户名',
  `bot_firstname` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '机器人显示名称',
  `bot_username` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '机器人名称',
  `comments` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '备注',
  `create_by` int(11) DEFAULT NULL COMMENT '创建人',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_by` int(11) DEFAULT NULL COMMENT '修改人',
  `update_time` datetime DEFAULT NULL COMMENT '修改时间',
  `recharge_wallet_addr` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '充值钱包地址',
  `get_tx_time` datetime DEFAULT NULL COMMENT '开始拉取交易时间',
  PRIMARY KEY (`rid`),
  UNIQUE KEY `idx_telegram_bot_1` (`bot_token`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='telegram机器人列表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_telegram_bot`
--

LOCK TABLES `t_telegram_bot` WRITE;
/*!40000 ALTER TABLE `t_telegram_bot` DISABLE KEYS */;
INSERT INTO `t_telegram_bot` VALUES (1,'6666666:AAHOcqAPQuqtO3','@aaaa','TRX 能量 会员 靓号 24小时营业','pri_bot','own-01',NULL,'2023-11-21 23:03:34',NULL,'2023-11-21 23:07:56','ttttttttt','2023-11-21 00:00:00');
/*!40000 ALTER TABLE `t_telegram_bot` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t_telegram_bot_ad`
--

DROP TABLE IF EXISTS `t_telegram_bot_ad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t_telegram_bot_ad` (
  `rid` int(11) NOT NULL AUTO_INCREMENT COMMENT '主ID',
  `bot_rid` int(11) NOT NULL COMMENT '机器人ID',
  `notice_cycle` tinyint(4) NOT NULL DEFAULT '1' COMMENT '通知周期:1每分钟,2每10分钟,3每30分钟,4每小时,5每天',
  `notice_obj` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '通知对象,可多个,英文逗号隔开,可为用户或者群组',
  `notice_photo` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '广告图片',
  `notice_ad` varchar(2000) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '广告内容',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态:0开启,1关闭,关闭则不发广告',
  `last_notice_time` datetime DEFAULT NULL COMMENT '上次通知时间',
  `create_by` int(11) DEFAULT NULL COMMENT '创建人',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_by` int(11) DEFAULT NULL COMMENT '修改人',
  `update_time` datetime DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`rid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='telegram广告设置';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_telegram_bot_ad`
--

LOCK TABLES `t_telegram_bot_ad` WRITE;
/*!40000 ALTER TABLE `t_telegram_bot_ad` DISABLE KEYS */;
INSERT INTO `t_telegram_bot_ad` VALUES (1,1,5,'-111111',NULL,'✅24小时兑换地址： <code>${trxusdtwallet}</code> (点击自动复制) \n\n实时汇率：\n10 USDT = ${trx10usdtrate} TRX\n100 USDT = ${trx100usdtrate} TRX\n1000 USDT = ${trx1000usdtrate} TRX\n\n❌请勿从交易所直接提现到机器人账户！！\n${trxusdtshownotes}\n✅只支持1 USDT及其以上的金额兑换，若转入1 USDT以下金额，将无法退还！！！\n✅另有波场靓号出售，选号咨询客服！\n\n联系客服：${tgbotadmin}\n✅能量租用：/buyenergy\n✅购买会员：/buypremium',0,'2023-12-02 00:52:00',NULL,'2023-11-22 00:19:22',NULL,'2023-11-22 00:52:14');
/*!40000 ALTER TABLE `t_telegram_bot_ad` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t_telegram_bot_ad_keyboard`
--

DROP TABLE IF EXISTS `t_telegram_bot_ad_keyboard`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t_telegram_bot_ad_keyboard` (
  `rid` int(11) NOT NULL AUTO_INCREMENT COMMENT '主ID',
  `bot_rid` int(11) NOT NULL COMMENT '机器人ID',
  `ad_rid` int(11) NOT NULL COMMENT '广告ID',
  `keyboard_rid` int(11) NOT NULL COMMENT '键盘ID',
  `create_by` int(11) DEFAULT NULL COMMENT '创建人',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_by` int(11) DEFAULT NULL COMMENT '修改人',
  `update_time` datetime DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`rid`),
  UNIQUE KEY `idx_telegram_bot_ad_keyboard_1` (`bot_rid`,`ad_rid`,`keyboard_rid`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='telegram广告键盘关联';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_telegram_bot_ad_keyboard`
--

LOCK TABLES `t_telegram_bot_ad_keyboard` WRITE;
/*!40000 ALTER TABLE `t_telegram_bot_ad_keyboard` DISABLE KEYS */;
INSERT INTO `t_telegram_bot_ad_keyboard` VALUES (1,1,1,5,NULL,'2023-11-22 00:49:14',NULL,NULL),(2,1,1,6,NULL,'2023-11-22 00:49:14',NULL,NULL),(3,1,1,7,NULL,'2023-11-22 00:49:14',NULL,NULL),(4,1,1,8,NULL,'2023-11-22 00:49:14',NULL,NULL);
/*!40000 ALTER TABLE `t_telegram_bot_ad_keyboard` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t_telegram_bot_command`
--

DROP TABLE IF EXISTS `t_telegram_bot_command`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t_telegram_bot_command` (
  `rid` int(11) NOT NULL AUTO_INCREMENT COMMENT '主ID',
  `bot_rid` int(11) NOT NULL COMMENT '机器人ID',
  `command` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '命令',
  `description` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '描述',
  `command_type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '命令类型:1通用,2私聊,3群聊',
  `seq_sn` int(11) NOT NULL DEFAULT '0' COMMENT '排序:数字越大越靠前',
  `create_by` int(11) DEFAULT NULL COMMENT '创建人',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_by` int(11) DEFAULT NULL COMMENT '修改人',
  `update_time` datetime DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`rid`),
  KEY `idx_telegram_bot_command_1` (`bot_rid`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='机器人快捷命令';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_telegram_bot_command`
--

LOCK TABLES `t_telegram_bot_command` WRITE;
/*!40000 ALTER TABLE `t_telegram_bot_command` DISABLE KEYS */;
INSERT INTO `t_telegram_bot_command` VALUES (1,1,'start','开始使用',1,99,NULL,'2023-11-21 23:20:47',NULL,NULL),(2,1,'trx','USDT兑换TRX',1,98,NULL,'2023-11-21 23:21:38',NULL,NULL),(3,1,'buyenergy','租用能量',1,97,NULL,'2023-11-21 23:22:03',NULL,NULL),(4,1,'buypremium','购买会员',2,96,NULL,'2023-11-21 23:22:46',NULL,NULL),(5,1,'z0','查询欧意价格',1,95,NULL,'2023-11-21 23:23:06',NULL,NULL);
/*!40000 ALTER TABLE `t_telegram_bot_command` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t_telegram_bot_group`
--

DROP TABLE IF EXISTS `t_telegram_bot_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t_telegram_bot_group` (
  `rid` int(11) NOT NULL AUTO_INCREMENT COMMENT '主ID',
  `bot_rid` int(11) NOT NULL COMMENT '机器人ID',
  `group_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '群组类型',
  `tg_groupid` bigint(20) NOT NULL COMMENT 'tg群组ID',
  `tg_groupusername` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'tg群组名',
  `tg_groupnickname` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'tg群组昵称',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '当前状态:1使用中,2已停用,3群组不存在',
  `first_time` datetime DEFAULT NULL COMMENT '关注时间',
  `last_time` datetime DEFAULT NULL COMMENT '最近时间',
  `stop_time` datetime DEFAULT NULL COMMENT '停用时间',
  `is_admin` char(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N' COMMENT '是否admin',
  PRIMARY KEY (`rid`),
  UNIQUE KEY `idx_telegram_bot_group_1` (`bot_rid`,`tg_groupid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='机器人群组列表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_telegram_bot_group`
--

LOCK TABLES `t_telegram_bot_group` WRITE;
/*!40000 ALTER TABLE `t_telegram_bot_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `t_telegram_bot_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t_telegram_bot_keyboard`
--

DROP TABLE IF EXISTS `t_telegram_bot_keyboard`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t_telegram_bot_keyboard` (
  `rid` int(11) NOT NULL AUTO_INCREMENT COMMENT '主ID',
  `keyboard_type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '类型:1键盘,2内联按钮',
  `keyboard_name` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '键盘名称',
  `inline_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '内联按钮类型:1url,2回调',
  `keyboard_value` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '内联按钮值',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态:0启动,1禁用',
  `create_by` int(11) DEFAULT NULL COMMENT '创建人',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_by` int(11) DEFAULT NULL COMMENT '修改人',
  `update_time` datetime DEFAULT NULL COMMENT '修改时间',
  `seq_sn` int(11) NOT NULL DEFAULT '0' COMMENT '排序,数字越大越靠前',
  PRIMARY KEY (`rid`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='telegram键盘设置';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_telegram_bot_keyboard`
--

LOCK TABLES `t_telegram_bot_keyboard` WRITE;
/*!40000 ALTER TABLE `t_telegram_bot_keyboard` DISABLE KEYS */;

INSERT INTO `t_telegram_bot_keyboard` (`rid`, `keyboard_type`, `keyboard_name`, `inline_type`, `keyboard_value`, `status`, `create_by`, `create_time`, `update_by`, `update_time`, `seq_sn`) VALUES
(1, 1, '🙎联系客服', 0, '-', 0, NULL, '2023-11-21 23:25:10', NULL, '2023-11-27 18:22:34', 80),
(2, 1, '💹闪兑TRX', 0, '-', 0, NULL, '2023-11-21 23:25:49', NULL, '2024-01-10 18:40:44', 98),
(3, 1, '🔋闪租能量', 0, '-', 0, NULL, '2023-11-21 23:26:09', NULL, '2024-01-10 17:40:02', 97),
(4, 1, '👑购买会员', 0, '-', 0, NULL, '2023-11-21 23:26:22', NULL, '2023-11-23 23:33:18', 96),
(5, 2, '💎TRX兑换', 2, '兑换', 0, NULL, '2023-11-22 00:47:13', NULL, NULL, 99),
(6, 2, '🔋购买能量', 2, '购买能量', 0, NULL, '2023-11-22 00:47:43', NULL, NULL, 98),
(7, 2, '🛎开通会员', 1, 'https://t.me/aaaa', 0, NULL, '2023-11-22 00:48:27', NULL, '2023-11-22 00:51:10', 97),
(8, 2, '👳‍♀️联系老板', 1, 'https://t.me/aa', 0, NULL, '2023-11-22 00:49:06', NULL, '2023-11-27 18:20:31', 92),
(9, 1, '⚡️我要充值', 0, '-', 1, NULL, '2023-11-23 23:23:48', NULL, '2023-11-23 23:31:46', 94),
(10, 1, '👁钱包监控', 0, '-', 0, NULL, '2023-11-25 18:10:30', NULL, NULL, 93),
(11, 1, '🔠购买靓号', 0, '-', 0, NULL, '2023-11-26 19:23:39', NULL, '2023-11-27 18:21:59', 95),
(12, 1, '👨‍💼个人中心', 0, '-', 0, NULL, '2023-11-26 23:22:34', NULL, NULL, 91),
(13, 1, '🏧欧意汇率', 0, '-', 1, NULL, '2023-11-26 23:56:58', NULL, NULL, 90),
(14, 1, '🖌笔数套餐', 0, '-', 0, NULL, '2024-01-10 17:31:56', NULL, '2024-01-10 17:39:12', 97),
(15, 1, '❇️智能托管', 0, '-', 0, NULL, '2024-01-10 18:31:25', NULL, NULL, 97);
/*!40000 ALTER TABLE `t_telegram_bot_keyboard` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t_telegram_bot_keyreply`
--

DROP TABLE IF EXISTS `t_telegram_bot_keyreply`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t_telegram_bot_keyreply` (
  `rid` int(11) NOT NULL AUTO_INCREMENT COMMENT '主ID',
  `bot_rid` int(11) NOT NULL COMMENT '机器人ID',
  `key_type` tinyint(4) NOT NULL COMMENT '关键字类型:1消息内容,2入群通知',
  `monitor_word` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '关键字,多个英文逗号隔开',
  `reply_photo` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '回复图片',
  `reply_content` varchar(2000) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '回复内容',
  `opt_type` int(11) NOT NULL COMMENT '操作指令:1回复消息',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态:0启动,1禁用',
  `create_by` int(11) DEFAULT NULL COMMENT '创建人',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_by` int(11) DEFAULT NULL COMMENT '修改人',
  `update_time` datetime DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`rid`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='telegram关键字回复设置';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_telegram_bot_keyreply`
--

LOCK TABLES `t_telegram_bot_keyreply` WRITE;
/*!40000 ALTER TABLE `t_telegram_bot_keyreply` DISABLE KEYS */;

INSERT INTO `t_telegram_bot_keyreply` (`rid`, `bot_rid`, `key_type`, `monitor_word`, `reply_photo`, `reply_content`, `opt_type`, `status`, `create_by`, `create_time`, `update_by`, `update_time`) VALUES
(1, 1, 2, '-', '', '24小时自动兑换地址:\n <code>${trxusdtwallet}</code>(点击自动复制) \n\n✅进U即兑,全自动返TRX,1U起兑,24小时全自动\n${trxusdtshownotes}\n❌请勿使用交易所或中心化钱包转账\n✅如有老板需要用交易所转账,提前联系群老板:  ${tgbotadmin}\n✅24小时兑换机器人：${tgbotname}\n\n✅能量租用：/buyenergy\n✅购买会员：/buypremium', 1, 0, NULL, '2023-11-21 23:28:35', NULL, '2023-12-02 20:17:01'),
(2, 1, 1, '💹闪兑TRX,闪兑TRX,USDT兑TRX,兑换,USDT,usdt,TRX,trx,闪兑,地址,换trx,换TRX,开始,jusdt,/trx,TRX兑换,eth兑换,预支点TRX,ETH,eth,btc,bnb,汇率,价格,1,2,3,4,5,6,7,10,100,50,20,30,200,100,帮助,菜单,help,menu,/start', '', '➖➖➖➖➖➖➖➖➖➖➖➖\n24小时自动兑换地址:\n <code>${trxusdtwallet}</code> (点击自动复制) \n➖➖➖➖➖➖➖➖➖➖➖➖\n当前汇率：\n1 USDT = ${trxusdtrate} TRX\n10 USDT = ${trx10usdtrate} TRX\n100 USDT = ${trx100usdtrate} TRX\n1000 USDT = ${trx1000usdtrate} TRX\n➖➖➖➖➖➖➖➖➖➖➖➖\n✅进U即兑,全自动返TRX,1U起兑\n${trxusdtshownotes}\n❌请勿使用交易所或中心化钱包转账\n✅如有老板需要用交易所转账,提前联系群老板:  ${tgbotadmin}\n➖➖➖➖➖➖➖➖➖➖➖➖\n✅24小时兑换机器人：${tgbotname}\n✅能量租用：/buyenergy\n✅购买会员：/buypremium', 1, 0, NULL, '2023-11-21 23:29:56', NULL, '2024-01-10 18:57:39'),
(3, 1, 1, '联系客服,客服', '', '✅联系老板：${tgbotadmin}\n✅24小时兑换机器人：${tgbotname}\n\n✅USDT兑换TRX：/trx\n✅能量租用：/buyenergy\n✅购买会员：/buypremium', 1, 0, NULL, '2023-11-21 23:33:13', NULL, '2023-11-23 23:34:17'),
(4, 1, 1, '转账手续费', '', 'TRX转账：268 带宽\r\nUSDT转账：\r\n对方有U：345 带宽、31895 能量\r\n对方无U：345 带宽、64895 能量\r\n\r\n燃烧价值\r\n1 TRX = 1000 带宽\r\n13.3959 TRX = 31895 能量\r\n27.2559 TRX = 64895 能量', 1, 0, NULL, '2023-11-21 23:33:43', NULL, NULL),
(5, 1, 1, '开通会员,购买会员,续费会员,/buypremium', '', '🔐<b>开通/续费 Telegram Premium会员</b>\n\n\n<b>根据下列菜单，选择开通会员月份，可多次重复购买</b>', 4, 0, NULL, '2023-11-21 23:34:14', NULL, '2023-11-21 23:39:59'),
(6, 1, 1, '能量,购买能量,租用能量,代理能量,买能量,/buyenergy,🔋租用能量,🔋闪租能量,闪租能量', '', '🔋租用能量，转账无需TRX消耗，0手续费！\n\n以下数据以USDT单笔转账为例\n波场实时消耗：31895 能量（对方地址有U）\n官方手续费：≈ 13.3959 TRX\n租用能量最低费用：= <b>4.14 TRX</b>\n最高节约手续费：9.2559 TRX\n节省手续费最高约 80%\n\n<b>注意：如果对方地址没U，转账一笔需要 64895 能量</b>\n<b>根据下列菜单，选择适合自己的套餐，可多次重复购买</b>', 3, 0, NULL, '2023-11-21 23:34:48', NULL, '2024-01-10 18:57:57'),
(7, 1, 1, '查ID,查id', '', '-', 2, 0, NULL, '2023-11-21 23:41:12', NULL, NULL),
(8, 1, 1, '⚡️我要充值', '', '请在下方选择您要充值的币种', 5, 0, NULL, '2023-11-23 23:24:17', NULL, '2023-11-23 23:31:39'),
(9, 1, 1, '钱包监控', '', '监控波场TRC链，USDT,TRX,授权,多签,代理能量', 6, 0, NULL, '2023-11-25 18:12:30', NULL, '2023-11-25 18:17:18'),
(10, 1, 1, '购买靓号', '', '请选择分类，然后再选择您心仪的商品\n使用<b>TRX余额</b>或者<b>USDT余额</b>支付,可点击下方充值', 7, 0, NULL, '2023-11-26 19:24:25', NULL, '2023-11-26 22:28:10'),
(11, 1, 1, '个人中心,👨‍💼个人中心', '', '欢迎使用本机器人，您可以使用本机器人功能：\n✅U闪兑TRX：/trx\n✅能量租用：/buyenergy\n✅购买会员：/buypremium', 8, 0, NULL, '2023-11-26 23:22:52', NULL, '2023-11-26 23:26:46'),
(12, 1, 1, '🏧欧意汇率,欧意汇率,z0,Z0,z1,Z1,z2,Z2,z3,Z3,/z0', '', '-', 9, 0, NULL, '2023-11-26 23:57:24', NULL, '2023-11-26 23:58:09'),
(13, 1, 1, '笔数套餐,🖌笔数套餐', '', '--', 10, 0, NULL, '2024-01-10 18:54:10', NULL, NULL),
(14, 1, 1, '❇️智能托管,智能托管', '', '--', 11, 0, NULL, '2024-01-10 18:54:26', NULL, NULL);
/*!40000 ALTER TABLE `t_telegram_bot_keyreply` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t_telegram_bot_keyreply_keyboard`
--

DROP TABLE IF EXISTS `t_telegram_bot_keyreply_keyboard`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t_telegram_bot_keyreply_keyboard` (
  `rid` int(11) NOT NULL AUTO_INCREMENT COMMENT '主ID',
  `bot_rid` int(11) NOT NULL COMMENT '机器人ID',
  `keyreply_rid` int(11) NOT NULL COMMENT '关键字ID',
  `keyboard_rid` int(11) NOT NULL COMMENT '键盘ID',
  `create_by` int(11) DEFAULT NULL COMMENT '创建人',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_by` int(11) DEFAULT NULL COMMENT '修改人',
  `update_time` datetime DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`rid`),
  UNIQUE KEY `idx_telegram_bot_keyreply_keyboard_1` (`bot_rid`,`keyreply_rid`,`keyboard_rid`)
) ENGINE=InnoDB AUTO_INCREMENT=571 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='telegram关键字键盘关联';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_telegram_bot_keyreply_keyboard`
--

LOCK TABLES `t_telegram_bot_keyreply_keyboard` WRITE;
/*!40000 ALTER TABLE `t_telegram_bot_keyreply_keyboard` DISABLE KEYS */;

INSERT INTO `t_telegram_bot_keyreply_keyboard` (`rid`, `bot_rid`, `keyreply_rid`, `keyboard_rid`, `create_by`, `create_time`, `update_by`, `update_time`) VALUES
(571, 1, 1, 1, NULL, '2024-01-10 18:54:34', NULL, NULL),
(572, 1, 1, 2, NULL, '2024-01-10 18:54:34', NULL, NULL),
(573, 1, 1, 3, NULL, '2024-01-10 18:54:34', NULL, NULL),
(574, 1, 1, 4, NULL, '2024-01-10 18:54:34', NULL, NULL),
(575, 1, 1, 9, NULL, '2024-01-10 18:54:34', NULL, NULL),
(576, 1, 1, 10, NULL, '2024-01-10 18:54:34', NULL, NULL),
(577, 1, 1, 11, NULL, '2024-01-10 18:54:34', NULL, NULL),
(578, 1, 1, 12, NULL, '2024-01-10 18:54:34', NULL, NULL),
(579, 1, 1, 13, NULL, '2024-01-10 18:54:34', NULL, NULL),
(580, 1, 1, 14, NULL, '2024-01-10 18:54:34', NULL, NULL),
(581, 1, 1, 15, NULL, '2024-01-10 18:54:34', NULL, NULL),
(586, 1, 2, 1, NULL, '2024-01-10 18:54:34', NULL, NULL),
(587, 1, 2, 2, NULL, '2024-01-10 18:54:34', NULL, NULL),
(588, 1, 2, 3, NULL, '2024-01-10 18:54:34', NULL, NULL),
(589, 1, 2, 4, NULL, '2024-01-10 18:54:34', NULL, NULL),
(590, 1, 2, 9, NULL, '2024-01-10 18:54:34', NULL, NULL),
(591, 1, 2, 10, NULL, '2024-01-10 18:54:34', NULL, NULL),
(592, 1, 2, 11, NULL, '2024-01-10 18:54:34', NULL, NULL),
(593, 1, 2, 12, NULL, '2024-01-10 18:54:34', NULL, NULL),
(594, 1, 2, 13, NULL, '2024-01-10 18:54:34', NULL, NULL),
(595, 1, 2, 14, NULL, '2024-01-10 18:54:34', NULL, NULL),
(596, 1, 2, 15, NULL, '2024-01-10 18:54:34', NULL, NULL),
(601, 1, 3, 1, NULL, '2024-01-10 18:54:34', NULL, NULL),
(602, 1, 3, 2, NULL, '2024-01-10 18:54:34', NULL, NULL),
(603, 1, 3, 3, NULL, '2024-01-10 18:54:34', NULL, NULL),
(604, 1, 3, 4, NULL, '2024-01-10 18:54:34', NULL, NULL),
(605, 1, 3, 9, NULL, '2024-01-10 18:54:34', NULL, NULL),
(606, 1, 3, 10, NULL, '2024-01-10 18:54:34', NULL, NULL),
(607, 1, 3, 11, NULL, '2024-01-10 18:54:34', NULL, NULL),
(608, 1, 3, 12, NULL, '2024-01-10 18:54:34', NULL, NULL),
(609, 1, 3, 13, NULL, '2024-01-10 18:54:34', NULL, NULL),
(610, 1, 3, 14, NULL, '2024-01-10 18:54:34', NULL, NULL),
(611, 1, 3, 15, NULL, '2024-01-10 18:54:34', NULL, NULL),
(616, 1, 4, 1, NULL, '2024-01-10 18:54:34', NULL, NULL),
(617, 1, 4, 2, NULL, '2024-01-10 18:54:34', NULL, NULL),
(618, 1, 4, 3, NULL, '2024-01-10 18:54:34', NULL, NULL),
(619, 1, 4, 4, NULL, '2024-01-10 18:54:34', NULL, NULL),
(620, 1, 4, 9, NULL, '2024-01-10 18:54:34', NULL, NULL),
(621, 1, 4, 10, NULL, '2024-01-10 18:54:34', NULL, NULL),
(622, 1, 4, 11, NULL, '2024-01-10 18:54:34', NULL, NULL),
(623, 1, 4, 12, NULL, '2024-01-10 18:54:34', NULL, NULL),
(624, 1, 4, 13, NULL, '2024-01-10 18:54:34', NULL, NULL),
(625, 1, 4, 14, NULL, '2024-01-10 18:54:34', NULL, NULL),
(626, 1, 4, 15, NULL, '2024-01-10 18:54:34', NULL, NULL),
(631, 1, 5, 1, NULL, '2024-01-10 18:54:34', NULL, NULL),
(632, 1, 5, 2, NULL, '2024-01-10 18:54:34', NULL, NULL),
(633, 1, 5, 3, NULL, '2024-01-10 18:54:34', NULL, NULL),
(634, 1, 5, 4, NULL, '2024-01-10 18:54:34', NULL, NULL),
(635, 1, 5, 9, NULL, '2024-01-10 18:54:34', NULL, NULL),
(636, 1, 5, 10, NULL, '2024-01-10 18:54:34', NULL, NULL),
(637, 1, 5, 11, NULL, '2024-01-10 18:54:34', NULL, NULL),
(638, 1, 5, 12, NULL, '2024-01-10 18:54:34', NULL, NULL),
(639, 1, 5, 13, NULL, '2024-01-10 18:54:34', NULL, NULL),
(640, 1, 5, 14, NULL, '2024-01-10 18:54:34', NULL, NULL),
(641, 1, 5, 15, NULL, '2024-01-10 18:54:34', NULL, NULL),
(646, 1, 6, 1, NULL, '2024-01-10 18:54:34', NULL, NULL),
(647, 1, 6, 2, NULL, '2024-01-10 18:54:34', NULL, NULL),
(648, 1, 6, 3, NULL, '2024-01-10 18:54:34', NULL, NULL),
(649, 1, 6, 4, NULL, '2024-01-10 18:54:34', NULL, NULL),
(650, 1, 6, 9, NULL, '2024-01-10 18:54:34', NULL, NULL),
(651, 1, 6, 10, NULL, '2024-01-10 18:54:34', NULL, NULL),
(652, 1, 6, 11, NULL, '2024-01-10 18:54:34', NULL, NULL),
(653, 1, 6, 12, NULL, '2024-01-10 18:54:34', NULL, NULL),
(654, 1, 6, 13, NULL, '2024-01-10 18:54:34', NULL, NULL),
(655, 1, 6, 14, NULL, '2024-01-10 18:54:34', NULL, NULL),
(656, 1, 6, 15, NULL, '2024-01-10 18:54:34', NULL, NULL),
(661, 1, 7, 1, NULL, '2024-01-10 18:54:34', NULL, NULL),
(662, 1, 7, 2, NULL, '2024-01-10 18:54:34', NULL, NULL),
(663, 1, 7, 3, NULL, '2024-01-10 18:54:34', NULL, NULL),
(664, 1, 7, 4, NULL, '2024-01-10 18:54:34', NULL, NULL),
(665, 1, 7, 9, NULL, '2024-01-10 18:54:34', NULL, NULL),
(666, 1, 7, 10, NULL, '2024-01-10 18:54:34', NULL, NULL),
(667, 1, 7, 11, NULL, '2024-01-10 18:54:34', NULL, NULL),
(668, 1, 7, 12, NULL, '2024-01-10 18:54:34', NULL, NULL),
(669, 1, 7, 13, NULL, '2024-01-10 18:54:34', NULL, NULL),
(670, 1, 7, 14, NULL, '2024-01-10 18:54:34', NULL, NULL),
(671, 1, 7, 15, NULL, '2024-01-10 18:54:34', NULL, NULL),
(676, 1, 8, 1, NULL, '2024-01-10 18:54:34', NULL, NULL),
(677, 1, 8, 2, NULL, '2024-01-10 18:54:34', NULL, NULL),
(678, 1, 8, 3, NULL, '2024-01-10 18:54:34', NULL, NULL),
(679, 1, 8, 4, NULL, '2024-01-10 18:54:34', NULL, NULL),
(680, 1, 8, 9, NULL, '2024-01-10 18:54:34', NULL, NULL),
(681, 1, 8, 10, NULL, '2024-01-10 18:54:34', NULL, NULL),
(682, 1, 8, 11, NULL, '2024-01-10 18:54:34', NULL, NULL),
(683, 1, 8, 12, NULL, '2024-01-10 18:54:34', NULL, NULL),
(684, 1, 8, 13, NULL, '2024-01-10 18:54:34', NULL, NULL),
(685, 1, 8, 14, NULL, '2024-01-10 18:54:34', NULL, NULL),
(686, 1, 8, 15, NULL, '2024-01-10 18:54:34', NULL, NULL),
(691, 1, 9, 1, NULL, '2024-01-10 18:54:34', NULL, NULL),
(692, 1, 9, 2, NULL, '2024-01-10 18:54:34', NULL, NULL),
(693, 1, 9, 3, NULL, '2024-01-10 18:54:34', NULL, NULL),
(694, 1, 9, 4, NULL, '2024-01-10 18:54:34', NULL, NULL),
(695, 1, 9, 9, NULL, '2024-01-10 18:54:34', NULL, NULL),
(696, 1, 9, 10, NULL, '2024-01-10 18:54:34', NULL, NULL),
(697, 1, 9, 11, NULL, '2024-01-10 18:54:34', NULL, NULL),
(698, 1, 9, 12, NULL, '2024-01-10 18:54:34', NULL, NULL),
(699, 1, 9, 13, NULL, '2024-01-10 18:54:34', NULL, NULL),
(700, 1, 9, 14, NULL, '2024-01-10 18:54:34', NULL, NULL),
(701, 1, 9, 15, NULL, '2024-01-10 18:54:34', NULL, NULL),
(706, 1, 10, 1, NULL, '2024-01-10 18:54:34', NULL, NULL),
(707, 1, 10, 2, NULL, '2024-01-10 18:54:34', NULL, NULL),
(708, 1, 10, 3, NULL, '2024-01-10 18:54:34', NULL, NULL),
(709, 1, 10, 4, NULL, '2024-01-10 18:54:34', NULL, NULL),
(710, 1, 10, 9, NULL, '2024-01-10 18:54:34', NULL, NULL),
(711, 1, 10, 10, NULL, '2024-01-10 18:54:34', NULL, NULL),
(712, 1, 10, 11, NULL, '2024-01-10 18:54:34', NULL, NULL),
(713, 1, 10, 12, NULL, '2024-01-10 18:54:34', NULL, NULL),
(714, 1, 10, 13, NULL, '2024-01-10 18:54:34', NULL, NULL),
(715, 1, 10, 14, NULL, '2024-01-10 18:54:34', NULL, NULL),
(716, 1, 10, 15, NULL, '2024-01-10 18:54:34', NULL, NULL),
(721, 1, 11, 1, NULL, '2024-01-10 18:54:34', NULL, NULL),
(722, 1, 11, 2, NULL, '2024-01-10 18:54:34', NULL, NULL),
(723, 1, 11, 3, NULL, '2024-01-10 18:54:34', NULL, NULL),
(724, 1, 11, 4, NULL, '2024-01-10 18:54:34', NULL, NULL),
(725, 1, 11, 9, NULL, '2024-01-10 18:54:34', NULL, NULL),
(726, 1, 11, 10, NULL, '2024-01-10 18:54:34', NULL, NULL),
(727, 1, 11, 11, NULL, '2024-01-10 18:54:34', NULL, NULL),
(728, 1, 11, 12, NULL, '2024-01-10 18:54:34', NULL, NULL),
(729, 1, 11, 13, NULL, '2024-01-10 18:54:34', NULL, NULL),
(730, 1, 11, 14, NULL, '2024-01-10 18:54:34', NULL, NULL),
(731, 1, 11, 15, NULL, '2024-01-10 18:54:34', NULL, NULL),
(736, 1, 12, 1, NULL, '2024-01-10 18:54:34', NULL, NULL),
(737, 1, 12, 2, NULL, '2024-01-10 18:54:34', NULL, NULL),
(738, 1, 12, 3, NULL, '2024-01-10 18:54:34', NULL, NULL),
(739, 1, 12, 4, NULL, '2024-01-10 18:54:34', NULL, NULL),
(740, 1, 12, 9, NULL, '2024-01-10 18:54:34', NULL, NULL),
(741, 1, 12, 10, NULL, '2024-01-10 18:54:34', NULL, NULL),
(742, 1, 12, 11, NULL, '2024-01-10 18:54:34', NULL, NULL),
(743, 1, 12, 12, NULL, '2024-01-10 18:54:34', NULL, NULL),
(744, 1, 12, 13, NULL, '2024-01-10 18:54:34', NULL, NULL),
(745, 1, 12, 14, NULL, '2024-01-10 18:54:34', NULL, NULL),
(746, 1, 12, 15, NULL, '2024-01-10 18:54:34', NULL, NULL),
(751, 1, 13, 1, NULL, '2024-01-10 18:54:34', NULL, NULL),
(752, 1, 13, 2, NULL, '2024-01-10 18:54:34', NULL, NULL),
(753, 1, 13, 3, NULL, '2024-01-10 18:54:34', NULL, NULL),
(754, 1, 13, 4, NULL, '2024-01-10 18:54:34', NULL, NULL),
(755, 1, 13, 9, NULL, '2024-01-10 18:54:34', NULL, NULL),
(756, 1, 13, 10, NULL, '2024-01-10 18:54:34', NULL, NULL),
(757, 1, 13, 11, NULL, '2024-01-10 18:54:34', NULL, NULL),
(758, 1, 13, 12, NULL, '2024-01-10 18:54:34', NULL, NULL),
(759, 1, 13, 13, NULL, '2024-01-10 18:54:34', NULL, NULL),
(760, 1, 13, 14, NULL, '2024-01-10 18:54:34', NULL, NULL),
(761, 1, 13, 15, NULL, '2024-01-10 18:54:34', NULL, NULL),
(766, 1, 14, 1, NULL, '2024-01-10 18:54:34', NULL, NULL),
(767, 1, 14, 2, NULL, '2024-01-10 18:54:34', NULL, NULL),
(768, 1, 14, 3, NULL, '2024-01-10 18:54:34', NULL, NULL),
(769, 1, 14, 4, NULL, '2024-01-10 18:54:34', NULL, NULL),
(770, 1, 14, 9, NULL, '2024-01-10 18:54:34', NULL, NULL),
(771, 1, 14, 10, NULL, '2024-01-10 18:54:34', NULL, NULL),
(772, 1, 14, 11, NULL, '2024-01-10 18:54:34', NULL, NULL),
(773, 1, 14, 12, NULL, '2024-01-10 18:54:34', NULL, NULL),
(774, 1, 14, 13, NULL, '2024-01-10 18:54:34', NULL, NULL),
(775, 1, 14, 14, NULL, '2024-01-10 18:54:34', NULL, NULL),
(776, 1, 14, 15, NULL, '2024-01-10 18:54:34', NULL, NULL);
/*!40000 ALTER TABLE `t_telegram_bot_keyreply_keyboard` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t_telegram_bot_user`
--

DROP TABLE IF EXISTS `t_telegram_bot_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t_telegram_bot_user` (
  `rid` int(11) NOT NULL AUTO_INCREMENT COMMENT '主ID',
  `bot_rid` int(11) NOT NULL COMMENT '机器人ID',
  `tg_uid` bigint(20) NOT NULL COMMENT 'tg用户ID',
  `tg_username` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'tg用户名',
  `tg_nickname` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'tg用户昵称',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '当前状态:1使用中,2已停用,3用户不存在',
  `bind_trc_wallet_addr` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '绑定波场钱包地址',
  `first_time` datetime DEFAULT NULL COMMENT '关注时间',
  `last_time` datetime DEFAULT NULL COMMENT '最近时间',
  `stop_time` datetime DEFAULT NULL COMMENT '停用时间',
  `cash_trx` decimal(16,6) NOT NULL DEFAULT '0.000000' COMMENT '可用trx余额',
  `cash_usdt` decimal(16,6) NOT NULL DEFAULT '0.000000' COMMENT '可用usdt余额',
  `total_recharge_trx` decimal(16,6) NOT NULL DEFAULT '0.000000' COMMENT '总充值trx',
  `total_recharge_usdt` decimal(16,6) NOT NULL DEFAULT '0.000000' COMMENT '总充值usdt',
  `max_monitor_wallet` int(11) NOT NULL DEFAULT '2' COMMENT '最大监控地址数量',
  PRIMARY KEY (`rid`),
  UNIQUE KEY `idx_telegram_bot_user_1` (`bot_rid`,`tg_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='机器人用户列表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_telegram_bot_user`
--

LOCK TABLES `t_telegram_bot_user` WRITE;
/*!40000 ALTER TABLE `t_telegram_bot_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `t_telegram_bot_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t_transit_user_wallet`
--

DROP TABLE IF EXISTS `t_transit_user_wallet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t_transit_user_wallet` (
  `rid` int(11) NOT NULL AUTO_INCREMENT COMMENT '主ID',
  `chain_type` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '链类型:trc波场,eth以太,okx欧易,bsc币安',
  `wallet_addr` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '钱包地址',
  `total_transit_usdt` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '总已闪兑USDT',
  `total_transit_sxf` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '总已闪兑手续费',
  `total_yuzhi_sxf` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '总已预支手续费',
  `need_feedback_sxf` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '未还预支手续费',
  `send_feedback_sxf` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '已还预支手续费',
  `last_transit_time` datetime DEFAULT NULL COMMENT '最近闪兑时间',
  `last_yuzhi_time` datetime DEFAULT NULL COMMENT '最近预支时间',
  PRIMARY KEY (`rid`),
  UNIQUE KEY `idx_transit_user_wallet_1` (`chain_type`,`wallet_addr`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='闪兑钱包用户闪兑表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_transit_user_wallet`
--

LOCK TABLES `t_transit_user_wallet` WRITE;
/*!40000 ALTER TABLE `t_transit_user_wallet` DISABLE KEYS */;
/*!40000 ALTER TABLE `t_transit_user_wallet` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t_transit_wallet`
--

DROP TABLE IF EXISTS `t_transit_wallet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t_transit_wallet` (
  `rid` int(11) NOT NULL AUTO_INCREMENT COMMENT '主ID',
  `chain_type` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '链类型:trc波场,eth以太,okx欧易,bsc币安',
  `receive_wallet` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '收款钱包地址',
  `send_wallet` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '出款钱包地址',
  `send_wallet_privatekey` varchar(2000) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '出款钱包私钥',
  `show_notes` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '说明',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态:0开启,1关闭,关闭则不拉交易,修改为开启则记录get_time为当前时间',
  `tg_notice_obj_receive` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'TG通知对象(收款),可多个,英文逗号隔开,可为用户或者群组',
  `tg_notice_obj_send` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'TG通知对象(出款),可多个,英文逗号隔开,可为用户或者群组',
  `get_tx_time` datetime DEFAULT NULL COMMENT '开始拉取交易时间',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '修改时间',
  `bot_rid` int(11) DEFAULT NULL COMMENT '机器人ID,用于发送通知的机器人,没有则不推送',
  `auto_stock_min_trx` int(11) NOT NULL DEFAULT '0' COMMENT 'TRX低于数量进货,0表示不自动进货',
  `auto_stock_per_usdt` int(11) NOT NULL DEFAULT '0' COMMENT 'USDT自动进货闪兑数量,0表示不自动进货',
  PRIMARY KEY (`rid`),
  UNIQUE KEY `idx_transit_wallet_1` (`chain_type`,`receive_wallet`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='闪兑钱包设置';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_transit_wallet`
--

LOCK TABLES `t_transit_wallet` WRITE;
/*!40000 ALTER TABLE `t_transit_wallet` DISABLE KEYS */;
INSERT INTO `t_transit_wallet` VALUES (1,'trc','tttttt','tttttt',NULL,'✅请认准靓号 TWxm1pW 开头 8个U 结尾',0,'6666666','-111111','2023-11-21 00:00:00','2023-11-21 23:37:38','2023-11-22 00:18:53',1,0,0);
/*!40000 ALTER TABLE `t_transit_wallet` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t_transit_wallet_black`
--

DROP TABLE IF EXISTS `t_transit_wallet_black`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t_transit_wallet_black` (
  `rid` int(11) NOT NULL AUTO_INCREMENT COMMENT '主ID',
  `chain_type` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '链类型:trc波场,eth以太,okx欧易,bsc币安',
  `black_wallet` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '黑钱包地址',
  `comments` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '备注',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`rid`),
  UNIQUE KEY `idx_transit_wallet_black_1` (`chain_type`,`black_wallet`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='闪兑钱包黑钱包,黑钱包的交易不处理';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_transit_wallet_black`
--

LOCK TABLES `t_transit_wallet_black` WRITE;
/*!40000 ALTER TABLE `t_transit_wallet_black` DISABLE KEYS */;
INSERT INTO `t_transit_wallet_black` VALUES (4,'trc','TWd4WrZ9wn84f5x1hZhL4DHvk738ns5jwb','币安',NULL,NULL),(5,'trc','TMuA6YqfCeX8EhbfYEg5y7S4DqzSJireY9','币安','2023-02-11 13:29:31','2023-02-11 13:29:42'),(6,'trc','TT1DyeqXaaJkt6UhVYFWUXBXknaXnBudTK','币安','2023-02-11 13:30:08',NULL),(7,'trc','TJCo98saj6WND61g1uuKwJ9GMWMT9WkJFo','币安','2023-02-11 13:30:36',NULL),(8,'trc','TV6MuMXfmLbBqPZvBHdwFsDnQeVfnmiuSi','币安','2023-02-11 13:30:51',NULL),(9,'trc','TDToUxX8sH4z6moQpK3ZLAN24eupu2ivA4',NULL,'2023-02-11 13:31:07',NULL),(10,'trc','TRYL7PKCG4b4xRCM554Q5J6o8f1UjUmfnY','Kucoin-Cold','2023-02-11 13:31:27',NULL),(11,'trc','TB1WQmj63bHV9Qmuhp39WABzutphMAetSc',NULL,'2023-02-11 13:31:41',NULL),(12,'trc','TNiq9AXBp9EjUqhDhrwrfvAA8U3GUQZH81',NULL,'2023-02-11 13:31:49',NULL),(13,'trc','TKHuVq1oKVruCGLvqVexFs6dawKv6fQgFs',NULL,'2023-02-11 13:32:02',NULL),(14,'trc','TMmhxjhqPbUwgzfV3eV94T398Qk1khE32v',NULL,'2023-02-11 13:32:11',NULL),(15,'trc','TMhJviFWiaxvqKLdng9dmsi1H5H5yTGEeu',NULL,'2023-02-11 13:32:24',NULL),(16,'trc','TTd9qHyjqiUkfTxe3gotbuTMpjU8LEbpkN','Kraken','2023-02-11 13:32:40',NULL),(17,'trc','TTiDLWE6fZK8okMJv6ijg42yrH6W2pjSr9',NULL,'2023-02-11 13:32:50',NULL),(18,'trc','TJYM8UnYvZ8iM5PjuHTYsDYXhY1YZBeKeX',NULL,'2023-02-11 13:32:57',NULL),(19,'trc','TQeNNo5zVarhdKm5EiJSekfNXg6H1tRN4n',NULL,'2023-02-11 13:33:04',NULL),(20,'trc','TJbHp48Shg4tTD5x6fKkU7PodggL5mjcJP',NULL,'2023-02-11 13:33:12',NULL),(21,'trc','TWGZbjofbTLY3UCjCV4yiLkRg89zLqwRgi',NULL,'2023-02-11 13:33:19',NULL),(22,'trc','TM1zzNDZD2DPASbKcgdVoTYhfmYgtfwx9R','okx','2023-02-11 13:33:35','2023-02-11 13:34:05'),(23,'trc','TBA6CypYJizwA9XdC7Ubgc5F1bxrQ7SqPt','gate','2023-02-11 13:34:32',NULL),(24,'trc','TNaRAoLUyYEV2uF7GUrzSjRQTU8v5ZJ5VR','huobi','2023-02-11 13:34:49',NULL),(25,'trc','TNXoiAJ3dct8Fjg4M9fkLFh9S2v9TXc32G',NULL,'2023-02-11 13:35:09',NULL),(26,'trc','TJDENsfBJs4RFETt1X1W8wMDc8M5XnJhCe',NULL,'2023-02-11 13:35:23',NULL),(27,'trc','TYASr5UV6HEcXatwdFQfmLVUqQQQMUxHLS',NULL,'2023-02-11 13:35:37',NULL),(29,'trc','TPF3KHqPbCFQL2UDrHr4LuHoYU9XPfzYLo',NULL,'2023-04-30 19:25:22',NULL),(30,'trc','TKk9y2F5oFnnjSiH53fUhvRC55joFwS8c9',NULL,'2023-05-23 13:18:09',NULL),(31,'trc','TX3xNEmn9S5c77qeNnQhsfgcrbgqYo9Xcc',NULL,'2023-10-06 15:34:50',NULL),(32,'trc','TCz47XgC9TjCeF4UzfB6qZbM9LTF9s1tG7','欧意','2023-10-06 20:14:42',NULL),(33,'trc','TAzsQ9Gx8eqFNFSKbeXrbi45CuVPHzA8wr',NULL,'2023-10-09 00:14:18',NULL),(34,'trc','TSaRZDiBPD8Rd5vrvX8a4zgunHczM9mj8S',NULL,'2023-10-09 00:15:05',NULL);
/*!40000 ALTER TABLE `t_transit_wallet_black` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t_transit_wallet_coin`
--

DROP TABLE IF EXISTS `t_transit_wallet_coin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t_transit_wallet_coin` (
  `rid` int(11) NOT NULL AUTO_INCREMENT COMMENT '主ID',
  `transit_wallet_id` int(11) NOT NULL COMMENT '闪兑钱包ID',
  `in_coin_name` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '转入币名,不能和回款币名一样',
  `out_coin_name` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '回款币名',
  `is_realtime_rate` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否实时汇率,1实时,2固定',
  `profit_rate` decimal(4,2) NOT NULL DEFAULT '1.00' COMMENT '汇率利润,仅实时汇率的时候有效',
  `exchange_rate` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '汇率,转入1个币回款的数量,设置为0使用实时汇率',
  `kou_out_amount` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '回款金额扣除手续费,从回款金额中扣除后再回款,0表示不扣',
  `min_transit_amount` int(11) NOT NULL COMMENT '最低转入,整形,高于该值才处理',
  `max_transit_amount` int(11) NOT NULL COMMENT '最高转入,整形,高于该值不处理',
  `comments` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '备注',
  `create_by` int(11) DEFAULT NULL COMMENT '创建人',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_by` int(11) DEFAULT NULL COMMENT '修改人',
  `update_time` datetime DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`rid`),
  UNIQUE KEY `idx_transit_wallet_coin_1` (`transit_wallet_id`,`in_coin_name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='闪兑钱包币种设置';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_transit_wallet_coin`
--

LOCK TABLES `t_transit_wallet_coin` WRITE;
/*!40000 ALTER TABLE `t_transit_wallet_coin` DISABLE KEYS */;
INSERT INTO `t_transit_wallet_coin` VALUES (1,1,'usdt','trx',3,0.10,8.76,0.00,1,200,NULL,NULL,'2023-11-21 23:38:02',NULL,'2023-11-22 00:53:41');
/*!40000 ALTER TABLE `t_transit_wallet_coin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t_transit_wallet_trade_list`
--

DROP TABLE IF EXISTS `t_transit_wallet_trade_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t_transit_wallet_trade_list` (
  `rid` int(11) NOT NULL AUTO_INCREMENT COMMENT '主ID',
  `tx_hash` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '交易hash',
  `transferfrom_address` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '发送钱包地址,交易发起方',
  `transferto_address` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '接收钱包地址,交易接收方',
  `coin_name` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '交易币名',
  `amount` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '交易数额',
  `timestamp` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '交易时间',
  `process_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '处理状态,数据字典',
  `process_time` datetime DEFAULT NULL COMMENT '处理时间',
  `process_comments` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '处理备注',
  `get_time` datetime DEFAULT NULL COMMENT '拉取交易时间',
  `sendback_address` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '返回钱包地址',
  `sendback_amount` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '出款数额',
  `sendback_time` datetime DEFAULT NULL COMMENT '出款时间',
  `sendback_coin_name` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '出款币名',
  `sendback_tx_hash` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '出款交易hash',
  `sendback_contract_ret` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '交易结果:SUCCESS表示成功',
  `tg_notice_status_receive` char(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N' COMMENT 'TG收款通知状态:N/Y,如果配置不通知,状态也更新为Y',
  `tg_notice_status_send` char(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N' COMMENT 'TG出款通知状态:N/Y,如果配置不通知,状态也更新为Y',
  `current_exchange_rate` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '当前兑换汇率',
  `current_huan_yuzhi_amount` decimal(14,2) NOT NULL DEFAULT '0.00' COMMENT '当前扣预支金额',
  PRIMARY KEY (`rid`),
  UNIQUE KEY `idx_transit_wallet_trade_list_1` (`tx_hash`),
  KEY `idx_transit_wallet_trade_list_2` (`transferfrom_address`),
  KEY `idx_transit_wallet_trade_list_3` (`transferto_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='闪兑钱包交易明细';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_transit_wallet_trade_list`
--

LOCK TABLES `t_transit_wallet_trade_list` WRITE;
/*!40000 ALTER TABLE `t_transit_wallet_trade_list` DISABLE KEYS */;
/*!40000 ALTER TABLE `t_transit_wallet_trade_list` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'trxswapbot'
--

--
-- Dumping routines for database 'trxswapbot'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2023-12-03  0:35:06

ALTER TABLE `t_energy_ai_trusteeship` ADD `max_buy_quantity` INT NOT NULL DEFAULT '0' COMMENT '最大允许购买次数,0表示无限制' AFTER `total_buy_quantity`;
ALTER TABLE `t_energy_ai_trusteeship` ADD `back_comments` VARCHAR(255) NULL COMMENT '后台备注' AFTER `comments`;


ALTER TABLE `t_energy_platform_bot` ADD `is_open_bishu` CHAR(1) NOT NULL DEFAULT 'Y' COMMENT '是否开启笔数套餐:Y/N' AFTER `per_energy_day`;


ALTER TABLE `t_energy_platform_bot` ADD `per_bishu_usdt_price` decimal(4,2) NOT NULL DEFAULT 0.5 COMMENT '每笔USDT价格' AFTER `is_open_bishu`;

ALTER TABLE `t_energy_platform_bot` ADD `per_bishu_energy_quantity` int NOT NULL DEFAULT 65000 COMMENT '每笔能量数量' AFTER `per_bishu_usdt_price`;

ALTER TABLE `t_energy_platform_bot` ADD `per_energy_day_bishu` INT NULL DEFAULT 1 COMMENT '笔数代理期限:0一小时,1一天,3三天' AFTER `per_bishu_energy_quantity`;



drop table if exists t_energy_ai_bishu;

/*==============================================================*/
/* Table: t_energy_ai_bishu                                     */
/*==============================================================*/
create table t_energy_ai_bishu
(
   rid                  int not null auto_increment  comment '主键ID',
   bot_rid              int not null  comment '机器人ID',
   tg_uid               varchar(100)  comment 'tg用户ID   ',
   wallet_addr          varchar(100) not null  comment '钱包地址',
   status               tinyint not null default 0  comment '状态:0开启,1关闭,2管理员关闭(会员禁止开启)  ',
   current_bandwidth_quantity bigint not null default 0  comment '当前带宽数量',
   current_energy_quantity bigint not null default 0  comment '当前能量数量',
   is_buy               char(1) not null default 'N'  comment '是否立马购买:Y立马下单,B下单中,监控了余额后,不足最低资源时,改为Y',
   is_notice            char(1) not null default 'N'  comment '是否通知用户:Y需要通知,自动购买后改为Y,通知用户后改为N.A表示用户不需要通知',
   is_notice_admin      char(1) not null default 'N'  comment '是否通知管理员:Y需要通知,通知后改为N.A表示管理员不需要通知',
   total_buy_usdt       decimal(14,2) not null default 0  comment '总购买USDT',
   max_buy_quantity     int not null default 0  comment '总次数',
   total_buy_quantity   int not null default 0  comment '已使用次数',
   total_buy_energy_quantity bigint not null default 0  comment '总已购买能量数量',
   last_buy_time        datetime  comment '最后一次购买时间',
   comments             varchar(3000)  comment '备注',
   back_comments        varchar(200)  comment '后台备注',
   create_time          datetime not null  comment '创建时间',
   update_time          datetime  comment '修改时间',
   primary key (rid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='机器人能量笔数表';

/*==============================================================*/
/* Index: idx_energy_ai_bishu_1                                 */
/*==============================================================*/
create unique index idx_energy_ai_bishu_1 on t_energy_ai_bishu
(
   wallet_addr
);

/*==============================================================*/
/* Index: idx_energy_ai_bishu_2                                 */
/*==============================================================*/
create index idx_energy_ai_bishu_2 on t_energy_ai_bishu
(
   bot_rid
);


ALTER TABLE `t_energy_platform_bot` ADD `bishu_recovery_type` TINYINT NOT NULL DEFAULT '1' COMMENT '笔数套餐回收方式' AFTER `per_energy_day_bishu`;

ALTER TABLE `t_energy_platform_bot` ADD `bishu_daili_type` TINYINT NOT NULL DEFAULT '1' COMMENT '笔数套餐代理方式' AFTER `bishu_recovery_type`;

ALTER TABLE `t_energy_ai_bishu` ADD `energy_platform_rid` int NULL COMMENT '当笔数使用第三方时,该值表示能量平台的ID' AFTER `back_comments`;

ALTER TABLE `t_monitor_wallet` ADD `monitor_usdt_transaction` VARCHAR(5) NOT NULL DEFAULT 'YY' COMMENT 'USDT交易监听' AFTER `balance_amount`;
ALTER TABLE `t_monitor_wallet` ADD `monitor_trx_transaction` VARCHAR(5) NOT NULL DEFAULT 'YY' COMMENT 'TRX交易监听' AFTER `monitor_usdt_transaction`;
ALTER TABLE `t_monitor_wallet` ADD `monitor_approve_transaction` VARCHAR(5) NOT NULL DEFAULT 'YY' COMMENT '授权交易监听' AFTER `monitor_trx_transaction`;
ALTER TABLE `t_monitor_wallet` ADD `monitor_multi_transaction` VARCHAR(5) NOT NULL DEFAULT 'YY' COMMENT '多签交易监听' AFTER `monitor_approve_transaction`;
ALTER TABLE `t_monitor_wallet` ADD `monitor_pledge_transaction` VARCHAR(5) NOT NULL DEFAULT 'YY' COMMENT '质押交易监听' AFTER `monitor_multi_transaction`;


ALTER TABLE `t_energy_platform_bot` ADD `ai_trusteeship_recovery_type` TINYINT NOT NULL DEFAULT '1' COMMENT '智能托管回收方式' AFTER `per_energy_day`;


/*==============================================================*/
/* Table: t_energy_third_part                                   */
/*==============================================================*/
create table t_energy_third_part
(
   rid                  int not null auto_increment  comment '主ID',
   order_type           tinyint not null  comment '下单方式:1笔数模式,2闪租模式',
   tg_uid               bigint not null  comment '下单tg用户id',
   platform_rid         int not null  comment '使用的平台ID',
   bot_rid              int not null  comment '使用的机器人ID',
   cishu_energy         int not null  comment '本次次数/能量',
   wallet_addr          varchar(50) COLLATE utf8mb4_unicode_ci not null  comment '本次下单地址',
   before_trx           decimal(16,6)	 not null  comment '下单前trx余额',
   change_trx           decimal(16,6)	 not null  comment '本次变动trx',
   after_trx            decimal(16,6)	 not null  comment '下单后trx余额',
   order_time           datetime not null  comment '下单时间',
   process_status       TINYINT NOT NULL DEFAULT '0' COMMENT '处理状态,数据字典',
   process_time         DATETIME NULL COMMENT '处理时间 ',
   process_comments     VARCHAR(2000) COLLATE utf8mb4_unicode_ci NULL COMMENT '处理备注',
   primary key (rid)
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='能量开发者代理模式';

/*==============================================================*/
/* Index: idx_energy_third_part_1                               */
/*==============================================================*/
create index idx_energy_third_part_1 on t_energy_third_part
(
   tg_uid
);

drop table if exists t_collection_wallet;

/*==============================================================*/
/* Table: t_collection_wallet                                   */
/*==============================================================*/
create table t_collection_wallet
(
   rid                  int not null auto_increment  comment '主ID',
   bot_rid              int not null  comment '机器人ID',
   chain_type           varchar(10) not null  comment '链类型',
   wallet_addr          	varchar(200) not null  comment '钱包地址',
   wallet_addr_privatekey 	varchar(2000)  comment '钱包地址私钥',
   permission_id        tinyint not null default 0  comment '权限ID',
   status               tinyint not null default 1  comment '状态:0开启,1关闭',
   tg_notice_obj        	varchar(200)  comment 'TG通知对象,可多个,英文逗号隔开,可为用户或者群组	',
   trx_balance          decimal(18,6) default 0  comment 'TRX余额',
   usdt_balance         decimal(18,6) default 0  comment 'USDT余额',
   trx_collection_amount decimal(18,6) default 0  comment 'TRX归集金额',
   usdt_collection_amount decimal(18,6) default 0  comment 'USDT归集金额',
   trx_reserve_amount   decimal(18,6) default 0  comment 'TRX预留金额',
   usdt_reserve_amount  decimal(18,6) default 0  comment 'USDT预留金额',
   collection_wallet_addr 	varchar(200)  comment '归集钱包',
   last_collection_time datetime  comment '最近归集时间',
   create_time          datetime not null  comment '创建时间',
   update_time          datetime  comment '修改时间',
   comments             	varchar(200)  comment '备注',
   collection_type      tinyint not null default 0  comment '归集类型:0暂无需归集,1仅归集TRX,2仅归集USDT,3TRX和USDT都归集.查询余额后判断',
   primary key (rid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='归集钱包表';

/*==============================================================*/
/* Index: idx_collection_wallet                                 */
/*==============================================================*/
create unique index idx_collection_wallet on t_collection_wallet
(
   chain_type,
   wallet_addr
);


drop table if exists t_collection_wallet_list;

/*==============================================================*/
/* Table: t_collection_wallet_list                              */
/*==============================================================*/
create table t_collection_wallet_list
(
   rid                  int not null auto_increment  comment '主ID',
   wallet_addr          	varchar(200) not null  comment '钱包地址',
   collection_wallet_addr 	varchar(200) not null  comment '归集钱包',
   coin_name            	varchar(50) not null  comment '归集币种',
   collection_amount    decimal(18,6) not null  comment '归集金额',
   collection_time      datetime not null  comment '归集时间',
   tx_hash              	varchar(200)  comment '归集hash',
   is_notice            char(1) not null default 'N'  comment '通知状态:N不需要通知,Y需要通知',
   primary key (rid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='归集记录表';

/*==============================================================*/
/* Index: idx_collection_wallet_list_1                          */
/*==============================================================*/
create index idx_collection_wallet_list_1 on t_collection_wallet_list
(
   wallet_addr
);

/*==============================================================*/
/* Index: idx_collection_wallet_list_2                          */
/*==============================================================*/
create index idx_collection_wallet_list_2 on t_collection_wallet_list
(
   collection_wallet_addr
);

drop table if exists t_admin_login_log;

/*==============================================================*/
/* Table: t_admin_login_log                                     */
/*==============================================================*/
create table t_admin_login_log
(
   rid                  int not null auto_increment  comment '主键ID',
   admin_name           varchar(100) not null  comment '用户',
   login_ip             varchar(1000)  comment '登录IP',
   login_time           datetime  comment '登录时间',
   primary key (rid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='后台登录日志';



/*==============================================================*/
/* Table: t_energy_quick_order                                  */
/*==============================================================*/
create table t_energy_quick_order
(
   rid                  int not null auto_increment  comment '主ID',
   bot_rid              int not null  comment '机器人ID',
   tg_uid               bigint not null  comment 'tg用户ID',
   wallet_addr          varchar(100) not null  comment '钱包地址',
   energy_amount        int not null  comment '能量数量',
   energy_day           tinyint not null  comment '能量期限',
   package_name         varchar(50)  comment '能量名称',
   package_rid          int  comment '能量套餐主ID',
   status               tinyint not null  comment '状态',
   pay_price            decimal(14,2) not null  comment '支付金额',
   pay_type             varchar(10) not null  comment '支付方式',
   pay_time             datetime not null  comment '支付时间',
   daili_time           datetime  comment '代理时间',
   comments             varchar(500)  comment '备注',
   process_time         datetime  comment '处理时间',
   is_notice            char(1) not null default 'N'  comment '是否通知',
   primary key (rid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='能量快捷购买表';


/*==============================================================*/
/* Index: idx_energy_quick_order_1                              */
/*==============================================================*/
create index idx_energy_quick_order_1 on t_energy_quick_order
(
   bot_rid,
   tg_uid
);

drop table if exists t_energy_special;

/*==============================================================*/
/* Table: t_energy_special                                      */
/*==============================================================*/
create table t_energy_special
(
   rid                  int not null auto_increment  comment '主ID',
   bot_rid              int  comment '机器人ID',
   tg_uid               bigint  comment 'tg用户ID',
   wallet_addr          varchar(50) not null  comment '钱包地址',
   wallet_energy        bigint not null default 0  comment '地址剩余能量',
   max_energy           bigint not null default 0  comment '最大给能量数量',
   send_energy          bigint not null default 0  comment '已给能量数量',
   per_energy           int not null default 0  comment '每次给能量数量',
   less_than_energy     int not null default 0  comment '地址低于多少能量给',
   status               tinyint not null default 0  comment '状态:0关闭,1开启',
   total_usdt_recharge  decimal(14,2) not null default 0  comment '充值USDT总数',
   total_trx_recharge   decimal(14,2) not null default 0  comment '充值TRX总数',
   seq_sn               int not null default 0  comment '排序',
   comments             varchar(200)  comment '备注',
   primary key (rid)
);

alter table t_energy_special comment '特殊地址能量设置';

/*==============================================================*/
/* Index: idx_energy_special_1                                  */
/*==============================================================*/
create unique index idx_energy_special_1 on t_energy_special
(
   wallet_addr
);

drop table if exists t_energy_special_list;

/*==============================================================*/
/* Table: t_energy_special_list                                 */
/*==============================================================*/
create table t_energy_special_list
(
   rid                  int not null auto_increment  comment '主ID',
   bot_rid              int  comment '机器人ID',
   tg_uid               bigint  comment 'tg用户ID',
   wallet_addr          varchar(50) not null  comment '钱包地址',
   send_wallet_addr     varchar(50) not null  comment '给能量的地址',
   before_energy        bigint  comment '代理前能量',
   daili_energy         int  comment '本次代理能量',
   daili_hash           varchar(100)  comment '代理hash',
   daili_trx            decimal(18,6)  comment '能量代理trx数量',
   status               tinyint not null default 1  comment '状态:1未回收,2已回收',
   daili_time           datetime  comment '代理时间',
   huishou_time         datetime  comment '回收时间',
   huishou_hash         varchar(100)  comment '回收hash',
   primary key (rid)
);

alter table t_energy_special_list comment '特殊地址能量明细';

/*==============================================================*/
/* Index: idx_energy_special_list_1                             */
/*==============================================================*/
create index idx_energy_special_list_1 on t_energy_special_list
(
   wallet_addr
);

ALTER TABLE `t_energy_platform_bot` ADD `agent_tg_uid` BIGINT NULL COMMENT '代理用户ID,有值表示是代理的地址,需要扣用户trx余额' AFTER `update_time`;
ALTER TABLE `t_energy_platform_bot` ADD `agent_per_price` DECIMAL(18,6) NULL COMMENT '代理每笔价格,扣trx' AFTER `agent_tg_uid`;
ALTER TABLE `t_energy_platform_package` ADD `agent_trx_price` DECIMAL(14,2) NOT NULL DEFAULT '0' COMMENT '代理trx售价,当该机器人对应地址为代理ID时,必填' AFTER `usdt_price`;

ALTER TABLE `t_energy_ai_trusteeship` ADD `agent_tg_uid` BIGINT NULL COMMENT '代理用户ID,有值表示是代理的地址,需要扣用户trx余额' AFTER `update_time`;
ALTER TABLE `t_energy_platform_bot` ADD `bishu_stop_day` INT NOT NULL DEFAULT '0' COMMENT '笔数模式滞留天数,超过天数没使用暂停,0表示不限制' AFTER `bishu_daili_type`;
ALTER TABLE `t_energy_ai_bishu` ADD `bishu_stop_day` INT NOT NULL DEFAULT '0' COMMENT '笔数模式滞留天数,超过天数没使用暂停,0表示不限制';

ALTER TABLE `t_premium_platform_order` ADD `tg_notice_user` CHAR(1) NOT NULL DEFAULT 'N' COMMENT '通知用户消息:N/Y,Y通知,N不通知' AFTER `tx_hash`;
ALTER TABLE `t_premium_platform_order` ADD `tg_notice_admin` CHAR(1) NOT NULL DEFAULT 'N' COMMENT '通知管理消息:N/Y,Y通知,N不通知' AFTER `tg_notice_user`;

