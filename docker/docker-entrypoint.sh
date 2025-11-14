#!/bin/bash
set -e

# Docker entrypoint script for Magento 2 B2B Engine
echo "Starting B2B Engine container..."

# Wait for MySQL to be ready
echo "Waiting for MySQL to be ready..."
while ! nc -z mysql 3306; do
    sleep 1
done
echo "MySQL is ready!"

# Wait for Elasticsearch to be ready
echo "Waiting for Elasticsearch to be ready..."
while ! nc -z elasticsearch 9200; do
    sleep 1
done
echo "Elasticsearch is ready!"

# Check if Magento is installed
if [ ! -f "/var/www/html/app/etc/env.php" ]; then
    echo "Magento is not installed yet. Please run the installation manually."
    echo "You can run: docker-compose exec php-fpm bash"
    echo "Then run: php bin/magento setup:install [options]"
else
    echo "Magento is already installed."
    
    # Run setup:upgrade if needed (check if database schema needs update)
    echo "Checking for pending database updates..."
    php bin/magento setup:db:status || {
        echo "Running setup:upgrade..."
        php bin/magento setup:upgrade --keep-generated
    }
    
    # Compile DI if needed
    if [ ! -d "generated/metadata" ]; then
        echo "Running setup:di:compile..."
        php bin/magento setup:di:compile
    fi
    
    # Clear cache
    echo "Clearing cache..."
    php bin/magento cache:flush
fi

# Set proper permissions (in case they were lost)
echo "Setting file permissions..."
chmod -R 775 var generated pub/static pub/media app/etc 2>/dev/null || true

echo "Container is ready!"

# Execute the main container command
exec "$@"
