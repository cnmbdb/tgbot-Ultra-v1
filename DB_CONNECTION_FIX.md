# 数据库连接问题修复文档

## 问题描述

机器人系统在使用过程中会出现数据库连接丢失的问题，导致：
- 后台仪表台一直显示加载中
- 授权激活状态丢失
- 系统无法正常使用

## 错误日志分析

从日志中发现以下错误类型：
1. `php_network_getaddresses: getaddrinfo failed: No address associated with hostname` - DNS解析失败
2. `No route to host` - 网络路由失败
3. `Connection timed out` - 连接超时

## 根本原因

1. **缺少连接保活机制**：PostgreSQL 连接在长时间空闲后会被服务器关闭
2. **缺少自动重连机制**：连接断开后没有自动重连
3. **缺少连接健康检查**：无法及时发现连接问题
4. **连接池配置不当**：job 系统的连接池配置需要优化

## 修复方案

### 1. Laravel Admin 系统修复

#### 1.1 数据库配置增强 (`admin/config/database.php`)

添加了以下配置：
- **连接超时设置**：`PDO::ATTR_TIMEOUT => 10`
- **错误模式**：`PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION`
- **连接重试配置**：
  - `retry_after`: 重试间隔（秒）
  - `max_retries`: 最大重试次数
- **连接保活配置**：
  - `keepalive`: 启用保活
  - `keepalive_idle`: 空闲时间（秒）
  - `keepalive_interval`: 保活间隔（秒）
  - `keepalive_count`: 保活探测次数

#### 1.2 自定义 PostgreSQL 连接器 (`admin/app/Database/Connectors/PostgresConnector.php`)

扩展了 Laravel 的 PostgresConnector，添加了：
- Keepalive 参数支持（`keepalives=1`, `keepalives_idle`, `keepalives_interval`, `keepalives_count`）
- 连接超时参数（`connect_timeout`）

#### 1.3 数据库连接检查中间件 (`admin/app/Http/Middleware/DatabaseConnectionCheck.php`)

实现了：
- **请求前连接检查**：每次请求前检查数据库连接状态
- **智能跳过**：跳过静态资源和健康检查请求，减少开销
- **缓存优化**：30秒内只检查一次，避免频繁检查
- **自动重连**：连接失败时自动重连，最多重试3次
- **错误处理**：API 请求返回 JSON 错误，Web 请求返回 503 错误页面

#### 1.4 AppServiceProvider 增强 (`admin/app/Providers/AppServiceProvider.php`)

添加了：
- **自定义连接器注册**：注册自定义 PostgresConnector
- **查询监听**：监听长时间查询（>5秒）
- **连接健康检查**：定期检查连接状态
- **自动重连逻辑**：连接失败时自动重连

### 2. Job 系统修复

#### 2.1 数据库连接池优化 (`job/config/autoload/databases.php`)

优化了连接池配置：
- **最小连接数**：从 1 增加到 2，确保有备用连接
- **等待超时**：从 3.0 秒增加到 10.0 秒
- **心跳检测**：从 -1（禁用）改为 30 秒，定期检查连接
- **最大空闲时间**：从 60 秒增加到 300 秒（5分钟）
- **重试配置**：
  - `max_retries`: 最大重试次数（默认3次）
  - `retry_interval`: 重试间隔（默认5秒）
- **PDO 选项**：
  - `ATTR_TIMEOUT`: 连接超时
  - `ATTR_ERRMODE`: 错误模式
  - `ATTR_AUTOCOMMIT`: 自动提交
  - `ATTR_EMULATE_PREPARES`: 不使用模拟预处理

## 环境变量配置

可以在 `.env` 文件中配置以下参数（可选）：

```env
# 数据库连接超时（秒）
DB_CONNECT_TIMEOUT=10

# 连接重试配置
DB_RETRY_AFTER=5
DB_MAX_RETRIES=3

# 连接保活配置
DB_KEEPALIVE=true
DB_KEEPALIVE_IDLE=600
DB_KEEPALIVE_INTERVAL=30
DB_KEEPALIVE_COUNT=3

# Job 系统配置
DB_MAX_IDLE_TIME=300
DB_RETRY_INTERVAL=5.0
```

## 工作原理

### 连接保活机制

1. **PostgreSQL Keepalive**：
   - 启用 TCP keepalive（`keepalives=1`）
   - 空闲 600 秒后开始发送 keepalive 探测
   - 每 30 秒发送一次探测
   - 连续 3 次失败后断开连接

2. **应用层保活**：
   - 中间件每 30 秒检查一次连接
   - 每次请求前检查连接状态
   - 连接失败时自动重连

### 自动重连机制

1. **检测连接失败**：
   - 捕获 PDOException
   - 检查错误代码（2002: 连接失败，HY000: 超时）

2. **重连流程**：
   - 断开当前连接（`DB::disconnect()`）
   - 等待重试间隔（默认5秒）
   - 重新连接（`DB::reconnect()`）
   - 测试连接（执行 `SELECT 1`）
   - 最多重试3次

3. **错误处理**：
   - 重连成功：继续处理请求
   - 重连失败：返回错误响应（API: JSON 503，Web: 503 页面）

## 测试验证

### 1. 测试连接保活

```bash
# 查看 PostgreSQL 连接状态
docker exec tgbot-postgres psql -U root -d tgbot -c "SELECT pid, usename, application_name, state, query FROM pg_stat_activity WHERE datname = 'tgbot';"
```

### 2. 测试自动重连

1. 临时停止 PostgreSQL 容器：
   ```bash
   docker stop tgbot-postgres
   ```

2. 访问后台，应该看到连接错误

3. 启动 PostgreSQL 容器：
   ```bash
   docker start tgbot-postgres
   ```

4. 刷新页面，应该自动重连成功

### 3. 查看日志

```bash
# 查看 Laravel 日志
tail -f admin/storage/logs/laravel/laravel-$(date +%Y-%m-%d).log

# 查看连接相关日志
grep -i "connection\|reconnect\|keepalive" admin/storage/logs/laravel/laravel-$(date +%Y-%m-%d).log
```

## 部署说明

1. **更新代码**：将修复后的代码部署到服务器

2. **清除缓存**：
   ```bash
   cd admin
   php artisan config:clear
   php artisan cache:clear
   ```

3. **重启服务**：
   ```bash
   docker-compose restart admin job
   ```

4. **验证配置**：
   - 检查 `.env` 文件中的数据库配置
   - 确认 `DB_HOST` 指向正确的数据库容器名称（`postgres`）

## 监控建议

1. **日志监控**：
   - 监控数据库连接错误日志
   - 监控重连成功/失败日志
   - 监控长时间查询日志

2. **性能监控**：
   - 监控数据库连接数
   - 监控查询执行时间
   - 监控重连频率

3. **告警设置**：
   - 连接失败次数超过阈值时告警
   - 重连失败时告警
   - 查询执行时间过长时告警

## 注意事项

1. **连接池大小**：根据实际并发量调整 `max_connections`
2. **保活参数**：根据网络环境调整 keepalive 参数
3. **重试次数**：避免重试次数过多导致请求堆积
4. **超时设置**：根据实际业务需求调整超时时间

## 相关文件

- `admin/config/database.php` - 数据库配置
- `admin/app/Database/Connectors/PostgresConnector.php` - 自定义连接器
- `admin/app/Http/Middleware/DatabaseConnectionCheck.php` - 连接检查中间件
- `admin/app/Providers/AppServiceProvider.php` - 服务提供者
- `admin/app/Http/Kernel.php` - 中间件注册
- `job/config/autoload/databases.php` - Job 系统数据库配置

## 更新日期

2026-02-15
