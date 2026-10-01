# --- Étape 1 : autoloader Composer optimisé (aucune dépendance d'exécution) ---
FROM composer:2 AS autoload
WORKDIR /app
COPY composer.json ./
COPY src ./src
RUN composer dump-autoload --no-dev --optimize --classmap-authoritative

# --- Étape 2 : image d'exécution PHP 8.2 + Apache ---
FROM php:8.2-apache

RUN docker-php-ext-install pdo_mysql \
    && a2enmod headers rewrite \
    && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Seul le dossier public/ est exposé ; toutes les routes passent par public/index.php.
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && printf '<Directory ${APACHE_DOCUMENT_ROOT}>\n    FallbackResource /index.php\n</Directory>\nServerTokens Prod\nServerSignature Off\n' \
       > /etc/apache2/conf-enabled/squadro.conf

WORKDIR /var/www/html
COPY --from=autoload /app/vendor ./vendor
COPY database ./database
COPY public ./public
COPY src ./src
COPY templates ./templates

EXPOSE 80
