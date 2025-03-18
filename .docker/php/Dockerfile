# Utiliser l'image PHP 7.4 avec Apache
FROM php:8.2-apache

# Installer les dépendances et extensions nécessaires
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip pdo pdo_mysql mysqli

# Activer les modules Apache nécessaires
RUN a2enmod rewrite

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Installer PHPUnit
RUN curl -sSL https://phar.phpunit.de/phpunit-9.phar -o /usr/local/bin/phpunit \
    && chmod +x /usr/local/bin/phpunit

# Installer et configurer Xdebug
RUN pecl channel-update pecl.php.net \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug

# Configurer Xdebug pour le développement
RUN echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/xdebug.ini

# Exposer le port 80
EXPOSE 80

# Définir le répertoire de travail
WORKDIR /var/www/html
