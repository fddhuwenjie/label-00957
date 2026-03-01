#!/bin/bash
set -e

# 卷挂载时 vendor 可能为空，从备份恢复
if [ ! -f /var/www/html/vendor/autoload.php ]; then
    cp -r /var/www/vendor-backup/* /var/www/html/vendor/
fi

exec apache2-foreground
