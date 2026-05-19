#!/bin/bash
set -e

if [ ! -f /var/www/html/vendor/autoload.php ]; then
    cp -r /var/www/vendor-backup/* /var/www/html/vendor/
fi

shutdown() {
    echo "Received shutdown signal, gracefully stopping Apache..."
    apachectl stop
    echo "Apache stopped gracefully."
    exit 0
}

trap shutdown SIGTERM SIGINT

exec apache2-foreground &
wait $!
