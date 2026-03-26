FROM php:8.3-apache

# Install system dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libwebp-dev \
    libzip-dev \
    unzip \
    git \
    mariadb-client \
  && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
  && docker-php-ext-install -j$(nproc) \
    gd \
    mysqli \
    pdo_mysql \
    zip \
    opcache \
    exif \
  && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Apache config: document root is web/
ENV APACHE_DOCUMENT_ROOT=/var/www/html/web
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf \
  && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
  && a2enmod rewrite

# PHP config
RUN { \
    echo 'upload_max_filesize=64M'; \
    echo 'post_max_size=64M'; \
    echo 'memory_limit=256M'; \
    echo 'max_execution_time=300'; \
  } > /usr/local/etc/php/conf.d/custom.ini

WORKDIR /var/www/html

# Install dependencies first (layer caching)
COPY composer.json ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Copy application files
COPY config/ config/
COPY web/ web/

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
  && chmod -R 755 /var/www/html

EXPOSE 80
