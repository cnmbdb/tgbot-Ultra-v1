-- MySQL dump 10.13  Distrib 5.7.40, for Linux (x86_64)
--
-- Host: localhost    Database: trxswapbot
-- ------------------------------------------------------
-- Server version	5.7.40-log

;
;
;
;
;

--
-- Table structure for table t_admin
--

DROP TABLE IF EXISTS t_admin;
;
CREATE TABLE t_admin (
  id BIGSERIAL NOT NULL,
  name VARCHAR(30) NOT NULL,
  password VARCHAR(100) NOT NULL,
  head VARCHAR(255) DEFAULT NULL,
  status SMALLINT NOT NULL DEFAULT '1' ,
  remember_token VARCHAR(100) DEFAULT NULL,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  white_ip VARCHAR(3000) NULL DEFAULT NULL ,
  PRIMARY KEY (id)
) ;

--
-- Dumping data for table t_admin
--

;
INSERT INTO t_admin VALUES (1,'trxadmin','$2y$10$4YoRzHXWRiSQH5Sdte09Zew6V98eAZPrZLw70V8xRok3STnxMUwIe','',1,NULL,NULL,'2023-12-02 16:28:30','');
UN

--
-- Table structure for table t_energy_ai_trusteeship
--

DROP TABLE IF EXISTS t_energy_ai_trusteeship;
;
CREATE TABLE t_energy_ai_trusteeship (
  rid SERIAL NOT NULL ,
  bot_rid INTEGER NOT NULL ,
  tg_uid VARCHAR(100) NOT NULL ,
  wallet_addr VARCHAR(100) NOT NULL ,
  status SMALLINT NOT NULL DEFAULT '1' ,
  current_bandwidth_quantity bigint(20) NOT NULL DEFAULT '0' ,
  current_energy_quantity bigint(20) NOT NULL DEFAULT '0' ,
  min_energy_quantity INTEGER NOT NULL DEFAULT '32000' ,
  per_buy_energy_quantity INTEGER NOT NULL DEFAULT '32000' ,
  total_buy_energy_quantity bigint(20) NOT NULL DEFAULT '0' ,
  total_used_trx decimal(14,2) NOT NULL DEFAULT '0.00' ,
  total_buy_quantity INTEGER NOT NULL DEFAULT '0' ,
  is_buy CHAR(1) NOT NULL DEFAULT 'N' ,
  is_notice CHAR(1) NOT NULL DEFAULT 'N' ,
  is_notice_admin CHAR(1) NOT NULL DEFAULT 'N' ,
  last_buy_time TIMESTAMP DEFAULT NULL ,
  last_used_trx decimal(14,2) DEFAULT NULL ,
  comments VARCHAR(2000) DEFAULT NULL ,
  create_by INTEGER NOT NULL ,
  create_time TIMESTAMP NOT NULL ,
  update_by INTEGER DEFAULT NULL ,
  update_time TIMESTAMP DEFAULT NULL ,
  PRIMARY KEY (rid)
) ;

--
-- Dumping data for table t_energy_ai_trusteeship
--

;
UN

--
-- Table structure for table t_energy_platform
--

DROP TABLE IF EXISTS t_energy_platform;
;
CREATE TABLE t_energy_platform (
  rid SERIAL NOT NULL ,
  poll_group CHAR(1) NOT NULL ,
  platform_name SMALLINT NOT NULL ,
  status SMALLINT NOT NULL DEFAULT '1' ,
  platform_uid VARCHAR(100) DEFAULT NULL ,
  permission_id INTEGER NOT NULL DEFAULT '0' ,
  platform_balance decimal(28,6) NOT NULL DEFAULT '0' ,
  alert_platform_balance decimal(28,6) NOT NULL DEFAULT '0' ,
  platform_apikey VARCHAR(3000) DEFAULT NULL ,
  tg_notice_obj VARCHAR(200) DEFAULT NULL ,
  tg_notice_bot_rid INTEGER DEFAULT NULL ,
  seq_sn INTEGER NOT NULL DEFAULT '0' ,
  last_alert_time TIMESTAMP DEFAULT NULL ,
  comments VARCHAR(3000) DEFAULT NULL ,
  create_time TIMESTAMP NOT NULL ,
  update_time TIMESTAMP DEFAULT NULL ,
  PRIMARY KEY (rid)
) ;

--
-- Dumping data for table t_energy_platform
--

;
INSERT INTO t_energy_platform VALUES (1,'A',3,1,'TBwHofgkkkttttttttttt',0,'233694','0',NULL,'6666666',1,98,'2023-11-25 14:23:00','','2023-11-25 13:06:13','2023-11-30 17:11:46'),(2,'A',2,1,'-',0,'864.1','0',NULL,'6666666',1,1,'2023-11-25 14:23:06','','2023-11-01 13:06:41','2023-11-30 19:25:43'),(3,'A',1,1,'3333',0,'910.426948','0',NULL,'6666666',1,90,NULL,'','2023-11-25 15:03:04','2023-11-30 22:02:38'),(4,'A',4,1,'user',0,'36.36','0',NULL,'6666666',1,999,NULL,'','2023-11-27 15:16:34',NULL);
UN

--
-- Table structure for table t_energy_platform_bot
--

DROP TABLE IF EXISTS t_energy_platform_bot;
;
CREATE TABLE t_energy_platform_bot (
  rid SERIAL NOT NULL ,
  bot_rid INTEGER NOT NULL ,
  poll_group CHAR(1) NOT NULL ,
  tg_admin_uid VARCHAR(500) DEFAULT NULL ,
  status SMALLINT NOT NULL DEFAULT '1' ,
  receive_wallet VARCHAR(100) DEFAULT NULL ,
  get_tx_time TIMESTAMP DEFAULT NULL ,
  tg_notice_obj_receive VARCHAR(200) DEFAULT NULL ,
  tg_notice_obj_send VARCHAR(200) DEFAULT NULL ,
  comments VARCHAR(255) DEFAULT NULL ,
  is_open_ai_trusteeship CHAR(1) NOT NULL DEFAULT 'N' ,
  trx_price_energy_32000 INTEGER DEFAULT NULL ,
  trx_price_energy_65000 INTEGER DEFAULT NULL ,
  per_energy_day INTEGER DEFAULT NULL ,
  create_time TIMESTAMP NOT NULL ,
  update_time TIMESTAMP DEFAULT NULL ,
  PRIMARY KEY (rid)
) ;

--
-- Dumping data for table t_energy_platform_bot
--

;
INSERT INTO t_energy_platform_bot VALUES (3,1,'A','6666666',0,'tttttttttttttttt','2023-11-25 00:00:00','6666666','-111111','','N',6,12,1,'2023-11-25 02:06:35','2023-12-02 11:36:36');
UN

--
-- Table structure for table t_energy_platform_order
--

DROP TABLE IF EXISTS t_energy_platform_order;
;
CREATE TABLE t_energy_platform_order (
  rid SERIAL NOT NULL ,
  energy_platform_rid INTEGER NOT NULL ,
  energy_platform_bot_rid INTEGER NOT NULL ,
  platform_name SMALLINT NOT NULL ,
  platform_uid VARCHAR(50) NOT NULL ,
  source_type SMALLINT NOT NULL DEFAULT '1' ,
  receive_address VARCHAR(50) NOT NULL ,
  platform_order_id VARCHAR(100) NOT NULL ,
  energy_amount INTEGER NOT NULL ,
  energy_day SMALLINT NOT NULL ,
  energy_time TIMESTAMP NOT NULL ,
  recovery_status SMALLINT NOT NULL DEFAULT '1' ,
  recovery_time TIMESTAMP DEFAULT NULL ,
  use_trx decimal(14,6) DEFAULT NULL ,
  PRIMARY KEY (rid)
) ;

--
-- Dumping data for table t_energy_platform_order
--

;
UN

--
-- Table structure for table t_energy_platform_package
--

DROP TABLE IF EXISTS t_energy_platform_package;
;
CREATE TABLE t_energy_platform_package (
  rid SERIAL NOT NULL ,
  bot_rid INTEGER NOT NULL ,
  package_type SMALLINT NOT NULL DEFAULT '1' ,
  package_name VARCHAR(50) NOT NULL ,
  energy_amount INTEGER NOT NULL ,
  energy_day SMALLINT NOT NULL ,
  trx_price decimal(14,2) NOT NULL DEFAULT '0.00' ,
  usdt_price decimal(14,2) NOT NULL DEFAULT '0.00' ,
  status SMALLINT NOT NULL DEFAULT '1' ,
  seq_sn INTEGER NOT NULL DEFAULT '0' ,
  create_by INTEGER DEFAULT NULL ,
  create_time TIMESTAMP DEFAULT NULL ,
  update_by INTEGER DEFAULT NULL ,
  update_time TIMESTAMP DEFAULT NULL ,
  callback_data VARCHAR(50) NOT NULL ,
  show_notes VARCHAR(1000) DEFAULT NULL ,
  package_pic VARCHAR(200) DEFAULT NULL ,
  PRIMARY KEY (rid) USING BTREE
) ;

--
-- Dumping data for table t_energy_platform_package
--

;

INSERT INTO t_energy_platform_package (rid, bot_rid, package_type, package_name, energy_amount, energy_day, trx_price, usdt_price, status, seq_sn, create_by, create_time, update_by, update_time, callback_data, show_notes, package_pic) VALUES
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
UN

--
-- Table structure for table t_energy_wallet_trade_list
--

DROP TABLE IF EXISTS t_energy_wallet_trade_list;
;
CREATE TABLE t_energy_wallet_trade_list (
  rid SERIAL NOT NULL ,
  tx_hash VARCHAR(100) NOT NULL ,
  transferfrom_address VARCHAR(200) NOT NULL ,
  transferto_address VARCHAR(200) NOT NULL ,
  coin_name VARCHAR(20) NOT NULL ,
  amount VARCHAR(100) NOT NULL ,
  timestamp VARCHAR(20) NOT NULL ,
  process_status SMALLINT NOT NULL DEFAULT '0' ,
  process_time TIMESTAMP DEFAULT NULL ,
  process_comments VARCHAR(2000) DEFAULT NULL ,
  get_time TIMESTAMP DEFAULT NULL ,
  energy_platform_rid INTEGER DEFAULT NULL ,
  energy_platform_bot_rid INTEGER DEFAULT NULL ,
  platform_order_rid INTEGER DEFAULT NULL ,
  energy_package_rid INTEGER DEFAULT NULL ,
  tg_notice_status_receive CHAR(1) NOT NULL DEFAULT 'N' ,
  tg_notice_status_send CHAR(1) NOT NULL DEFAULT 'N' ,
  PRIMARY KEY (rid)
) ;

--
-- Dumping data for table t_energy_wallet_trade_list
--

;
UN

--
-- Table structure for table t_fms_recharge_order
--

DROP TABLE IF EXISTS t_fms_recharge_order;
;
CREATE TABLE t_fms_recharge_order (
  rid SERIAL NOT NULL ,
  bot_rid INTEGER NOT NULL ,
  recharge_tg_uid VARCHAR(100) NOT NULL ,
  recharge_tg_username VARCHAR(100) DEFAULT NULL ,
  recharge_coin_name VARCHAR(50) NOT NULL ,
  recharge_pay_price decimal(12,4) NOT NULL ,
  need_pay_price decimal(12,4) NOT NULL ,
  status SMALLINT NOT NULL DEFAULT '0' ,
  comments VARCHAR(255) DEFAULT NULL ,
  create_time TIMESTAMP NOT NULL ,
  update_time TIMESTAMP DEFAULT NULL ,
  expire_time TIMESTAMP NOT NULL ,
  cancel_time TIMESTAMP DEFAULT NULL ,
  complete_time TIMESTAMP DEFAULT NULL ,
  tx_hash VARCHAR(100) DEFAULT NULL ,
  PRIMARY KEY (rid)
) ;

--
-- Dumping data for table t_fms_recharge_order
--

;
UN

--
-- Table structure for table t_fms_wallet_trade_list
--

DROP TABLE IF EXISTS t_fms_wallet_trade_list;
;
CREATE TABLE t_fms_wallet_trade_list (
  rid SERIAL NOT NULL ,
  tx_hash VARCHAR(100) NOT NULL ,
  transferfrom_address VARCHAR(200) NOT NULL ,
  transferto_address VARCHAR(200) NOT NULL ,
  coin_name VARCHAR(20) NOT NULL ,
  amount VARCHAR(100) NOT NULL ,
  timestamp VARCHAR(20) NOT NULL ,
  process_status SMALLINT NOT NULL DEFAULT '0' ,
  process_time TIMESTAMP DEFAULT NULL ,
  process_comments VARCHAR(255) DEFAULT NULL ,
  get_time TIMESTAMP DEFAULT NULL ,
  tg_notice_status_receive CHAR(1) NOT NULL DEFAULT 'N' ,
  tg_notice_status_send CHAR(1) NOT NULL DEFAULT 'N' ,
  recharge_order_rid INTEGER DEFAULT NULL ,
  PRIMARY KEY (rid)
) ;

--
-- Dumping data for table t_fms_wallet_trade_list
--

;
UN

--
-- Table structure for table t_model_has_permissions
--

DROP TABLE IF EXISTS t_model_has_permissions;
;
CREATE TABLE t_model_has_permissions (
  permission_id int(10) unsigned NOT NULL,
  model_type VARCHAR(191) NOT NULL,
  model_id BIGINT NOT NULL,
  PRIMARY KEY (permission_id,model_id,model_type),
  CONSTRAINT t_model_has_permissions_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES t_permissions (id) ON DELETE CASCADE
) ;

--
-- Dumping data for table t_model_has_permissions
--

;
UN

--
-- Table structure for table t_model_has_roles
--

DROP TABLE IF EXISTS t_model_has_roles;
;
CREATE TABLE t_model_has_roles (
  role_id int(10) unsigned NOT NULL,
  model_type VARCHAR(191) NOT NULL,
  model_id BIGINT NOT NULL,
  PRIMARY KEY (role_id,model_id,model_type),
  CONSTRAINT t_model_has_roles_role_id_foreign FOREIGN KEY (role_id) REFERENCES t_roles (id) ON DELETE CASCADE
) ;

--
-- Dumping data for table t_model_has_roles
--

;
INSERT INTO t_model_has_roles VALUES (1,'App\\Models\\Admin\\Admin',1),(1,'App\\Models\\Admin\\Admin',2),(1,'App\\Models\\Admin\\Admin',6);
UN

--
-- Table structure for table t_monitor_bot
--

DROP TABLE IF EXISTS t_monitor_bot;
;
CREATE TABLE t_monitor_bot (
  rid SERIAL NOT NULL ,
  bot_rid INTEGER NOT NULL ,
  status SMALLINT NOT NULL DEFAULT '1' ,
  price_usdt_5 decimal(16,4) NOT NULL DEFAULT '0.0000' ,
  price_usdt_10 decimal(16,4) NOT NULL DEFAULT '0.0000' ,
  price_usdt_20 decimal(16,4) NOT NULL DEFAULT '0.0000' ,
  price_usdt_50 decimal(16,4) NOT NULL DEFAULT '0.0000' ,
  price_usdt_100 decimal(16,4) NOT NULL DEFAULT '0.0000' ,
  price_usdt_200 decimal(16,4) NOT NULL DEFAULT '0.0000' ,
  comments VARCHAR(3000) DEFAULT NULL ,
  create_time TIMESTAMP NOT NULL ,
  update_time TIMESTAMP DEFAULT NULL ,
  PRIMARY KEY (rid)
) ;

--
-- Dumping data for table t_monitor_bot
--

;
INSERT INTO t_monitor_bot VALUES (2,1,0,5.0000,8.0000,13.0000,24.0000,38.0000,56.0000,'','2023-11-25 19:54:54','2023-11-25 23:44:54');
UN

--
-- Table structure for table t_monitor_wallet
--

DROP TABLE IF EXISTS t_monitor_wallet;
;
CREATE TABLE t_monitor_wallet (
  rid SERIAL NOT NULL ,
  chain_type VARCHAR(10) NOT NULL ,
  monitor_wallet VARCHAR(200) NOT NULL ,
  status SMALLINT NOT NULL DEFAULT '1' ,
  bot_rid INTEGER DEFAULT NULL ,
  tg_notice_obj VARCHAR(200) DEFAULT NULL ,
  balance_alert decimal(14,2) NOT NULL DEFAULT '0.00' ,
  balance_amount decimal(14,2) NOT NULL DEFAULT '0.00' ,
  comments VARCHAR(100) DEFAULT NULL ,
  create_time TIMESTAMP DEFAULT NULL ,
  update_time TIMESTAMP DEFAULT NULL ,
  PRIMARY KEY (rid)
) ;

--
-- Dumping data for table t_monitor_wallet
--

;
UN

--
-- Table structure for table t_permissions
--

DROP TABLE IF EXISTS t_permissions;
;
CREATE TABLE t_permissions (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  name VARCHAR(191) NOT NULL,
  guard_name VARCHAR(191) NOT NULL,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  route VARCHAR(255) DEFAULT NULL,
  pid INTEGER DEFAULT NULL,
  PRIMARY KEY (id)
) ;

--
-- Dumping data for table t_permissions
--

;
INSERT INTO t_permissions VALUES (5,'主页','web','2020-09-22 08:33:50','2020-09-22 08:33:50','',0),(6,'主页列表','web','2020-09-22 08:37:02','2020-09-22 08:37:02','admin.home',5),(7,'系统管理','web','2020-09-22 08:37:37','2020-09-22 08:37:37','',0),(8,'管理员管理','web','2020-09-22 08:38:14','2020-09-22 08:38:14','admin.system.admin.index',7),(9,'权限管理','web','2020-09-22 08:38:23','2020-09-22 08:38:23','admin.system.permission.index',7),(10,'角色管理','web','2020-09-22 08:38:34','2020-09-22 08:38:34','admin.system.role.index',7),(11,'添加管理员','web','2020-09-22 08:39:33','2020-09-22 08:39:33','admin.system.admin.add',8),(12,'修改管理员状态','web','2020-09-22 09:23:59','2020-09-22 09:23:59','admin.system.admin.change_status',8),(13,'修改管理员资料','web','2020-09-22 09:24:12','2020-09-22 09:24:12','admin.system.admin.update',8),(14,'删除管理员','web','2020-09-22 09:24:21','2020-09-22 09:24:21','admin.system.admin.delete',8),(15,'添加角色','web','2020-09-22 09:25:14','2020-09-22 09:25:14','admin.system.role.add',10),(16,'编辑角色权限','web','2020-09-22 09:25:23','2020-09-22 09:25:23','admin.system.role.show_permissions',10),(17,'修改角色名称','web','2020-09-22 09:25:30','2020-09-22 09:25:30','admin.system.role.update',10),(18,'删除角色','web','2020-09-22 09:25:38','2020-09-22 09:25:38','admin.system.role.del',10),(19,'系统设置','web','2021-07-09 05:42:29','2021-07-09 05:42:29','',0),(20,'配置信息','web','2021-07-09 05:42:47','2021-07-09 05:42:47','admin.setting.config.index',19),(21,'搜索查询_配置信息','web','2021-07-09 05:42:55','2021-07-09 05:42:55','admin.search',20),(22,'数据字典','web','2021-07-09 05:43:06','2021-07-09 05:43:06','admin.setting.dictionary.index',19),(23,'添加数据字典','web','2021-07-09 05:43:27','2021-07-09 05:43:27','admin.setting.dictionary.store',22),(24,'编辑数据字典','web','2021-07-09 05:43:38','2021-07-09 05:43:38','admin.setting.dictionary.update',22),(25,'删除数据字典','web','2021-07-09 05:43:49','2021-07-09 05:43:49','admin.setting.dictionary.delete',22),(26,'搜索查询_数据字典','web','2021-07-09 05:43:56','2021-07-09 05:43:56','admin.search',22),(27,'应用升级','web','2021-07-09 05:44:09','2021-07-09 05:44:09','admin.setting.app_version.index',19),(28,'升级应用升级','web','2021-07-09 05:44:25','2021-07-09 05:44:25','admin.setting.app_version.store',27),(29,'编辑应用升级','web','2021-07-09 05:44:36','2021-07-09 05:44:36','admin.setting.app_version.edit',27),(30,'搜索查询_应用升级','web','2021-07-09 05:44:53','2021-07-09 05:44:53','admin.search',27);
UN

--
-- Table structure for table t_premium_platform
--

DROP TABLE IF EXISTS t_premium_platform;
;
CREATE TABLE t_premium_platform (
  rid SERIAL NOT NULL ,
  bot_rid INTEGER NOT NULL ,
  platform_name SMALLINT NOT NULL ,
  tg_admin_uid VARCHAR(500) DEFAULT NULL ,
  platform_hash VARCHAR(500) DEFAULT NULL ,
  platform_cookie VARCHAR(3000) DEFAULT NULL ,
  platform_phrase VARCHAR(3000) DEFAULT NULL ,
  status SMALLINT NOT NULL DEFAULT '1' ,
  receive_wallet VARCHAR(100) DEFAULT NULL ,
  get_tx_time TIMESTAMP DEFAULT NULL ,
  tg_notice_obj_receive VARCHAR(200) DEFAULT NULL ,
  tg_notice_obj_send VARCHAR(200) DEFAULT NULL ,
  comments VARCHAR(255) DEFAULT NULL ,
  create_time TIMESTAMP NOT NULL ,
  update_time TIMESTAMP DEFAULT NULL ,
  PRIMARY KEY (rid)
) ;

--
-- Dumping data for table t_premium_platform
--

;
INSERT INTO t_premium_platform VALUES (1,1,1,'6666666','23deweww',NULL,NULL,0,'ttttttttttttttt','2023-11-21 00:00:00','6666666','-1111111','','2023-11-21 23:45:17',NULL);
UN

--
-- Table structure for table t_premium_platform_order
--

DROP TABLE IF EXISTS t_premium_platform_order;
;
CREATE TABLE t_premium_platform_order (
  rid SERIAL NOT NULL ,
  bot_rid INTEGER NOT NULL ,
  premium_platform_rid INTEGER NOT NULL ,
  source_type SMALLINT NOT NULL DEFAULT '1' ,
  buy_tg_uid VARCHAR(100) NOT NULL ,
  buy_tg_username VARCHAR(100) DEFAULT NULL ,
  premium_tg_username VARCHAR(100) NOT NULL ,
  need_pay_usdt decimal(12,4) NOT NULL ,
  status SMALLINT NOT NULL DEFAULT '0' ,
  premium_platform_package_rid INTEGER NOT NULL ,
  premium_package_month INTEGER NOT NULL ,
  comments VARCHAR(255) DEFAULT NULL ,
  create_time TIMESTAMP NOT NULL ,
  update_time TIMESTAMP DEFAULT NULL ,
  expire_time TIMESTAMP NOT NULL ,
  cancel_time TIMESTAMP DEFAULT NULL ,
  complete_time TIMESTAMP DEFAULT NULL ,
  recipient VARCHAR(1000) DEFAULT NULL ,
  tx_hash VARCHAR(200) DEFAULT NULL ,
  PRIMARY KEY (rid)
) ;

--
-- Dumping data for table t_premium_platform_order
--

;
UN

--
-- Table structure for table t_premium_platform_package
--

DROP TABLE IF EXISTS t_premium_platform_package;
;
CREATE TABLE t_premium_platform_package (
  rid SERIAL NOT NULL ,
  bot_rid INTEGER NOT NULL ,
  premium_platform_rid INTEGER NOT NULL ,
  package_name VARCHAR(50) NOT NULL ,
  package_month INTEGER NOT NULL ,
  usdt_price decimal(14,2) NOT NULL ,
  callback_data VARCHAR(50) NOT NULL ,
  seq_sn INTEGER NOT NULL DEFAULT '0' ,
  status SMALLINT NOT NULL DEFAULT '1' ,
  show_notes VARCHAR(1000) DEFAULT NULL ,
  package_pic VARCHAR(200) DEFAULT NULL ,
  comments VARCHAR(255) DEFAULT NULL ,
  create_time TIMESTAMP NOT NULL ,
  update_time TIMESTAMP DEFAULT NULL ,
  PRIMARY KEY (rid)
) ;

--
-- Dumping data for table t_premium_platform_package
--

;
INSERT INTO t_premium_platform_package VALUES (1,1,1,'3个月 价格 15 USDT',3,15.00,'premium_aee6034741a76c9c947844f196fc58e3',1,0,'',NULL,NULL,'2023-11-21 23:46:14',NULL),(2,1,1,'6个月 价格 25 USDT',6,25.00,'premium_310fe79585da99e1b3edd1393ff6a36a',2,0,'',NULL,NULL,'2023-11-21 23:46:31',NULL),(3,1,1,'12个月 价格 45 USDT',12,45.00,'premium_a21a27e202cdc317de8466ef7250a6f1',3,0,'',NULL,NULL,'2023-11-21 23:46:44',NULL);
UN

--
-- Table structure for table t_premium_wallet_trade_list
--

DROP TABLE IF EXISTS t_premium_wallet_trade_list;
;
CREATE TABLE t_premium_wallet_trade_list (
  rid SERIAL NOT NULL ,
  tx_hash VARCHAR(100) NOT NULL ,
  transferfrom_address VARCHAR(200) NOT NULL ,
  transferto_address VARCHAR(200) NOT NULL ,
  coin_name VARCHAR(20) NOT NULL ,
  amount VARCHAR(100) NOT NULL ,
  timestamp VARCHAR(20) NOT NULL ,
  process_status SMALLINT NOT NULL DEFAULT '0' ,
  process_time TIMESTAMP DEFAULT NULL ,
  process_comments VARCHAR(255) DEFAULT NULL ,
  get_time TIMESTAMP DEFAULT NULL ,
  tg_notice_status_receive CHAR(1) NOT NULL DEFAULT 'N' ,
  tg_notice_status_send CHAR(1) NOT NULL DEFAULT 'N' ,
  platform_order_rid INTEGER DEFAULT NULL ,
  premium_package_rid INTEGER DEFAULT NULL ,
  premium_platform_rid INTEGER DEFAULT NULL ,
  PRIMARY KEY (rid)
) ;

--
-- Dumping data for table t_premium_wallet_trade_list
--

;
UN

--
-- Table structure for table t_role_has_permissions
--

DROP TABLE IF EXISTS t_role_has_permissions;
;
CREATE TABLE t_role_has_permissions (
  permission_id int(10) unsigned NOT NULL,
  role_id int(10) unsigned NOT NULL,
  PRIMARY KEY (permission_id,role_id),
  CONSTRAINT t_role_has_permissions_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES t_permissions (id) ON DELETE CASCADE,
  CONSTRAINT t_role_has_permissions_role_id_foreign FOREIGN KEY (role_id) REFERENCES t_roles (id) ON DELETE CASCADE
) ;

--
-- Dumping data for table t_role_has_permissions
--

;
UN

--
-- Table structure for table t_roles
--

DROP TABLE IF EXISTS t_roles;
;
CREATE TABLE t_roles (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  name VARCHAR(191) NOT NULL,
  guard_name VARCHAR(191) NOT NULL,
  created_at timestamp NULL DEFAULT NULL,
  updated_at timestamp NULL DEFAULT NULL,
  status SMALLINT DEFAULT NULL,
  PRIMARY KEY (id)
) ;

--
-- Dumping data for table t_roles
--

;
INSERT INTO t_roles VALUES (1,'超级管理员','web',NULL,NULL,NULL);
UN

--
-- Table structure for table t_shop_goods
--

DROP TABLE IF EXISTS t_shop_goods;
;
CREATE TABLE t_shop_goods (
  rid SERIAL NOT NULL ,
  goods_name VARCHAR(50) NOT NULL ,
  goods_type SMALLINT NOT NULL ,
  goods_usdt_price decimal(16,4) NOT NULL DEFAULT '0.0000' ,
  goods_trx_price decimal(16,4) NOT NULL DEFAULT '0.0000' ,
  show_notes VARCHAR(1000) DEFAULT NULL ,
  status SMALLINT NOT NULL DEFAULT '1' ,
  seq_sn INTEGER NOT NULL DEFAULT '0' ,
  comments VARCHAR(3000) DEFAULT NULL ,
  create_time TIMESTAMP NOT NULL ,
  update_time TIMESTAMP DEFAULT NULL ,
  PRIMARY KEY (rid)
) ;

--
-- Dumping data for table t_shop_goods
--

;
UN

--
-- Table structure for table t_shop_goods_bot
--

DROP TABLE IF EXISTS t_shop_goods_bot;
;
CREATE TABLE t_shop_goods_bot (
  rid SERIAL NOT NULL ,
  goods_rid INTEGER NOT NULL ,
  bot_rid INTEGER NOT NULL ,
  goods_usdt_discount decimal(4,2) NOT NULL DEFAULT '1.00' ,
  goods_trx_discount decimal(4,2) NOT NULL DEFAULT '1.00' ,
  status SMALLINT NOT NULL DEFAULT '1' ,
  comments VARCHAR(3000) DEFAULT NULL ,
  create_time TIMESTAMP NOT NULL ,
  update_time TIMESTAMP DEFAULT NULL ,
  PRIMARY KEY (rid)
) ;

--
-- Dumping data for table t_shop_goods_bot
--

;
UN

--
-- Table structure for table t_shop_goods_cdkey
--

DROP TABLE IF EXISTS t_shop_goods_cdkey;
;
CREATE TABLE t_shop_goods_cdkey (
  rid SERIAL NOT NULL ,
  goods_rid INTEGER NOT NULL ,
  cdkey_no VARCHAR(100) NOT NULL ,
  cdkey_pwd VARCHAR(2000) NOT NULL ,
  cdkey_usdt_price decimal(16,4) NOT NULL DEFAULT '0.0000' ,
  cdkey_trx_price decimal(16,4) NOT NULL DEFAULT '0.0000' ,
  status SMALLINT NOT NULL DEFAULT '0' ,
  seq_sn INTEGER NOT NULL DEFAULT '0' ,
  create_time TIMESTAMP NOT NULL ,
  update_time TIMESTAMP DEFAULT NULL ,
  PRIMARY KEY (rid)
) ;

--
-- Dumping data for table t_shop_goods_cdkey
--

;
UN

--
-- Table structure for table t_shop_order
--

DROP TABLE IF EXISTS t_shop_order;
;
CREATE TABLE t_shop_order (
  rid SERIAL NOT NULL ,
  bot_rid INTEGER NOT NULL ,
  tg_uid VARCHAR(50) NOT NULL ,
  tg_username VARCHAR(100) DEFAULT NULL ,
  cdkey_no VARCHAR(100) DEFAULT NULL ,
  cdkey_pwd VARCHAR(2000) DEFAULT NULL ,
  pay_type SMALLINT NOT NULL ,
  pay_price VARCHAR(50) DEFAULT NULL ,
  comments VARCHAR(3000) DEFAULT NULL ,
  pay_time TIMESTAMP DEFAULT NULL ,
  PRIMARY KEY (rid)
) ;

--
-- Dumping data for table t_shop_order
--

;
UN

--
-- Table structure for table t_sys_config
--

DROP TABLE IF EXISTS t_sys_config;
;
CREATE TABLE t_sys_config (
  rid SERIAL NOT NULL ,
  config_key VARCHAR(50) NOT NULL ,
  config_val VARCHAR(500) NOT NULL ,
  comments VARCHAR(255) NOT NULL ,
  create_by VARCHAR(50) DEFAULT NULL ,
  create_time TIMESTAMP DEFAULT NULL ,
  update_by VARCHAR(50) DEFAULT NULL ,
  update_time TIMESTAMP DEFAULT NULL ,
  PRIMARY KEY (rid)
) ;

--
-- Dumping data for table t_sys_config
--

;
INSERT INTO t_sys_config VALUES (1,'job_url','{\"url\":\"https:\\/\\/job44.xxx.pro\"}','任务域名url','1','2022-05-05 12:55:54','1','2022-05-05 12:55:54'),(2,'ton_url','{\"url\":\"https:\\/\\/pytonpay.walletim.vip\\/api\\/premium\"}','ton支付接口url(不需要开通tg会员,用不到这个接口)','1','2022-05-05 12:55:54','1','2022-05-05 12:55:54');
UN

--
-- Table structure for table t_telegram_bot
--

DROP TABLE IF EXISTS t_telegram_bot;
;
CREATE TABLE t_telegram_bot (
  rid SERIAL NOT NULL ,
  bot_token VARCHAR(100) NOT NULL ,
  bot_admin_username VARCHAR(100) DEFAULT '-' ,
  bot_firstname VARCHAR(100) DEFAULT NULL ,
  bot_username VARCHAR(100) DEFAULT NULL ,
  comments VARCHAR(255) DEFAULT NULL ,
  create_by INTEGER DEFAULT NULL ,
  create_time TIMESTAMP DEFAULT NULL ,
  update_by INTEGER DEFAULT NULL ,
  update_time TIMESTAMP DEFAULT NULL ,
  recharge_wallet_addr VARCHAR(100) DEFAULT NULL ,
  get_tx_time TIMESTAMP DEFAULT NULL ,
  PRIMARY KEY (rid)
) ;

--
-- Dumping data for table t_telegram_bot
--

;
INSERT INTO t_telegram_bot VALUES (1,'6666666:AAHOcqAPQuqtO3','@aaaa','TRX 能量 会员 靓号 24小时营业','pri_bot','own-01',NULL,'2023-11-21 23:03:34',NULL,'2023-11-21 23:07:56','ttttttttt','2023-11-21 00:00:00');
UN

--
-- Table structure for table t_telegram_bot_ad
--

DROP TABLE IF EXISTS t_telegram_bot_ad;
;
CREATE TABLE t_telegram_bot_ad (
  rid SERIAL NOT NULL ,
  bot_rid INTEGER NOT NULL ,
  notice_cycle SMALLINT NOT NULL DEFAULT '1' ,
  notice_obj VARCHAR(200) NOT NULL ,
  notice_photo VARCHAR(500) DEFAULT NULL ,
  notice_ad VARCHAR(2000) NOT NULL ,
  status SMALLINT NOT NULL DEFAULT '1' ,
  last_notice_time TIMESTAMP DEFAULT NULL ,
  create_by INTEGER DEFAULT NULL ,
  create_time TIMESTAMP DEFAULT NULL ,
  update_by INTEGER DEFAULT NULL ,
  update_time TIMESTAMP DEFAULT NULL ,
  PRIMARY KEY (rid)
) ;

--
-- Dumping data for table t_telegram_bot_ad
--

;
INSERT INTO t_telegram_bot_ad VALUES (1,1,5,'-111111',NULL,'✅24小时兑换地址： <code>${trxusdtwallet}</code> (点击自动复制) \n\n实时汇率：\n10 USDT = ${trx10usdtrate} TRX\n100 USDT = ${trx100usdtrate} TRX\n1000 USDT = ${trx1000usdtrate} TRX\n\n❌请勿从交易所直接提现到机器人账户！！\n${trxusdtshownotes}\n✅只支持1 USDT及其以上的金额兑换，若转入1 USDT以下金额，将无法退还！！！\n✅另有波场靓号出售，选号咨询客服！\n\n联系客服：${tgbotadmin}\n✅能量租用：/buyenergy\n✅购买会员：/buypremium',0,'2023-12-02 00:52:00',NULL,'2023-11-22 00:19:22',NULL,'2023-11-22 00:52:14');
UN

--
-- Table structure for table t_telegram_bot_ad_keyboard
--

DROP TABLE IF EXISTS t_telegram_bot_ad_keyboard;
;
CREATE TABLE t_telegram_bot_ad_keyboard (
  rid SERIAL NOT NULL ,
  bot_rid INTEGER NOT NULL ,
  ad_rid INTEGER NOT NULL ,
  keyboard_rid INTEGER NOT NULL ,
  create_by INTEGER DEFAULT NULL ,
  create_time TIMESTAMP DEFAULT NULL ,
  update_by INTEGER DEFAULT NULL ,
  update_time TIMESTAMP DEFAULT NULL ,
  PRIMARY KEY (rid)
) ;

--
-- Dumping data for table t_telegram_bot_ad_keyboard
--

;
INSERT INTO t_telegram_bot_ad_keyboard VALUES (1,1,1,5,NULL,'2023-11-22 00:49:14',NULL,NULL),(2,1,1,6,NULL,'2023-11-22 00:49:14',NULL,NULL),(3,1,1,7,NULL,'2023-11-22 00:49:14',NULL,NULL),(4,1,1,8,NULL,'2023-11-22 00:49:14',NULL,NULL);
UN

--
-- Table structure for table t_telegram_bot_command
--

DROP TABLE IF EXISTS t_telegram_bot_command;
;
CREATE TABLE t_telegram_bot_command (
  rid SERIAL NOT NULL ,
  bot_rid INTEGER NOT NULL ,
  command VARCHAR(50) NOT NULL ,
  description VARCHAR(100) NOT NULL ,
  command_type SMALLINT NOT NULL DEFAULT '1' ,
  seq_sn INTEGER NOT NULL DEFAULT '0' ,
  create_by INTEGER DEFAULT NULL ,
  create_time TIMESTAMP DEFAULT NULL ,
  update_by INTEGER DEFAULT NULL ,
  update_time TIMESTAMP DEFAULT NULL ,
  PRIMARY KEY (rid)
) ;

--
-- Dumping data for table t_telegram_bot_command
--

;
INSERT INTO t_telegram_bot_command VALUES (1,1,'start','开始使用',1,99,NULL,'2023-11-21 23:20:47',NULL,NULL),(2,1,'trx','USDT兑换TRX',1,98,NULL,'2023-11-21 23:21:38',NULL,NULL),(3,1,'buyenergy','租用能量',1,97,NULL,'2023-11-21 23:22:03',NULL,NULL),(4,1,'buypremium','购买会员',2,96,NULL,'2023-11-21 23:22:46',NULL,NULL),(5,1,'z0','查询欧意价格',1,95,NULL,'2023-11-21 23:23:06',NULL,NULL);
UN

--
-- Table structure for table t_telegram_bot_group
--

DROP TABLE IF EXISTS t_telegram_bot_group;
;
CREATE TABLE t_telegram_bot_group (
  rid SERIAL NOT NULL ,
  bot_rid INTEGER NOT NULL ,
  group_type VARCHAR(50) NOT NULL ,
  tg_groupid bigint(20) NOT NULL ,
  tg_groupusername VARCHAR(200) DEFAULT NULL ,
  tg_groupnickname VARCHAR(200) DEFAULT NULL ,
  status SMALLINT NOT NULL DEFAULT '1' ,
  first_time TIMESTAMP DEFAULT NULL ,
  last_time TIMESTAMP DEFAULT NULL ,
  stop_time TIMESTAMP DEFAULT NULL ,
  is_admin CHAR(1) NOT NULL DEFAULT 'N' ,
  PRIMARY KEY (rid)
) ;

--
-- Dumping data for table t_telegram_bot_group
--

;
UN

--
-- Table structure for table t_telegram_bot_keyboard
--

DROP TABLE IF EXISTS t_telegram_bot_keyboard;
;
CREATE TABLE t_telegram_bot_keyboard (
  rid SERIAL NOT NULL ,
  keyboard_type SMALLINT NOT NULL DEFAULT '1' ,
  keyboard_name VARCHAR(20) NOT NULL ,
  inline_type SMALLINT NOT NULL DEFAULT '0' ,
  keyboard_value VARCHAR(500) NOT NULL ,
  status SMALLINT NOT NULL DEFAULT '0' ,
  create_by INTEGER DEFAULT NULL ,
  create_time TIMESTAMP DEFAULT NULL ,
  update_by INTEGER DEFAULT NULL ,
  update_time TIMESTAMP DEFAULT NULL ,
  seq_sn INTEGER NOT NULL DEFAULT '0' ,
  PRIMARY KEY (rid)
) ;

--
-- Dumping data for table t_telegram_bot_keyboard
--

;

INSERT INTO t_telegram_bot_keyboard (rid, keyboard_type, keyboard_name, inline_type, keyboard_value, status, create_by, create_time, update_by, update_time, seq_sn) VALUES
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
UN

--
-- Table structure for table t_telegram_bot_keyreply
--

DROP TABLE IF EXISTS t_telegram_bot_keyreply;
;
CREATE TABLE t_telegram_bot_keyreply (
  rid SERIAL NOT NULL ,
  bot_rid INTEGER NOT NULL ,
  key_type SMALLINT NOT NULL ,
  monitor_word VARCHAR(500) NOT NULL ,
  reply_photo VARCHAR(300) DEFAULT NULL ,
  reply_content VARCHAR(2000) NOT NULL ,
  opt_type INTEGER NOT NULL ,
  status SMALLINT NOT NULL DEFAULT '0' ,
  create_by INTEGER DEFAULT NULL ,
  create_time TIMESTAMP DEFAULT NULL ,
  update_by INTEGER DEFAULT NULL ,
  update_time TIMESTAMP DEFAULT NULL ,
  PRIMARY KEY (rid)
) ;

--
-- Dumping data for table t_telegram_bot_keyreply
--

;

INSERT INTO t_telegram_bot_keyreply (rid, bot_rid, key_type, monitor_word, reply_photo, reply_content, opt_type, status, create_by, create_time, update_by, update_time) VALUES
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
UN

--
-- Table structure for table t_telegram_bot_keyreply_keyboard
--

DROP TABLE IF EXISTS t_telegram_bot_keyreply_keyboard;
;
CREATE TABLE t_telegram_bot_keyreply_keyboard (
  rid SERIAL NOT NULL ,
  bot_rid INTEGER NOT NULL ,
  keyreply_rid INTEGER NOT NULL ,
  keyboard_rid INTEGER NOT NULL ,
  create_by INTEGER DEFAULT NULL ,
  create_time TIMESTAMP DEFAULT NULL ,
  update_by INTEGER DEFAULT NULL ,
  update_time TIMESTAMP DEFAULT NULL ,
  PRIMARY KEY (rid)
) ;

--
-- Dumping data for table t_telegram_bot_keyreply_keyboard
--

;

INSERT INTO t_telegram_bot_keyreply_keyboard (rid, bot_rid, keyreply_rid, keyboard_rid, create_by, create_time, update_by, update_time) VALUES
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
UN

--
-- Table structure for table t_telegram_bot_user
--

DROP TABLE IF EXISTS t_telegram_bot_user;
;
CREATE TABLE t_telegram_bot_user (
  rid SERIAL NOT NULL ,
  bot_rid INTEGER NOT NULL ,
  tg_uid bigint(20) NOT NULL ,
  tg_username VARCHAR(200) DEFAULT NULL ,
  tg_nickname VARCHAR(200) DEFAULT NULL ,
  status SMALLINT NOT NULL DEFAULT '1' ,
  bind_trc_wallet_addr VARCHAR(200) DEFAULT NULL ,
  first_time TIMESTAMP DEFAULT NULL ,
  last_time TIMESTAMP DEFAULT NULL ,
  stop_time TIMESTAMP DEFAULT NULL ,
  cash_trx decimal(16,6) NOT NULL DEFAULT '0.000000' ,
  cash_usdt decimal(16,6) NOT NULL DEFAULT '0.000000' ,
  total_recharge_trx decimal(16,6) NOT NULL DEFAULT '0.000000' ,
  total_recharge_usdt decimal(16,6) NOT NULL DEFAULT '0.000000' ,
  max_monitor_wallet INTEGER NOT NULL DEFAULT '2' ,
  PRIMARY KEY (rid)
) ;

--
-- Dumping data for table t_telegram_bot_user
--

;
UN

--
-- Table structure for table t_transit_user_wallet
--

DROP TABLE IF EXISTS t_transit_user_wallet;
;
CREATE TABLE t_transit_user_wallet (
  rid SERIAL NOT NULL ,
  chain_type VARCHAR(10) NOT NULL ,
  wallet_addr VARCHAR(200) NOT NULL ,
  total_transit_usdt VARCHAR(200) NOT NULL DEFAULT '0' ,
  total_transit_sxf VARCHAR(200) NOT NULL DEFAULT '0' ,
  total_yuzhi_sxf VARCHAR(200) NOT NULL DEFAULT '0' ,
  need_feedback_sxf VARCHAR(200) NOT NULL DEFAULT '0' ,
  send_feedback_sxf VARCHAR(200) NOT NULL DEFAULT '0' ,
  last_transit_time TIMESTAMP DEFAULT NULL ,
  last_yuzhi_time TIMESTAMP DEFAULT NULL ,
  PRIMARY KEY (rid)
) ;

--
-- Dumping data for table t_transit_user_wallet
--

;
UN

--
-- Table structure for table t_transit_wallet
--

DROP TABLE IF EXISTS t_transit_wallet;
;
CREATE TABLE t_transit_wallet (
  rid SERIAL NOT NULL ,
  chain_type VARCHAR(10) NOT NULL ,
  receive_wallet VARCHAR(200) NOT NULL ,
  send_wallet VARCHAR(200) NOT NULL ,
  send_wallet_privatekey VARCHAR(2000) DEFAULT NULL ,
  show_notes VARCHAR(500) DEFAULT NULL ,
  status SMALLINT NOT NULL DEFAULT '1' ,
  tg_notice_obj_receive VARCHAR(200) DEFAULT NULL ,
  tg_notice_obj_send VARCHAR(200) DEFAULT NULL ,
  get_tx_time TIMESTAMP DEFAULT NULL ,
  create_time TIMESTAMP DEFAULT NULL ,
  update_time TIMESTAMP DEFAULT NULL ,
  bot_rid INTEGER DEFAULT NULL ,
  auto_stock_min_trx INTEGER NOT NULL DEFAULT '0' ,
  auto_stock_per_usdt INTEGER NOT NULL DEFAULT '0' ,
  PRIMARY KEY (rid)
) ;

--
-- Dumping data for table t_transit_wallet
--

;
INSERT INTO t_transit_wallet VALUES (1,'trc','tttttt','tttttt',NULL,'✅请认准靓号 TWxm1pW 开头 8个U 结尾',0,'6666666','-111111','2023-11-21 00:00:00','2023-11-21 23:37:38','2023-11-22 00:18:53',1,0,0);
UN

--
-- Table structure for table t_transit_wallet_black
--

DROP TABLE IF EXISTS t_transit_wallet_black;
;
CREATE TABLE t_transit_wallet_black (
  rid SERIAL NOT NULL ,
  chain_type VARCHAR(10) NOT NULL ,
  black_wallet VARCHAR(200) NOT NULL ,
  comments VARCHAR(255) DEFAULT NULL ,
  create_time TIMESTAMP DEFAULT NULL ,
  update_time TIMESTAMP DEFAULT NULL ,
  PRIMARY KEY (rid)
) ;

--
-- Dumping data for table t_transit_wallet_black
--

;
INSERT INTO t_transit_wallet_black VALUES (4,'trc','TWd4WrZ9wn84f5x1hZhL4DHvk738ns5jwb','币安',NULL,NULL),(5,'trc','TMuA6YqfCeX8EhbfYEg5y7S4DqzSJireY9','币安','2023-02-11 13:29:31','2023-02-11 13:29:42'),(6,'trc','TT1DyeqXaaJkt6UhVYFWUXBXknaXnBudTK','币安','2023-02-11 13:30:08',NULL),(7,'trc','TJCo98saj6WND61g1uuKwJ9GMWMT9WkJFo','币安','2023-02-11 13:30:36',NULL),(8,'trc','TV6MuMXfmLbBqPZvBHdwFsDnQeVfnmiuSi','币安','2023-02-11 13:30:51',NULL),(9,'trc','TDToUxX8sH4z6moQpK3ZLAN24eupu2ivA4',NULL,'2023-02-11 13:31:07',NULL),(10,'trc','TRYL7PKCG4b4xRCM554Q5J6o8f1UjUmfnY','Kucoin-Cold','2023-02-11 13:31:27',NULL),(11,'trc','TB1WQmj63bHV9Qmuhp39WABzutphMAetSc',NULL,'2023-02-11 13:31:41',NULL),(12,'trc','TNiq9AXBp9EjUqhDhrwrfvAA8U3GUQZH81',NULL,'2023-02-11 13:31:49',NULL),(13,'trc','TKHuVq1oKVruCGLvqVexFs6dawKv6fQgFs',NULL,'2023-02-11 13:32:02',NULL),(14,'trc','TMmhxjhqPbUwgzfV3eV94T398Qk1khE32v',NULL,'2023-02-11 13:32:11',NULL),(15,'trc','TMhJviFWiaxvqKLdng9dmsi1H5H5yTGEeu',NULL,'2023-02-11 13:32:24',NULL),(16,'trc','TTd9qHyjqiUkfTxe3gotbuTMpjU8LEbpkN','Kraken','2023-02-11 13:32:40',NULL),(17,'trc','TTiDLWE6fZK8okMJv6ijg42yrH6W2pjSr9',NULL,'2023-02-11 13:32:50',NULL),(18,'trc','TJYM8UnYvZ8iM5PjuHTYsDYXhY1YZBeKeX',NULL,'2023-02-11 13:32:57',NULL),(19,'trc','TQeNNo5zVarhdKm5EiJSekfNXg6H1tRN4n',NULL,'2023-02-11 13:33:04',NULL),(20,'trc','TJbHp48Shg4tTD5x6fKkU7PodggL5mjcJP',NULL,'2023-02-11 13:33:12',NULL),(21,'trc','TWGZbjofbTLY3UCjCV4yiLkRg89zLqwRgi',NULL,'2023-02-11 13:33:19',NULL),(22,'trc','TM1zzNDZD2DPASbKcgdVoTYhfmYgtfwx9R','okx','2023-02-11 13:33:35','2023-02-11 13:34:05'),(23,'trc','TBA6CypYJizwA9XdC7Ubgc5F1bxrQ7SqPt','gate','2023-02-11 13:34:32',NULL),(24,'trc','TNaRAoLUyYEV2uF7GUrzSjRQTU8v5ZJ5VR','huobi','2023-02-11 13:34:49',NULL),(25,'trc','TNXoiAJ3dct8Fjg4M9fkLFh9S2v9TXc32G',NULL,'2023-02-11 13:35:09',NULL),(26,'trc','TJDENsfBJs4RFETt1X1W8wMDc8M5XnJhCe',NULL,'2023-02-11 13:35:23',NULL),(27,'trc','TYASr5UV6HEcXatwdFQfmLVUqQQQMUxHLS',NULL,'2023-02-11 13:35:37',NULL),(29,'trc','TPF3KHqPbCFQL2UDrHr4LuHoYU9XPfzYLo',NULL,'2023-04-30 19:25:22',NULL),(30,'trc','TKk9y2F5oFnnjSiH53fUhvRC55joFwS8c9',NULL,'2023-05-23 13:18:09',NULL),(31,'trc','TX3xNEmn9S5c77qeNnQhsfgcrbgqYo9Xcc',NULL,'2023-10-06 15:34:50',NULL),(32,'trc','TCz47XgC9TjCeF4UzfB6qZbM9LTF9s1tG7','欧意','2023-10-06 20:14:42',NULL),(33,'trc','TAzsQ9Gx8eqFNFSKbeXrbi45CuVPHzA8wr',NULL,'2023-10-09 00:14:18',NULL),(34,'trc','TSaRZDiBPD8Rd5vrvX8a4zgunHczM9mj8S',NULL,'2023-10-09 00:15:05',NULL);
UN

--
-- Table structure for table t_transit_wallet_coin
--

DROP TABLE IF EXISTS t_transit_wallet_coin;
;
CREATE TABLE t_transit_wallet_coin (
  rid SERIAL NOT NULL ,
  transit_wallet_id INTEGER NOT NULL ,
  in_coin_name VARCHAR(20) NOT NULL ,
  out_coin_name VARCHAR(20) NOT NULL ,
  is_realtime_rate SMALLINT NOT NULL DEFAULT '1' ,
  profit_rate decimal(4,2) NOT NULL DEFAULT '1.00' ,
  exchange_rate decimal(8,2) NOT NULL DEFAULT '0.00' ,
  kou_out_amount decimal(8,2) NOT NULL DEFAULT '0.00' ,
  min_transit_amount INTEGER NOT NULL ,
  max_transit_amount INTEGER NOT NULL ,
  comments VARCHAR(255) DEFAULT NULL ,
  create_by INTEGER DEFAULT NULL ,
  create_time TIMESTAMP DEFAULT NULL ,
  update_by INTEGER DEFAULT NULL ,
  update_time TIMESTAMP DEFAULT NULL ,
  PRIMARY KEY (rid)
) ;

--
-- Dumping data for table t_transit_wallet_coin
--

;
INSERT INTO t_transit_wallet_coin VALUES (1,1,'usdt','trx',3,0.10,8.76,0.00,1,200,NULL,NULL,'2023-11-21 23:38:02',NULL,'2023-11-22 00:53:41');
UN

--
-- Table structure for table t_transit_wallet_trade_list
--

DROP TABLE IF EXISTS t_transit_wallet_trade_list;
;
CREATE TABLE t_transit_wallet_trade_list (
  rid SERIAL NOT NULL ,
  tx_hash VARCHAR(100) NOT NULL ,
  transferfrom_address VARCHAR(200) NOT NULL ,
  transferto_address VARCHAR(200) NOT NULL ,
  coin_name VARCHAR(20) NOT NULL ,
  amount VARCHAR(100) NOT NULL ,
  timestamp VARCHAR(20) NOT NULL ,
  process_status SMALLINT NOT NULL DEFAULT '0' ,
  process_time TIMESTAMP DEFAULT NULL ,
  process_comments VARCHAR(255) DEFAULT NULL ,
  get_time TIMESTAMP DEFAULT NULL ,
  sendback_address VARCHAR(200) DEFAULT NULL ,
  sendback_amount VARCHAR(100) NOT NULL DEFAULT '0' ,
  sendback_time TIMESTAMP DEFAULT NULL ,
  sendback_coin_name VARCHAR(20) DEFAULT NULL ,
  sendback_tx_hash VARCHAR(100) DEFAULT NULL ,
  sendback_contract_ret VARCHAR(20) DEFAULT NULL ,
  tg_notice_status_receive CHAR(1) NOT NULL DEFAULT 'N' ,
  tg_notice_status_send CHAR(1) NOT NULL DEFAULT 'N' ,
  current_exchange_rate decimal(8,2) NOT NULL DEFAULT '0.00' ,
  current_huan_yuzhi_amount decimal(14,2) NOT NULL DEFAULT '0.00' ,
  PRIMARY KEY (rid)
) ;

--
-- Dumping data for table t_transit_wallet_trade_list
--

;
UN

--
-- Dumping events for database 'trxswapbot'
--

--
-- Dumping routines for database 'trxswapbot'
--
;
;
;
;

-- Dump completed on 2023-12-03  0:35:06

ALTER TABLE t_energy_ai_trusteeship ADD max_buy_quantity INT NOT NULL DEFAULT '0'  AFTER total_buy_quantity;
ALTER TABLE t_energy_ai_trusteeship ADD back_comments VARCHAR(255) NULL  AFTER comments;

ALTER TABLE t_energy_platform_bot ADD is_open_bishu CHAR(1) NOT NULL DEFAULT 'Y'  AFTER per_energy_day;

ALTER TABLE t_energy_platform_bot ADD per_bishu_usdt_price decimal(4,2) NOT NULL DEFAULT 0.5  AFTER is_open_bishu;

ALTER TABLE t_energy_platform_bot ADD per_bishu_energy_quantity int NOT NULL DEFAULT 65000  AFTER per_bishu_usdt_price;

ALTER TABLE t_energy_platform_bot ADD per_energy_day_bishu INT NULL DEFAULT 1  AFTER per_bishu_energy_quantity;

drop table if exists t_energy_ai_bishu;

/*==============================================================*/
/* Table: t_energy_ai_bishu                                     */
/*==============================================================*/
create table t_energy_ai_bishu
(
   rid                  int not null auto_increment  ,
   bot_rid              int not null  ,
   tg_uid               VARCHAR(100)  ,
   wallet_addr          VARCHAR(100) not null  ,
   status               tinyint not null default 0  ,
   current_bandwidth_quantity bigint not null default 0  ,
   current_energy_quantity bigint not null default 0  ,
   is_buy               CHAR(1) not null default 'N'  ,
   is_notice            CHAR(1) not null default 'N'  ,
   is_notice_admin      CHAR(1) not null default 'N'  ,
   total_buy_usdt       decimal(14,2) not null default 0  ,
   max_buy_quantity     int not null default 0  ,
   total_buy_quantity   int not null default 0  ,
   total_buy_energy_quantity bigint not null default 0  ,
   last_buy_time        TIMESTAMP  ,
   comments             VARCHAR(3000)  ,
   back_comments        VARCHAR(200)  ,
   create_time          TIMESTAMP not null  ,
   update_time          TIMESTAMP  ,
   primary key (rid)
) ;

/*==============================================================*/
/* Index: idx_energy_ai_bishu_2                                 */
/*==============================================================*/
create index idx_energy_ai_bishu_2 on t_energy_ai_bishu
(
   bot_rid
);

ALTER TABLE t_energy_platform_bot ADD bishu_recovery_type TINYINT NOT NULL DEFAULT '1'  AFTER per_energy_day_bishu;

ALTER TABLE t_energy_platform_bot ADD bishu_daili_type TINYINT NOT NULL DEFAULT '1'  AFTER bishu_recovery_type;

ALTER TABLE t_energy_ai_bishu ADD energy_platform_rid int NULL  AFTER back_comments;

ALTER TABLE t_monitor_wallet ADD monitor_usdt_transaction VARCHAR(5) NOT NULL DEFAULT 'YY'  AFTER balance_amount;
ALTER TABLE t_monitor_wallet ADD monitor_trx_transaction VARCHAR(5) NOT NULL DEFAULT 'YY'  AFTER monitor_usdt_transaction;
ALTER TABLE t_monitor_wallet ADD monitor_approve_transaction VARCHAR(5) NOT NULL DEFAULT 'YY'  AFTER monitor_trx_transaction;
ALTER TABLE t_monitor_wallet ADD monitor_multi_transaction VARCHAR(5) NOT NULL DEFAULT 'YY'  AFTER monitor_approve_transaction;
ALTER TABLE t_monitor_wallet ADD monitor_pledge_transaction VARCHAR(5) NOT NULL DEFAULT 'YY'  AFTER monitor_multi_transaction;

ALTER TABLE t_energy_platform_bot ADD ai_trusteeship_recovery_type TINYINT NOT NULL DEFAULT '1'  AFTER per_energy_day;

/*==============================================================*/
/* Table: t_energy_third_part                                   */
/*==============================================================*/
create table t_energy_third_part
(
   rid                  int not null auto_increment  ,
   order_type           tinyint not null  ,
   tg_uid               bigint not null  ,
   platform_rid         int not null  ,
   bot_rid              int not null  ,
   cishu_energy         int not null  ,
   wallet_addr          VARCHAR(50) not null  ,
   before_trx           decimal(16,6)	 not null  ,
   change_trx           decimal(16,6)	 not null  ,
   after_trx            decimal(16,6)	 not null  ,
   order_time           TIMESTAMP not null  ,
   process_status       TINYINT NOT NULL DEFAULT '0' ,
   process_time         TIMESTAMP NULL ,
   process_comments     VARCHAR(2000) NULL ,
   primary key (rid)
);

drop table if exists t_collection_wallet;

/*==============================================================*/
/* Table: t_collection_wallet                                   */
/*==============================================================*/
create table t_collection_wallet
(
   rid                  int not null auto_increment  ,
   bot_rid              int not null  ,
   chain_type           VARCHAR(10) not null  ,
   wallet_addr          	VARCHAR(200) not null  ,
   wallet_addr_privatekey 	VARCHAR(2000)  ,
   permission_id        tinyint not null default 0  ,
   status               tinyint not null default 1  ,
   tg_notice_obj        	VARCHAR(200)  ,
   trx_balance          decimal(18,6) default 0  ,
   usdt_balance         decimal(18,6) default 0  ,
   trx_collection_amount decimal(18,6) default 0  ,
   usdt_collection_amount decimal(18,6) default 0  ,
   trx_reserve_amount   decimal(18,6) default 0  ,
   usdt_reserve_amount  decimal(18,6) default 0  ,
   collection_wallet_addr 	VARCHAR(200)  ,
   last_collection_time TIMESTAMP  ,
   create_time          TIMESTAMP not null  ,
   update_time          TIMESTAMP  ,
   comments             	VARCHAR(200)  ,
   collection_type      tinyint not null default 0  ,
   primary key (rid)
) ;

drop table if exists t_collection_wallet_list;

/*==============================================================*/
/* Table: t_collection_wallet_list                              */
/*==============================================================*/
create table t_collection_wallet_list
(
   rid                  int not null auto_increment  ,
   wallet_addr          	VARCHAR(200) not null  ,
   collection_wallet_addr 	VARCHAR(200) not null  ,
   coin_name            	VARCHAR(50) not null  ,
   collection_amount    decimal(18,6) not null  ,
   collection_time      TIMESTAMP not null  ,
   tx_hash              	VARCHAR(200)  ,
   is_notice            CHAR(1) not null default 'N'  ,
   primary key (rid)
) ;

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
   rid                  int not null auto_increment  ,
   admin_name           VARCHAR(100) not null  ,
   login_ip             VARCHAR(1000)  ,
   login_time           TIMESTAMP  ,
   primary key (rid)
) ;

drop table if exists t_energy_special;

/*==============================================================*/
/* Table: t_energy_special                                      */
/*==============================================================*/
create table t_energy_special
(
   rid                  int not null auto_increment  ,
   bot_rid              int  ,
   tg_uid               bigint  ,
   wallet_addr          VARCHAR(50) not null  ,
   wallet_energy        bigint not null default 0  ,
   max_energy           bigint not null default 0  ,
   send_energy          bigint not null default 0  ,
   per_energy           int not null default 0  ,
   less_than_energy     int not null default 0  ,
   status               tinyint not null default 0  ,
   total_usdt_recharge  decimal(14,2) not null default 0  ,
   total_trx_recharge   decimal(14,2) not null default 0  ,
   seq_sn               int not null default 0  ,
   comments             VARCHAR(200)  ,
   primary key (rid)
);

alter table t_energy_special ;

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
   rid                  int not null auto_increment  ,
   bot_rid              int  ,
   tg_uid               bigint  ,
   wallet_addr          VARCHAR(50) not null  ,
   send_wallet_addr     VARCHAR(50) not null  ,
   before_energy        bigint  ,
   daili_energy         int  ,
   daili_hash           VARCHAR(100)  ,
   daili_trx            decimal(18,6)  ,
   status               tinyint not null default 1  ,
   daili_time           TIMESTAMP  ,
   huishou_time         TIMESTAMP  ,
   huishou_hash         VARCHAR(100)  ,
   primary key (rid)
);

alter table t_energy_special_list ;

/*==============================================================*/
/* Index: idx_energy_special_list_1                             */
/*==============================================================*/
create index idx_energy_special_list_1 on t_energy_special_list
(
   wallet_addr
);

ALTER TABLE t_energy_platform_bot ADD agent_tg_uid BIGINT NULL  AFTER update_time;
ALTER TABLE t_energy_platform_bot ADD agent_per_price DECIMAL(18,6) NULL  AFTER agent_tg_uid;
ALTER TABLE t_energy_platform_package ADD agent_trx_price DECIMAL(14,2) NOT NULL DEFAULT '0'  AFTER usdt_price;

ALTER TABLE t_energy_ai_trusteeship ADD agent_tg_uid BIGINT NULL  AFTER update_time;
ALTER TABLE t_energy_platform_bot ADD bishu_stop_day INT NOT NULL DEFAULT '0'  AFTER bishu_daili_type;
ALTER TABLE t_energy_ai_bishu ADD bishu_stop_day INT NOT NULL DEFAULT '0' ;

ALTER TABLE t_premium_platform_order ADD tg_notice_user CHAR(1) NOT NULL DEFAULT 'N'  AFTER tx_hash;
ALTER TABLE t_premium_platform_order ADD tg_notice_admin CHAR(1) NOT NULL DEFAULT 'N'  AFTER tg_notice_user;

