#!/bin/bash

# TGBot-Ultra-v1 启动脚本

echo "=========================================="
echo "  TGBot-Ultra-v1 启动脚本"
echo "=========================================="

# 检查 Docker 是否运行
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker 未运行，请先启动 Docker"
    exit 1
fi

# 检查是否在项目目录
if [ ! -f "docker-compose.yml" ]; then
    echo "❌ 请在 tgbot-Ultra-v1 项目根目录下运行此脚本"
    exit 1
fi

# 检查 API-Web 服务
echo ""
echo "检查 API-Web 服务..."
if curl -s http://localhost:4444/api/health > /dev/null 2>&1; then
    echo "✅ API-Web 服务已运行"
    API_WEB_URL="http://host.docker.internal:4444"
elif curl -s http://host.docker.internal:4444/api/health > /dev/null 2>&1; then
    echo "✅ API-Web 服务已运行（通过 host.docker.internal）"
    API_WEB_URL="http://host.docker.internal:4444"
else
    echo "⚠️  未检测到 API-Web 服务"
    echo "   请确保 API-Web 服务已启动，或手动设置 API_WEB_URL 环境变量"
    read -p "请输入 API-Web 服务地址（默认: http://host.docker.internal:4444）: " input_url
    API_WEB_URL=${input_url:-http://host.docker.internal:4444}
fi

export API_WEB_URL

echo ""
echo "使用 API-Web 地址: $API_WEB_URL"
echo ""

echo "1. 构建并启动服务..."
docker-compose up -d --build

echo ""
echo "2. 等待服务启动（30秒）..."
sleep 30

echo ""
echo "3. 检查服务状态..."
docker-compose ps

echo ""
echo "4. 测试服务..."
if curl -s http://localhost:8080/admin/login > /dev/null 2>&1; then
    echo "✅ Admin 服务运行正常"
else
    echo "⚠️  Admin 服务可能还在启动中，请稍后访问 http://localhost:8080/admin/login"
fi

echo ""
echo "=========================================="
echo "  ✅ 启动完成！"
echo "=========================================="
echo ""
echo "访问地址："
echo "  - 后台管理: http://localhost:8080/admin/login"
echo "    用户名: trxadmin"
echo "    密码: admin"
echo ""
echo "  - Job 服务: http://localhost:9503"
echo ""
echo "查看日志: docker-compose logs -f [admin|job]"
echo "停止服务: docker-compose down"
echo ""
