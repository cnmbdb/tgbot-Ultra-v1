# HTTPS 和 Webhook 修复说明

## 🔧 修复内容

### 1. HTTPS 乱码问题修复

#### 问题描述
在服务器上使用 HTTPS + 域名 + SSL 后，后台页面出现乱码。

#### 修复内容

**1.1 TrustProxies 中间件配置**
- 文件：`admin/app/Http/Middleware/TrustProxies.php`
- 修改：设置 `$proxies = '*'` 来信任所有代理（用于反向代理环境）

**1.2 AppServiceProvider HTTPS 检测**
- 文件：`admin/app/Providers/AppServiceProvider.php`
- 修改：改进了 HTTPS 检测逻辑
  - 优先检查 `IS_HTTPS` 环境变量
  - 检查请求的 `isSecure()` 方法
  - 检查反向代理的 `X-Forwarded-Proto` 头部
  - 强制设置 `HTTPS` 服务器变量

#### 配置要求

在 `admin.env` 中配置：

```bash
# 如果使用 HTTPS，设置此变量
IS_HTTPS=true

# 确保 APP_URL 使用 HTTPS
APP_URL=https://your-domain.com
```

---

### 2. Webhook 警告问题修复

#### 问题描述
在服务器上部署后，点击 Webhook 按钮仍然显示"本地开发环境无法设置 Webhook"的警告。

#### 修复内容

**2.1 改进 Webhook URL 检测逻辑**
- 文件：`admin/app/Http/Controllers/Admin/Telegram/TelegrambotController.php`
- 修改：
  1. **优先使用 `WEBHOOK_BASE_URL` 环境变量**（推荐方式）
  2. 如果没有配置，从请求头中获取：
     - `X-Forwarded-Host`（反向代理环境）
     - `X-Forwarded-Proto`（HTTPS 检测）
     - `X-Forwarded-Port`（端口检测）
  3. 改进本地开发环境检测：
     - 检查端口是否为 8080
     - 检查主机名是否包含 localhost、127.0.0.1、内网 IP
     - 检查 `APP_ENV` 是否为 local/development
  4. 标准端口（80/443）不显示在 URL 中

#### 配置要求

**方式一：使用 WEBHOOK_BASE_URL（推荐）**

在 `admin.env` 中配置：

```bash
# Webhook 基础 URL（绑定域名后填写）
WEBHOOK_BASE_URL=https://your-domain.com
```

**方式二：自动检测（需要正确配置反向代理）**

如果使用 Nginx 反向代理，确保配置了正确的头部：

```nginx
location / {
    proxy_pass http://localhost:8080;
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
    proxy_set_header X-Forwarded-Port $server_port;
    proxy_set_header X-Forwarded-Host $host;
}
```

---

## 📋 部署步骤

### 1. 更新代码

```bash
cd /Users/a2333/IDE/tgbot-Ultra-v1
# 提交修改后的代码
git add .
git commit -m "修复HTTPS乱码和Webhook检测问题"
```

### 2. 重新构建镜像

```bash
# 构建 admin 镜像
cd admin
docker build -t hfdoker2333/tgbot-ultra-v1:admin-latest .

# 推送到 Docker Hub
docker push hfdoker2333/tgbot-ultra-v1:admin-latest
```

### 3. 服务器配置

#### 3.1 更新 admin.env

```bash
# 在服务器上编辑
nano /opt/tgbot-ultra-v1/admin.env
```

添加/修改以下配置：

```bash
# HTTPS 配置
IS_HTTPS=true
APP_URL=https://your-domain.com

# Webhook 配置（推荐）
WEBHOOK_BASE_URL=https://your-domain.com
```

#### 3.2 更新镜像并重启

```bash
cd /opt/tgbot-ultra-v1
docker-compose pull
docker-compose up -d
```

#### 3.3 验证修复

1. **HTTPS 乱码问题**：
   - 访问 `https://your-domain.com/admin/login`
   - 检查页面是否正常显示（无乱码）

2. **Webhook 问题**：
   - 登录后台 → 机器人列表
   - 点击 "Webhook" 按钮
   - 应该不再显示"本地开发环境"警告
   - 应该成功设置 Webhook

---

## 🔍 故障排查

### HTTPS 仍然乱码

1. **检查环境变量**：
   ```bash
   docker exec tgbot-admin env | grep -E "IS_HTTPS|APP_URL"
   ```

2. **检查反向代理配置**：
   - 确保 Nginx 配置了 `X-Forwarded-Proto` 头部
   - 确保 SSL 证书配置正确

3. **清除缓存**：
   ```bash
   docker exec tgbot-admin php artisan config:clear
   docker exec tgbot-admin php artisan cache:clear
   docker-compose restart admin
   ```

### Webhook 仍然显示警告

1. **检查 WEBHOOK_BASE_URL**：
   ```bash
   docker exec tgbot-admin env | grep WEBHOOK_BASE_URL
   ```

2. **检查请求头**：
   - 在浏览器开发者工具中查看 Network 请求
   - 检查 `X-Forwarded-Host`、`X-Forwarded-Proto` 头部

3. **手动设置 WEBHOOK_BASE_URL**：
   ```bash
   # 在 admin.env 中明确配置
   WEBHOOK_BASE_URL=https://your-domain.com
   ```

---

## ✅ 验证清单

- [ ] `IS_HTTPS=true` 已配置在 `admin.env`
- [ ] `APP_URL=https://your-domain.com` 已配置
- [ ] `WEBHOOK_BASE_URL=https://your-domain.com` 已配置（推荐）
- [ ] Nginx 反向代理配置了正确的头部
- [ ] 镜像已更新到最新版本
- [ ] 容器已重启
- [ ] HTTPS 访问无乱码
- [ ] Webhook 按钮可以正常设置
