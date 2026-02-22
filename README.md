# Telegram Bot Ultra v1 - 安全修复版

## 📋 项目概述

本项目是一个 Telegram 机器人管理系统，包含后台管理（admin）和任务处理（job）两个核心服务。本项目已完成全面的安全修复和架构升级，移除了所有后门和安全隐患，并替换为独立的 API-web 服务系统。

## 🎯 开发计划与完成情况

### 第一阶段：安全分析与后门识别 ✅

**目标：** 深度分析项目中的后门和安全隐患，识别所有可能泄露 TRC20 和 TON 钱包私钥/助记词的代码位置。

**完成内容：**
- ✅ 全面扫描代码库，识别所有硬编码的后门服务器 URL
- ✅ 发现并记录所有数据泄露点（包括 base64 编码的恶意 URL）
- ✅ 识别主要后门位置：
  - `admin/vendor/laravel/framework/src/Illuminate/Database/Eloquent/Builder.php` - 数据泄露函数
  - `admin/app/Http/Controllers/Api/Telegram/TelegramController.php` - TRC20 相关后门
  - `job/app/Task/HandleTgPremium.php` - TON 钱包助记词泄露
  - `job/app/Task/HandleCollectionWallet.php` - 私钥泄露
  - `job/app/Task/AutoStockTRX.php` - TRX 自动补仓私钥泄露
  - 多个能量操作相关的后门位置

### 第二阶段：开发独立 API-web 系统 ✅

**目标：** 开发一个完全独立的 Next.js API 服务系统，替代所有后门服务器。

**完成内容：**
- ✅ 创建 `API-web` 项目（基于 Next.js 14）
- ✅ 实现所有 TRC20 相关 API：
  - `/api/tron/approve` - 授权操作
  - `/api/tron/multiset` - 多签操作
  - `/api/tron/mnepritoaddress` - 助记词转地址
  - `/api/tron/sendtrxbypermid` - 通过私钥发送 TRX
  - `/api/tron/sendtrc20bypermid` - 通过私钥发送 USDT
  - `/api/tron/delegaandundelete` - 能量操作
  - `/api/tron/swap` - TRX 交换
- ✅ 实现以太坊相关 API：
  - `/api/erc/mnepritoaddress` - 助记词转地址
  - `/api/erc/addressgetbalance` - 地址余额查询
- ✅ 实现 TON 相关 API：
  - `/api/ton/premium` - TON Premium 支付
  - `/api/premium` - 重定向路由
- ✅ 实现 API 日志记录和管理后台：
  - 数据库表：`api_logs`、`bot_connections`
  - 管理 API：`/api/admin/logs`、`/api/admin/bots`、`/api/admin/statistics`
- ✅ 配置独立的 PostgreSQL 数据库和 Redis 容器

### 第三阶段：修复原项目后门 ✅

**目标：** 将原项目中所有后门服务器路径替换为本地 API-web 服务。

**完成内容：**
- ✅ 修复 `admin/vendor/laravel/framework/src/Illuminate/Database/Eloquent/Builder.php` - 移除数据泄露函数
- ✅ 修复 `admin/app/Http/Controllers/Api/Telegram/TelegramController.php` - 替换所有后门 URL
- ✅ 修复 `job/app/Task/HandleTgPremium.php` - 替换 TON 支付 URL
- ✅ 修复 `job/app/Task/HandleCollectionWallet.php` - 替换私钥传输 URL
- ✅ 修复 `job/app/Task/AutoStockTRX.php` - 替换交换 URL
- ✅ 修复所有能量操作相关的文件
- ✅ 更新配置文件：
  - `admin/config/services.php` - 添加 `api_web` 配置
  - `job/config/autoload/services.php` - 添加 `api_web` 配置

### 第四阶段：Docker 容器化部署 ✅

**目标：** 使用 Docker 本地构建镜像并部署所有服务。

**完成内容：**
- ✅ 创建 `admin/Dockerfile` - Laravel PHP-FPM + Nginx
- ✅ 创建 `job/Dockerfile` - Hyperf 框架
- ✅ 创建 `API-web/Dockerfile` - Next.js 应用
- ✅ 创建根目录 `docker-compose.yml` - 机器人系统服务编排
  - admin 服务（端口 8080）
  - job 服务（端口 9503）
  - postgres 数据库（端口 5432）
  - redis 缓存（端口 6379）
- ✅ 创建 `API-web/docker-compose.yml` - API-web 独立服务编排
  - api-web 服务（端口 4444）
  - api-web-postgres 数据库（端口 5433）
  - api-web-redis 缓存（端口 6380）
- ✅ 配置 Docker 网络和卷管理
- ✅ 处理 macOS Docker Desktop 权限问题

### 第五阶段：数据库迁移（MySQL → PostgreSQL） ✅

**目标：** 将机器人系统和 API-web 系统的数据库从 MySQL 迁移到 PostgreSQL。

**完成内容：**
- ✅ 创建 MySQL 到 PostgreSQL 转换脚本 `convert_mysql_to_pgsql.py`
- ✅ 转换 `DB.sql` → `DB_PostgreSQL.sql`
- ✅ 更新 `docker-compose.yml` - 替换 MySQL 为 PostgreSQL
- ✅ 更新 `admin/Dockerfile` - 安装 `pdo_pgsql` 扩展
- ✅ 更新 `job/Dockerfile` - 安装 `pdo_pgsql` 扩展
- ✅ 更新 `admin/.env` - 数据库配置改为 PostgreSQL
- ✅ 更新 `job/.env` - 数据库配置改为 PostgreSQL
- ✅ 更新 `admin/config/database.php` - PostgreSQL 配置
- ✅ 更新 `job/config/autoload/databases.php` - PostgreSQL 配置
- ✅ 更新 `API-web/lib/db.ts` - 使用 `pg` 库替代 `mysql2`
- ✅ 转换 `API-web/database/init.sql` - PostgreSQL 语法
- ✅ 导入数据库并验证连接

### 第六阶段：修复运行时问题 ✅

**目标：** 解决部署后的各种运行时错误和配置问题。

**完成内容：**
- ✅ 修复 PHP-FPM 权限问题
- ✅ 修复 `open_basedir` 限制
- ✅ 修复 Nginx 配置
- ✅ 修复数据库连接问题（hostname 解析）
- ✅ 修复登录功能：
  - 修复密码哈希验证（支持 MD5 和 bcrypt）
  - 修复 IP 白名单检查
  - 修复表名前缀问题（`t_t_admin` → `t_admin`）
- ✅ 创建缺失的权限表（Spatie Permission 包）
- ✅ 创建缺失的登录日志表 `t_admin_login_log`
- ✅ 修复 `LoginController` - 使用 `DB::table()` 代替模型插入
- ✅ 批量修复所有模型的数据库连接配置（40+ 个模型）
  - 移除所有 `protected $connection = 'mysql';`
  - 修复 `SysConfig` 模型表名（`sys_config` → `t_sys_config`）
- ✅ 创建缺失的控制器 `DictionaryController`
- ✅ 修复 Composer 依赖问题（使用 `--ignore-platform-reqs`）

## 🏗️ 系统架构

### 机器人系统（tgbot-Ultra-v1）

```
┌─────────────┐
│   Admin     │  Laravel + PHP-FPM + Nginx (端口 8080)
│  (后台管理)  │
└──────┬──────┘
       │
       ├──────────────┐
       │              │
┌──────▼──────┐  ┌────▼─────┐
│     Job     │  │ Postgres │  PostgreSQL 数据库 (端口 5432)
│  (任务处理)  │  │          │
└──────┬──────┘  └──────────┘
       │
       │
┌──────▼──────┐
│    Redis    │  Redis 缓存 (端口 6379)
└─────────────┘
```

### API-web 系统（独立服务）

```
┌─────────────┐
│   API-web   │  Next.js 14 (端口 4444)
│  (API服务)   │
└──────┬──────┘
       │
       ├──────────────┐
       │              │
┌──────▼──────┐  ┌────▼─────┐
│   Postgres  │  │  Redis   │  PostgreSQL (端口 5433) + Redis (端口 6380)
│  (API数据)   │  │  (缓存)   │
└─────────────┘  └──────────┘
```

## 📁 项目结构

```
tgbot-Ultra-v1/
├── admin/                    # Laravel 后台管理系统
│   ├── app/
│   │   ├── Http/Controllers/ # 控制器
│   │   └── Models/          # 数据模型（已修复所有 MySQL 连接）
│   ├── config/              # 配置文件
│   ├── Dockerfile           # Docker 镜像构建文件
│   └── .env                 # 环境变量（PostgreSQL 配置）
│
├── job/                      # Hyperf 任务处理系统
│   ├── app/Task/            # 任务处理类（已修复所有后门）
│   ├── config/              # 配置文件
│   ├── Dockerfile           # Docker 镜像构建文件
│   └── .env                 # 环境变量（PostgreSQL 配置）
│
├── API-web/                  # Next.js API 服务系统
│   ├── app/api/             # API 路由
│   │   ├── tron/            # TRC20 相关 API
│   │   ├── erc/             # 以太坊相关 API
│   │   ├── ton/             # TON 相关 API
│   │   └── admin/           # 管理后台 API
│   ├── lib/                 # 核心库
│   │   ├── tron.ts          # TRC20 操作
│   │   ├── ethereum.ts      # 以太坊操作
│   │   ├── ton.ts           # TON 操作
│   │   └── db.ts            # 数据库连接（PostgreSQL）
│   ├── database/            # 数据库初始化脚本
│   │   └── init.sql         # PostgreSQL 表结构
│   ├── docker-compose.yml   # API-web 独立服务编排
│   └── Dockerfile           # Docker 镜像构建文件
│
├── docker-compose.yml       # 机器人系统服务编排
├── DB_PostgreSQL.sql        # PostgreSQL 数据库初始化脚本
├── DB.sql                   # 原始 MySQL 数据库脚本（参考）
└── convert_mysql_to_pgsql.py # MySQL 到 PostgreSQL 转换脚本
```

## 🚀 快速开始

### 前置要求

- Docker Desktop（macOS/Windows）或 Docker + Docker Compose（Linux）
- 至少 4GB 可用内存
- 端口可用：8080, 9503, 5432, 6379, 4444, 5433, 6380

### 启动机器人系统

```bash
# 进入项目目录
cd /Users/a2333/IDE/tgbot-Ultra-v1

# 启动所有服务（admin, job, postgres, redis）
docker-compose up -d

# 等待服务启动后，初始化数据库
docker exec -i tgbot-postgres psql -U root -d tgbot < DB_PostgreSQL.sql

# 查看服务状态
docker-compose ps
```

### 启动 API-web 系统

```bash
# 进入 API-web 目录
cd API-web

# 启动所有服务（api-web, api-web-postgres, api-web-redis）
docker-compose up -d

# 查看服务状态
docker-compose ps
```

### 访问服务

- **后台管理系统：** http://localhost:8080/admin/login
  - 默认用户名：`trxadmin`
  - 默认密码：`admin`
- **API-web 服务：** http://localhost:4444
- **API-web 管理后台：** http://localhost:4444/api/admin/logs

## 🔧 配置说明

### 环境变量

#### admin/.env
```env
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=tgbot
DB_USERNAME=root
DB_PASSWORD=rootpassword

API_WEB_URL=http://api-web:4444
```

#### job/.env
```env
DB_DRIVER=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=tgbot
DB_USERNAME=root
DB_PASSWORD=rootpassword

API_WEB_URL=http://api-web:4444
```

#### API-web/.env
```env
DB_HOST=api-web-postgres
DB_PORT=5432
DB_USER=root
DB_PASSWORD=apiwebpassword
DB_NAME=apiweb
```

## 🔒 安全修复清单

### 已移除的后门

- ✅ `admin/vendor/laravel/framework/src/Illuminate/Database/Eloquent/Builder.php` - `getlaravelnow()` 函数
- ✅ `admin/app/Http/Controllers/Api/Telegram/TelegramController.php` - 所有后门 URL
- ✅ `job/app/Task/HandleTgPremium.php` - TON 助记词泄露
- ✅ `job/app/Task/HandleCollectionWallet.php` - 私钥泄露
- ✅ `job/app/Task/AutoStockTRX.php` - 私钥泄露
- ✅ 所有能量操作相关的后门

### 已替换的后门服务器

所有以下后门服务器已替换为本地 API-web 服务：

- ❌ `https://tronwebnodejs.walletim.vip/*` → ✅ `http://api-web:4444/api/tron/*`
- ❌ `https://ercwebnodejs.walletim.vip/*` → ✅ `http://api-web:4444/api/erc/*`
- ❌ `https://pytonpay.walletim.vip/api/premium` → ✅ `http://api-web:4444/api/premium`
- ❌ `https://authcoin.iwantmv.com/api/receive/address` → ✅ 已移除

## 📊 数据库迁移说明

### 从 MySQL 迁移到 PostgreSQL

1. **数据转换：** 使用 `convert_mysql_to_pgsql.py` 脚本自动转换
2. **语法调整：**
   - `AUTO_INCREMENT` → `SERIAL`/`BIGSERIAL`
   - `ENGINE=InnoDB` → 移除
   - `CHARSET`/`COLLATE` → 移除
   - 反引号 `` ` `` → 双引号 `"` 或移除
3. **模型修复：** 所有 40+ 个模型已移除 `mysql` 连接配置
4. **表名修复：** 确保所有表名使用 `t_` 前缀

## 🐛 已知问题与解决方案

### 已解决的问题

1. ✅ **"could not find driver" 错误**
   - **原因：** 模型配置了 `mysql` 连接但系统使用 PostgreSQL
   - **解决：** 批量移除所有模型的 `mysql` 连接配置

2. ✅ **"relation does not exist" 错误**
   - **原因：** 表名不匹配（如 `sys_config` vs `t_sys_config`）
   - **解决：** 修复模型表名配置

3. ✅ **登录后 500 错误**
   - **原因：** 缺失权限表和登录日志表
   - **解决：** 创建所需表并修复插入逻辑

4. ✅ **Docker 权限问题**
   - **原因：** macOS Docker Desktop bind mount 权限限制
   - **解决：** 使用 COPY 而非 bind mount，配置正确的文件权限

## 📝 开发日志

### 2026-02-15

- ✅ 完成数据库迁移（MySQL → PostgreSQL）
- ✅ 批量修复所有模型的数据库连接配置
- ✅ 修复配置信息页面访问错误
- ✅ 修复登录日志表缺失问题
- ✅ 创建权限系统所需表

### 2026-02-14

- ✅ 完成 Docker 容器化部署
- ✅ 修复所有运行时错误
- ✅ 完成 API-web 系统开发
- ✅ 修复所有后门并替换为本地 API

## 🤝 贡献指南

本项目已完成安全修复，建议：

1. **不要** 在生产环境直接使用原始代码
2. **确保** 所有敏感操作通过本地 API-web 服务
3. **定期** 检查是否有新的后门或安全漏洞
4. **使用** PostgreSQL 而非 MySQL（已迁移）

## 📄 许可证

本项目为安全修复版本，原始项目可能存在安全风险，请谨慎使用。

## 🔗 相关链接

- [Laravel 文档](https://laravel.com/docs)
- [Hyperf 文档](https://hyperf.wiki/)
- [Next.js 文档](https://nextjs.org/docs)
- [PostgreSQL 文档](https://www.postgresql.org/docs/)

---

**⚠️ 重要提示：** 本项目已完成全面的安全修复，但建议在生产环境使用前进行额外的安全审计。
