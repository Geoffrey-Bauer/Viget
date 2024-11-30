FROM php:8.3-fpm-alpine

# Installation des extensions supplémentaires
RUN apk add --no-cache icu-dev \
    && docker-php-ext-install pdo pdo_mysql \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl \
    && docker-php-ext-enable intl

ARG UNAME=www-data
ARG UGROUP=www-data
ARG UID=1000
ARG GID=1000

# Modification de l'UID de l'utilisateur www-data existant
RUN apk add --no-cache shadow && \
    usermod -u ${UID} www-data && \
    groupmod -g ${GID} www-data

# Installation de composer dans le container
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Déclaration du répertoire de travail
WORKDIR /var/www/html

# Changement des permissions du répertoire de travail
RUN chown -R www-data:www-data /var/www/html