#!/bin/bash
# 快速开发重载脚本 - 清除缓存，无需重新构建镜像

echo "🔄 快速重载开发环境..."

# 清除 Laravel 缓存
echo "📦 清除 Laravel 缓存..."
docker exec tgbot-admin php artisan cache:clear 2>/dev/null || echo "⚠️  缓存清除失败（可能未启动）"
docker exec tgbot-admin php artisan config:clear 2>/dev/null || echo "⚠️  配置缓存清除失败"
docker exec tgbot-admin php artisan route:clear 2>/dev/null || echo "⚠️  路由缓存清除失败"
docker exec tgbot-admin php artisan view:clear 2>/dev/null || echo "⚠️  视图缓存清除失败"

# 重启 Hyperf 服务（让代码修改生效）
echo "🔄 重启 Hyperf 服务..."
docker exec tgbot-job php /opt/www/bin/hyperf.php di:init-proxy 2>/dev/null || echo "⚠️  代理初始化失败"
docker restart tgbot-job 2>/dev/null || echo "⚠️  Job 服务重启失败"

echo "✅ 重载完成！代码修改已生效"
echo ""
echo "💡 提示："
echo "   - Laravel 代码修改后会自动生效（PHP-FPM 支持）"
echo "   - Hyperf 代码修改后需要重启 job 服务（已自动重启）"
echo "   - 如果还有问题，可以运行: docker-compose restart admin job"
