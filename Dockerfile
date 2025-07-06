FROM php:8.3-fpm-alpine

# Installation des dépendances système
RUN apk add --no-cache \
  bash \
  curl \
  git \
  unzip \
  postgresql-dev \
  icu-dev \
  libzip-dev \
  oniguruma-dev \
  freetype-dev \
  libjpeg-turbo-dev \
  libpng-dev \
  nodejs \
  npm

# Installation des extensions PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install -j$(nproc) \
  pdo \
  pdo_pgsql \
  pgsql \
  intl \
  zip \
  opcache \
  mbstring \
  gd

# Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configuration PHP
RUN echo "memory_limit = 1G" >> /usr/local/etc/php/conf.d/memory-limit.ini \
  && echo "max_execution_time = 120" >> /usr/local/etc/php/conf.d/max-execution-time.ini \
  && echo "upload_max_filesize = 64M" >> /usr/local/etc/php/conf.d/upload-max-filesize.ini \
  && echo "post_max_size = 64M" >> /usr/local/etc/php/conf.d/post-max-size.ini

# Configuration d'OPcache pour le développement
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini \
  && echo "opcache.revalidate_freq=0" >> /usr/local/etc/php/conf.d/opcache.ini \
  && echo "opcache.validate_timestamps=1" >> /usr/local/etc/php/conf.d/opcache.ini

# Définition du répertoire de travail
WORKDIR /var/www/html

# Copie des fichiers de dépendances
COPY composer.json composer.lock ./
COPY package.json package-lock.json ./

# Installation des dépendances PHP
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Installation des dépendances Node.js
RUN npm ci --only=production

# Copie du code source
COPY . .

# Permissions
RUN chown -R www-data:www-data /var/www/html \
  && chmod -R 755 /var/www/html

# Création des répertoires de cache et logs
RUN mkdir -p var/cache var/log \
  && chown -R www-data:www-data var/ \
  && chmod -R 775 var/

USER www-data

EXPOSE 9000

CMD ["php-fpm"]