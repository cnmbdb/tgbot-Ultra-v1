# TGBot-Ultra-v1 本地开发环境

> 🛠️ **本地开发专用** - 使用本地构建镜像，代码挂载实时生效  
> 📦 **生产部署**: 请使用 `/Users/a2333/IDE/部署到服务器的文件/tgbot-Ultra-v1`

## 🚀 快速开始

### 1. 环境准备

- **Docker**: 已安装 Docker Desktop 或 Docker Engine
- **Docker Compose**: 已安装 Docker Compose 2.0+
- **API-Web 服务**: 本地运行在 `http://localhost:4444`（或修改 `API_WEB_URL`）

### 2. 配置环境变量

```bash
# 检查并配置 admin/.env
cat admin/.env

# 检查并配置 job/.env  
cat job/.env
```

**必需配置：**
- `DB_PASSWORD`: 数据库密码（默认: `rootpassword`）
- `API_WEB_URL`: API-Web 服务地址（默认: `http://host.docker.internal:4444`）

### 3. 启动服务

```bash
# 构建并启动所有服务
docker-compose up -d --build

# 查看日志
docker-compose logs -f
```

### 4. 访问服务

- **后台管理**: http://localhost:8080/admin/login
  - 默认账号: `trxadmin` / `admin`
- **Job 服务**: http://localhost:9503

## 🔧 开发说明

### 代码实时生效

本地开发环境已配置代码挂载，修改代码后**无需重新构建镜像**：

- **Admin 代码**: `./admin/` → `/var/www/html/`
- **Job 代码**: `./job/` → `/opt/www/`

修改 PHP 代码后，服务会自动重新加载（Laravel 和 Hyperf 都支持热重载）。

### 数据库初始化

首次启动会自动从 `DB_PostgreSQL.sql` 初始化数据库。

如需重置数据库：

```bash
# 停止服务并删除数据卷
docker-compose down -v

# 重新启动（会重新初始化数据库）
docker-compose up -d --build
```

### 本地开发模式：消息轮询

本地开发环境使用 **长轮询（Polling）** 方式获取 Telegram 消息：

- 任务：`PollTelegramMessages`（每 5 秒运行一次）
- 自动删除 Webhook，使用 `getUpdates` API
- 消息通过内部 HTTP 调用传递给 Admin 服务

**注意**：生产环境应使用 Webhook 方式（配置 `WEBHOOK_BASE_URL`）。

## 📋 常用命令

```bash
# 启动服务
docker-compose up -d

# 停止服务
docker-compose down

# 重启服务
docker-compose restart

# 查看日志
docker-compose logs -f admin
docker-compose logs -f job

# 重新构建镜像（代码修改后）
docker-compose build

# 进入容器
docker exec -it tgbot-admin bash
docker exec -it tgbot-job sh

# 查看数据库
docker exec -it tgbot-postgres psql -U root -d tgbot
```

## 🔍 调试技巧

### 查看 Laravel 日志

```bash
docker exec tgbot-admin tail -f /var/www/html/storage/logs/laravel/laravel-$(date +%Y-%m-%d).log
```

### 查看 Hyperf 日志

```bash
docker exec tgbot-job tail -f /opt/www/runtime/logs/hyperf.log
```

### 测试消息处理

```bash
# 查看 PollTelegramMessages 任务日志
docker-compose logs job | grep -i polltelegram

# 查看消息处理日志
docker-compose logs admin | grep -i "telegram\|关键字匹配"
```

## ⚠️ 注意事项

1. **本地开发**：使用 `build` 构建本地镜像，代码挂载实时生效
2. **生产部署**：请使用 `/Users/a2333/IDE/部署到服务器的文件/tgbot-Ultra-v1`，使用远程镜像
3. **API-Web 服务**：确保本地 API-Web 服务运行在 `http://localhost:4444`
4. **端口占用**：确保 8080、9503、5432、6379 端口未被占用

## 📝 文件结构

```
tgbot-Ultra-v1/
├── admin/              # Laravel 后台管理
│   ├── .env           # 环境变量配置
│   └── ...
├── job/                # Hyperf 任务服务
│   ├── .env           # 环境变量配置
│   └── ...
├── DB_PostgreSQL.sql   # 数据库初始化脚本
├── docker-compose.yml  # Docker Compose 配置（本地开发）
└── README.md          # 本文档
```

## 🔗 相关文档

- **生产部署**: `/Users/a2333/IDE/部署到服务器的文件/tgbot-Ultra-v1/README.md`
- **服务器部署指南**: `/Users/a2333/IDE/部署到服务器的文件/tgbot-Ultra-v1/服务器部署指南.md`
