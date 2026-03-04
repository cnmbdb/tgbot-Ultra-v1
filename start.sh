#!/bin/bash

# API-Web 启动脚本

echo "=========================================="
echo "  API-Web 服务启动脚本"
echo "=========================================="

# 检查 Docker 是否运行
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker 未运行，请先启动 Docker"
    exit 1
fi

# 检查是否在项目目录
if [ ! -f "docker-compose.yml" ]; then
    echo "❌ 请在 API-web 项目根目录下运行此脚本"
    exit 1
fi

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
echo "4. 测试服务健康状态..."
if curl -s http://localhost:4444/api/health > /dev/null 2>&1; then
    echo "✅ API-Web 服务运行正常"
else
    echo "⚠️  API-Web 服务可能还在启动中，请稍后访问 http://localhost:4444"
fi

echo ""
echo "=========================================="
echo "  ✅ 启动完成！"
echo "=========================================="
echo ""
echo "访问地址："
echo "  - API 服务: http://localhost:4444"
echo "  - 管理页面: 请联系管理员获取安全管理地址"
echo "  - 健康检查: http://localhost:4444/api/health"
echo ""
echo "查看日志: docker-compose logs -f api-web"
echo "停止服务: docker-compose down"
echo ""
