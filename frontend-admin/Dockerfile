# 多阶段构建 - 支持 ARM 和 X86 架构
FROM --platform=$BUILDPLATFORM composer:2 AS composer

WORKDIR /app
COPY composer.json composer.lock* ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

COPY . .
RUN composer dump-autoload --optimize --no-scripts

# 运行时镜像
FROM php:8.2-apache

# 安装 PHP 扩展
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql zip opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# 配置 Apache
RUN a2enmod rewrite headers
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf
RUN echo '<Directory /var/www/html/public>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' >> /etc/apache2/apache2.conf

# 配置 PHP
RUN echo "opcache.enable=1\n\
opcache.memory_consumption=128\n\
opcache.interned_strings_buffer=8\n\
opcache.max_accelerated_files=4000\n\
opcache.validate_timestamps=0\n\
opcache.revalidate_freq=60" > /usr/local/etc/php/conf.d/opcache.ini

# 复制应用代码
WORKDIR /var/www/html
COPY --from=composer /app .

# 备份 vendor 到临时目录
RUN cp -r /var/www/html/vendor /var/www/vendor-backup

# 设置权限
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && mkdir -p runtime public/uploads \
    && chmod -R 777 runtime public/uploads

# 创建启动脚本
RUN echo '#!/bin/bash\n\
if [ ! -f /var/www/html/vendor/autoload.php ]; then\n\
    cp -r /var/www/vendor-backup/* /var/www/html/vendor/\n\
fi\n\
apache2-foreground' > /usr/local/bin/start.sh \
    && chmod +x /usr/local/bin/start.sh

EXPOSE 80

CMD ["/usr/local/bin/start.sh"]
