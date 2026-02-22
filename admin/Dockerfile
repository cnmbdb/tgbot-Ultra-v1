# 生产环境镜像，兼容服务器部署 (linux/amd64)
FROM --platform=linux/amd64 php:8.0-fpm

# 设置工作目录
WORKDIR /var/www/html

# 安装系统依赖
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    nginx \
    libpq-dev \
    && docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 安装 Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 复制项目文件
COPY . /var/www/html

# 确保 .env 文件存在（如果构建时没有，使用默认配置）
# 注意：.env 文件通常在运行时通过挂载或环境变量提供
RUN if [ ! -f /var/www/html/.env ]; then \
        echo "⚠️  Warning: .env file not found. Please ensure it's mounted or provided at runtime."; \
    fi

# 设置权限
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache \
    && chmod -R 755 /var/www/html/public

# 安装 PHP 依赖（忽略平台要求，因为某些包对 PHP 版本要求过严）
RUN composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs

# 配置 Nginx
RUN mkdir -p /etc/nginx/sites-enabled && \
    echo 'server { \
    listen 80; \
    server_name _; \
    root /var/www/html/public; \
    index index.php index.html; \
    \
    location / { \
        try_files $uri $uri/ /index.php?$query_string; \
    } \
    \
    location ~ \.php$ { \
        fastcgi_pass 127.0.0.1:9000; \
        fastcgi_index index.php; \
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; \
        include fastcgi_params; \
    } \
    \
    location ~ /\.ht { \
        deny all; \
    } \
}' > /etc/nginx/sites-available/default && \
    ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default && \
    rm -f /etc/nginx/sites-enabled/default.bak

# 配置 PHP open_basedir
RUN echo 'open_basedir = /var/www/html/:/tmp/' >> /usr/local/etc/php/php.ini

# 启动脚本 - 在运行时修复权限（处理挂载卷的权限问题）
RUN echo '#!/bin/bash\n\
set -e\n\
# 以 root 用户修复权限（处理挂载卷的权限问题）\n\
if [ "$(id -u)" = "0" ]; then\n\
    # 修复权限，但跳过只读挂载的文件（如 .env）\n\
    find /var/www/html -type f -writable -exec chown www-data:www-data {} + 2>/dev/null || true\n\
    find /var/www/html -type d -writable -exec chown www-data:www-data {} + 2>/dev/null || true\n\
    chmod -R 755 /var/www/html/public 2>/dev/null || true\n\
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true\n\
    # 动态获取 PostgreSQL 容器 IP 并添加到 hosts\n\
    POSTGRES_IP=$(getent hosts postgres | awk '\''{ print $1 }'\'' || echo "")\n\
    if [ -z "$POSTGRES_IP" ]; then\n\
        POSTGRES_IP=$(docker inspect -f '\''{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}'\'' tgbot-postgres 2>/dev/null || echo "")\n\
    fi\n\
    if [ -n "$POSTGRES_IP" ] && ! grep -q "^$POSTGRES_IP.*postgres" /etc/hosts; then\n\
        echo "$POSTGRES_IP postgres" >> /etc/hosts\n\
    fi\n\
fi\n\
# 启动 PHP-FPM\n\
php-fpm -D\n\
# 启动 Nginx\n\
nginx -g "daemon off;"' > /start.sh && chmod +x /start.sh

EXPOSE 80

CMD ["/start.sh"]
