# TGBot-Ultra-v1 本地开发环境

## 📋 说明

本目录用于**本地开发**，使用 `docker-compose build` 本地构建镜像。

- **镜像来源**: 本地构建（`docker-compose build`）
- **代码挂载**: 是，修改代码后立即生效
- **适用场景**: 开发、调试、测试

## 🚀 快速启动

```bash
# 1. 构建并启动
docker-compose up -d --build

# 2. 或分步执行
docker-compose build
docker-compose up -d
```

## 📁 与生产环境的区别

| 项目 | 本地开发 (本目录) | 生产部署 (部署到服务器的文件) |
|------|-------------------|-------------------------------|
| 镜像 | 本地 build 构建 | 拉取远程镜像 |
| 代码 | 挂载本地目录 | 代码在镜像内 |
| 配置 | admin/.env, 环境变量 | admin.env, job.env |
| 用途 | 开发调试 | 服务器部署 |

## 🔗 生产环境部署

生产环境请使用：`/Users/a2333/IDE/部署到服务器的文件/tgbot-Ultra-v1`

```bash
cd /Users/a2333/IDE/部署到服务器的文件/tgbot-Ultra-v1
./deploy.sh
```

## 📝 访问地址

- Admin 后台: http://localhost:8080/admin/login
- Job 服务: http://localhost:9503
