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

WORKDIR /var/www/html

COPY . .

RUN git config --global --add safe.directory /var/www/html

RUN composer install --no-interaction --prefer-dist --optimize-autoloader

RUN npm install -g yarn
RUN yarn install
RUN yarn encore production

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
