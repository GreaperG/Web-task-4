FROM php:8.4-fpm

# 1. Устанавливаем системные зависимости
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libzip-dev \
    libpq-dev \
    libcurl4-openssl-dev \
    libssl-dev \
    libonig-dev \
    libxml2-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    curl \
    gnupg \
    wget \
    && rm -rf /var/lib/apt/lists/*

# 2. Устанавливаем расширения PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    intl \
    pdo \
    pdo_mysql \
    mysqli \
    zip \
    bcmath \
    mbstring \
    xml \
    gd \
    opcache

# 3. Включаем расширения
RUN docker-php-ext-enable pdo_mysql

# 4. Устанавливаем Node.js (опционально, только если нужен Encore)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g yarn

# 5. Копируем composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# 6. Копируем проект
COPY . .

# 7. Даем права (важно для Render)
RUN chmod -R 755 /var/www/html && \
    chown -R www-data:www-data /var/www/html

# 8. Устанавливаем Composer зависимости (БЕЗ DEV зависимостей!)
RUN composer install --no-interaction --no-dev --prefer-dist --optimize-autoloader --no-scripts

# 9. Проверяем расширения PHP
RUN php -m | grep -i mysql && \
    php -m | grep -i pdo

# 10. Создаем .env файл если его нет
RUN if [ ! -f .env ]; then \
    if [ -f .env.prod ]; then \
        cp .env.prod .env; \
    else \
        echo "APP_ENV=prod" > .env; \
        echo "APP_SECRET=$(openssl rand -base64 32)" >> .env; \
    fi; \
fi

# 11. Очищаем кэш Symfony
RUN php bin/console cache:clear --env=prod --no-debug

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
