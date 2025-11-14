# Dockerfile for Magento 2 B2B Engine
# Based on PHP 8.3-fpm with all required Magento extensions

FROM php:8.3-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    bash \
    curl \
    freetype-dev \
    git \
    icu-dev \
    jpeg-dev \
    libpng-dev \
    libxml2-dev \
    libxslt-dev \
    libzip-dev \
    oniguruma-dev \
    shadow \
    wget \
    zip \
    unzip

# Install PHP extensions required by Magento
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        bcmath \
        ctype \
        curl \
        dom \
        gd \
        hash \
        iconv \
        intl \
        mbstring \
        mysqli \
        opcache \
        pdo_mysql \
        simplexml \
        soap \
        sockets \
        sodium \
        xsl \
        zip

# Install Redis extension
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Configure PHP for Magento
RUN { \
    echo 'memory_limit = 4G'; \
    echo 'max_execution_time = 1800'; \
    echo 'zlib.output_compression = On'; \
    echo 'upload_max_filesize = 64M'; \
    echo 'post_max_size = 64M'; \
    echo 'max_input_vars = 10000'; \
} > /usr/local/etc/php/conf.d/magento.ini

# Configure OPcache for production
RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.memory_consumption=512'; \
    echo 'opcache.interned_strings_buffer=16'; \
    echo 'opcache.max_accelerated_files=60000'; \
    echo 'opcache.validate_timestamps=0'; \
    echo 'opcache.consistency_checks=0'; \
    echo 'opcache.save_comments=1'; \
} > /usr/local/etc/php/conf.d/opcache.ini

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY --chown=www-data:www-data . /var/www/html/

# Create necessary directories with correct permissions
RUN mkdir -p var generated pub/static pub/media app/etc \
    && chown -R www-data:www-data var generated pub/static pub/media app/etc \
    && chmod -R 775 var generated pub/static pub/media app/etc

# Copy entrypoint script
COPY docker/docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Switch to www-data user
USER www-data

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm"]
