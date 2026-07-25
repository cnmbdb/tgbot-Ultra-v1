# TGBot Ultra v1 - Railway 部署

本仓库只保留 Railway 生产部署需要的环境变量模板和 PostgreSQL 初始化 SQL。`admin` 与 `job` 已分别由 Railway 直接拉取预构建镜像，因此不再使用 Docker Compose，也不再运行 Cloudflare Tunnel。

## 服务

| Railway 服务 | 镜像 / 类型 | 用途 |
|---|---|---|
| `admin` | `ghcr.io/cnmbdb/tgbot-admin:latest` | Laravel 后台、API、Telegram Webhook |
| `job` | `ghcr.io/cnmbdb/tgbot-job:latest` | Hyperf 定时任务、队列和链上监控 |
| `Postgres` | `ghcr.io/railwayapp-templates/postgres-ssl:18` | 主数据库 |
| `Redis` | `redis:8.2.1` | 缓存、队列和运行状态 |

Cloudflare Tunnel（`cftun` / `cloudflared`）不再需要。请直接给 Railway 的 `admin` 服务生成域名或绑定自定义域名。

## 仓库文件

```text
.
├── .env.admin.example    # admin 服务独立变量模板
├── .env.job.example      # job 服务独立变量模板
├── .env.postgres.example # PostgreSQL 服务独立变量模板
├── .env.redis.example    # Redis 服务独立变量模板
├── DB_PostgreSQL.sql     # 新数据库初始化结构
├── .gitignore            # 防止真实环境变量进入 Git
└── README.md
```

`.env.*.example` 只是变量清单。Railway 从镜像部署时不会读取本仓库中的 `.env` 文件，请将四份模板分别填写到对应服务的 **Variables** 页面。

## Railway 配置

### 1. Admin 服务

1. 使用镜像 `ghcr.io/cnmbdb/tgbot-admin:latest`。
2. 按 `.env.admin.example` 配置 Variables。
3. 给服务生成 Railway Domain 或绑定自定义域名。
4. 将公开域名填写到 `APP_URL`，并设置 `IS_HTTPS=true`。
5. Web 容器当前监听端口为 `80`；如果 Railway 没有自动识别，请将域名的 Target Port 设置为 `80`。
6. 如果上传文件需要跨部署保留，请创建 Volume 并挂载到 `/var/www/html/storage`。

### 2. Job 服务

1. 使用镜像 `ghcr.io/cnmbdb/tgbot-job:latest`。
2. 按 `.env.job.example` 配置 Variables。
3. `job` 通常只需要私网连接，不必生成公网域名。
4. 如果确实需要公网访问，容器当前服务端口为 `9501`，将 Target Port 设置为 `9501`。

### 3. PostgreSQL 与 Redis

在同一个 Railway Project 中添加 PostgreSQL 和 Redis：

1. PostgreSQL 服务按 `.env.postgres.example` 配置，`POSTGRES_PASSWORD` 使用强随机值。
2. Redis 服务按 `.env.redis.example` 配置，`REDISPASSWORD` 使用强随机值。
3. PostgreSQL 需要持久化时，将 Railway Volume 挂载到 `/var/lib/postgresql/data`。
4. Redis 需要持久化时，将 Railway Volume 挂载到 `/data`。
5. Redis 保留 Railway 当前的 Custom Start Command：

   ```sh
   /bin/sh -c "rm -rf $RAILWAY_VOLUME_MOUNT_PATH/lost+found/ && exec docker-entrypoint.sh redis-server --requirepass $REDIS_PASSWORD --save 60 1 --dir $RAILWAY_VOLUME_MOUNT_PATH"
   ```

   原生 Redis 镜像不会仅凭 `REDIS_PASSWORD` 环境变量自动启用密码。

6. 在 `admin`、`job` 两个服务里分别使用 Railway Reference Variables。

假设服务名是 `Postgres` 和 `Redis`，应用变量映射如下：

| 应用变量 | Railway 引用 |
|---|---|
| `DB_HOST` | `${{Postgres.PGHOST}}` |
| `DB_PORT` | `${{Postgres.PGPORT}}` |
| `DB_DATABASE` | `${{Postgres.PGDATABASE}}` |
| `DB_USERNAME` | `${{Postgres.PGUSER}}` |
| `DB_PASSWORD` | `${{Postgres.PGPASSWORD}}` |
| `REDIS_HOST` | `${{Redis.REDISHOST}}` |
| `REDIS_PORT` | `${{Redis.REDISPORT}}` |
| Admin: `REDIS_PASSWORD` | `${{Redis.REDISPASSWORD}}` |
| Job: `REDIS_AUTH` | `${{Redis.REDISPASSWORD}}` |

Job 镜像的 Hyperf Redis 配置读取 `REDIS_AUTH` ，不读取 `REDIS_PASSWORD`；Job 同时显式设置 `REDIS_DB=1`。

如果 Railway 中的数据库服务名称不同，请相应修改引用前缀。不要在两个应用服务之间手工复制数据库密码，使用引用变量可让凭据自动同步。

## 初始化数据库

Railway PostgreSQL 不会自动执行本仓库的 `DB_PostgreSQL.sql`。首次创建空数据库后，需要使用 Railway 提供的 PostgreSQL 连接信息执行一次：

```bash
psql "<Railway DATABASE_PUBLIC_URL>" -f DB_PostgreSQL.sql
```

执行前确认目标是新建的空数据库。生产库已有数据时，不要重复导入初始化 SQL。

## 环境隔离

每个 Railway Environment（例如 `production`、`staging`）都有独立的 Variables。请在每个环境中分别配置：

- `admin` 使用自己的 Admin 变量集合；
- `job` 使用自己的 Job 变量集合；
- PostgreSQL 使用自己的数据库变量集合；
- Redis 使用自己的缓存变量集合；
- 数据库与 Redis 使用该环境对应的 Reference Variables；
- `APP_URL`、API 地址、机器人配置和第三方密钥按环境隔离。

不要把真实 `.env` 提交到仓库。`.gitignore` 已忽略 `.env`、`.env.admin`、`.env.job`、`.env.postgres`、`.env.redis` 等本地文件，只允许提交四个 `.example` 模板。

## 重要说明

Railway 只会把 Variables 注入为进程环境变量，并不会自动生成容器内的物理 `.env` 文件。如果镜像里的应用仍直接调用 `file_get_contents(.../.env)`，需要在应用镜像中移除这种依赖并重新构建；仅修改本部署仓库无法改变已经发布的镜像。

### Cloudflare Turnstile

Turnstile 当前不启用。`tgbot-admin` 只设置 `TURNSTILE_ENABLE=false`，不配置 Site Key 或 Secret；`job`、PostgreSQL 和 Redis 不需要任何 Turnstile 变量。

## 上线检查

- [ ] `admin` 和 `job` 使用各自独立的 Variables
- [ ] PostgreSQL 和 Redis 使用各自独立的 Variables
- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `APP_KEY` 已配置
- [ ] PostgreSQL / Redis 引用指向当前 Railway Environment
- [ ] `admin` 域名和 `APP_URL` 一致
- [ ] 数据库结构已初始化
- [ ] 没有 cftun / cloudflared 服务或变量
- [ ] 日志中没有缺失变量、数据库连接或 Redis 连接错误
