FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libzip-dev \
    libpq-dev \
    curl \
    && docker-php-ext-install intl pdo pdo_mysql zip

RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs
    
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN npm install -g yarn

WORKDIR /var/www/html

COPY . .


RUN chmod -R 755 /var/www/html && \
    chown -R www-data:www-data /var/www/html


RUN composer install --no-interaction --no-dev --prefer-dist --optimize-autoloader --no-scripts


COPY package.json yarn.lock* ./
RUN if [ -f yarn.lock ]; then yarn install --frozen-lockfile; else yarn install; fi


COPY webpack.config.js ./


RUN yarn encore production --mode=production


RUN composer run-script post-install-cmd


CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]