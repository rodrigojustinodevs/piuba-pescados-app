FROM php:8.1-fpm

ENV ACCEPT_EULA=Y

# Set working directory
WORKDIR /var/www

# Fix debconf warnings upon build
ARG DEBIAN_FRONTEND=noninteractive

# Install selected extensions and other stuff
RUN apt-get update \
    && apt-get -y --no-install-recommends install \
    build-essential \
    apt-utils \
    nginx \
    supervisor \
    libxml2-dev \
    gnupg \
    apt-transport-https \
    zlib1g-dev \
    libicu-dev \
    g++ \
    libpng-dev \
    libjpeg-dev \
    libzip-dev \
    zip \
    unzip \
    libonig-dev \
    libpq-dev \
    curl \
    git \
    libsodium-dev \
    libcurl4-openssl-dev \
    libssl-dev \
    libfreetype6-dev \
    && apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

# Install the gd extension and other required extensions
RUN apt-get update \
    && apt-get install -y libfreetype6-dev libjpeg62-turbo-dev libpng-dev \
    && docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install intl sodium pdo_pgsql pdo zip exif pcntl bcmath  # Added pdo_pgsql for PostgreSQL

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Add user for Laravel application
RUN groupadd -g 1001 www
RUN useradd -u 1001 -ms /bin/bash -g www www

# Copy the Laravel project to the container
COPY --chown=www-data:www-data . /var/www

# Create the storage folder and set permissions
RUN mkdir -p /var/www/storage/temp/ \
    && chmod -R ug+w /var/www/storage \
    && chown www:www-data -R /var/www/ \
    && chmod 774 -R /var/www/

# Copy nginx/php/supervisor configs
# RUN cp docker/supervisor.conf /etc/supervisord.conf \
#    && cp docker/php.ini /usr/local/etc/php/conf.d/app.ini \
#    && cp docker/nginx.conf /etc/nginx/sites-enabled/default

# PHP Error Log Files
# RUN mkdir /var/log/php
# RUN touch /var/log/php/errors.log && chmod 777 /var/log/php/errors.log

# Deployment steps
RUN composer update && composer install --optimize-autoloader --no-dev
# RUN chmod +x /var/www/docker/run.sh

# Expose port 80
EXPOSE 80

CMD ["php-fpm"]
