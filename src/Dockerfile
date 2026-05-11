FROM node:22-bookworm-slim AS node

# 開発用イメージ。Laravel の CLI サーバーと Vite を同じ app コンテナ内で動かす。
FROM php:8.4-cli-bookworm

RUN apt-get update \
    && apt-get install -y \
        git \
        unzip \
        libzip-dev \
        libicu-dev \
        libonig-dev \
        libxml2-dev \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        zip \
        intl \
        xml \
    && pecl install redis-6.3.0 \
    && docker-php-ext-enable redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 開発ではホットリロードに Vite が必要なため、app コンテナにも Node.js/npm を入れる。
COPY --from=node /usr/local/bin/node /usr/local/bin/node
COPY --from=node /usr/local/lib/node_modules /usr/local/lib/node_modules

RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
    && ln -s /usr/local/lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx

WORKDIR /var/www/html
