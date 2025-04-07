FROM php:8.3-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libicu-dev \
    libpq-dev \
    supervisor \
    librabbitmq-dev \
    libssh-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    postgresql-client \
    && pecl install swoole \
    && docker-php-ext-enable swoole \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure intl \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install \
        pdo_pgsql \
        pgsql \
        xml \
        gd \
        zip \
        intl \
        opcache \
        pcntl \
        bcmath \
    && rm -rf /var/lib/apt/lists/*

# Install redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Configure PHP
# RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Set recommended PHP.ini settings
RUN { \
        echo 'opcache.memory_consumption=128'; \
        echo 'opcache.interned_strings_buffer=8'; \
        echo 'opcache.max_accelerated_files=4000'; \
        echo 'opcache.revalidate_freq=2'; \
        echo 'opcache.fast_shutdown=1'; \
        echo 'opcache.enable_cli=1'; \
    } > /usr/local/etc/php/conf.d/opcache-recommended.ini

# Set PHP-FPM configuration
RUN { \
        echo '[global]'; \
        echo 'daemonize = no'; \
        echo '[www]'; \
        echo 'listen = 9000'; \
    } > /usr/local/etc/php-fpm.d/zz-docker.conf

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
ARG user
ARG bin

# Create system user to run Composer and Artisan Commands
RUN useradd -G www-data,root -u 1000 -d /home/dev dev
RUN mkdir -p /home/dev/.composer && \
    chown -R dev:dev /home/dev

# Set working directory
WORKDIR /var/www

# Copy existing application directory
COPY --chown=dev:dev . .

# Switch to non-root user
USER dev

# Install dependencies
#RUN composer install --no-interaction --no-progress --prefer-dist

# Switch back to root for operations that need it
USER root

# Set recommended directory permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
#RUN php artisan reverb:start && php artisan queue:listen
