# Webhook 问题排查命令

## 🔍 在服务器上执行以下命令排查问题

### 1. 查看 Laravel 应用日志（不是 Nginx 访问日志）

```bash
# 进入容器查看 Laravel 日志
docker exec -it tgbot-admin tail -f /var/www/html/storage/logs/laravel.log

# 或者查看最近的错误日志
docker exec -it tgbot-admin tail -100 /var/www/html/storage/logs/laravel.log

# 查看所有日志文件
docker exec -it tgbot-admin ls -la /var/www/html/storage/logs/
```

### 2. 检查 Webhook 是否被 Telegram 调用

```bash
# 实时监控 Webhook 请求
docker exec -it tgbot-admin tail -f /var/www/html/storage/logs/laravel.log | grep -i "webhook\|telegram\|getdata"

# 或者查看 Nginx 访问日志中的 POST 请求
docker exec -it tgbot-admin grep "POST.*telegram.*getdata" /var/log/nginx/access.log
```

### 3. 测试 Webhook URL 是否可访问

```bash
# 获取机器人 rid（从数据库或后台查看）
# 假设 rid=1，替换为实际的 rid

# 测试 Webhook URL（替换为实际的域名和 rid）
curl -X POST https://your-domain.com/api/telegram/getdata?rid=1 \
  -H "Content-Type: application/json" \
  -d '{"message":{"message_id":1,"from":{"id":123456,"is_bot":false,"first_name":"Test"},"chat":{"id":123456,"type":"private"},"date":1234567890,"text":"test"}}'

# 或者从容器内部测试
docker exec -it tgbot-admin curl -X POST http://localhost/api/telegram/getdata?rid=1 \
  -H "Content-Type: application/json" \
  -d '{"message":{"message_id":1,"from":{"id":123456,"is_bot":false,"first_name":"Test"},"chat":{"id":123456,"type":"private"},"date":1234567890,"text":"test"}}'
```

### 4. 检查 Webhook 设置状态

```bash
# 使用 Telegram Bot API 检查 Webhook 信息
# 替换 <BOT_TOKEN> 为实际的机器人 token
curl "https://api.telegram.org/bot<BOT_TOKEN>/getWebhookInfo"
```

### 5. 查看 500 错误的详细信息

```bash
# 查看 Laravel 错误日志
docker exec -it tgbot-admin tail -50 /var/www/html/storage/logs/laravel.log | grep -A 20 "ERROR\|Exception"

# 查看 PHP 错误日志
docker exec -it tgbot-admin tail -50 /var/log/php8.0-fpm.log
```

### 6. 检查环境变量配置

```bash
# 检查环境变量是否正确加载
docker exec -it tgbot-admin env | grep -E "APP_ENV|APP_URL|WEBHOOK_BASE_URL|API_WEB_URL"

# 检查 .env 文件
docker exec -it tgbot-admin cat /var/www/html/.env | grep -E "APP_ENV|APP_URL|WEBHOOK_BASE_URL"
```

### 7. 检查路由是否正确

```bash
# 列出所有路由，确认 getdata 路由存在
docker exec -it tgbot-admin php artisan route:list | grep getdata
```

### 8. 测试数据库连接

```bash
# 测试数据库连接
docker exec -it tgbot-admin php artisan tinker
# 然后在 tinker 中执行：
# DB::connection()->getPdo();
# TelegramBot::first();
```

### 9. 清除缓存并重启

```bash
# 清除所有缓存
docker exec -it tgbot-admin php artisan config:clear
docker exec -it tgbot-admin php artisan cache:clear
docker exec -it tgbot-admin php artisan route:clear
docker exec -it tgbot-admin php artisan view:clear

# 重启容器
docker-compose restart admin
```

### 10. 查看完整的容器日志（包括错误）

```bash
# 查看完整的容器日志
docker logs tgbot-admin --tail 100

# 实时查看日志
docker logs -f tgbot-admin
```

## 🎯 重点检查项

1. **Laravel 日志**：`/var/www/html/storage/logs/laravel.log`
2. **Webhook URL**：确认格式为 `https://your-domain.com/api/telegram/getdata?rid=xxx`
3. **500 错误**：查看 Laravel 日志中的详细错误信息
4. **环境变量**：确认 `WEBHOOK_BASE_URL` 已配置
5. **路由**：确认路由 `/api/telegram/getdata` 存在且可访问

## 📝 常见问题

### 问题1：没有看到 Webhook 请求日志

**可能原因**：
- Webhook 未正确设置
- Telegram 无法访问 Webhook URL
- 防火墙阻止了请求

**解决方法**：
```bash
# 检查 Webhook 状态
curl "https://api.telegram.org/bot<BOT_TOKEN>/getWebhookInfo"

# 检查防火墙
ufw status
iptables -L -n | grep 443
```

### 问题2：看到 500 错误

**可能原因**：
- 代码错误
- 数据库连接问题
- 环境变量未正确加载

**解决方法**：
```bash
# 查看详细错误
docker exec -it tgbot-admin tail -100 /var/www/html/storage/logs/laravel.log
```

### 问题3：Webhook URL 返回 404

**可能原因**：
- 路由未正确配置
- Nginx 配置问题

**解决方法**：
```bash
# 检查路由
docker exec -it tgbot-admin php artisan route:list | grep getdata

# 检查 Nginx 配置
docker exec -it tgbot-admin cat /etc/nginx/sites-available/default
```
