## API-web 使用说明（给人看的版本）

> 这份是放在 `API-web` 项目根目录的文档，内容和 `.cursor/rules/api-web.mdc` 一致，只是给人直接查看用。

### 1. 项目角色

- `API-web` 是一个独立的 **Next.js 14 + Node API 服务**，专门给 `tgbot-Ultra-v1` 机器人提供安全的区块链相关接口（**TRON / ERC / TON / Premium**）。
- 所有敏感操作（出款、能量委托、Premium 开通等）尽量都放到这里做，而不是直接写在 PHP 机器人里。

### 2. 重要访问路径

- **首页** `/`  
  - 只显示“服务正常运行中”，不暴露具体 API 路径和后台地址。
- **管理后台入口** `/secure-admin-8f3k9q`  
  - 登录页：`/secure-admin-8f3k9q/login`  
  - 左侧菜单：仪表台、服务器列表、机器人列表、API 用户列表、TRX/TON 信息等。
- **管理接口** `/api/admin/*`  
  - 统计：`/api/admin/statistics`  
  - 日志：`/api/admin/logs`  
  - 机器人连接：`/api/admin/bots`  
  - 新增的：`/api/admin/servers`、`/api/admin/api-users`。

### 3. 中间件与安全

- 中间件文件：`middleware.ts`
- 作用：
  - 保护 `/secure-admin-8f3k9q/*` 和 `/api/admin/*`（除了 `/api/admin/login`），必须带有效的后台 Cookie 才能访问。
  - 给普通 `/api/*` 请求自动加 `x-request-id`，方便日志追踪。

### 4. 环境变量与后台密码

- 示例配置在：`env.example`，实际运行时用 `.env` 或部署环境注入。
- 关键项：
  - `ADMIN_PASSWORD`：后台登录密码（请务必改成强密码，不要用默认值）。
  - `TRON_NETWORK`、`ETH_RPC_URL`：链上 RPC 地址。
  - `TON_ENDPOINT`、`TON_PREMIUM_RECEIVER_ADDRESS` 等：TON Premium 相关配置。

### 5. Docker 与本地开发

- 编排文件：`docker-compose.yml`
  - `api-web` 服务使用 `node:20-alpine`，挂载当前目录到 `/app`，命令是：`npm install && npm run dev -p 4444`，适合本地开发热更新。
  - PostgreSQL：`api-web-postgres`，初始 SQL 在 `database/init.sql`。
  - Redis：`api-web-redis`。
- 本地启动：

```bash
cd API-web
ADMIN_PASSWORD=你的密码 docker-compose up -d
```

### 6. 与 tgbot-Ultra-v1 的关系（简要）

- `tgbot-Ultra-v1` 中通过环境变量 `API_WEB_URL` 指向本服务（本地默认 `http://host.docker.internal:4444`）。
- Premium 开通、TRX/TRC20 出款、ETH 查询等，都会通过 `API_WEB_URL` 访问本项目的 `/api/*` 路由。

